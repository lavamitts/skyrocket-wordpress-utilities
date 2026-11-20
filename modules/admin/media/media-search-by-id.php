<?php
/**
 * Enable searching for media attachments by their WordPress ID.
 *
 * Hooks into the main WordPress query on the Media Library admin page.
 * When the search term is numeric, it treats the value as an attachment
 * post ID instead of performing a standard text-based search.
 *
 * This allows media items to be located directly using their attachment ID.
 *
 * @param WP_Query $query The current WordPress query being processed.
 * @return void
 */
function custom_media_search_by_id($query)
{
    global $pagenow;

    // Only modify the main query on the Media Library admin page.
    if (is_admin() && 'upload.php' === $pagenow && $query->is_main_query()) {

        // Retrieve the search term entered in the Media Library search field.
        $search = $query->get('s');

        // If the search term is numeric, treat it as a media attachment ID.
        if (!empty($search) && is_numeric($search)) {

            // Set the query to retrieve the attachment with the specified post ID.
            $query->set('p', intval($search));

            // Clear the standard text search to prevent conflicting query conditions.
            $query->set('s', '');
        }
    }
}

// Modify the main WordPress query before it retrieves Media Library results.
add_action('pre_get_posts', 'custom_media_search_by_id');
