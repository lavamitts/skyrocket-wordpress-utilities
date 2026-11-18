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

/**
 * Add the usage column to the Media Library.
 *
 * @param array $columns Existing Media Library columns.
 * @return array Modified columns.
 */
function swu_media_library_usage_column(array $columns): array
{

    $new_columns = [];

    foreach ($columns as $key => $label) {

        $new_columns[$key] = $label;

        // Insert the usage column after the attachment title.
        if ($key === 'title') {
            $new_columns['swu_usage'] = __('Usage', 'skyrocket');
        }
    }

    // Append the column if the title column was not available.
    if (!isset($new_columns['swu_usage'])) {
        $new_columns['swu_usage'] = __('Usage', 'skyrocket');
    }

    return $new_columns;
}

add_filter(
    'manage_upload_columns',
    'swu_media_library_usage_column'
);

/**
 * Determine whether an attachment is referenced by standard content.
 *
 * Checks attachment parent relationships, featured images, Gutenberg
 * and classic editor content, and WooCommerce product galleries.
 *
 * Results are cached for the duration of the current request.
 *
 * @param int $attachment_id Attachment post ID.
 * @return bool Whether a recognised reference exists.
 */

/**
 * Determine whether a media attachment is used by standard WordPress
 * content or a WooCommerce product category image.
 *
 * Checks:
 * - Attachment parent relationships.
 * - Featured images.
 * - WooCommerce product galleries.
 * - Standard content references, including Gutenberg image blocks.
 * - WooCommerce product category images.
 *
 * Results are cached for the duration of the request.
 *
 * @param int $attachment_id The attachment ID to check.
 *
 * @return bool Whether the attachment appears to be in use.
 */
function swu_media_library_attachment_is_used(int $attachment_id): bool
{

    static $cache = array();

    if (isset($cache[$attachment_id])) {
        return $cache[$attachment_id];
    }

    global $wpdb;

    // Check whether the attachment has a parent post.
    $attachment = get_post($attachment_id);

    if ($attachment && $attachment->post_parent > 0) {
        $parent_status = get_post_status($attachment->post_parent);

        if ($parent_status && $parent_status !== 'trash') {
            return $cache[$attachment_id] = true;
        }
    }

    // Check featured images.
    $featured_image = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT meta_id
            FROM {$wpdb->postmeta}
            WHERE meta_key = '_thumbnail_id'
            AND meta_value = %d
            LIMIT 1",
            $attachment_id
        )
    );

    if ($featured_image) {
        return $cache[$attachment_id] = true;
    }

    // Check WooCommerce product galleries.
    $product_gallery = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT meta_id
            FROM {$wpdb->postmeta}
            WHERE meta_key = '_product_image_gallery'
            AND FIND_IN_SET(%d, meta_value)
            LIMIT 1",
            $attachment_id
        )
    );

    if ($product_gallery) {
        return $cache[$attachment_id] = true;
    }

    // Check WooCommerce product category images.
    if (taxonomy_exists('product_cat')) {
        $category_image = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT meta_id
                FROM {$wpdb->termmeta}
                WHERE meta_key = 'thumbnail_id'
                AND meta_value = %d
                LIMIT 1",
                $attachment_id
            )
        );

        if ($category_image) {
            return $cache[$attachment_id] = true;
        }
    }

    // Check attachment references in standard post content.
    $attachment_url = wp_get_attachment_url($attachment_id);

    $content_patterns = array(
        '%wp-image-' . $attachment_id . '%',
        '%"id":' . $attachment_id . '%',
        '%"id": ' . $attachment_id . '%',
        '%data-id="' . $attachment_id . '"%',
    );

    if ($attachment_url) {
        $content_patterns[] = '%' . $wpdb->esc_like($attachment_url) . '%';
    }

    $content_conditions = array();

    foreach ($content_patterns as $pattern) {
        $content_conditions[] = 'post_content LIKE %s';
    }

    $content_query = "
        SELECT ID
        FROM {$wpdb->posts}
        WHERE post_type != 'attachment'
        AND post_status NOT IN ('trash', 'auto-draft')
        AND (" . implode(' OR ', $content_conditions) . ")
        LIMIT 1
    ";

    $content_used = $wpdb->get_var(
        $wpdb->prepare($content_query, $content_patterns)
    );

    if ($content_used) {
        return $cache[$attachment_id] = true;
    }

    return $cache[$attachment_id] = false;
}


/**
 * Display attachment usage information in the Media Library.
 *
 * @param string $column_name Current column name.
 * @param int    $post_id     Attachment post ID.
 * @return void
 */

/**
 * Display attachment usage information in the Media Library.
 *
 * @param string $column_name Current column name.
 * @param int    $post_id     Attachment post ID.
 * @return void
 */
function swu_media_library_usage_content(
    string $column_name,
    int $post_id
): void {

    if ($column_name !== 'swu_usage') {
        return;
    }

    $is_used = swu_media_library_attachment_is_used($post_id);

    if ($is_used) {

        $label = __('In use', 'skyrocket');
        $class = 'swu-media-usage-in-use';

    } else {

        $label = __('Potentially unused', 'skyrocket');
        $class = 'swu-media-usage-unused';

    }

    printf(
        '<span class="swu-media-usage %s">%s</span>',
        esc_attr($class),
        esc_html($label)
    );
}

add_action(
    'manage_media_custom_column',
    'swu_media_library_usage_content',
    10,
    2
);

/**
 * Add styling to the Media Library usage badges.
 *
 * @return void
 */
function swu_media_library_usage_styles(): void
{

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
}

add_action(
    'admin_head',
    'swu_media_library_usage_styles'
);

add_action(
    'manage_media_custom_column',
    'swu_media_library_usage_content',
    10,
    2
);
