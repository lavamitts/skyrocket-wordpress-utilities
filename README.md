
# Skyrocket WordPress Utilities

A collection of reusable WordPress enhancements, customisations
and utility functions developed by Skyrocket Phytopharma.

Designed for internal use across multiple WordPress websites.

## Admin modules

### Customise the WordPress admin toolbar navigation.

**admin-bar.php**

Adds a collection of custom navigation links to the site-title dropdown menu in the WordPress admin toolbar, providing convenient access to frequently used administration and content management screens.

### Hide the admin toolbar user account dropdown on content editing screens.

*admin-disable-user-dropdown-on-edit.php*

Prevents the user account dropdown (containing Profile and Log Out links) from appearing when editing posts, pages or custom post types in the WordPress administration area.

### WooCommerce Global Attribute Descriptions

*admin-extend-attributes-model.php*

Adds an internal description field to WooCommerce global product attributes, allowing administrators to record the purpose or intended use of individual attributes.

Descriptions are stored separately from WooCommerce's native attribute data in a dedicated database table, associated with the corresponding WooCommerce attribute IDs.

### Extended Category Taxonomy Fields and Display Controls

*admin-extend-categories-model.php*

Extends the WordPress category and WooCommerce product category taxonomies with additional term metadata, administrative editing fields and frontend display functionality.

### Custom Page Breadcrumb Title Override

*admin-extend-pages-model.php*

Extends WordPress pages with an optional custom breadcrumb title, allowing a shorter or more suitable title to be displayed in breadcrumb navigation without changing the page's actual title.

The custom title is stored as WordPress post metadata and is applied specifically to the final breadcrumb item using Blocksy's breadcrumb items filter.

### Custom Post Breadcrumb Title Override

*admin-extend-posts-model.php*

Extends standard WordPress posts with an optional custom breadcrumb title, allowing a shorter or more suitable title to be displayed in breadcrumb navigation without changing the post's actual title.

The custom title is stored as WordPress post metadata and is applied specifically to the final breadcrumb item using Blocksy's breadcrumb items filter.

### Prioritise Pages in WordPress editor link search.

*admin-link-priority.php*

Intercepts the REST API post search and moves Pages ahead of other post types without changing relevance ordering within either group.

### Customise the WordPress login screen branding

*admin-logo.php*

Replaces the default WordPress login logo with an image selected from the Media Library and customises the logo link to point to the site's homepage.

Also loads a dedicated stylesheet for additional login screen styling.

### Custom WordPress Admin Menu Order

*admin-menu-order.php*

Controls the order of items in the WordPress administration menu, allowing a preferred menu structure to be defined centrally and optionally customised through the plugin's settings.

The menu order is retrieved from the `swu_options` option. If no valid custom order has been saved, the file uses a predefined default order.

### Featured Image Column for the WordPress Posts List

*admin-posts-show-feature-image.php*

Adds a dedicated Image column to the standard WordPress posts administration list, displaying a thumbnail of each post's featured image.

The column is inserted immediately before the post title, making featured images easy to identify when browsing and managing posts.

### Quoted Title-Only Search in the WordPress Admin

*admin-title-search-on-quotes.php*

Modifies the standard WordPress administration search behaviour so that searches enclosed in matching single or double quotation marks are restricted to post and page titles.

Unquoted searches retain WordPress's normal search behaviour. This provides a way to perform a more precise title search without changing the default search functionality.

### Administrator Dashboard Tasks Widget

*dashboard-administrator.php*

Adds a custom widget to the WordPress administration dashboard, providing convenient shortcuts to commonly used plugin management, theme customisation and Skyrocket Utilities screens.

The widget is registered in the dashboard's sidebar area with a high display priority and is intended for administrator access.

### WordPress Admin Dashboard Styling and Widget Management

*dashboard-common.php*

Customises the WordPress administration dashboard by loading a dedicated dashboard stylesheet, removing selected default and plugin-provided dashboard widgets, and conditionally hiding a specific dashboard widget from users who do not pass the administrator capability check.

### Common Tasks and WooCommerce Dashboard Widgets

*dashboard-editor.php*

Adds role-restricted dashboard widgets to the WordPress administration dashboard, providing shortcuts to frequently used content management and store management screens.

The Common tasks widget provides links for managing pages, posts, media and navigation menus. When WooCommerce is active, an additional Store tasks widget provides shortcuts for managing products, product categories, attributes, reviews and orders.

## Search modules

### Exclude Slim SEO Noindex Content from Front-End Search

*exclude-hidden-pages-from-live-search.php*

Modifies standard WordPress front-end search queries to include both posts and pages while excluding content that has been marked as noindex in Slim SEO.

### Live Search Columns

*live-search-columnar-display.php*

Provides grouped Blocksy live search results for posts, pages and Pavilion Events, including dedicated styling, JavaScript and REST API configuration. Integrates with Blocksy's live search functionality to display search results grouped by content type, with configurable result limits and separate REST API endpoints.

## UI modules

### Add Singular Content Slug to the Body Class

Adds the current post or page slug as an additional CSS class to the HTML body element on individual WordPress content views.

This provides a convenient way to target specific posts, pages and custom post type entries with CSS or JavaScript, without requiring manually assigned classes or additional theme settings.

### Skyrocket Utilities Settings

Provides the central administration settings interface for the Skyrocket Utilities plugin, including settings registration, sanitisation, tab navigation, module loading and administration asset management.

## Administration

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
