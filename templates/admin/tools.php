<?php
if (!defined('ABSPATH')) {
    exit;
}

// Check user permissions
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.', 'amf'));
}

// Handle form submissions
$message = '';
$error = '';

if (isset($_POST['amf_action']) && wp_verify_nonce($_POST['amf_tools_nonce'], 'amf_tools_action')) {
    switch ($_POST['amf_action']) {
        case 'clear_cache':
            amf_clear_cache();
            $message = __('Cache cleared successfully.', 'amf');
            break;

        case 'flush_rewrite_rules':
            flush_rewrite_rules();
            $message = __('Rewrite rules flushed successfully.', 'amf');
            break;

        case 'export_meta':
            // Export meta boxes configuration
            $meta_boxes = amf_get_meta_boxes();
            $export_data = [
                'meta_boxes' => $meta_boxes,
                'exported_at' => current_time('mysql'),
            ];
            // In a real implementation, this would trigger a file download
            $message = __('Meta boxes configuration ready for export. (Implementation pending)', 'amf');
            break;

        case 'reset_settings':
            delete_option('amf_settings');
            $message = __('Settings reset to defaults.', 'amf');
            break;

        case 'regenerate_thumbnails':
            // Placeholder for thumbnail regeneration
            $message = __('Thumbnail regeneration feature coming soon.', 'amf');
            break;
    }
}

// Get system information
$php_version = PHP_VERSION;
$wp_version = get_bloginfo('version');
$plugin_version = AMF_VERSION;
$memory_limit = ini_get('memory_limit');
$max_execution_time = ini_get('max_execution_time');
?>

