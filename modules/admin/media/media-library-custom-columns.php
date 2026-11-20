<?php
/**
 * WordPress Admin: Media Library Custom Columns (ID & Alt Text)
 *
 * Adds ID and Alt Text columns to the Media Library list view.
 *
 * - ID Column: Placed immediately after the bulk-select checkbox and made
 *   sortable for quick reference and ordering.
 * - Alt Text Column: Appended to the table to display the alternative text
 *   (alt attribute) stored against each image attachment, making it easier to
 *   identify images with missing alt text without opening each item.
 *
 * Note:
 *   - Applies to Media Library List View only.
 *   - Does not add columns to Grid View.
 *   - Display only; does not provide editing functionality.
 *
 * WordPress stores image alt text in the following post meta field:
 *
 *   _wp_attachment_image_alt
 */


/* ============================================================
 * 1. REGISTER CUSTOM COLUMNS
 * ============================================================
 *
 * Register additional columns in Media → Library.
 *
 * Places 'media_id' right after the checkbox column, preserves
 * all existing WordPress columns, and appends 'alt_text' at the end.
 */

add_filter('manage_media_columns', function ($columns) {

    $new_columns = [];

    // Place 'media_id' as the first data column (right after the checkbox)
    if (isset($columns['cb'])) {
        $new_columns['cb'] = $columns['cb'];
        unset($columns['cb']);
    }

    $new_columns['media_id'] = 'ID';

    // Merge the rest of the original columns and append Alt Text
    $columns = array_merge($new_columns, $columns);
    $columns['alt_text'] = 'Alt Text';

    return $columns;

});


/* ============================================================
 * 2. MAKE ID COLUMN SORTABLE
 * ============================================================
 *
 * Allows the Media Library list table to be sorted by attachment ID.
 */

add_filter('manage_upload_sortable_columns', function ($columns) {

    $columns['media_id'] = 'ID';

    return $columns;

});


/* ============================================================
 * 3. POPULATE CUSTOM COLUMNS
 * ============================================================
 *
 * Populate the custom ID and Alt Text columns with their values.
 *
 * For Alt Text:
 * get_post_meta() retrieves the value of the attachment's
 * _wp_attachment_image_alt field. The third parameter (true)
 * requests a single value rather than an array.
 *
 * If alt text exists:
 *   Display the value, safely escaped.
 *
 * If alt text is empty or missing:
 *   Display a grey italicised 'No alt text' message.
 */

add_action('manage_media_custom_column', function ($column_name, $post_id) {

    if ($column_name === 'media_id') {

        // Display the attachment post ID.
        echo esc_html($post_id);
        return;

    }

    if ($column_name === 'alt_text') {

        // Retrieve the stored alt text for this attachment.
        $alt_text = get_post_meta($post_id, '_wp_attachment_image_alt', true);

        if ($alt_text !== '') {

            // Display the existing alt text.
            echo esc_html($alt_text);

        } else {

            // Indicate that no alt text has been assigned.
            echo '<span class="skyrocket-no-alt-text">No alt text</span>';

        }

    }

}, 10, 2);


/* ============================================================
 * 4. STYLE CUSTOM COLUMNS
 * ============================================================
 *
 * Apply column width constraints and a subtle visual distinction
 * to attachments without alt text.
 *
 * Scoped to upload.php so styles only load on the Media Library
 * screen, keeping presentation separate from PHP functionality.
 */

add_action('admin_head-upload.php', function () {
    ?>

    <style>
        .column-media_id {
            width: 60px;
        }

        .skyrocket-no-alt-text {
            color: #999;
            font-style: italic;
        }
    </style>

    <?php
});