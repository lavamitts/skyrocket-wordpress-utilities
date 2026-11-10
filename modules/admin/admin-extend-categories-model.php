<?php
/**
 * Extend categories with a custom title override field.
 */

/**
 * Add field to the 'Add New Category' screen.
 */
function swu_add_category_title_override_field($taxonomy)
{
    ?>
    <div class="form-field term-title-override-wrap">
        <label for="category_title_override">Custom title override</label>
        <input type="text" name="category_title_override" id="category_title_override" value="">
        <p>Optional. Provide a custom heading for this category archive page.</p>
    </div>
    <?php
}
add_action('category_add_form_fields', 'swu_add_category_title_override_field');

/**
 * Add field to the 'Edit Category' screen.
 */
function swu_edit_category_title_override_field($term, $taxonomy = '')
{
    $override = get_term_meta($term->term_id, 'category_title_override', true);
    ?>
    <tr class="form-field term-title-override-wrap">
        <th scope="row">
            <label for="category_title_override">Custom title override</label>
        </th>
        <td>
            <input type="text" name="category_title_override" id="category_title_override"
                value="<?php echo esc_attr($override); ?>">
            <p class="description">
                Optional. Provide a custom heading for this category archive page.
            </p>
        </td>
    </tr>
    <?php
}
add_action('category_edit_form_fields', 'swu_edit_category_title_override_field');

/**
 * Save the custom category title override.
 */
function swu_save_category_title_override($term_id)
{
    if (!isset($_POST['category_title_override'])) {
        return;
    }

    if (
        !isset($_POST['_wpnonce']) ||
        !wp_verify_nonce(
            sanitize_text_field(wp_unslash($_POST['_wpnonce'])),
            'update-tag_' . $term_id
        )
    ) {
        return;
    }

    if (!current_user_can('manage_categories')) {
        return;
    }

    update_term_meta(
        $term_id,
        'category_title_override',
        sanitize_text_field(wp_unslash($_POST['category_title_override']))
    );
}
add_action('created_category', 'swu_save_category_title_override');
add_action('edited_category', 'swu_save_category_title_override');

/**
 * Apply the custom title override on category archive pages.
 */
function swu_filter_category_archive_title($title)
{
    if (!is_category()) {
        return $title;
    }

    $category = get_queried_object();

    if (!$category || !isset($category->term_id)) {
        return $title;
    }

    $override = get_term_meta($category->term_id, 'category_title_override', true);

    return !empty($override) ? $override : $title;
}
add_filter('get_the_archive_title', 'swu_filter_category_archive_title');
