<?php
/**
 * Exclude selected categories from the main blog page.
 *
 * @param WP_Query $query The WP_Query instance (passed by reference).
 */
function exclude_categories_from_blog($query)
{
    if (is_admin() || !$query->is_home() || !$query->is_main_query()) {
        return;
    }

    $options = get_option('swu_options', []);
    $excluded = isset($options['excluded_blog_categories']) && is_array($options['excluded_blog_categories'])
        ? array_map('absint', $options['excluded_blog_categories'])
        : [];

    $excluded = array_filter($excluded);

    if (!empty($excluded)) {
        $query->set('category__not_in', array_values($excluded));
    }
}
add_action('pre_get_posts', 'exclude_categories_from_blog');