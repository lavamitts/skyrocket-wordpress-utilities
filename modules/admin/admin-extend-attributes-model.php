<?php
if (!defined('ABSPATH')) {
    exit;
}

error_log("Adding attr desc");

/**
 * WooCommerce Global Attribute Descriptions
 *
 * Adds an internal description field to global product attributes.
 *
 * Features:
 * - Adds a description field when creating an attribute.
 * - Adds a description field when editing an attribute.
 * - Stores descriptions in a separate database table.
 * - Associates descriptions with WooCommerce attribute IDs.
 * - Removes descriptions when their attributes are deleted.
 *
 * Descriptions are for administrative reference only.
 * They are never displayed on the frontend.
 */

/**
 * Return the custom attribute descriptions table name.
 *
 * @return string
 */
function custom_wc_attribute_description_table(): string
{
    global $wpdb;

    return $wpdb->prefix . 'woocommerce_attribute_descriptions';
}

/**
 * Create the attribute descriptions table.
 *
 * Uses dbDelta so the table can be updated safely if
 * the schema changes in a future version.
 *
 * @return void
 */
function custom_wc_attribute_description_install(): void
{
    global $wpdb;

    $version = '1.0.0';

    if (get_option('custom_wc_attribute_description_version') === $version) {
        return;
    }

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $table_name = custom_wc_attribute_description_table();
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$table_name} (
        attribute_id bigint(20) unsigned NOT NULL,
        description longtext NOT NULL,
        PRIMARY KEY  (attribute_id)
    ) {$charset_collate};";

    dbDelta($sql);

    update_option('custom_wc_attribute_description_version', $version);
}
add_action('init', 'custom_wc_attribute_description_install', 20);

/**
 * Retrieve an attribute description.
 *
 * @param int $attribute_id WooCommerce attribute ID.
 * @return string
 */
function custom_wc_get_attribute_description(int $attribute_id): string
{
    global $wpdb;

    $table_name = custom_wc_attribute_description_table();

    $description = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT description
            FROM {$table_name}
            WHERE attribute_id = %d",
            $attribute_id
        )
    );

    return $description ?? '';
}

/**
 * Render the description field on the Add Attribute screen.
 *
 * @return void
 */
function custom_wc_attribute_description_add_field(): void
{
    ?>
    <div class="form-field">
        <label for="attribute_description">
            <?php esc_html_e('Description', 'woocommerce'); ?>
        </label>

        <textarea name="attribute_description" id="attribute_description" rows="4" cols="40"></textarea>

        <p class="description">
            <?php
            esc_html_e(
                'Internal reference only. Describe what this attribute is used for. Not displayed to customers.',
                'woocommerce'
            );
            ?>
        </p>
    </div>
    <?php
}
add_action(
    'woocommerce_after_add_attribute_fields',
    'custom_wc_attribute_description_add_field'
);

/**
 * Render the description field on the Edit Attribute screen.
 *
 * @return void
 */
function custom_wc_attribute_description_edit_field(): void
{
    // Retrieve the attribute ID from the current admin URL.
    $attribute_id = isset($_GET['edit'])
        ? absint($_GET['edit'])
        : 0;

    if (!$attribute_id) {
        return;
    }

    $description = custom_wc_get_attribute_description($attribute_id);
    ?>
    <tr class="form-field">
        <th scope="row">
            <label for="attribute_description">
                <?php esc_html_e('Description', 'woocommerce'); ?>
            </label>
        </th>

        <td>
            <textarea name="attribute_description" id="attribute_description" rows="4" cols="50"
                class="large-text"><?php echo esc_textarea($description); ?></textarea>

            <p class="description">
                <?php
                esc_html_e(
                    'Internal reference only. Describe what this attribute is used for. Not displayed to customers.',
                    'woocommerce'
                );
                ?>
            </p>
        </td>
    </tr>
    <?php
}
add_action(
    'woocommerce_after_edit_attribute_fields',
    'custom_wc_attribute_description_edit_field'
);

/**
 * Save an attribute description.
 *
 * An empty description removes the corresponding database record.
 *
 * @param int $attribute_id WooCommerce attribute ID.
 * @return void
 */
function custom_wc_save_attribute_description(int $attribute_id): void
{
    global $wpdb;

    if (
        !$attribute_id ||
        !isset($_POST['attribute_description'])
    ) {
        return;
    }

    $table_name = custom_wc_attribute_description_table();

    $description = sanitize_textarea_field(
        wp_unslash($_POST['attribute_description'])
    );

    if ($description === '') {
        $wpdb->delete(
            $table_name,
            ['attribute_id' => $attribute_id],
            ['%d']
        );

        return;
    }

    $wpdb->replace(
        $table_name,
        [
            'attribute_id' => $attribute_id,
            'description' => $description,
        ],
        [
            '%d',
            '%s',
        ]
    );
}

/**
 * Save descriptions when attributes are created.
 *
 * @param int   $attribute_id New attribute ID.
 * @param array $data         Attribute data.
 * @return void
 */
function custom_wc_attribute_description_added(
    int $attribute_id,
    array $data
): void {
    custom_wc_save_attribute_description($attribute_id);
}
add_action(
    'woocommerce_attribute_added',
    'custom_wc_attribute_description_added',
    10,
    2
);

/**
 * Save descriptions when attributes are updated.
 *
 * @param int    $attribute_id Attribute ID.
 * @param array  $data         Attribute data.
 * @param string $old_slug     Previous attribute slug.
 * @return void
 */
function custom_wc_attribute_description_updated(
    int $attribute_id,
    array $data,
    string $old_slug
): void {
    custom_wc_save_attribute_description($attribute_id);
}
add_action(
    'woocommerce_attribute_updated',
    'custom_wc_attribute_description_updated',
    10,
    3
);

/**
 * Delete descriptions when their attributes are deleted.
 *
 * @param int    $attribute_id Attribute ID.
 * @param string $name         Attribute name.
 * @param string $taxonomy     Attribute taxonomy.
 * @return void
 */
function custom_wc_attribute_description_deleted(
    int $attribute_id,
    string $name,
    string $taxonomy
): void {
    global $wpdb;

    $wpdb->delete(
        custom_wc_attribute_description_table(),
        ['attribute_id' => $attribute_id],
        ['%d']
    );
}
add_action(
    'woocommerce_attribute_deleted',
    'custom_wc_attribute_description_deleted',
    10,
    3
);
