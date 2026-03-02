<?php
if (!defined('ABSPATH')) {
    exit;
}

// Check user permissions
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.', 'amf'));
}

// Get action
$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';

// Handle different actions
if ($action === 'new') {
    include AMF_PLUGIN_DIR . 'templates/admin/post-types-form.php';
    return;
}

// Get registered post types
$post_types = amf_get_post_types();

// Get WordPress built-in post types
$wp_post_types = get_post_types(['public' => true], 'objects');
?>

<div class="wrap amf-post-types-page">
    <h1>
        <?php esc_html_e('Post Types', 'amf'); ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=amf-post-types&action=new')); ?>" class="page-title-action">
            <?php esc_html_e('Add New', 'amf'); ?>
        </a>
    </h1>

    <h2><?php esc_html_e('Registered Post Types', 'amf'); ?></h2>

    <?php if (empty($post_types)): ?>
        <div class="amf-empty-state">
            <h2><?php esc_html_e('No Custom Post Types Yet', 'amf'); ?></h2>
            <p><?php esc_html_e('Create your first custom post type to organize your content.', 'amf'); ?></p>
            <a href="<?php echo esc_url(admin_url('admin.php?page=amf-post-types&action=new')); ?>" class="button button-primary button-hero">
                <?php esc_html_e('Create Post Type', 'amf'); ?>
            </a>
        </div>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Key', 'amf'); ?></th>
                    <th><?php esc_html_e('Name', 'amf'); ?></th>
                    <th><?php esc_html_e('Singular Name', 'amf'); ?></th>
                    <th><?php esc_html_e('Public', 'amf'); ?></th>
                    <th><?php esc_html_e('REST API', 'amf'); ?></th>
                    <th><?php esc_html_e('Actions', 'amf'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($post_types as $post_type): ?>
                    <tr>
                        <td><code><?php echo esc_html($post_type['key']); ?></code></td>
                        <td><strong><?php echo esc_html($post_type['labels']['name'] ?? ''); ?></strong></td>
                        <td><?php echo esc_html($post_type['labels']['singular_name'] ?? ''); ?></td>
                        <td>
                            <?php echo !empty($post_type['public'])
                                ? '<span style="color: green;">' . esc_html__('Yes', 'amf') . '</span>'
                                : '<span style="color: #999;">' . esc_html__('No', 'amf') . '</span>'; ?>
                        </td>
                        <td>
                            <?php echo !empty($post_type['show_in_rest'])
                                ? '<span style="color: green;">' . esc_html__('Yes', 'amf') . '</span>'
                                : '<span style="color: #999;">' . esc_html__('No', 'amf') . '</span>'; ?>
                        </td>
                        <td>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=amf-post-types&action=edit&id=' . $post_type['key'])); ?>">
                                <?php esc_html_e('Edit', 'amf'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <h2 style="margin-top: 40px;"><?php esc_html_e('Built-in Post Types', 'amf'); ?></h2>

    <table class="wp-list-table widefat fixed striped" style="opacity: 0.7;">
        <thead>
            <tr>
                <th><?php esc_html_e('Key', 'amf'); ?></th>
                <th><?php esc_html_e('Name', 'amf'); ?></th>
                <th><?php esc_html_e('Description', 'amf'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($wp_post_types as $post_type): ?>
                <?php if (in_array($post_type->name, ['post', 'page', 'attachment'])): ?>
                    <tr>
                        <td><code><?php echo esc_html($post_type->name); ?></code></td>
                        <td><strong><?php echo esc_html($post_type->label); ?></strong></td>
                        <td><?php echo esc_html($post_type->description); ?></td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="amf-info-box" style="margin-top: 20px;">
        <h3><?php esc_html_e('Programmatic Registration', 'amf'); ?></h3>
        <p><?php esc_html_e('Post types can also be registered programmatically:', 'amf'); ?></p>
        <pre style="background: #f5f5f5; padding: 15px; overflow-x: auto;">
amf_register_post_type([
    'key' => 'product',
    'labels' => [
        'name' => __('Products', 'amf'),
        'singular_name' => __('Product', 'amf'),
    ],
    'public' => true,
    'show_in_rest' => true,
    'supports' => ['title', 'editor', 'thumbnail'],
]);</pre>
        <p>
            <a href="https://github.com/KhanIgo/axiom-meta-fields/wiki/Custom-Post-Types" target="_blank">
                <?php esc_html_e('Learn more in the documentation', 'amf'); ?>
            </a>
        </p>
    </div>
</div>

<style>
    .amf-empty-state {
        text-align: center;
        padding: 50px 20px;
        background: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-top: 20px;
    }

    .amf-empty-state h2 {
        margin-top: 0;
    }

    .amf-info-box {
        background: #fff;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .amf-info-box h3 {
        margin-top: 0;
    }
</style>
