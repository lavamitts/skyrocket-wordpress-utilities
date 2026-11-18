<?php
/**
 * Add image dimensions to the WordPress Media Library list view.
 *
 * Adds a Dimensions column displaying the width and height of image
 * attachments using their stored WordPress attachment metadata.
 *
 * Non-image attachments and images without available dimension metadata
 * display a dash.
 *
 * @package Skyrocket
 */

defined('ABSPATH') || exit;

/**
 * Add the dimensions column to the Media Library.
 *
 * @param array $columns Existing Media Library columns.
 * @return array Modified columns.
 */
function swu_media_library_dimensions_column(array $columns): array
{

    $new_columns = [];

    foreach ($columns as $key => $label) {

        $new_columns[$key] = $label;

        // Insert the dimensions column after the attachment title.
        if ($key === 'title') {
            $new_columns['swu_dimensions'] = __('Dimensions', 'skyrocket');
        }
    }

    // Append the column if the title column was not available.
    if (!isset($new_columns['swu_dimensions'])) {
        $new_columns['swu_dimensions'] = __('Dimensions', 'skyrocket');
    }

    return $new_columns;
}

add_filter(
    'manage_upload_columns',
    'swu_media_library_dimensions_column'
);

/**
 * Display attachment dimensions in the Media Library.
 *
 * @param string $column_name Current column name.
 * @param int    $post_id     Attachment post ID.
 * @return void
 */
function swu_media_library_dimensions_content(
    string $column_name,
    int $post_id
): void {

    if ($column_name !== 'swu_dimensions') {
        return;
    }

    // Retrieve the attachment metadata.
    $metadata = wp_get_attachment_metadata($post_id);

    // Only images have dimensions.
    if (
        !is_array($metadata) ||
        empty($metadata['width']) ||
        empty($metadata['height'])
    ) {
        echo '&mdash;';
        return;
    }

    // Display the image dimensions.
    printf(
        '%s × %s px',
        esc_html(number_format_i18n((int) $metadata['width'])),
        esc_html(number_format_i18n((int) $metadata['height']))
    );
}

add_action(
    'manage_media_custom_column',
    'swu_media_library_dimensions_content',
    10,
    2
);
