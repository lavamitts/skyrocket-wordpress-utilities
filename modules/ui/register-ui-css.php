<?php
/**
 * Register UI CSS
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'bbc-ui',
        SWU_URL . 'assets/css/ui/ui.css',
        [],
        '1.0.0'
    );
});