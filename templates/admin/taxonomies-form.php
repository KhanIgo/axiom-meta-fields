<?php
if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission
if (isset($_POST['amf_taxonomy_nonce']) && wp_verify_nonce($_POST['amf_taxonomy_nonce'], 'amf_save_taxonomy')) {
    $config = [
        'key' => sanitize_text_field($_POST['key'] ?? ''),
        'labels' => [
            'name' => sanitize_text_field($_POST['label_name'] ?? ''),
            'singular_name' => sanitize_text_field($_POST['label_singular'] ?? ''),
            'menu_name' => sanitize_text_field($_POST['label_menu'] ?? ''),
            'search_items' => sanitize_text_field($_POST['label_search'] ?? ''),
            'popular_items' => sanitize_text_field($_POST['label_popular'] ?? ''),
            'all_items' => sanitize_text_field($_POST['label_all'] ?? ''),
            'edit_item' => sanitize_text_field($_POST['label_edit'] ?? ''),
            'update_item' => sanitize_text_field($_POST['label_update'] ?? ''),
            'add_new_item' => sanitize_text_field($_POST['label_add_new'] ?? ''),
            'new_item_name' => sanitize_text_field($_POST['label_new_name'] ?? ''),
        ],
        'post_types' => isset($_POST['post_types']) ? $_POST['post_types'] : ['post'],
        'public' => isset($_POST['public']) ? (bool) $_POST['public'] : false,
        'show_ui' => isset($_POST['show_ui']) ? (bool) $_POST['show_ui'] : false,
        'show_in_rest' => isset($_POST['show_in_rest']) ? (bool) $_POST['show_in_rest'] : false,
        'rest_base' => sanitize_text_field($_POST['rest_base'] ?? ''),
        'hierarchical' => isset($_POST['hierarchical']) ? (bool) $_POST['hierarchical'] : false,
        'show_admin_column' => isset($_POST['show_admin_column']) ? (bool) $_POST['show_admin_column'] : false,
        'meta_box' => sanitize_text_field($_POST['meta_box'] ?? 'default'),
        'show_in_nav_menus' => isset($_POST['show_in_nav_menus']) ? (bool) $_POST['show_in_nav_menus'] : false,
        'show_tagcloud' => isset($_POST['show_tagcloud']) ? (bool) $_POST['show_tagcloud'] : false,
        'show_in_quick_edit' => isset($_POST['show_in_quick_edit']) ? (bool) $_POST['show_in_quick_edit'] : false,
    ];

    // Clean empty labels
    $config['labels'] = array_filter($config['labels']);

    if (!empty($config['key'])) {
        amf_register_taxonomy($config);
        
        // Save to database
        $saved = get_option('amf_taxonomies', []);
        $saved[$config['key']] = $config;
        update_option('amf_taxonomies', $saved);

        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Taxonomy registered successfully!', 'amf') . '</p></div>';
    } else {
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Taxonomy key is required.', 'amf') . '</p></div>';
    }
}

// Default values
$defaults = [
    'key' => '',
    'label_name' => '',
    'label_singular' => '',
    'label_menu' => '',
    'label_search' => 'Search',
    'label_popular' => 'Popular',
    'label_all' => 'All',
    'label_edit' => 'Edit',
    'label_update' => 'Update',
    'label_add_new' => 'Add New',
    'label_new_name' => 'New Name',
    'post_types' => ['post'],
    'public' => true,
    'show_ui' => true,
    'show_in_rest' => true,
    'rest_base' => '',
    'hierarchical' => true,
    'show_admin_column' => true,
    'meta_box' => 'default',
    'show_in_nav_menus' => true,
    'show_tagcloud' => true,
    'show_in_quick_edit' => true,
];

// Get available dashicons
$dashicons = [
    'dashicons-admin-post',
    'dashicons-admin-page',
    'dashicons-admin-media',
    'dashicons-admin-comments',
    'dashicons-admin-users',
    'dashicons-admin-settings',
    'dashicons-admin-tools',
    'dashicons-admin-home',
    'dashicons-admin-generic',
    'dashicons-businessman',
    'dashicons-businessperson',
    'dashicons-businesswoman',
    'dashicons-carrot',
    'dashicons-building',
    'dashicons-store',
    'dashicons-album',
    'dashicons-portfolio',
    'dashicons-heart',
    'dashicons-star-filled',
    'dashicons-star-empty',
    'dashicons-flag',
    'dashicons-location',
    'dashicons-location-alt',
    'dashicons-camera',
    'dashicons-images-alt',
    'dashicons-video-alt',
    'dashicons-calendar',
    'dashicons-clock',
    'dashicons-database',
    'dashicons-chart-line',
    'dashicons-chart-bar',
    'dashicons-chart-pie',
];

