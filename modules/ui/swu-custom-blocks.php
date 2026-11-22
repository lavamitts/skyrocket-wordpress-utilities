<?php
/**
 * SWU Custom Blocks
 *
 * Automatically registers all Gutenberg blocks found in the
 * root /blocks directory.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register all custom blocks.
 */
function swu_custom_blocks_init(): void
{
    static $registered = false;

    if ($registered) {
        return;
    }

    $registered = true;

    // Resolve package root (two levels above this file).
    $package_dir = dirname(__DIR__, 2);

    // Blocks directory is located at package root.
    $blocks_dir = $package_dir . '/blocks';

    if (!is_dir($blocks_dir)) {
        return;
    }

    $folders = scandir($blocks_dir);

    if ($folders === false) {
        return;
    }

    foreach ($folders as $folder) {

        if ($folder === '.' || $folder === '..') {
            continue;
        }

        $block_path = $blocks_dir . '/' . $folder;

        if (!is_dir($block_path) || !file_exists($block_path . '/block.json')) {
            continue;
        }

        $block_type = register_block_type($block_path);

        // if ($block_type !== false) {
        //     error_log('SWU registered: ' . $block_type->name);
        // } else {
        //     error_log('SWU FAILED: ' . $block_path);
        // }
    }
}

add_action('init', 'swu_custom_blocks_init');