<?php
/**
 * Restrict admin search to the post or page title only 
 * when the search query is enclosed in quotation marks.
 */
function swu_custom_admin_title_search_on_quotes($search, $wp_query)
{
    // Execute only in the WordPress administration area for the main query.
    if (!is_admin() || !$wp_query->is_main_query()) {
        return $search;
    }

    // Retrieve the search term entered by the user.
    $search_term = $wp_query->get('s');

    if (empty($search_term)) {
        return $search;
    }

    // Check if the search term is enclosed in double or single quotes.
    $first_char = substr($search_term, 0, 1);
    $last_char = substr($search_term, -1, 1);

    if (($first_char === '"' && $last_char === '"') || ($first_char === "'" && $last_char === "'")) {
        // Strip the surrounding quotation marks to get the raw search phrase.
        $unquoted_term = trim($search_term, "\"'");

        if (empty($unquoted_term)) {
            return $search;
        }

        // Determine the current post type from the query or global admin context.
        $post_type = $wp_query->get('post_type');
        if (empty($post_type)) {
            global $typenow;
            $post_type = !empty($typenow) ? $typenow : 'post';
        }

        // Apply the restriction only when inside the posts or pages admin sections.
        if (in_array($post_type, ['post', 'page'], true)) {
            global $wpdb;

            // Prepare the escaped term for a title-only SQL search.
            $escaped_term = '%' . $wpdb->esc_like($unquoted_term) . '%';

            // Overwrite the default search SQL to target only the post_title column.
            $search = $wpdb->prepare(" AND ({$wpdb->posts}.post_title LIKE %s) ", $escaped_term);
        }
    }

    return $search;
}
add_filter('posts_search', 'swu_custom_admin_title_search_on_quotes', 10, 2);