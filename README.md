
# Skyrocket WordPress Utilities

A collection of reusable WordPress enhancements, customisations
and utility functions developed by Skyrocket Phytopharma.

Designed for internal use across multiple WordPress websites.

## Features

### Administration

- Custom WordPress admin menu ordering.

## Installation

1. Download the latest release ZIP from GitHub.
2. In WordPress, navigate to Plugins > Add Plugin.
3. Select Upload Plugin.
4. Upload the ZIP and activate the plugin.

## Structure

    skyrocket-wordpress-utilities/
    ├── skyrocket-wordpress-utilities.php
    ├── includes/
    │   └── loader.php
    └── modules/
        └── admin/
            └── admin-menu-order.php

## Development

New functionality should be added as independent modules.

Register each module in `includes/loader.php`.

Use the `swu_` prefix for global functions to avoid
conflicts with WordPress and other plugins.

## CSS build

Run this ...

`npm run watch:css`


## Building a release

The plugin uses [Skyrocket Plugin Updater](https://github.com/lavamitts/skyrocket-plugin-updater) as a Composer dependency.

The updater dependency also provides the distribution build script, which prepares the plugin files for release.

After installing the Composer dependencies, run the following command from the plugin root directory:

```bash
./vendor/lavamitts/skyrocket-plugin-updater/tools/build-distribution.sh
```

This builds the release candidate, preparing a distributable version of the plugin without requiring the development dependency structure to be included in the release.

The resulting distribution package is intended for installation through the standard WordPress plugin installation process.

## Versioning

Version numbers follow semantic versioning:

- MAJOR: Breaking changes
- MINOR: New functionality
- PATCH: Bug fixes

## Licence

Private project. Not intended for public distribution.
