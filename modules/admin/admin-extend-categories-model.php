<?php
/**
 * Extend category taxonomies with custom term meta fields.
 *
 * - Posts "category":      custom archive title override (text)
 * - WooCommerce "product_cat": short description (textarea)
 * - WooCommerce "product_cat": display priority (integer)
 *
 * Fields are defined once in swu_term_fields(); the add/edit screen output
 * and saving are handled by shared code.
 *
 * Frontend usage (product categories):
 *
 *     swu_category_short_description();
 *     swu_category_get_short_description();
 *     swu_category_get_display_priority();
 */

defined('ABSPATH') || exit;

const SWU_CATEGORY_TITLE_META = 'category_title_override';
const SWU_CATEGORY_SHORT_DESC_META = '_wc_category_short_description';
const SWU_CATEGORY_PRIORITY_META = '_swu_category_display_priority';

/**
 * Field definitions, keyed by taxonomy.
 */
function swu_term_fields(): array
{
    return [
        'category' => [
            [
                'key' => SWU_CATEGORY_TITLE_META,
                'label' => __('Custom title override', 'default'),
                'type' => 'text',
                'description' => __('Optional. Provide a custom heading for this category archive page.', 'default'),
            ],
        ],
        'product_cat' => [
            [
                'key' => SWU_CATEGORY_SHORT_DESC_META,
                'label' => __('Short Description', 'woocommerce'),
                'type' => 'textarea',
                'description' => __('A brief description displayed on product category listings.', 'woocommerce'),
            ],
            [
                'key' => SWU_CATEGORY_PRIORITY_META,
                'label' => __('Display Priority', 'woocommerce'),
                'type' => 'number',
                'description' => __('Controls the display order of this category. Lower numbers appear first. 0 is the highest priority.', 'woocommerce'),
            ],
        ],
    ];
}

/**
 * Render a single field input.
 */
function swu_render_term_field(array $field, string $value): void
{
    $id = 'swu_term_meta_' . $field['key'];
    $name = 'swu_term_meta[' . $field['key'] . ']';

    if ($field['type'] === 'textarea') {
        printf(
            '<textarea name="%1$s" id="%2$s" rows="4" class="large-text">%3$s</textarea>',
            esc_attr($name),
            esc_attr($id),
            esc_textarea($value)
        );
        return;
    }

    if ($field['type'] === 'number') {
        printf(
            '<input type="number" name="%1$s" id="%2$s" value="%3$s" min="0" step="1" class="small-text">',
            esc_attr($name),
            esc_attr($id),
            esc_attr($value)
        );
        return;
    }

    printf(
        '<input type="text" name="%1$s" id="%2$s" value="%3$s">',
        esc_attr($name),
        esc_attr($id),
        esc_attr($value)
    );
}

/**
 * Register hooks for every taxonomy defined above.
 */
foreach (swu_term_fields() as $swu_taxonomy => $swu_fields) {

    // "Add New" screen.
    add_action("{$swu_taxonomy}_add_form_fields", function () use ($swu_fields) {
        foreach ($swu_fields as $field) {
            $id = 'swu_term_meta_' . $field['key'];
            $value = $field['type'] === 'number' ? '0' : '';
            ?>
            <div class="form-field">
                <label for="<?php echo esc_attr($id); ?>">
                    <?php echo esc_html($field['label']); ?>
                </label>

                <?php swu_render_term_field($field, $value); ?>

                <p class="description">
                    <?php echo esc_html($field['description']); ?>
                </p>
            </div>
            <?php
        }
    });

    // "Edit" screen.
    add_action("{$swu_taxonomy}_edit_form_fields", function ($term) use ($swu_fields) {
        foreach ($swu_fields as $field) {
            $id = 'swu_term_meta_' . $field['key'];

            $value = (string) get_term_meta(
                $term->term_id,
                $field['key'],
                true
            );

            if ($field['type'] === 'number' && $value === '') {
                $value = '0';
            }
            ?>
            <tr class="form-field">
                <th scope="row">
                    <label for="<?php echo esc_attr($id); ?>">
                        <?php echo esc_html($field['label']); ?>
                    </label>
                </th>
                <td>
                    <?php swu_render_term_field($field, $value); ?>

                    <p class="description">
                        <?php echo esc_html($field['description']); ?>
                    </p>
                </td>
            </tr>
            <?php
        }
    });

    // Saving.
    add_action("created_{$swu_taxonomy}", function ($term_id) use ($swu_taxonomy) {
        swu_save_term_fields((int) $term_id, $swu_taxonomy, 'add');
    });

    add_action("edited_{$swu_taxonomy}", function ($term_id) use ($swu_taxonomy) {
        swu_save_term_fields((int) $term_id, $swu_taxonomy, 'edit');
    });
}

/**
 * Save submitted fields for a term.
 *
 * WordPress uses different nonces for the two screens:
 * - Add:  '_wpnonce_add-tag' / 'add-tag'
 * - Edit: '_wpnonce' / 'update-tag_{term_id}'
 */
