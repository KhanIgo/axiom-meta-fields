<?php
if (!defined('ABSPATH')) {
    exit;
}

// Check user permissions
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.', 'amf'));
}

// Get registered meta boxes
$meta_boxes = amf_get_meta_boxes();
?>

<div class="wrap amf-meta-boxes-page">
    <h1>
        <?php esc_html_e('Meta Boxes', 'amf'); ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=amf-meta-boxes&action=new')); ?>" class="page-title-action">
            <?php esc_html_e('Add New', 'amf'); ?>
        </a>
    </h1>

    <?php if (empty($meta_boxes)): ?>
        <div class="amf-empty-state">
            <h2><?php esc_html_e('No Meta Boxes Yet', 'amf'); ?></h2>
            <p><?php esc_html_e('Create your first meta box to start adding custom fields to your posts.', 'amf'); ?></p>
            <a href="<?php echo esc_url(admin_url('admin.php?page=amf-meta-boxes&action=new')); ?>" class="button button-primary button-hero">
                <?php esc_html_e('Create Meta Box', 'amf'); ?>
            </a>
        </div>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('ID', 'amf'); ?></th>
                    <th><?php esc_html_e('Title', 'amf'); ?></th>
                    <th><?php esc_html_e('Post Types', 'amf'); ?></th>
                    <th><?php esc_html_e('Fields', 'amf'); ?></th>
                    <th><?php esc_html_e('Context', 'amf'); ?></th>
                    <th><?php esc_html_e('Actions', 'amf'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($meta_boxes as $meta_box): ?>
                    <tr>
                        <td><code><?php echo esc_html($meta_box['id']); ?></code></td>
                        <td><strong><?php echo esc_html($meta_box['title']); ?></strong></td>
                        <td>
                            <?php
                            $post_types = implode(', ', $meta_box['post_types'] ?? []);
                            echo esc_html($post_types);
                            ?>
                        </td>
                        <td>
                            <?php
                            $fields_count = count($meta_box['fields'] ?? []);
                            echo esc_html($fields_count);
                            ?>
                        </td>
                        <td><?php echo esc_html($meta_box['context'] ?? 'normal'); ?></td>
                        <td>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=amf-meta-boxes&action=edit&id=' . $meta_box['id'])); ?>">
                                <?php esc_html_e('Edit', 'amf'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="amf-info-box" style="margin-top: 20px;">
        <h3><?php esc_html_e('Programmatic Registration', 'amf'); ?></h3>
        <p><?php esc_html_e('Meta boxes can also be registered programmatically in your theme or plugin:', 'amf'); ?></p>
        <pre style="background: #f5f5f5; padding: 15px; overflow-x: auto;">
amf_register_meta_box([
    'id' => 'my_meta_box',
    'title' => __('My Meta Box', 'amf'),
    'post_types' => ['post', 'page'],
    'fields' => [
        [
            'id' => 'my_field',
            'type' => 'text',
            'name' => __('My Field', 'amf'),
        ],
    ],
]);</pre>
        <p>
            <a href="https://github.com/KhanIgo/axiom-meta-fields/wiki/Creating-Meta-Boxes" target="_blank">
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
