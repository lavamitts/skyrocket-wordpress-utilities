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
