<?php
/**
 * Customise the WordPress login screen branding.
 *
 * Replaces the default WordPress login logo with an image selected from the
 * Media Library and customises the logo link to point to the site's homepage.
 * Also loads a dedicated stylesheet for additional login screen styling.
 *
 * Features:
 *
 * - Custom login logo:
 *   Retrieves the attachment ID stored in the `login_logo_id` option within
 *   the `swu_options` settings array and displays the selected Media Library
 *   image in place of the default WordPress login logo.
 *   Falls back to the default logo text if no image is configured or the
 *   selected attachment cannot be displayed.
 *
 * - Custom logo destination:
 *   Changes the login logo link to point to the site's homepage rather than
 *   the default WordPress.org destination.
 *
 * - Login stylesheet:
 *   Enqueues `assets/css/login/login.css` on the WordPress login screen,
 *   using the file modification time as its version number to facilitate
 *   browser cache invalidation when the stylesheet changes.
 *
 * Configuration:
 *
 * - Option: `swu_options`
 * - Setting: `login_logo_id` (WordPress Media Library attachment ID)
 *
 * Hooks:
 *
 * - `login_headertext`       Replaces the login logo text with the image.
 * - `login_headerurl`        Sets the login logo destination URL.
 * - `login_enqueue_scripts`  Enqueues the custom login stylesheet.
 *
 * Dependencies:
 *
 * - WordPress core functions and APIs.
 * - `SWU_PATH` and `SWU_URL` plugin constants.
 * - The `swu_options` plugin settings array.
 *
 * Stylesheet:
 *
 * - `assets/css/login/login.css`
 *
 * @package Skyrocket
 */


defined('ABSPATH') || exit;

/**
 * Replace the standard login logo text with the selected Media Library image.
 */
function swu_custom_login_logo($text)
{
    $options = get_option('swu_options', []);
    $logo_id = absint($options['login_logo_id'] ?? 0);

    if (!$logo_id) {
        return $text;
    }

    $logo_html = wp_get_attachment_image(
        $logo_id,
        'full',
        false,
        [
            'class' => 'swu-login-logo',
            'alt' => get_bloginfo('name'),
        ]
    );

    return $logo_html ?: $text;
}
add_filter('login_headertext', 'swu_custom_login_logo');

/**
 * Set the login logo link to the site's homepage.
 */
function swu_custom_login_logo_url($url)
{
    return home_url('/');
}
add_filter('login_headerurl', 'swu_custom_login_logo_url');

/**
 * Enqueue login stylesheet.
 */
function swu_login_styles()
{
    $css_file_part = 'assets/css/login/login.css';
    $css_file = SWU_PATH . $css_file_part;

    if (!file_exists($css_file)) {
        return;
    }

    wp_enqueue_style(
        'swu-login',
        SWU_URL . $css_file_part,
        [],
        filemtime($css_file)
    );
}
add_action('login_enqueue_scripts', 'swu_login_styles');
