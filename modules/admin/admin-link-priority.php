<?php
/**
 * Prioritise Pages in WordPress Editor Link Search
 *
 * Modifies the ordering of results returned by the WordPress REST API
 * post search used by the editor's link insertion interface, placing
 * Pages ahead of other matching post types.
 *
 * The modification changes only the relative priority of post types.
 * WordPress's original relevance ordering is retained within the
 * Pages group and within the remaining results.
 *
 * Features:
 *
 * - Pages-first ordering:
 *   Prioritises Pages over other post types in REST API post search
 *   results, making Pages easier to find when inserting links in
 *   the WordPress editor.
 *
 * - Relevance preservation:
 *   Adds a post type priority condition to the SQL `ORDER BY` clause
 *   without replacing WordPress's existing relevance ordering.
 *
 * - Query-specific filtering:
 *   Marks the REST search query with a custom query variable so that
 *   the ordering modification applies only to the intended search.
 *
 * - Temporary SQL filter:
 *   Adds a `posts_orderby` filter for the search operation and removes
 *   it immediately after processing the marked query, preventing it
 *   from affecting subsequent queries.
 *
 * Behaviour:
 *
 * - Applies to REST API post searches using `rest_post_search_query`.
 * - Places matching Pages before results belonging to other post types.
 * - Preserves the original WordPress relevance ordering within each group.
 * - Does not change search terms, matching criteria or result content.
 * - Does not affect ordinary front-end searches or unrelated queries.
 *
 * Hooks:
 *
 * - `rest_post_search_query`:
 *   Marks the REST API search query for Pages-first ordering.
 *
 * - `posts_orderby`:
 *   Temporarily modifies the SQL ordering for the marked query and
 *   removes the filter after it has been applied.
 *
 * Dependencies:
 *
 * - WordPress core REST API post search and query APIs.
 * - WordPress database abstraction through `$wpdb`.
 *
 * @package Skyrocket
 */

function swu_prioritise_pages_in_editor_search($args, $request)
{
    // Mark this particular query.
    $args['_swu_pages_first'] = true;

    // Add a temporary ORDER BY filter.
    $priority_filter = null;

    $priority_filter = function ($orderby, $query) use (&$priority_filter) {

        // Only affect our marked REST search query.
        if (!$query->get('_swu_pages_first')) {
            return $orderby;
        }

        // Remove immediately so other queries are unaffected.
        remove_filter('posts_orderby', $priority_filter, 10);

        global $wpdb;

        $page_priority = "
            CASE
                WHEN {$wpdb->posts}.post_type = 'page' THEN 0
                ELSE 1
            END ASC
        ";

        // Preserve WordPress's original relevance ordering.
        return $page_priority . ($orderby ? ', ' . $orderby : '');
    };

    add_filter('posts_orderby', $priority_filter, 10, 2);

    return $args;
}
add_filter('rest_post_search_query', 'swu_prioritise_pages_in_editor_search', 10, 2);