// Get available post types
$available_post_types = get_post_types(['public' => true], 'objects');
?>

<div class="wrap amf-taxonomy-form">
    <h1>
        <?php esc_html_e('Add New Taxonomy', 'amf'); ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=amf-taxonomies')); ?>" class="page-title-action">
            <?php esc_html_e('Back to List', 'amf'); ?>
        </a>
    </h1>

    <form method="post" action="">
        <?php wp_nonce_field('amf_save_taxonomy', 'amf_taxonomy_nonce'); ?>

        <div class="amf-form-grid">
            <!-- Basic Settings -->
            <div class="amf-form-section">
                <h2><?php esc_html_e('Basic Settings', 'amf'); ?></h2>

                <div class="amf-form-field">
                    <label for="key"><?php esc_html_e('Taxonomy Key', 'amf'); ?> *</label>
                    <input type="text" id="key" name="key" class="regular-text" required 
                           value="<?php echo esc_attr($defaults['key']); ?>" 
                           placeholder="<?php esc_attr_e('e.g., genre, topic, collection'); ?>">
                    <p class="description"><?php esc_html_e('Lowercase, no spaces. Use underscores for multi-word keys.', 'amf'); ?></p>
                </div>

                <div class="amf-form-field">
                    <label for="label_name"><?php esc_html_e('Name (Plural)', 'amf'); ?></label>
                    <input type="text" id="label_name" name="label_name" class="regular-text" 
                           value="<?php echo esc_attr($defaults['label_name']); ?>" 
                           placeholder="<?php esc_attr_e('e.g., Genres'); ?>">
                </div>

                <div class="amf-form-field">
                    <label for="label_singular"><?php esc_html_e('Singular Name', 'amf'); ?></label>
                    <input type="text" id="label_singular" name="label_singular" class="regular-text" 
                           value="<?php echo esc_attr($defaults['label_singular']); ?>" 
                           placeholder="<?php esc_attr_e('e.g., Genre'); ?>">
                </div>

                <div class="amf-form-field">
                    <label for="rest_base"><?php esc_html_e('REST Base', 'amf'); ?></label>
                    <input type="text" id="rest_base" name="rest_base" class="regular-text" 
                           value="<?php echo esc_attr($defaults['rest_base']); ?>" 
                           placeholder="<?php esc_attr_e('e.g., genres (defaults to key)'); ?>">
                </div>
            </div>

            <!-- Post Type Association -->
            <div class="amf-form-section">
                <h2><?php esc_html_e('Post Type Association', 'amf'); ?></h2>

                <div class="amf-form-field">
                    <label><?php esc_html_e('Attach to Post Types', 'amf'); ?></label>
                    <div class="amf-checkbox-group">
                        <?php foreach ($available_post_types as $post_type): ?>
                            <label>
                                <input type="checkbox" name="post_types[]" value="<?php echo esc_attr($post_type->name); ?>" 
                                       <?php echo in_array($post_type->name, $defaults['post_types']) ? 'checked' : ''; ?>>
                                <?php echo esc_html($post_type->label); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <p class="description"><?php esc_html_e('Select which post types this taxonomy should be available for.', 'amf'); ?></p>
                </div>
            </div>

            <!-- Visibility Settings -->
            <div class="amf-form-section">
                <h2><?php esc_html_e('Visibility Settings', 'amf'); ?></h2>

                <div class="amf-form-field amf-checkbox">
                    <label>
                        <input type="checkbox" name="public" value="1" <?php checked($defaults['public']); ?>>
                        <?php esc_html_e('Public', 'amf'); ?>
                    </label>
                    <p class="description"><?php esc_html_e('Visible on the frontend to all users.', 'amf'); ?></p>
                </div>

                <div class="amf-form-field amf-checkbox">
                    <label>
                        <input type="checkbox" name="show_ui" value="1" <?php checked($defaults['show_ui']); ?>>
                        <?php esc_html_e('Show Admin UI', 'amf'); ?>
                    </label>
                    <p class="description"><?php esc_html_e('Generate a default UI for managing this taxonomy.', 'amf'); ?></p>
                </div>

                <div class="amf-form-field amf-checkbox">
                    <label>
                        <input type="checkbox" name="show_in_rest" value="1" <?php checked($defaults['show_in_rest']); ?>>
                        <?php esc_html_e('Show in REST API', 'amf'); ?>
                    </label>
                    <p class="description"><?php esc_html_e('Enable Gutenberg and REST API access.', 'amf'); ?></p>
                </div>

                <div class="amf-form-field amf-checkbox">
                    <label>
                        <input type="checkbox" name="show_admin_column" value="1" <?php checked($defaults['show_admin_column']); ?>>
                        <?php esc_html_e('Show Admin Column', 'amf'); ?>
                    </label>
                    <p class="description"><?php esc_html_e('Add taxonomy column to post type list tables.', 'amf'); ?></p>
                </div>

                <div class="amf-form-field amf-checkbox">
                    <label>
                        <input type="checkbox" name="show_in_nav_menus" value="1" <?php checked($defaults['show_in_nav_menus']); ?>>
                        <?php esc_html_e('Show in Nav Menus', 'amf'); ?>
                    </label>
                    <p class="description"><?php esc_html_e('Make available for navigation menus.', 'amf'); ?></p>
                </div>

                <div class="amf-form-field amf-checkbox">
                    <label>
                        <input type="checkbox" name="show_tagcloud" value="1" <?php checked($defaults['show_tagcloud']); ?>>
                        <?php esc_html_e('Show in Tag Cloud', 'amf'); ?>
                    </label>
                </div>

                <div class="amf-form-field amf-checkbox">
                    <label>
                        <input type="checkbox" name="show_in_quick_edit" value="1" <?php checked($defaults['show_in_quick_edit']); ?>>
                        <?php esc_html_e('Show in Quick Edit', 'amf'); ?>
                    </label>
                    <p class="description"><?php esc_html_e('Show taxonomy options in quick edit box.', 'amf'); ?></p>
                </div>
            </div>

            <!-- Type Settings -->
            <div class="amf-form-section">
                <h2><?php esc_html_e('Type Settings', 'amf'); ?></h2>

                <div class="amf-form-field amf-checkbox">
                    <label>
                        <input type="checkbox" name="hierarchical" value="1" <?php checked($defaults['hierarchical']); ?>>
                        <?php esc_html_e('Hierarchical', 'amf'); ?>
                    </label>
                    <p class="description"><?php esc_html_e('Check for category-like (parent/child) behavior. Uncheck for tag-like behavior.', 'amf'); ?></p>
                </div>

                <div class="amf-form-field">
                    <label for="meta_box"><?php esc_html_e('Meta Box Type', 'amf'); ?></label>
                    <select id="meta_box" name="meta_box" class="regular-text">
                        <option value="default" <?php selected($defaults['meta_box'], 'default'); ?>><?php esc_html_e('Default', 'amf'); ?></option>
                        <option value="none" <?php selected($defaults['meta_box'], 'none'); ?>><?php esc_html_e('None (Hidden)', 'amf'); ?></option>
                        <option value="postbox" <?php selected($defaults['meta_box'], 'postbox'); ?>><?php esc_html_e('Postbox', 'amf'); ?></option>
                    </select>
                </div>
            </div>
        </div>

        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Register Taxonomy', 'amf'); ?>">
            <a href="<?php echo esc_url(admin_url('admin.php?page=amf-taxonomies')); ?>" class="button">
                <?php esc_html_e('Cancel', 'amf'); ?>
            </a>
        </p>
    </form>
</div>

<style>
    .amf-taxonomy-form h1 {
        margin-bottom: 20px;
    }

    .amf-form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .amf-form-section {
        background: #fff;
        padding: 20px;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
    }

    .amf-form-section h2 {
        margin-top: 0;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
        font-size: 18px;
    }

    .amf-form-field {
        margin-bottom: 20px;
    }

    .amf-form-field label {
        display: block;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .amf-form-field .description {
        font-style: italic;
        color: #666;
        margin-top: 5px;
    }

    .amf-checkbox label {
        font-weight: normal !important;
    }

    .amf-checkbox-group {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }

    .amf-checkbox-group label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: normal !important;
    }

    @media (max-width: 782px) {
        .amf-form-grid {
            grid-template-columns: 1fr;
        }

        .amf-checkbox-group {
            grid-template-columns: 1fr;
        }
    }
</style>
