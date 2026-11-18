<?php
/**
 * Local Google Fonts Frontend Execution Engine
 *
 * @package Skyrocket
 */

defined('ABSPATH') || exit;

/**
 * Helper to check if local Google Fonts are enabled.
 */
function swu_is_local_fonts_active()
{
    $options = get_option('swu_options', []);

    if (empty($options['local_fonts_enabled'])) {
        return false;
    }

    $file_path = get_stylesheet_directory() . '/assets/fonts/local-fonts.css';
    return file_exists($file_path);
}

/**
 * Swap remote Google Fonts for local @font-face stylesheet on the frontend.
 */
add_action('wp_enqueue_scripts', function () {
    if (!swu_is_local_fonts_active()) {
        return;
    }

    // Target only handles belonging to Blocksy fonts to avoid heavy looping.
    global $wp_styles;
    if (!empty($wp_styles->registered)) {
        foreach ($wp_styles->registered as $handle => $data) {
            if (0 === strpos($handle, 'blocksy-fonts-')) {
                wp_dequeue_style($handle);
                wp_deregister_style($handle);
            }
        }
    }

    // Enqueue local font definitions from the child theme with cache-busting.
    $rel_path = '/assets/fonts/local-fonts.css';
    $file_path = get_stylesheet_directory() . $rel_path;

    if (file_exists($file_path)) {
        wp_enqueue_style(
            'skyrocket-local-fonts',
            get_stylesheet_directory_uri() . $rel_path,
            array(),
            (string) filemtime($file_path)
        );
    }
}, 99); // Priority 99 ensures it runs after Blocksy registers its styles

/**
 * Strip resource hints pointing to Google's font servers when local mode is active.
 */
add_filter('wp_resource_hints', function ($urls, $relation_type) {
    if (!swu_is_local_fonts_active()) {
        return $urls;
    }

    if (in_array($relation_type, array('preconnect', 'dns-prefetch'), true)) {
        $urls = array_filter($urls, function ($url) {
            $href = is_array($url) ? ($url['href'] ?? '') : $url;
            return false === strpos($href, 'fonts.googleapis.com')
                && false === strpos($href, 'fonts.gstatic.com');
        });
    }

    return $urls;
}, 20, 2);

/**
 * Align Gutenberg Block Editor assets to use local fonts as well.
 */
add_action('enqueue_block_editor_assets', function () {
    if (!swu_is_local_fonts_active()) {
        return;
    }

    global $wp_styles;
    if (!empty($wp_styles->registered)) {
        foreach ($wp_styles->registered as $handle => $data) {
            if (0 === strpos($handle, 'blocksy-fonts-')) {
                wp_dequeue_style($handle);
                wp_deregister_style($handle);
            }
        }
    }

    $rel_path = '/assets/fonts/local-fonts.css';
    $file_path = get_stylesheet_directory() . $rel_path;

    if (file_exists($file_path)) {
        wp_enqueue_style(
            'skyrocket-local-fonts-editor',
            get_stylesheet_directory_uri() . $rel_path,
            array(),
            (string) filemtime($file_path)
        );
    }
}, 99);