<?php
/**
 * Exclude Slim SEO noindex posts from all front-end search queries at the database level.
 */

if (swu_is_slim_seo_active()) {

    // 1. Include both posts and pages in front-end search queries
    add_action('pre_get_posts', function ($query) {
        if (!is_admin() && $query->is_search()) {
            $query->set('post_type', ['post', 'page']);
        }
    });

    // 2. Exclude Slim SEO noindex posts and pages at the database level
    add_filter('posts_clauses', function ($clauses, $query) {
        if (!is_admin() && $query->is_search()) {
            global $wpdb;

            // Join postmeta to inspect Slim SEO settings
            $clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS bbc_slim ON ({$wpdb->posts}.ID = bbc_slim.post_id AND bbc_slim.meta_key = 'slim_seo')";

            // Exclude posts/pages where slim_seo contains the serialized array pattern for noindex = 1
            $clauses['where'] .= " AND (bbc_slim.meta_value NOT LIKE '%\"noindex\";i:1;%' OR bbc_slim.meta_value IS NULL)";
        }

        return $clauses;
    }, 10, 2);
}