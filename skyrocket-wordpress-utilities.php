<?php
/**
 * Plugin Name: Skyrocket WordPress Utilities
 * Description: A collection of reusable WordPress enhancements and utilities.
 * Version: 1.0.2
 * Author: Biddenham Society
 * Text Domain: skyrocket-wordpress-utilities
 */

defined('ABSPATH') || exit;

// Plugin version constant, used for cache-busting CSS and JavaScript.
define('SWU_VERSION', '1.0.1');

// Plugin directory path and URL.
define('SWU_PATH', plugin_dir_path(__FILE__));
define('SWU_URL', plugin_dir_url(__FILE__));

// Token loaded
require_once SWU_PATH . 'includes/token.php';

// Load the module loader.
require_once SWU_PATH . 'includes/loader.php';

// Load asset management.
require_once SWU_PATH . 'includes/assets.php';

/**
 * Initialise the GitHub plugin update checker.
 */
function swu_init_update_checker()
{
    // Require a GitHub access token.
    if (!defined('SWU_GITHUB_TOKEN') || SWU_GITHUB_TOKEN === '') {
        return;
    }

    // Check that the updater library is available.
    $updater_file = SWU_PATH . 'vendor/plugin-update-checker/plugin-update-checker.php';

    if (!file_exists($updater_file)) {
        return;
    }

    require_once $updater_file;

    // Initialise the GitHub update checker.
    $update_checker = \YahnisElsts\PluginUpdateChecker\v6\PucFactory::buildUpdateChecker(
        'https://github.com/lavamitts/skyrocket-wordpress-utilities/',
        __FILE__,
        'skyrocket-wordpress-utilities'
    );

    // Authenticate with the private GitHub repository.
    $update_checker->setAuthentication(SWU_GITHUB_TOKEN);
}
add_action('plugins_loaded', 'swu_init_update_checker');