function swu_save_term_fields(int $term_id, string $taxonomy, string $context): void
{
    if (!isset($_POST['swu_term_meta']) || !is_array($_POST['swu_term_meta'])) {
        return;
    }

    if ($context === 'add') {
        $nonce_field = '_wpnonce_add-tag';
        $nonce_action = 'add-tag';
    } else {
        $nonce_field = '_wpnonce';
        $nonce_action = 'update-tag_' . $term_id;
    }

    if (
        !isset($_POST[$nonce_field]) ||
        !wp_verify_nonce(
            sanitize_text_field(wp_unslash($_POST[$nonce_field])),
            $nonce_action
        )
    ) {
        return;
    }

    if (!current_user_can('edit_term', $term_id)) {
        return;
    }

    $submitted = wp_unslash($_POST['swu_term_meta']);
    $fields = swu_term_fields()[$taxonomy] ?? [];

    foreach ($fields as $field) {
        if (
            !isset($submitted[$field['key']]) ||
            !is_string($submitted[$field['key']])
        ) {
            continue;
        }

        switch ($field['type']) {
            case 'textarea':
                $value = sanitize_textarea_field($submitted[$field['key']]);
                break;

            case 'number':
                $value = absint($submitted[$field['key']]);
                break;

            default:
                $value = sanitize_text_field($submitted[$field['key']]);
                break;
        }

        update_term_meta($term_id, $field['key'], $value);
    }
}

/**
 * Apply the custom title override on category archive pages.
 */
add_filter('get_the_archive_title', function ($title) {
    if (!is_category()) {
        return $title;
    }

    $category = get_queried_object();

    if (!$category || !isset($category->term_id)) {
        return $title;
    }

    $override = get_term_meta(
        $category->term_id,
        SWU_CATEGORY_TITLE_META,
        true
    );

    return !empty($override) ? $override : $title;
});

/**
 * Retrieve a product category's short description.
 */
function swu_category_get_short_description(int $term_id = 0): string
{
    if (!$term_id) {
        $term = get_queried_object();

        if (!$term instanceof WP_Term) {
            return '';
        }

        $term_id = $term->term_id;
    }

    return (string) get_term_meta(
        $term_id,
        SWU_CATEGORY_SHORT_DESC_META,
        true
    );
}

/**
 * Retrieve a product category's display priority.
 *
 * Returns 0 when no priority has been explicitly assigned.
 */
function swu_category_get_display_priority(int $term_id): int
{
    return absint(
        get_term_meta(
            $term_id,
            SWU_CATEGORY_PRIORITY_META,
            true
        )
    );
}

/**
 * Output a product category's short description.
 */
function swu_category_short_description(int $term_id = 0): void
{
    $description = swu_category_get_short_description($term_id);

    if ($description === '') {
        return;
    }

    echo '<div class="product-category-short-description">';
    echo wpautop(esc_html($description));
    echo '</div>';
}

/**
 * Show the short description under each category tile on category listings.
 */
function swu_loop_category_short_description($category): void
{
    if (!$category instanceof WP_Term) {
        return;
    }

    swu_category_short_description($category->term_id);
}

add_action(
    'woocommerce_after_subcategory_title',
    'swu_loop_category_short_description',
    10
);

/**
 * Remove Blocksy's standard category excerpt from category tiles.
 */
function swu_loop_category_buffer_start(): void
{
    ob_start();
}

add_action(
    'woocommerce_before_subcategory',
    'swu_loop_category_buffer_start',
    0
);

function swu_loop_category_buffer_end(): void
{
    $html = ob_get_clean();

    if ($html === false) {
        return;
    }

    echo preg_replace(
        '#<div class="entry-excerpt">.*?</div>#s',
        '',
        $html
    );
}

add_action(
    'woocommerce_after_subcategory',
    'swu_loop_category_buffer_end',
    PHP_INT_MAX
);

/**
 * Add Display Priority column to the WooCommerce product categories list table.
 */
add_filter('manage_edit-product_cat_columns', function (array $columns): array {
    $new_columns = [];

    // Insert the Priority column right after the Category Name column
    foreach ($columns as $key => $title) {
        $new_columns[$key] = $title;
        if ($key === 'name') {
            $new_columns['swu_display_priority'] = __('Priority', 'woocommerce');
        }
    }

    // Fallback if 'name' column isn't found
    if (!isset($new_columns['swu_display_priority'])) {
        $new_columns['swu_display_priority'] = __('Priority', 'woocommerce');
    }

    return $new_columns;
});

/**
 * Render the value for the Display Priority column.
 */
add_action('manage_product_cat_custom_column', function (string $content, string $column_name, int $term_id): string {
    if ($column_name === 'swu_display_priority') {
        return (string) swu_category_get_display_priority($term_id);
    }

    return $content;
}, 10, 3);

/**
 * Make the Display Priority column sortable in the admin table.
 */
add_filter('manage_edit-product_cat_sortable_columns', function (array $sortable): array {
    $sortable['swu_display_priority'] = 'swu_display_priority';
    return $sortable;
});