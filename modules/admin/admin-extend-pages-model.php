<?php
/**
 * Extend pages with a custom breadcrumb title override field.
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
