<?php
/**
 * Customise the WordPress login logo.
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
