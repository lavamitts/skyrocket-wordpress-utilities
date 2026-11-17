<?php
/**
 * Custom Page Breadcrumb Title Override
 *
 * Extends WordPress pages with an optional custom breadcrumb title,
 * allowing a shorter or more suitable title to be displayed in breadcrumb
 * navigation without changing the page's actual title.
 *
 * The custom title is stored as WordPress post metadata and is applied
 * specifically to the final breadcrumb item using Blocksy's breadcrumb
 * items filter.
 *
 * Features:
 *
 * - Page editor integration:
 *   Adds a Breadcrumb override meta box to the WordPress page editing
 *   screen, allowing an alternative breadcrumb title to be specified.
 *
 * - Custom title:
 *   Provides a text field for entering a shorter breadcrumb label when
 *   the page's main title is too long or unsuitable for navigation.
 *
 * - Metadata storage:
 *   Stores the custom title against the page using the
 *   `_page_breadcrumb_title` post meta key.
 *   Empty values remove the existing metadata rather than storing
 *   an empty string.
 *
 * - Secure saving:
 *   Verifies the submitted nonce, excludes autosaves and revisions,
 *   checks the user's page editing capability and sanitises submitted
 *   text before saving.
 *
 * - Breadcrumb integration:
 *   Uses Blocksy's `blocksy:breadcrumbs:items-array` filter to replace
 *   the name of the final breadcrumb item with the custom title.
 *
 * Behaviour:
 *
 * - Only applies to individual WordPress page views.
 * - Only modifies the final breadcrumb item.
 * - Leaves the original page title and all other breadcrumb items
 *   unchanged.
 * - Falls back to the standard breadcrumb title when no custom
 *   override has been configured.
 *
 * Metadata:
 *
 * - `_page_breadcrumb_title`:
 *   Optional custom breadcrumb title associated with a WordPress page.
 *
 * Hooks:
 *
 * - `add_meta_boxes`:
 *   Registers the breadcrumb override meta box.
 *
 * - `save_post_page`:
 *   Validates and saves the submitted breadcrumb title.
 *
 * - `blocksy:breadcrumbs:items-array`:
 *   Applies the custom title to the final breadcrumb item.
 *
 * Dependencies:
 *
 * - WordPress core post metadata and meta box APIs.
 * - Blocksy theme breadcrumb filter.
 *
 * @package Skyrocket
 */

/**
 * Add meta box to the page editing screen.
 */
function swu_add_page_breadcrumb_title_meta_box()
{
    add_meta_box(
        'swu_page_breadcrumb_title_meta_box',
        'Breadcrumb override',
        'swu_render_page_breadcrumb_title_meta_box',
        'page',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'swu_add_page_breadcrumb_title_meta_box');

/**
 * Render the meta box content.
 */
function swu_render_page_breadcrumb_title_meta_box($post)
{
    wp_nonce_field('swu_save_page_breadcrumb_title', 'swu_page_breadcrumb_title_nonce');

    $breadcrumb_title = get_post_meta($post->ID, '_page_breadcrumb_title', true);
    ?>
    <p>
            <label for="swu_page_breadcrumb_title">Short title for breadcrumbs</label>
        <input type="text" id="swu_page_breadcrumb_title" name="swu_page_breadcrumb_title"
            value="<?php echo esc_attr($breadcrumb_title); ?>" style="width: 100%;">
    </p>
        <p class="description">
            Optional. Provide a shorter title to use in breadcrumb trails if the main title is too long.
        </p>
        <?php
}

/**
 * Save the meta box data.
 */
function swu_save_page_breadcrumb_title($post_id)
{
    // Verify nonce.
    if (
        !isset($_POST['swu_page_breadcrumb_title_nonce']) ||
        !wp_verify_nonce(
            sanitize_text_field(wp_unslash($_POST['swu_page_breadcrumb_title_nonce'])),
            'swu_save_page_breadcrumb_title'
        )
    ) {
        return;
    }

    // Check autosave.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check revision.
    if (wp_is_post_revision($post_id)) {
        return;
    }

    // Check permissions.
    if (!current_user_can('edit_page', $post_id)) {
        return;
    }

    // Sanitise and save.
    if (isset($_POST['swu_page_breadcrumb_title'])) {
        $title = sanitize_text_field(wp_unslash($_POST['swu_page_breadcrumb_title']));

        if ($title !== '') {
            update_post_meta($post_id, '_page_breadcrumb_title', $title);
        } else {
            delete_post_meta($post_id, '_page_breadcrumb_title');
        }
    }
}
add_action('save_post_page', 'swu_save_page_breadcrumb_title');

/**
 * Override the last breadcrumb item name using Blocksy's built-in filter
 * and the custom breadcrumb title meta field for pages.
 */
function swu_filter_page_breadcrumb_title($items)
{
    if (!is_page() || !is_array($items) || empty($items)) {
        return $items;
    }

    $post_id = get_the_ID();

    if (!$post_id) {
        return $items;
    }

    $breadcrumb_title = get_post_meta($post_id, '_page_breadcrumb_title', true);

    if (!empty($breadcrumb_title)) {
        $last_index = count($items) - 1;

        if (isset($items[$last_index])) {
            $items[$last_index]['name'] = $breadcrumb_title;
        }
    }

    return $items;
}
add_filter('blocksy:breadcrumbs:items-array', 'swu_filter_page_breadcrumb_title');
