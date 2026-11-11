<?php

defined('ABSPATH') || exit;

/**
 * List of active modules.
 *
 * Add new module paths here as functionality is developed.
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
    'admin/admin-extend-pages-model.php',
    'admin/admin-extend-posts-model.php',

    'search/exclude-hidden-pages-from-live-search.php',
    'search/live-search-columnar-display.php',
];

/**
 * Load each active module.
 */
foreach ($modules as $module) {
    $file = SWU_PATH . 'modules/' . $module;

    if (file_exists($file)) {
        require_once $file;
    }
}
