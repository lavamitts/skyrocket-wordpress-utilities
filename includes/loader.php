<?php

defined('ABSPATH') || exit;

/**
 * List of active modules.
 *
 * Add new module paths here as functionality is developed.
 */
$modules = [
    'admin/admin-bar.php',
    // 'admin/admin-menu-order.php',
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
