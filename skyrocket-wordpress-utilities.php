<?php
/**
 * Plugin Name: Skyrocket WordPress Utilities
 * Description: A collection of reusable WordPress enhancements and utilities.
 * Version: 1.3.4
 * Author: Skyrocket Digital Development
 * Text Domain: skyrocket-wordpress-utilities
 */

defined('ABSPATH') || exit;

// Plugin version constant, used for cache-busting CSS and JavaScript.
define('SWU_VERSION', '1.3.4');

// Plugin directory path and URL.
define('SWU_PATH', plugin_dir_path(__FILE__));
define('SWU_URL', plugin_dir_url(__FILE__));

// GitHub updater configuration
$github_updater_config = [
    'username' => 'lavamitts',
    'repository' => 'skyrocket-wordpress-utilities',
    'plugin_file' => __FILE__,
];

// Updater code
require_once __DIR__ . '/vendor/lavamitts/skyrocket-plugin-updater/src/plugin-updater.php';


// Remove the temporary configuration variable.
unset($github_updater_config);

// Helper functions
require_once plugin_dir_path(__FILE__) . 'includes/helpers.php';

// Load the module loader.
require_once SWU_PATH . 'includes/loader.php';

// Load CSS assets
require_once SWU_PATH . 'includes/assets.php';
