<?php
/**
 * Add a featured image column to the posts list table.
 */
function swu_custom_posts_column($columns)
{
    $new_columns = [];

    foreach ($columns as $key => $title) {
        if ($key === 'title') {
            $new_columns['featured_image'] = 'Image';
        }

        $new_columns[$key] = $title;
    }

    return $new_columns;
}
add_filter('manage_posts_columns', 'swu_custom_posts_column');

/**
 * Display the featured image thumbnail inside the column.
 */
function swu_custom_posts_column_content($column_name, $post_id)
{
    if ($column_name === 'featured_image') {
        if (has_post_thumbnail($post_id)) {
            echo get_the_post_thumbnail($post_id, [64, 36]);
        } else {
            echo 'None';
        }
    }
}
add_action('manage_posts_custom_column', 'swu_custom_posts_column_content', 10, 2);

/**
 * Add inline CSS to ensure the column and image stay neat and tidy.
 */
function swu_custom_posts_column_css()
{
    echo '<style>
        .column-featured_image { width: 60px; }
        .column-featured_image img {
            max-width: 50px;
            height: auto;
            display: block;
            border-radius: 4px;
        }
    </style>';
}
add_action('admin_head', 'swu_custom_posts_column_css');
