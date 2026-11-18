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
        'admin/admin-posts-show-feature-image.php',
        'admin/admin-disable-user-dropdown-on-edit.php',
        'admin/admin-link-priority.php',
        'admin/admin-extend-categories-model.php',
        'admin/admin-extend-attributes-model.php',
        'admin/admin-extend-pages-model.php',
        'admin/admin-extend-posts-model.php',
        'admin/dashboard-common.php',
        'admin/dashboard-administrator.php',
        'admin/dashboard-editor.php',

        'search/exclude-hidden-pages-from-live-search.php',
        'search/live-search-columnar-display.php',

        'ui/body-add-slug-class.php',
        'ui/local-fonts-loader.php',
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