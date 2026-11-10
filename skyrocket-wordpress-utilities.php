<?php
/**
 * Plugin Name: Skyrocket WordPress Utilities
 * Description: A collection of reusable WordPress enhancements and utilities.
 * Version: 1.0.0
 * Author: Biddenham Society
 * Text Domain: skyrocket-wordpress-utilities
 */

defined('ABSPATH') || exit;

// Plugin directory path.
define('SWU_PATH', plugin_dir_path(__FILE__));

// Load the module loader.
require_once SWU_PATH . 'includes/loader.php';
