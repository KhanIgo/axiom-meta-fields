<?php
if (!defined('ABSPATH')) {
    exit;
}

// Check user permissions
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.', 'amf'));
}

// Handle form submission
$saved = false;
if (isset($_POST['amf_settings_submit']) && wp_verify_nonce($_POST['amf_settings_nonce'], 'amf_settings_save')) {
    $settings = [
        'debug_mode' => isset($_POST['amf_debug_mode']) ? (bool) $_POST['amf_debug_mode'] : false,
        'enable_gutenberg' => isset($_POST['amf_enable_gutenberg']) ? (bool) $_POST['amf_enable_gutenberg'] : true,
        'enable_rest_api' => isset($_POST['amf_enable_rest_api']) ? (bool) $_POST['amf_enable_rest_api'] : true,
        'enable_graphql' => isset($_POST['amf_enable_graphql']) ? (bool) $_POST['amf_enable_graphql'] : false,
        'cache_enabled' => isset($_POST['amf_cache_enabled']) ? (bool) $_POST['amf_cache_enabled'] : true,
        'cache_ttl' => absint($_POST['amf_cache_ttl'] ?? 3600),
    ];

    update_option('amf_settings', $settings);
    $saved = true;
}

// Get current settings
$settings = get_option('amf_settings', []);
$debug_mode = $settings['debug_mode'] ?? false;
$enable_gutenberg = $settings['enable_gutenberg'] ?? true;
$enable_rest_api = $settings['enable_rest_api'] ?? true;
$enable_graphql = $settings['enable_graphql'] ?? false;
$cache_enabled = $settings['cache_enabled'] ?? true;
$cache_ttl = $settings['cache_ttl'] ?? 3600;
?>

<div class="wrap amf-settings-page">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php if ($saved): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e('Settings saved successfully.', 'amf'); ?></p>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <?php wp_nonce_field('amf_settings_save', 'amf_settings_nonce'); ?>

        <h2><?php esc_html_e('General Settings', 'amf'); ?></h2>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="amf_debug_mode"><?php esc_html_e('Debug Mode', 'amf'); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" id="amf_debug_mode" name="amf_debug_mode" value="1" <?php checked($debug_mode); ?>>
                        <?php esc_html_e('Enable debug mode', 'amf'); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e('When enabled, the plugin will log additional information for troubleshooting.', 'amf'); ?>
                    </p>
                </td>
            </tr>
        </table>

        <h2><?php esc_html_e('Features', 'amf'); ?></h2>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="amf_enable_gutenberg"><?php esc_html_e('Gutenberg Support', 'amf'); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" id="amf_enable_gutenberg" name="amf_enable_gutenberg" value="1" <?php checked($enable_gutenberg); ?>>
                        <?php esc_html_e('Enable Gutenberg/Block Editor integration', 'amf'); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e('Allow meta box fields to be edited in the Block Editor.', 'amf'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="amf_enable_rest_api"><?php esc_html_e('REST API', 'amf'); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" id="amf_enable_rest_api" name="amf_enable_rest_api" value="1" <?php checked($enable_rest_api); ?>>
                        <?php esc_html_e('Enable REST API endpoints', 'amf'); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e('Allow access to meta data via REST API.', 'amf'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="amf_enable_graphql"><?php esc_html_e('GraphQL', 'amf'); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" id="amf_enable_graphql" name="amf_enable_graphql" value="1" <?php checked($enable_graphql); ?>>
                        <?php esc_html_e('Enable GraphQL integration (requires WPGraphQL plugin)', 'amf'); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e('Allow access to meta data via GraphQL.', 'amf'); ?>
                    </p>
                </td>
            </tr>
        </table>

        <h2><?php esc_html_e('Performance', 'amf'); ?></h2>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="amf_cache_enabled"><?php esc_html_e('Enable Caching', 'amf'); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" id="amf_cache_enabled" name="amf_cache_enabled" value="1" <?php checked($cache_enabled); ?>>
                        <?php esc_html_e('Enable meta caching for improved performance', 'amf'); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e('Cache meta values to reduce database queries.', 'amf'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="amf_cache_ttl"><?php esc_html_e('Cache TTL', 'amf'); ?></label>
                </th>
                <td>
                    <input type="number" id="amf_cache_ttl" name="amf_cache_ttl" value="<?php echo esc_attr($cache_ttl); ?>" class="small-text" min="60" step="60">
                    <?php esc_html_e('seconds', 'amf'); ?>
                    <p class="description">
                        <?php esc_html_e('Time-to-live for cached values. Minimum 60 seconds.', 'amf'); ?>
                    </p>
                </td>
            </tr>
        </table>

        <?php submit_button(__('Save Settings', 'amf')); ?>
    </form>

    <hr>

    <div class="amf-documentation-link">
        <h3><?php esc_html_e('Need Help?', 'amf'); ?></h3>
        <p>
            <?php esc_html_e('Check out our documentation for more information:', 'amf'); ?>
            <a href="https://github.com/KhanIgo/axiom-meta-fields/wiki/Settings" target="_blank">
                <?php esc_html_e('Settings Documentation', 'amf'); ?>
            </a>
        </p>
    </div>
</div>

<style>
    .amf-documentation-link {
        margin-top: 30px;
        padding: 20px;
        background: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .amf-documentation-link h3 {
        margin-top: 0;
    }
</style>
