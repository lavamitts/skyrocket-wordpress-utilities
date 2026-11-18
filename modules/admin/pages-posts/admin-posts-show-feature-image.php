<?php
/**
 * Featured Image Column for the WordPress Posts List
 *
 * Adds a dedicated Image column to the standard WordPress posts
 * administration list, displaying a thumbnail of each post's
 * featured image.
 *
 * The column is inserted immediately before the post title, making
 * featured images easy to identify when browsing and managing posts.
 *
 * Features:
 *
 * - Image column:
 *   Adds an Image column to the standard Posts list table, positioned
 *   immediately before the Title column.
 *
 * - Featured image thumbnails:
 *   Displays each post's featured image using WordPress's
 *   `get_the_post_thumbnail()` function, requesting a 64 × 36 pixel
 *   thumbnail.
 *
 * - Missing image handling:
 *   Displays `None` when a post does not have a featured image.
 *
 * - Column styling:
 *   Adds admin-side CSS to constrain the column width and thumbnail
 *   dimensions, display images as block elements and apply slightly
 *   rounded corners.
 *
 * Behaviour:
 *
 * - Applies to the standard WordPress Posts list table.
 * - Does not add the column to Pages or custom post type list tables.
 * - Does not provide featured image editing or upload functionality.
 * - Uses the post's existing featured image, without modifying it.
 *
 * Hooks:
 *
 * - `manage_posts_columns`:
 *   Registers the Image column in the Posts list table.
 *
 * - `manage_posts_custom_column`:
 *   Outputs the featured image thumbnail or the `None` placeholder.
 *
 * - `admin_head`:
 *   Outputs the CSS used to style the column and its thumbnails.
 *
 * Dependencies:
 *
 * - WordPress core post thumbnail and admin list table APIs.
 *
 * @package Skyrocket
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
