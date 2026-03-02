<?php
if (!defined('ABSPATH')) {
    exit;
}

// Check user permissions
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.', 'amf'));
}

// Get statistics
$meta_boxes_count = count(get_option('amf_meta_boxes', []));
$post_types_count = count(get_option('amf_post_types', []));
$taxonomies_count = count(get_option('amf_taxonomies', []));
?>

<div class="wrap amf-dashboard">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="amf-dashboard-welcome">
        <h2><?php esc_html_e('Welcome to Axiom Meta Fields', 'amf'); ?></h2>
        <p><?php esc_html_e('Create and manage custom post types, taxonomies, meta boxes, and fields with ease.', 'amf'); ?></p>

        <div class="amf-dashboard-stats">
            <div class="amf-stat-card">
                <div class="amf-stat-number"><?php echo esc_html($meta_boxes_count); ?></div>
                <div class="amf-stat-label"><?php esc_html_e('Meta Boxes', 'amf'); ?></div>
                <a href="<?php echo esc_url(admin_url('admin.php?page=amf-meta-boxes')); ?>" class="button button-small">
                    <?php esc_html_e('Manage', 'amf'); ?>
                </a>
            </div>

            <div class="amf-stat-card">
                <div class="amf-stat-number"><?php echo esc_html($post_types_count); ?></div>
                <div class="amf-stat-label"><?php esc_html_e('Post Types', 'amf'); ?></div>
                <a href="<?php echo esc_url(admin_url('admin.php?page=amf-post-types')); ?>" class="button button-small">
                    <?php esc_html_e('Manage', 'amf'); ?>
                </a>
            </div>

            <div class="amf-stat-card">
                <div class="amf-stat-number"><?php echo esc_html($taxonomies_count); ?></div>
                <div class="amf-stat-label"><?php esc_html_e('Taxonomies', 'amf'); ?></div>
                <a href="<?php echo esc_url(admin_url('admin.php?page=amf-taxonomies')); ?>" class="button button-small">
                    <?php esc_html_e('Manage', 'amf'); ?>
                </a>
            </div>

            <div class="amf-stat-card">
                <div class="amf-stat-number">30+</div>
                <div class="amf-stat-label"><?php esc_html_e('Field Types', 'amf'); ?></div>
                <a href="https://github.com/KhanIgo/axiom-meta-fields/wiki/Field-Types" target="_blank" class="button button-small">
                    <?php esc_html_e('Learn More', 'amf'); ?>
                </a>
            </div>
        </div>
    </div>

    <div class="amf-dashboard-quick-start">
        <h2><?php esc_html_e('Quick Start Guide', 'amf'); ?></h2>

        <div class="amf-quick-start-steps">
            <div class="amf-step">
                <h3><?php esc_html_e('1. Create a Meta Box', 'amf'); ?></h3>
                <p><?php esc_html_e('Start by creating a meta box to hold your custom fields.', 'amf'); ?></p>
                <a href="<?php echo esc_url(admin_url('admin.php?page=amf-meta-boxes&action=new')); ?>" class="button button-primary">
                    <?php esc_html_e('Create Meta Box', 'amf'); ?>
                </a>
            </div>

            <div class="amf-step">
                <h3><?php esc_html_e('2. Add Fields', 'amf'); ?></h3>
                <p><?php esc_html_e('Add fields to your meta box from over 30 available field types.', 'amf'); ?></p>
            </div>

            <div class="amf-step">
                <h3><?php esc_html_e('3. Assign to Post Types', 'amf'); ?></h3>
                <p><?php esc_html_e('Choose which post types should display your meta box.', 'amf'); ?></p>
            </div>

            <div class="amf-step">
                <h3><?php esc_html_e('4. Use in Templates', 'amf'); ?></h3>
                <p><?php esc_html_e('Display field values in your theme using template tags.', 'amf'); ?></p>
                <a href="https://github.com/KhanIgo/axiom-meta-fields/wiki/Template-Tags" target="_blank" class="button">
                    <?php esc_html_e('Documentation', 'amf'); ?>
                </a>
            </div>
        </div>
    </div>

    <div class="amf-dashboard-resources">
        <h2><?php esc_html_e('Resources', 'amf'); ?></h2>

        <ul class="amf-resource-list">
            <li>
                <a href="https://github.com/KhanIgo/axiom-meta-fields/wiki" target="_blank">
                    <?php esc_html_e('Documentation', 'amf'); ?>
                </a>
                - <?php esc_html_e('Complete plugin documentation', 'amf'); ?>
            </li>
            <li>
                <a href="https://github.com/KhanIgo/axiom-meta-fields/wiki/Field-Types" target="_blank">
                    <?php esc_html_e('Field Types Reference', 'amf'); ?>
                </a>
                - <?php esc_html_e('All available field types and options', 'amf'); ?>
            </li>
            <li>
                <a href="https://github.com/KhanIgo/axiom-meta-fields/wiki/Template-Tags" target="_blank">
                    <?php esc_html_e('Template Tags', 'amf'); ?>
                </a>
                - <?php esc_html_e('Display meta values in your theme', 'amf'); ?>
            </li>
            <li>
                <a href="https://github.com/KhanIgo/axiom-meta-fields/wiki/REST-API" target="_blank">
                    <?php esc_html_e('REST API', 'amf'); ?>
                </a>
                - <?php esc_html_e('Access data via REST API', 'amf'); ?>
            </li>
            <li>
                <a href="https://github.com/KhanIgo/axiom-meta-fields/issues" target="_blank">
                    <?php esc_html_e('Support', 'amf'); ?>
                </a>
                - <?php esc_html_e('Report issues and request features', 'amf'); ?>
            </li>
        </ul>
    </div>
</div>

<style>
    .amf-dashboard-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin: 30px 0;
    }

    .amf-stat-card {
        background: #fff;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-align: center;
    }

    .amf-stat-number {
        font-size: 36px;
        font-weight: 300;
        color: #2271b1;
    }

    .amf-stat-label {
        color: #666;
        margin: 10px 0;
    }

    .amf-quick-start-steps {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .amf-step {
        background: #f9f9f9;
        padding: 20px;
        border-radius: 4px;
    }

    .amf-step h3 {
        margin-top: 0;
    }

    .amf-resource-list {
        list-style: none;
        padding: 0;
    }

    .amf-resource-list li {
        padding: 10px 0;
        border-bottom: 1px solid #eee;
    }

    .amf-resource-list li:last-child {
        border-bottom: none;
    }
</style>
