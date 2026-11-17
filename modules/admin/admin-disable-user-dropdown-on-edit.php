
<?php
/**
 * Hide the admin toolbar user account dropdown on content editing screens.
 *
 * Prevents the user account dropdown (containing Profile and Log Out links)
 * from appearing when editing posts, pages or custom post types in the
 * WordPress administration area.
 *
 * The "Howdy, [username]" account label remains visible in the admin toolbar,
 * but its dropdown submenu is hidden to prevent it from overlapping the
 * Publish or Update controls.
 *
 * Implementation:
 *
 * - Identifies the current administration screen using `get_current_screen()`.
 * - Targets screens with a `post` base, covering the standard post editing
 *   interface for posts, pages and custom post types.
 * - Also checks for block editor screens using the `is_block_editor()`
 *   method where available.
 * - Outputs inline CSS in the administration head to hide the user account
 *   dropdown submenu without removing the account label itself.
 *
 * Hooks:
 *
 * - `admin_head`:
 *   Outputs the CSS on WordPress administration screens.
 *
 * Dependencies:
 *
 * - WordPress administration screen API (`get_current_screen()`).
 * - WordPress admin toolbar markup (`#wpadminbar`).
 *
 * @package Skyrocket
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
