<?php
/**
 * WordPress Admin: Page Slug Column
 *
 * Adds a Slug column to the Pages admin listing.
 *
 * Displays the WordPress page slug (post_name), making it
 * easier to inspect URLs without opening individual pages.
 *
 * The column is also sortable alphabetically.
 */


/* ============================================================
 * 1. ADD SLUG COLUMN
 * ============================================================
 *
 * Register an additional column in Pages → All Pages.
 *
 * Existing WordPress columns are preserved.
 */

add_filter('manage_pages_columns', function ($columns) {

    $columns['slug'] = 'Slug';

    return $columns;

});


/* ============================================================
 * 2. DISPLAY PAGE SLUG
 * ============================================================
 *
 * Populate the Slug column with the page's post_name value.
 *
 * post_name is the WordPress database field containing the
 * URL-friendly slug, e.g. 'about-us'.
 *
 * esc_html() ensures the value is safely output as HTML text.
 */

add_action('manage_pages_custom_column', function ($column_name, $post_id) {

    // Only process our custom Slug column.
    if ($column_name !== 'slug') {
        return;
    }

    // Retrieve the page object.
    $post = get_post($post_id);

    // Display its slug, provided the post exists.
    if ($post) {
        echo esc_html($post->post_name);
    }

}, 10, 2);


/* ============================================================
 * 3. MAKE SLUG COLUMN SORTABLE
 * ============================================================
 *
 * Register the Slug column as sortable.
 *
 * When clicked, WordPress adds:
 *
 *     ?orderby=slug
 *
 * to the admin URL.
 *
 * The actual sorting is implemented in section 4.
 */

add_filter('manage_edit-page_sortable_columns', function ($columns) {

    $columns['slug'] = 'slug';

    return $columns;

});


/* ============================================================
 * 4. SORT PAGES BY SLUG
 * ============================================================
 *
 * Modify the main Pages admin query when sorting by Slug.
 *
 * post_name is a native WordPress database column, so there
 * is no need to use post meta or an ACF field.
 *
 * Sorting is alphabetical (ascending or descending depending
 * on the selected sort direction).
 */

add_action('pre_get_posts', function ($query) {

    // Only modify the main query within the WordPress admin.
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    // Only apply to the standard Pages listing.
    if ($query->get('post_type') !== 'page') {
        return;
    }

    // Check whether the Slug column was selected.
    if ($query->get('orderby') === 'slug') {

        // Sort directly using the WordPress post_name field.
        $query->set('orderby', 'name');

    }

});
