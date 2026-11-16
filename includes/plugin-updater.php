<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

if (
    !isset($github_updater_config) ||
    !is_array($github_updater_config) ||
    empty($github_updater_config['username']) ||
    empty($github_updater_config['repository']) ||
    empty($github_updater_config['plugin_file'])
) {
    return;
}

$updaterClassFile = __DIR__ . '/Updater.php';

if (!class_exists(\Skyrocket\GitHubUpdater\Updater::class)) {
    if (!file_exists($updaterClassFile)) {
        return;
    }
    require_once $updaterClassFile;
}

new \Skyrocket\GitHubUpdater\Updater(
    $github_updater_config['plugin_file'],
    $github_updater_config['username'] . '/' . $github_updater_config['repository'],
    $github_updater_config['access_token'] ?? null
);