
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

## Versioning

Version numbers follow semantic versioning:

- MAJOR: Breaking changes
- MINOR: New functionality
- PATCH: Bug fixes

## Licence

Private project. Not intended for public distribution.
