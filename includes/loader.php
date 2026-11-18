<?php

defined('ABSPATH') || exit;

add_action('plugins_loaded', function () {

    /**
     * List of active modules.
     */
    $modules = [
        'admin/admin-bar.php',
        'admin/admin-settings.php',
        'admin/admin-logo.php',
        'admin/admin-menu-order.php',
        'admin/admin-title-search-on-quotes.php',
        'admin/admin-disable-user-dropdown-on-edit.php',
        'admin/admin-link-priority.php',
        'admin/admin-extend-categories-model.php',
        'admin/admin-extend-attributes-model.php',

        // Dashboard enhancements
        'admin/dashboard/dashboard-common.php',
        'admin/dashboard/dashboard-administrator.php',
        'admin/dashboard/dashboard-editor.php',

        // Media library enhancements
        'admin/media/editor-featured-image-dimensions-alt.php',
        'admin/media/media-library-dimensions.php',
        'admin/media/media-library-unused-media.php',

        // Pages and posts enhancements
        'admin/pages-posts/admin-extend-pages-model.php',
        'admin/pages-posts/admin-extend-posts-model.php',
        'admin/pages-posts/admin-posts-show-feature-image.php',
        'admin/pages-posts/editor-reading-time.php',

        // Search enhancements
        'search/exclude-hidden-pages-from-live-search.php',
        'search/live-search-columnar-display.php',

        // UI enhancements
        'ui/body-add-slug-class.php',
        'ui/local-fonts-loader.php',
        'ui/frontend-current-date.php',
    ];

    if (class_exists('WooCommerce')) {
        $modules[] = 'woocommerce/bootstrapper.php';
    }
    // $modules = [];

    // print_array($modules);

    /**
     * Load each active module.
     */
    foreach ($modules as $module) {
        $file = SWU_PATH . 'modules/' . $module;

        if (file_exists($file)) {
            require_once $file;
        }
    }

}, 20);