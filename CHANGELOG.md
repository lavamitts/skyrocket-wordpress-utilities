# Changelog

## 1.0.0 - Initial release

- Added custom WordPress administration bar content

## 1.0.1 - Additional admin functionality

- Added WordPress login screen custom logo management
- Added WordPress left hand main menu sequence customisation
- Added the ability to search for pages and posts using quotes to only search in the title field
- Added the feature image to posts in the post lister
- Prioritise pages if the list of entities when searching for URLs in the admin
- Extend the categories model to present a different on page title to the one used in post meta and slugs
- Extend the page and post model to permit an override to the breadcrumb text

## 1.0.2 - Addition of self-update functionality

- Self-update preparation

## 1.0.3 - Correctly added vendor files

- Self-update permitted

## 1.0.4 - Version testing

- Version testing

## 1.0.5 - Release and search management

- Automatically strips non-english language files from the vendor-supplied Plugin Update Checker to remove bloat from distribution
- Adds in columnar search to live search under blocksy
- Excludes pages hidden by Slim SEO from live search

## 1.0.6 - Enhanced distribution script

- Checks the plugin's PHP file to ensure that the versions match in the plugin itself and in the SWU_VERSION constant
- Checks the CHANGELOG.md file for a relevant update matching the plugin's PHP file

## 1.0.7 - Adds a class to the body to match the page or post's slug

- Allows for fine-controlled CSS if needed

## 1.0.8 - Replace 3rd party plugin updater with bespoke plugin

- Replace 3rd party plugin updater with bespoke plugin

## 1.0.9 - Correct repo name in script updater

- Correct repo name in script updater

## 1.1.0 - Update to use the renamed plugin updater

- Now using "lavamitts/skyrocket-plugin-updater" instead of "lavamitts/github-plugin-updater".
- Build tooling adjusted to NOT include the composer and autoload files in the distribution.

## 1.2.0 - Documentation enhancements

- Updated readme file to document all utilities included to date

## 1.2.1 - Addition of local Google fonts option

- Added a Local Fonts governance module allowing Google Fonts to be self-hosted directly from the active child theme (/assets/fonts/local-fonts.css).
- Added a dedicated settings tab under Skyrocket Utilities with a master toggle and live stylesheet validation check.
- When enabled and the local asset is present, the plugin automatically dequeues Blocksy's remote Google Fonts stylesheets, strips external googleapis.com and gstatic.com resource hints across both the frontend and block editor, and enqueues the local @font-face definitions with filemtime() cache-busting—falling back gracefully to remote fonts if local files are absent.

## 1.2.2 - Utilities admin tools fix

- Fix to ensure all tab changes are captured correctly.

## 1.2.3 - Addition of new functionality

### Display featured image dimensions and ALT text in the WordPress block editor.

*editor-featured-image-dimensions-alt.php*

Retrieves the featured image dimensions and ALT text from WordPress attachment metadata and displays them beneath the Featured Image panel.

### Display word count and estimated reading time in the WordPress block editor.

*editor-reading-time.php*

Adds a Reading Time panel to the editor's Document sidebar, displaying the current word count and estimated reading time for the post content.

### Add image dimensions to the WordPress Media Library list view.

*media-library-dimensions.php*

Adds a Dimensions column displaying the width and height of image attachments using their stored WordPress attachment metadata.

### Identify potentially unused attachments in the WordPress Media Library.

*media-library-unused-media.php*

Adds a Usage column to the Media Library list view, identifying attachments referenced by standard WordPress content sources.

### Current date shotrcode

*frontend-current-date.php*

Provides shortcodes for displaying the current year and date.

## 1.2.4 - Split menu order and login logo into two separate tabs

- Remove `admin-tweaks` tab from the Skyrocket utilities admin.
- Replace with two tabs `login-logo` and `menu-order`.
- Fix display of admin menu for logged in users on the 'live site'

## 1.2.5 - Addition of completely disable comments function

- Added a toggle under **Settings > Skyrocket Utilities > Disable comments** to completely disable comments, pingbacks, comment feeds, `/wp/v2/comments` REST API endpoints, and comment admin menus/widgets across the site without deleting existing comment data.

## 1.2.6 - Fix BOM in disable user dropdown

- Newline character caused Updraft Plus warning re: possible BOM

## 1.2.7 - Correct path to custom dashboards

- Correct path to custom dashboards

## 1.2.8 - Small changes

- Remove the no wrap from the plugin title column
- Correct path to admin dashboard CSS

## 1.2.9 - Initial icon blocks

- Person, block and stars icons

## 1.3.0 - Additional icons and move of local fonts CSS

- Additional icons (Document, email, facebook, instagram, pdf, price, video, web)
- Move local fonts CSS folder to CSS not fonts

## 1.3.1 - Include media library features

- Search for a media item by its ID
- Add in the ALT attribute and the ID into the media library list view
- Move to media bootstrapper
- Document the common library and add date utils
- Exclude categories from blog

## 1.3.2 - Performance fixes

- Refactor media-library-unused-media to remove N+1s

## 1.3.3 - Brand trademark enforcer

- Move brand trademark enforcer from JS to PHP

## 1.3.4 - Fix serious issues with brand trademark enforcer

- Fix serious issues with brand trademark enforcer
- No longer operates on metadata, scripts or URLs


## 1.3.5 - Bump release

- Bump release