<?php
/**
 * Hide the admin bar user dropdown (Profile / Log Out) on edit screens,
 * so it can't pop over the Publish/Update button.
 */
function swu_hide_admin_bar_user_dropdown()
{
    $screen = get_current_screen();

    if (!$screen) {
        return;
    }

    // 'post' base covers add/edit screens for posts, pages and all CPTs.
    // Also covers screens using the block editor.
    $is_editing = ('post' === $screen->base)
        || (method_exists($screen, 'is_block_editor') && $screen->is_block_editor());

    if (!$is_editing) {
        return;
    }
    ?>
    <style>
        /* Keep the "Howdy, name" label visible, but hide the dropdown */
        #wpadminbar #wp-admin-bar-my-account .ab-sub-wrapper {
            display: none !important;
        }
    </style>
    <?php
}
add_action('admin_head', 'swu_hide_admin_bar_user_dropdown');
