<?php
/**
 * Identify potentially unused attachments in the WordPress Media Library.
 *
 * Adds a Usage column to the Media Library list view, identifying
 * attachments referenced by standard WordPress content sources.
 *
 * Checks include:
 * - Featured image references.
 * - Gutenberg and classic editor content.
 * - WooCommerce product gallery references.
 * - Attachment parent relationships.
 *
 * Attachments without a recognised reference are labelled as potentially
 * unused. No attachments are automatically deleted.
 *
 * @package Skyrocket
 */

defined('ABSPATH') || exit;

add_filter('manage_upload_columns', function (array $columns): array {
    $new_columns = [];
    foreach ($columns as $key => $label) {
        $new_columns[$key] = $label;
        if ($key === 'title') {
            $new_columns['swu_usage'] = __('Usage', 'skyrocket');
        }
    }
    if (!isset($new_columns['swu_usage'])) {
        $new_columns['swu_usage'] = __('Usage', 'skyrocket');
    }
    return $new_columns;
});

/**
 * Build a map of [attachment_id => bool] for a batch of attachment IDs.
 */
function swu_get_batch_attachment_usage(array $attachment_ids): array
{
    $attachment_ids = array_values(array_unique(array_map('intval', $attachment_ids)));
    if (empty($attachment_ids)) {
        return [];
    }

    $results = array_fill_keys($attachment_ids, false);
    $transient_key = 'swu_media_usage_' . md5(implode(',', $attachment_ids));
    $cached = get_transient($transient_key);
    if (is_array($cached)) {
        return $cached;
    }

    global $wpdb;
    $placeholders = implode(',', array_fill(0, count($attachment_ids), '%d'));

    // 1. Parent post check (primed via _prime_post_caches).
    _prime_post_caches($attachment_ids, false, true);
    $parent_ids = [];
    foreach ($attachment_ids as $id) {
        $att = get_post($id);
        if ($att && (int) $att->post_parent > 0) {
            $parent_ids[$id] = (int) $att->post_parent;
        }
    }
    if (!empty($parent_ids)) {
        _prime_post_caches(array_values($parent_ids), false, false);
        foreach ($parent_ids as $att_id => $parent_id) {
            $status = get_post_status($parent_id);
            if ($status && $status !== 'trash') {
                $results[$att_id] = true;
            }
        }
    }

    // 2. Featured images (_thumbnail_id).
    $thumb_rows = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT DISTINCT meta_value FROM {$wpdb->postmeta}
             WHERE meta_key = '_thumbnail_id' AND meta_value IN ($placeholders)",
            $attachment_ids
        )
    );
    foreach ($thumb_rows as $id) {
        $results[(int) $id] = true;
    }

    // 3. WooCommerce product galleries (_product_image_gallery) - fetched once, parsed in PHP.
    $galleries = $wpdb->get_col(
        "SELECT meta_value FROM {$wpdb->postmeta}
         WHERE meta_key = '_product_image_gallery' AND meta_value != ''"
    );
    if (!empty($galleries)) {
        $target_set = array_flip($attachment_ids);
        foreach ($galleries as $csv) {
            foreach (explode(',', $csv) as $gallery_id) {
                $gid = (int) trim($gallery_id);
                if ($gid > 0 && isset($target_set[$gid])) {
                    $results[$gid] = true;
                }
            }
        }
    }

    // 4. WooCommerce product category thumbnails.
    if (taxonomy_exists('product_cat')) {
        $cat_rows = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT DISTINCT meta_value FROM {$wpdb->termmeta}
                 WHERE meta_key = 'thumbnail_id' AND meta_value IN ($placeholders)",
                $attachment_ids
            )
        );
        foreach ($cat_rows as $id) {
            $results[(int) $id] = true;
        }
    }

    // 5. Post content check for remaining unresolved IDs.
    $remaining = array_keys(array_filter($results, fn($v) => !$v));
    if (!empty($remaining)) {
        $post_types = get_post_types(['public' => true], 'names');
        $post_types = array_diff($post_types, ['attachment']);
        if (!empty($post_types)) {
            $pt_placeholders = implode(',', array_fill(0, count($post_types), '%s'));
            $contents = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT post_content FROM {$wpdb->posts}
                     WHERE post_type IN ($pt_placeholders)
                     AND post_status NOT IN ('trash', 'auto-draft')
                     AND post_content != ''",
                    $post_types
                )
            );

            if (!empty($contents)) {
                $joined_content = implode("\n", $contents);
                foreach ($remaining as $id) {
                    $url = wp_get_attachment_url($id);
                    $needles = [
                        'wp-image-' . $id,
                        '"id":' . $id,
                        '"id": ' . $id,
                        'data-id="' . $id . '"',
                    ];
                    if ($url) {
                        $needles[] = $url;
                    }
                    foreach ($needles as $needle) {
                        if (strpos($joined_content, $needle) !== false) {
                            $results[$id] = true;
                            break;
                        }
                    }
                }
            }
        }
    }

    set_transient($transient_key, $results, 12 * HOUR_IN_SECONDS);
    return $results;
}

add_action('manage_media_custom_column', function (string $column_name, int $post_id): void {
    if ($column_name !== 'swu_usage') {
        return;
    }

    static $page_map = null;
    if ($page_map === null) {
        global $wp_query;
        $ids = [];
        if (!empty($wp_query->posts)) {
            foreach ($wp_query->posts as $p) {
                $ids[] = is_object($p) ? (int) $p->ID : (int) $p;
            }
        }
        $page_map = swu_get_batch_attachment_usage($ids);
    }

    $is_used = !empty($page_map[$post_id]);
    $label = $is_used ? __('In use', 'skyrocket') : __('Potentially unused', 'skyrocket');
    $class = $is_used ? 'swu-media-usage-in-use' : 'swu-media-usage-unused';

    printf(
        '<span class="swu-media-usage %s">%s</span>',
        esc_attr($class),
        esc_html($label)
    );
}, 10, 2);

add_action('admin_head', function (): void {
    $screen = get_current_screen();
    if (!$screen || $screen->base !== 'upload') {
        return;
    }
    ?>
    <style>
        .swu-media-usage {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
            line-height: 1.5;
            white-space: nowrap;
        }

        .swu-media-usage-in-use {
            color: #176b35;
            background: #e2f3e7;
        }

        .swu-media-usage-unused {
            color: #a12626;
            background: #fbe5e5;
        }
    </style>
    <?php
});