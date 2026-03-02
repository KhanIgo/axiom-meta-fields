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
    include AMF_PLUGIN_DIR . 'templates/admin/taxonomies-form.php';
    return;
}

// Get registered taxonomies
$taxonomies = amf_get_taxonomies();

// Get WordPress built-in taxonomies
$wp_taxonomies = get_taxonomies(['public' => true], 'objects');
?>

<div class="wrap amf-taxonomies-page">
    <h1>
        <?php esc_html_e('Taxonomies', 'amf'); ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=amf-taxonomies&action=new')); ?>" class="page-title-action">
            <?php esc_html_e('Add New', 'amf'); ?>
        </a>
    </h1>

    <h2><?php esc_html_e('Registered Taxonomies', 'amf'); ?></h2>

    <?php if (empty($taxonomies)): ?>
        <div class="amf-empty-state">
            <h2><?php esc_html_e('No Custom Taxonomies Yet', 'amf'); ?></h2>
            <p><?php esc_html_e('Create your first custom taxonomy to classify your content.', 'amf'); ?></p>
            <a href="<?php echo esc_url(admin_url('admin.php?page=amf-taxonomies&action=new')); ?>" class="button button-primary button-hero">
                <?php esc_html_e('Create Taxonomy', 'amf'); ?>
            </a>
        </div>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Key', 'amf'); ?></th>
                    <th><?php esc_html_e('Name', 'amf'); ?></th>
                    <th><?php esc_html_e('Post Types', 'amf'); ?></th>
                    <th><?php esc_html_e('Hierarchical', 'amf'); ?></th>
                    <th><?php esc_html_e('REST API', 'amf'); ?></th>
                    <th><?php esc_html_e('Actions', 'amf'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($taxonomies as $taxonomy): ?>
                    <tr>
                        <td><code><?php echo esc_html($taxonomy['key']); ?></code></td>
                        <td><strong><?php echo esc_html($taxonomy['labels']['name'] ?? ''); ?></strong></td>
                        <td>
                            <?php
                            $post_types = implode(', ', $taxonomy['post_types'] ?? []);
                            echo esc_html($post_types);
                            ?>
                        </td>
                        <td>
                            <?php echo !empty($taxonomy['hierarchical'])
                                ? esc_html__('Yes (Category-like)', 'amf')
                                : esc_html__('No (Tag-like)', 'amf'); ?>
                        </td>
                        <td>
                            <?php echo !empty($taxonomy['show_in_rest'])
                                ? '<span style="color: green;">' . esc_html__('Yes', 'amf') . '</span>'
                                : '<span style="color: #999;">' . esc_html__('No', 'amf') . '</span>'; ?>
                        </td>
                        <td>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=amf-taxonomies&action=edit&id=' . $taxonomy['key'])); ?>">
                                <?php esc_html_e('Edit', 'amf'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <h2 style="margin-top: 40px;"><?php esc_html_e('Built-in Taxonomies', 'amf'); ?></h2>

    <table class="wp-list-table widefat fixed striped" style="opacity: 0.7;">
        <thead>
            <tr>
                <th><?php esc_html_e('Key', 'amf'); ?></th>
                <th><?php esc_html_e('Name', 'amf'); ?></th>
                <th><?php esc_html_e('Post Types', 'amf'); ?></th>
                <th><?php esc_html_e('Type', 'amf'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($wp_taxonomies as $taxonomy): ?>
                <?php if (in_array($taxonomy->name, ['category', 'post_tag'])): ?>
                    <tr>
                        <td><code><?php echo esc_html($taxonomy->name); ?></code></td>
                        <td><strong><?php echo esc_html($taxonomy->label); ?></strong></td>
                        <td><?php echo esc_html(implode(', ', $taxonomy->object_type)); ?></td>
                        <td>
                            <?php echo $taxonomy->hierarchical
                                ? esc_html__('Hierarchical (Category-like)', 'amf')
                                : esc_html__('Non-Hierarchical (Tag-like)', 'amf'); ?>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="amf-info-box" style="margin-top: 20px;">
        <h3><?php esc_html_e('Programmatic Registration', 'amf'); ?></h3>
        <p><?php esc_html_e('Taxonomies can also be registered programmatically:', 'amf'); ?></p>
        <pre style="background: #f5f5f5; padding: 15px; overflow-x: auto;">
// Hierarchical (Category-like)
amf_register_taxonomy([
    'key' => 'genre',
    'labels' => [
        'name' => __('Genres', 'amf'),
        'singular_name' => __('Genre', 'amf'),
    ],
    'post_types' => ['book'],
    'hierarchical' => true,
]);

// Non-Hierarchical (Tag-like)
amf_register_taxonomy([
    'key' => 'book_tag',
    'labels' => [
        'name' => __('Book Tags', 'amf'),
        'singular_name' => __('Book Tag', 'amf'),
    ],
    'post_types' => ['book'],
    'hierarchical' => false,
]);</pre>
        <p>
            <a href="https://github.com/KhanIgo/axiom-meta-fields/wiki/Custom-Taxonomies" target="_blank">
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
