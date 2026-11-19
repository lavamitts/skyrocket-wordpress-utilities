<?php
/**
 * Disable Comments Settings Tab
 *
 * Provides the settings UI and runtime logic to completely disable
 * comments, pingbacks/trackbacks, comment feeds, REST API comment
 * endpoints, and admin comment interfaces across the site.
 *
 * @package Skyrocket
 */

defined('ABSPATH') || exit;

$swu_options = get_option('swu_options', []);
$disable_comments_enabled = !empty($swu_options['disable_comments_enabled']);
?>

<h2>Disable Comments</h2>
<p>
    When enabled, comments, trackbacks, and comment feeds are disabled across the site,
    and comment menus, widgets, and direct admin access are hidden or blocked.
</p>

<table class="form-table" role="presentation">
    <tr>
        <th scope="row">
            <label for="swu_disable_comments_enabled">Disable comments globally</label>
        </th>
        <td>
            <label for="swu_disable_comments_enabled">
                <input type="checkbox" id="swu_disable_comments_enabled" name="swu_options[disable_comments_enabled]"
                    value="1" <?php checked($disable_comments_enabled, true); ?>
                >
                Completely disable comments and pingbacks across WordPress
            </label>
            <p class="description">
                Existing comments in the database are not deleted; they are simply hidden and closed.
            </p>
        </td>
    </tr>
</table>