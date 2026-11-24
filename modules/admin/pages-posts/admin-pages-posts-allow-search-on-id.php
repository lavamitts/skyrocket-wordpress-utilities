<?php
/**
 * Enable searching for posts and pages by their WordPress ID.
 *
 * Hooks into the main WordPress query on the post/page list admin screen.
 * When the search term is numeric, it treats the value as a post ID
 * instead of performing a standard text-based search.
 *
 * @param WP_Query $query The current WordPress query being processed.
 * @return void
 */
function custom_posts_and_pages_search_by_id($query)
{
    global $pagenow;

    // Only modify the main query on the posts/pages list screen.
    if (is_admin() && 'edit.php' === $pagenow && $query->is_main_query()) {

        // Limit this behaviour to standard posts and pages.
        $post_type = $query->get('post_type');
        $allowed_types = array('', 'post', 'page');

        if (!in_array($post_type, $allowed_types, true)) {
            return;
        }

        // Retrieve the search term entered in the admin search field.
        $search = $query->get('s');

        // If the search term is numeric, treat it as a post or page ID.
        if (!empty($search) && is_numeric($search)) {

            // Set the query to retrieve the item with the specified ID.
            $query->set('p', intval($search));

            // Clear the standard text search to prevent conflicting query conditions.
            $query->set('s', '');
        }
    }
}

// Modify the main WordPress query before it retrieves post or page results.
add_action('pre_get_posts', 'custom_posts_and_pages_search_by_id');