<?php
/**
 * Prioritise Pages in WordPress editor link search.
 *
 * Intercepts the REST API post search and moves Pages
 * ahead of other post types without changing relevance
 * ordering within either group.
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
