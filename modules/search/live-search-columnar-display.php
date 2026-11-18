<?php
/**
 * Module Name: Live Search Columns
 * Description: Provides grouped Blocksy live search results for posts, pages and Pavilion Events, including dedicated styling, JavaScript and REST API configuration.
 *
 * Integrates with Blocksy's live search functionality to display
 * search results grouped by content type, with configurable result
 * limits and separate REST API endpoints.
 *
 * The module is enabled only when Blocksy or a Blocksy child theme
 * is active.
 *
 * Features:
 *
 * - Grouped search results:
 *   Configures separate result groups for Posts, Pages and Events.
 *
 * - REST API integration:
 *   Uses the WordPress REST API to retrieve results from the relevant
 *   post type endpoints.
 *
 * - Pavilion Events integration:
 *   Adds an Events search group when the Pavilion Events post type
 *   is registered and exposed through the REST API.
 *   Automatically uses the post type's configured REST namespace
 *   and REST base.
 *
 * - Configurable result limits:
 *   Sets the maximum number of results per content type.
 *
 * - Search query handling:
 *   Reasserts the requested post type on REST search queries using
 *   the `strict_type` parameter, at a late `pre_get_posts` priority.
 *
 * - Dedicated assets:
 *   Loads the live search stylesheet and JavaScript, passing the
 *   search configuration to the script through an inline JavaScript
 *   object.
 *
 * Theme compatibility:
 *
 * - Runs only when Blocksy or a Blocksy child theme is active.
 * - Detects the parent theme using `wp_get_theme()->get_template()`.
 * - Does not register its hooks when another theme is active.
 *
 * Hooks:
 *
 * - `after_setup_theme`:
 *   Checks the active parent theme and conditionally registers
 *   the module's functionality.
 *
 * - `pre_get_posts`:
 *   Reasserts the requested post type on qualifying REST API searches.
 *
 * - `wp_enqueue_scripts`:
 *   Loads the live search assets and supplies the REST API
 *   configuration.
 *
 * Dependencies:
 *
 * - WordPress core theme, query, post type and REST API functions.
 * - Blocksy theme or a Blocksy child theme.
 * - `assets/css/search/live-search.css`.
 * - `assets/js/search/live-search.js`.
 * - `SWU_URL` plugin constant.
 *
 * @package Skyrocket
 */

defined('ABSPATH') || exit;

// Post type slug for the Pavilion Events plugin.
const BBC_LS_EVENT_POST_TYPE = 'pavilion-event';

/**
 * Register live search functionality when Blocksy is active.
 */
function bbc_ls_register_module()
{
    $theme = wp_get_theme();

    // get_template() returns the parent theme directory for child themes.
    if ($theme->get_template() !== 'blocksy') {
        return;
    }

    // Re-assert the post type after other pre_get_posts hooks.
    add_action('pre_get_posts', function ($query) {
        if (!defined('REST_REQUEST') || !REST_REQUEST) {
            return;
        }

        if (!$query->is_search() || empty($_GET['strict_type'])) {
            return;
        }

        $query->set(
            'post_type',
            sanitize_key(wp_unslash($_GET['strict_type']))
        );
    }, PHP_INT_MAX);

    // Load live search assets and configuration.
    add_action('wp_enqueue_scripts', function () {
        wp_enqueue_style(
            'bbc-live-search',
            SWU_URL . 'assets/css/search/live-search.css',
            [],
            '1.0.0'
        );

        wp_enqueue_script(
            'bbc-live-search',
            SWU_URL . 'assets/js/search/live-search.js',
            [],
            '1.0.0',
            true
        );

        $groups = [
            [
                'endpoint' => esc_url_raw(rest_url('wp/v2/posts')),
                'postType' => 'post',
                'label' => 'Posts',
            ],
            [
                'endpoint' => esc_url_raw(rest_url('wp/v2/pages')),
                'postType' => 'page',
                'label' => 'Pages',
            ],
        ];

        // Add Events only when the post type is registered and REST-enabled.
        if (post_type_exists(BBC_LS_EVENT_POST_TYPE)) {
            $event_type = get_post_type_object(BBC_LS_EVENT_POST_TYPE);

            if ($event_type && $event_type->show_in_rest) {
                $namespace = !empty($event_type->rest_namespace)
                    ? $event_type->rest_namespace
                    : 'wp/v2';

                $rest_base = !empty($event_type->rest_base)
                    ? $event_type->rest_base
                    : $event_type->name;

                $groups[] = [
                    'endpoint' => esc_url_raw(
                        rest_url($namespace . '/' . $rest_base)
                    ),
                    'postType' => BBC_LS_EVENT_POST_TYPE,
                    'label' => 'Events',
                ];
            }
        }

        $config = [
            'perType' => 5,
            'groups' => $groups,
        ];

        wp_add_inline_script(
            'bbc-live-search',
            'window.bbcLiveSearch = ' . wp_json_encode($config) . ';',
            'before'
        );
    });
}
add_action('after_setup_theme', 'bbc_ls_register_module');
