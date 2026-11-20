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
        'admin/dashboards/dashboard-common.php',
        'admin/dashboards/dashboard-administrator.php',
        'admin/dashboards/dashboard-editor.php',

        // Media library enhancements
        'admin/media/media-bootstrapper.php',

        // Pages and posts enhancements
        'admin/pages-posts/admin-pages-posts-bootstrapper.php',

        // Search enhancements
        'search/exclude-hidden-pages-from-live-search.php',
        'search/live-search-columnar-display.php',

        // UI enhancements
        'ui/body-add-slug-class.php',
        'ui/disable-comments-runtime.php',
        'ui/exclude-categories-from-blog.php',
        'ui/frontend-current-date.php',
        'ui/local-fonts-loader.php',
        'ui/register-ui-css.php',
        'ui/swu-custom-blocks.php',
    ];

    if (class_exists('WooCommerce')) {
        $modules[] = 'woocommerce/bootstrapper.php';
    }

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