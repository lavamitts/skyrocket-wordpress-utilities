<?php
/**
 * WooCommerce Global Attribute Descriptions
 *
 * Adds an internal description field to WooCommerce global product
 * attributes, allowing administrators to record the purpose or intended
 * use of individual attributes.
 *
 * Descriptions are stored separately from WooCommerce's native attribute
 * data in a dedicated database table, associated with the corresponding
 * WooCommerce attribute IDs.
 *
 * Features:
 *
 * - Add Attribute screen:
 *   Adds a description field when creating a global product attribute.
 *
 * - Edit Attribute screen:
 *   Displays the existing description when editing a global product
 *   attribute and allows it to be updated.
 *
 * - Description storage:
 *   Stores descriptions in a dedicated database table named
 *   `woocommerce_attribute_descriptions`, using the WordPress database
 *   prefix.
 *
 * - Description retrieval:
 *   Provides a function to retrieve the description associated with
 *   a WooCommerce attribute ID.
 *
 * - Description updates:
 *   Saves descriptions when attributes are created or updated.
 *   Empty descriptions remove the corresponding database record.
 *
 * - Attribute deletion:
 *   Automatically removes the associated description when a global
 *   product attribute is deleted.
 *
 * - Database installation:
 *   Creates the custom database table using `dbDelta()` and tracks
 *   the installed schema version using a WordPress option.
 *
 * All descriptions are for administrative reference only and are never
 * displayed on the frontend.
 *
 * Dependencies:
 *
 * - WooCommerce must be installed, active and loaded.
 * - WordPress core database, options and sanitisation APIs.
 *
 * WooCommerce dependency handling:
 *
 * - All functionality is registered through a bootstrap function hooked
 *   to `plugins_loaded`.
 * - Checks for the WooCommerce class before registering any functionality.
 * - No hooks are registered and no database operations are performed
 *   when WooCommerce is unavailable.
 *
 * Hooks:
 *
 * - `plugins_loaded`:
 *   Checks for WooCommerce and registers the functionality.
 * - `init`:
 *   Installs or updates the custom database table.
 * - `woocommerce_after_add_attribute_fields`:
 *   Displays the description field when creating an attribute.
 * - `woocommerce_after_edit_attribute_fields`:
 *   Displays the description field when editing an attribute.
 * - `woocommerce_attribute_added`:
 *   Saves the description when an attribute is created.
 * - `woocommerce_attribute_updated`:
 *   Saves the description when an attribute is updated.
 * - `woocommerce_attribute_deleted`:
 *   Deletes the description when an attribute is deleted.
 *
 * @package Skyrocket
 */

defined('ABSPATH') || exit;


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
