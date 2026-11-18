<?php
/**
 * Exclude Slim SEO Noindex Content from Front-End Search
 *
 * Modifies standard WordPress front-end search queries to include
 * both posts and pages while excluding content that has been marked
 * as noindex in Slim SEO.
 *
 * The exclusion is applied directly to the database query, using
 * the Slim SEO post metadata stored against each post or page.
 * This prevents noindex content from appearing in front-end search
 * results without changing its publication status or affecting its
 * availability through direct URLs.
 *
 * Features:
 *
 * - Slim SEO integration:
 *   Checks whether Slim SEO is active before registering any search
 *   query modifications.
 *
 * - Posts and Pages:
 *   Explicitly includes standard WordPress posts and pages in
 *   front-end search queries.
 *
 * - Noindex exclusion:
 *   Excludes posts and pages whose Slim SEO metadata contains the
 *   serialised `noindex` setting with a value of `1`.
 *
 * - Database-level filtering:
 *   Uses a conditional LEFT JOIN against the WordPress postmeta
 *   table and an additional SQL WHERE condition to exclude
 *   noindex content.
 *
 * - Missing metadata handling:
 *   Retains posts and pages that have no Slim SEO metadata, as well
 *   as those whose metadata does not contain the specified noindex
 *   setting.
 *
 * Behaviour:
 *
 * - Applies only to front-end WordPress search queries.
 * - Does not modify administration searches.
 * - Does not affect individual post or page URLs.
 * - Does not change Slim SEO settings or post metadata.
 * - Does not apply the noindex exclusion when Slim SEO is inactive.
 *
 * Hooks:
 *
 * - `pre_get_posts`:
 *   Restricts front-end search queries to standard posts and pages.
 *
 * - `posts_clauses`:
 *   Adds the Slim SEO metadata join and noindex exclusion condition
 *   to the database query.
 *
 * Dependencies:
 *
 * - WordPress core query and database APIs.
 * - Slim SEO plugin.
 * - `swu_is_slim_seo_active()` plugin helper function.
 *
 * @package Skyrocket
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