<div class="wrap amf-tools-page">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php if ($message): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html($message); ?></p>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo esc_html($error); ?></p>
        </div>
    <?php endif; ?>

    <div class="amf-tools-sections">
        <!-- Cache Management -->
        <div class="amf-tool-card">
            <h2><?php esc_html_e('Cache Management', 'amf'); ?></h2>
            <p><?php esc_html_e('Clear plugin cache to refresh meta values.', 'amf'); ?></p>

            <form method="post">
                <?php wp_nonce_field('amf_tools_action', 'amf_tools_nonce'); ?>
                <input type="hidden" name="amf_action" value="clear_cache">

                <p>
                    <button type="submit" class="button button-secondary">
                        <?php esc_html_e('Clear Cache', 'amf'); ?>
                    </button>
                </p>
                <p class="description">
                    <?php esc_html_e('This will clear all cached meta values. They will be regenerated on next access.', 'amf'); ?>
                </p>
            </form>
        </div>

        <!-- Rewrite Rules -->
        <div class="amf-tool-card">
            <h2><?php esc_html_e('Rewrite Rules', 'amf'); ?></h2>
            <p><?php esc_html_e('Flush rewrite rules if you\'re experiencing 404 errors with custom post types.', 'amf'); ?></p>

            <form method="post">
                <?php wp_nonce_field('amf_tools_action', 'amf_tools_nonce'); ?>
                <input type="hidden" name="amf_action" value="flush_rewrite_rules">

                <p>
                    <button type="submit" class="button button-secondary">
                        <?php esc_html_e('Flush Rewrite Rules', 'amf'); ?>
                    </button>
                </p>
                <p class="description">
                    <?php esc_html_e('This will regenerate all WordPress rewrite rules.', 'amf'); ?>
                </p>
            </form>
        </div>

        <!-- Export/Import -->
        <div class="amf-tool-card">
            <h2><?php esc_html_e('Export & Import', 'amf'); ?></h2>
            <p><?php esc_html_e('Export your meta boxes configuration for backup or migration.', 'amf'); ?></p>

            <form method="post">
                <?php wp_nonce_field('amf_tools_action', 'amf_tools_nonce'); ?>
                <input type="hidden" name="amf_action" value="export_meta">

                <p>
                    <button type="submit" class="button button-secondary">
                        <?php esc_html_e('Export Meta Boxes', 'amf'); ?>
                    </button>
                </p>
                <p class="description">
                    <?php esc_html_e('Export all registered meta boxes as JSON.', 'amf'); ?>
                </p>
            </form>

            <hr>

            <p class="description">
                <?php esc_html_e('Import feature coming soon.', 'amf'); ?>
            </p>
            <button class="button button-secondary" disabled>
                <?php esc_html_e('Import Meta Boxes', 'amf'); ?>
            </button>
        </div>

        <!-- Reset Settings -->
        <div class="amf-tool-card">
            <h2><?php esc_html_e('Reset Settings', 'amf'); ?></h2>
            <p><?php esc_html_e('Reset plugin settings to default values.', 'amf'); ?></p>

            <form method="post" onsubmit="return confirm('<?php esc_attr_e('Are you sure you want to reset all settings? This cannot be undone.', 'amf'); ?>')">
                <?php wp_nonce_field('amf_tools_action', 'amf_tools_nonce'); ?>
                <input type="hidden" name="amf_action" value="reset_settings">

                <p>
                    <button type="submit" class="button button-link-delete">
                        <?php esc_html_e('Reset Settings', 'amf'); ?>
                    </button>
                </p>
                <p class="description" style="color: #d63638;">
                    <?php esc_html_e('Warning: This will reset all plugin settings to their default values.', 'amf'); ?>
                </p>
            </form>
        </div>

        <!-- Media Tools -->
        <div class="amf-tool-card">
            <h2><?php esc_html_e('Media Tools', 'amf'); ?></h2>
            <p><?php esc_html_e('Regenerate thumbnails for images uploaded to the site.', 'amf'); ?></p>

            <form method="post">
                <?php wp_nonce_field('amf_tools_action', 'amf_tools_nonce'); ?>
                <input type="hidden" name="amf_action" value="regenerate_thumbnails">

                <p>
                    <button type="submit" class="button button-secondary">
                        <?php esc_html_e('Regenerate Thumbnails', 'amf'); ?>
                    </button>
                </p>
                <p class="description">
                    <?php esc_html_e('This will regenerate all image thumbnail sizes.', 'amf'); ?>
                </p>
            </form>
        </div>

        <!-- System Information -->
        <div class="amf-tool-card amf-system-info">
            <h2><?php esc_html_e('System Information', 'amf'); ?></h2>

            <table class="widefat">
                <tbody>
                    <tr>
                        <th><?php esc_html_e('Axiom Meta Fields Version', 'amf'); ?>:</th>
                        <td><?php echo esc_html($plugin_version); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('WordPress Version', 'amf'); ?>:</th>
                        <td><?php echo esc_html($wp_version); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('PHP Version', 'amf'); ?>:</th>
                        <td><?php echo esc_html($php_version); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Memory Limit', 'amf'); ?>:</th>
                        <td><?php echo esc_html($memory_limit); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Max Execution Time', 'amf'); ?>:</th>
                        <td><?php echo esc_html($max_execution_time); ?> <?php esc_html_e('seconds', 'amf'); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Debug Mode', 'amf'); ?>:</th>
                        <td>
                            <?php
                            $debug_mode = amf_is_debug();
                            echo $debug_mode
                                ? '<span style="color: green;">' . esc_html__('Enabled', 'amf') . '</span>'
                                : '<span style="color: #999;">' . esc_html__('Disabled', 'amf') . '</span>';
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Cache Enabled', 'amf'); ?>:</th>
                        <td>
                            <?php
                            $cache_enabled = amf_is_enabled('cache_enabled');
                            echo $cache_enabled
                                ? '<span style="color: green;">' . esc_html__('Yes', 'amf') . '</span>'
                                : '<span style="color: #999;">' . esc_html__('No', 'amf') . '</span>';
                            ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .amf-tools-sections {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .amf-tool-card {
        background: #fff;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .amf-tool-card h2 {
        margin-top: 0;
        font-size: 18px;
    }

    .amf-tool-card hr {
        margin: 20px 0;
        border: none;
        border-top: 1px solid #eee;
    }

    .amf-tool-card button[disabled] {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .amf-system-info table {
        margin-top: 10px;
    }

    .amf-system-info th {
        text-align: left;
        padding: 8px;
        width: 40%;
    }

    .amf-system-info td {
        padding: 8px;
    }

    @media (max-width: 782px) {
        .amf-tools-sections {
            grid-template-columns: 1fr;
        }
    }
</style>
