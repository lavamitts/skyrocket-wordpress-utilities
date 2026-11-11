<?php
/**
 * Module Name: Live Search Columns
 * Description: Enqueues custom styles, script, and configuration for Blocksy live search columns.
 */

defined('ABSPATH') || exit;

// Post type slug for the Pavilion Events plugin (change here if it differs)
const BBC_LS_EVENT_POST_TYPE = 'pavilion-event';

// Re-assert the post type after any other pre_get_posts hook (e.g. Blocksy's search settings)
add_action('pre_get_posts', function ($query) {
    if (!defined('REST_REQUEST') || !REST_REQUEST) {
        return;
    }
    if (!$query->is_search() || empty($_GET['strict_type'])) {
        return;
    }
    $query->set('post_type', sanitize_key(wp_unslash($_GET['strict_type'])));
}, PHP_INT_MAX);

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

    // Add events only if the Pavilion Events post type is registered and exposed to REST
    if (post_type_exists(BBC_LS_EVENT_POST_TYPE)) {
        $event_type = get_post_type_object(BBC_LS_EVENT_POST_TYPE);

        if ($event_type && $event_type->show_in_rest) {
            $namespace = !empty($event_type->rest_namespace) ? $event_type->rest_namespace : 'wp/v2';
            $rest_base = !empty($event_type->rest_base) ? $event_type->rest_base : $event_type->name;

            $groups[] = [
                'endpoint' => esc_url_raw(rest_url($namespace . '/' . $rest_base)),
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