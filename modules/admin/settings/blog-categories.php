<?php
/**
 * Blog Categories Settings Tab.
 *
 * @package Skyrocket
 */

defined('ABSPATH') || exit;

$options      = get_option('swu_options', []);
$excluded_ids = isset($options['excluded_blog_categories']) && is_array($options['excluded_blog_categories'])
    ? array_map('absint', $options['excluded_blog_categories'])
    : [];

$categories = get_categories([
    'taxonomy'   => 'category',
    'hide_empty' => false,
    'orderby'    => 'name',
    'order'      => 'ASC',
]);
?>

<h2>Exclude Categories from Main Blog Page</h2>
<p class="description">
    Select any categories whose posts should be hidden on the main blog page. Leave all unchecked to show all categories.
</p>

<table class="form-table" role="presentation">
    <tbody>
        <tr>
            <th scope="row">Excluded categories</th>
            <td>
                <?php if (!empty($categories)) : ?>
                    <fieldset>
                        <legend class="screen-reader-text">
                            <span>Excluded categories</span>
                        </legend>

                        <?php foreach ($categories as $category) : ?>
                            <?php $field_id = 'swu_exclude_cat_' . (int) $category->term_id; ?>
                            <label for="<?php echo esc_attr($field_id); ?>" style="display: block; margin-bottom: 6px;">
                                <input
                                    type="checkbox"
                                    id="<?php echo esc_attr($field_id); ?>"
                                    name="swu_options[excluded_blog_categories][]"
                                    value="<?php echo esc_attr($category->term_id); ?>"
                                    <?php checked(in_array((int) $category->term_id, $excluded_ids, true)); ?>
                                >
                                <?php echo esc_html($category->name); ?>
                                <span class="description">(<?php echo (int) $category->count; ?> posts)</span>
                            </label>
                        <?php endforeach; ?>
                    </fieldset>
                <?php else : ?>
                    <p class="description">No categories found.</p>
                <?php endif; ?>
            </td>
        </tr>
    </tbody>
</table>