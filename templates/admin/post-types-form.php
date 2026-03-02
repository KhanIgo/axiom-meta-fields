<?php
if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission
if (isset($_POST['amf_post_type_nonce']) && wp_verify_nonce($_POST['amf_post_type_nonce'], 'amf_save_post_type')) {
    $config = [
        'key' => sanitize_text_field($_POST['key'] ?? ''),
        'labels' => [
            'name' => sanitize_text_field($_POST['label_name'] ?? ''),
            'singular_name' => sanitize_text_field($_POST['label_singular'] ?? ''),
            'menu_name' => sanitize_text_field($_POST['label_menu'] ?? ''),
            'add_new' => sanitize_text_field($_POST['label_add_new'] ?? ''),
            'add_new_item' => sanitize_text_field($_POST['label_add_new_item'] ?? ''),
            'edit_item' => sanitize_text_field($_POST['label_edit_item'] ?? ''),
            'new_item' => sanitize_text_field($_POST['label_new_item'] ?? ''),
            'view_item' => sanitize_text_field($_POST['label_view_item'] ?? ''),
            'search_items' => sanitize_text_field($_POST['label_search'] ?? ''),
            'not_found' => sanitize_text_field($_POST['label_not_found'] ?? ''),
            'not_found_in_trash' => sanitize_text_field($_POST['label_not_found_trash'] ?? ''),
        ],
        'public' => isset($_POST['public']) ? (bool) $_POST['public'] : false,
        'show_ui' => isset($_POST['show_ui']) ? (bool) $_POST['show_ui'] : false,
        'show_in_menu' => isset($_POST['show_in_menu']) ? (bool) $_POST['show_in_menu'] : false,
        'show_in_rest' => isset($_POST['show_in_rest']) ? (bool) $_POST['show_in_rest'] : false,
        'rest_base' => sanitize_text_field($_POST['rest_base'] ?? ''),
        'menu_position' => absint($_POST['menu_position'] ?? null),
        'menu_icon' => sanitize_text_field($_POST['menu_icon'] ?? 'dashicons-admin-post'),
        'supports' => isset($_POST['supports']) ? $_POST['supports'] : [],
        'has_archive' => isset($_POST['has_archive']) ? (bool) $_POST['has_archive'] : false,
        'hierarchical' => isset($_POST['hierarchical']) ? (bool) $_POST['hierarchical'] : false,
        'show_in_nav_menus' => isset($_POST['show_in_nav_menus']) ? (bool) $_POST['show_in_nav_menus'] : false,
        'can_export' => isset($_POST['can_export']) ? (bool) $_POST['can_export'] : false,
        'delete_with_user' => isset($_POST['delete_with_user']) ? (bool) $_POST['delete_with_user'] : false,
        'capability_type' => sanitize_text_field($_POST['capability_type'] ?? 'post'),
        'map_meta_cap' => isset($_POST['map_meta_cap']) ? (bool) $_POST['map_meta_cap'] : true,
    ];

    // Clean empty labels
    $config['labels'] = array_filter($config['labels']);

    if (!empty($config['key'])) {
        amf_register_post_type($config);
        
        // Save to database
        $saved = get_option('amf_post_types', []);
        $saved[$config['key']] = $config;
        update_option('amf_post_types', $saved);

        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Post type registered successfully!', 'amf') . '</p></div>';
    } else {
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Post type key is required.', 'amf') . '</p></div>';
    }
}

// Default values
$defaults = [
    'key' => '',
    'label_name' => '',
    'label_singular' => '',
    'label_menu' => '',
    'label_add_new' => 'Add New',
    'label_add_new_item' => 'Add New',
    'label_edit_item' => 'Edit',
    'label_new_item' => 'New',
    'label_view_item' => 'View',
    'label_search' => 'Search',
    'label_not_found' => 'Not Found',
    'label_not_found_trash' => 'Not Found in Trash',
    'public' => true,
    'show_ui' => true,
    'show_in_menu' => true,
    'show_in_rest' => true,
    'rest_base' => '',
    'menu_position' => '',
    'menu_icon' => 'dashicons-admin-post',
    'supports' => ['title', 'editor'],
    'has_archive' => false,
    'hierarchical' => false,
    'show_in_nav_menus' => true,
    'can_export' => true,
    'delete_with_user' => false,
    'capability_type' => 'post',
    'map_meta_cap' => true,
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
?>

<div class="wrap amf-post-type-form">
    <h1>
        <?php esc_html_e('Add New Post Type', 'amf'); ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=amf-post-types')); ?>" class="page-title-action">
            <?php esc_html_e('Back to List', 'amf'); ?>
        </a>
    </h1>

    <form method="post" action="">
        <?php wp_nonce_field('amf_save_post_type', 'amf_post_type_nonce'); ?>

        <div class="amf-form-grid">
            <!-- Basic Settings -->
            <div class="amf-form-section">
                <h2><?php esc_html_e('Basic Settings', 'amf'); ?></h2>

                <div class="amf-form-field">
                    <label for="key"><?php esc_html_e('Post Type Key', 'amf'); ?> *</label>
                    <input type="text" id="key" name="key" class="regular-text" required 
                           value="<?php echo esc_attr($defaults['key']); ?>" 
                           placeholder="<?php esc_attr_e('e.g., product, book, movie'); ?>">
                    <p class="description"><?php esc_html_e('Lowercase, no spaces. Use underscores for multi-word keys.', 'amf'); ?></p>
                </div>

                <div class="amf-form-field">
                    <label for="label_name"><?php esc_html_e('Name (Plural)', 'amf'); ?></label>
                    <input type="text" id="label_name" name="label_name" class="regular-text" 
                           value="<?php echo esc_attr($defaults['label_name']); ?>" 
                           placeholder="<?php esc_attr_e('e.g., Products'); ?>">
                </div>

                <div class="amf-form-field">
                    <label for="label_singular"><?php esc_html_e('Singular Name', 'amf'); ?></label>
                    <input type="text" id="label_singular" name="label_singular" class="regular-text" 
                           value="<?php echo esc_attr($defaults['label_singular']); ?>" 
                           placeholder="<?php esc_attr_e('e.g., Product'); ?>">
                </div>

                <div class="amf-form-field">
                    <label for="menu_icon"><?php esc_html_e('Menu Icon', 'amf'); ?></label>
                    <select id="menu_icon" name="menu_icon" class="regular-text">
                        <?php foreach ($dashicons as $icon): ?>
                            <option value="<?php echo esc_attr($icon); ?>" <?php selected($defaults['menu_icon'], $icon); ?>>
                                <span class="dashicons <?php echo esc_attr($icon); ?>"></span>
                                <?php echo esc_html($icon); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="amf-form-field">
                    <label for="menu_position"><?php esc_html_e('Menu Position', 'amf'); ?></label>
                    <input type="number" id="menu_position" name="menu_position" class="small-text" 
                           value="<?php echo esc_attr($defaults['menu_position']); ?>" 
                           placeholder="<?php esc_attr_e('e.g., 20'); ?>">
                    <p class="description"><?php esc_html_e('Lower numbers = higher position. Posts=5, Pages=20, Media=10.', 'amf'); ?></p>
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
                    <p class="description"><?php esc_html_e('Generate a default UI for managing this post type.', 'amf'); ?></p>
                </div>

                <div class="amf-form-field amf-checkbox">
                    <label>
                        <input type="checkbox" name="show_in_menu" value="1" <?php checked($defaults['show_in_menu']); ?>>
                        <?php esc_html_e('Show in Menu', 'amf'); ?>
                    </label>
                    <p class="description"><?php esc_html_e('Show in the WordPress admin menu.', 'amf'); ?></p>
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
                        <input type="checkbox" name="has_archive" value="1" <?php checked($defaults['has_archive']); ?>>
                        <?php esc_html_e('Has Archive', 'amf'); ?>
                    </label>
                    <p class="description"><?php esc_html_e('Enable post type archive page.', 'amf'); ?></p>
                </div>

                <div class="amf-form-field amf-checkbox">
                    <label>
                        <input type="checkbox" name="hierarchical" value="1" <?php checked($defaults['hierarchical']); ?>>
                        <?php esc_html_e('Hierarchical', 'amf'); ?>
                    </label>
                    <p class="description"><?php esc_html_e('Enable parent/child relationships (like Pages).', 'amf'); ?></p>
                </div>

                <div class="amf-form-field amf-checkbox">
                    <label>
                        <input type="checkbox" name="show_in_nav_menus" value="1" <?php checked($defaults['show_in_nav_menus']); ?>>
                        <?php esc_html_e('Show in Nav Menus', 'amf'); ?>
                    </label>
                </div>

                <div class="amf-form-field amf-checkbox">
                    <label>
                        <input type="checkbox" name="can_export" value="1" <?php checked($defaults['can_export']); ?>>
                        <?php esc_html_e('Can Export', 'amf'); ?>
                    </label>
                </div>
            </div>

            <!-- Supported Features -->
            <div class="amf-form-section">
                <h2><?php esc_html_e('Supported Features', 'amf'); ?></h2>

                <div class="amf-form-field">
                    <label><?php esc_html_e('Supports', 'amf'); ?></label>
                    <div class="amf-checkbox-group">
                        <label>
                            <input type="checkbox" name="supports[]" value="title" <?php echo in_array('title', $defaults['supports']) ? 'checked' : ''; ?>>
                            <?php esc_html_e('Title', 'amf'); ?>
                        </label>
                        <label>
                            <input type="checkbox" name="supports[]" value="editor" <?php echo in_array('editor', $defaults['supports']) ? 'checked' : ''; ?>>
                            <?php esc_html_e('Editor', 'amf'); ?>
                        </label>
                        <label>
                            <input type="checkbox" name="supports[]" value="excerpt" <?php echo in_array('excerpt', $defaults['supports']) ? 'checked' : ''; ?>>
                            <?php esc_html_e('Excerpt', 'amf'); ?>
                        </label>
                        <label>
                            <input type="checkbox" name="supports[]" value="thumbnail" <?php echo in_array('thumbnail', $defaults['supports']) ? 'checked' : ''; ?>>
                            <?php esc_html_e('Featured Image', 'amf'); ?>
                        </label>
                        <label>
                            <input type="checkbox" name="supports[]" value="comments" <?php echo in_array('comments', $defaults['supports']) ? 'checked' : ''; ?>>
                            <?php esc_html_e('Comments', 'amf'); ?>
                        </label>
                        <label>
                            <input type="checkbox" name="supports[]" value="revisions" <?php echo in_array('revisions', $defaults['supports']) ? 'checked' : ''; ?>>
                            <?php esc_html_e('Revisions', 'amf'); ?>
                        </label>
                        <label>
                            <input type="checkbox" name="supports[]" value="author" <?php echo in_array('author', $defaults['supports']) ? 'checked' : ''; ?>>
                            <?php esc_html_e('Author', 'amf'); ?>
                        </label>
                        <label>
                            <input type="checkbox" name="supports[]" value="custom-fields" <?php echo in_array('custom-fields', $defaults['supports']) ? 'checked' : ''; ?>>
                            <?php esc_html_e('Custom Fields', 'amf'); ?>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Advanced Settings -->
            <div class="amf-form-section">
                <h2><?php esc_html_e('Advanced Settings', 'amf'); ?></h2>

                <div class="amf-form-field">
                    <label for="rest_base"><?php esc_html_e('REST Base', 'amf'); ?></label>
                    <input type="text" id="rest_base" name="rest_base" class="regular-text" 
                           value="<?php echo esc_attr($defaults['rest_base']); ?>" 
                           placeholder="<?php esc_attr_e('e.g., products (defaults to key)'); ?>">
                </div>

                <div class="amf-form-field">
                    <label for="capability_type"><?php esc_html_e('Capability Type', 'amf'); ?></label>
                    <input type="text" id="capability_type" name="capability_type" class="regular-text" 
                           value="<?php echo esc_attr($defaults['capability_type']); ?>">
                </div>

                <div class="amf-form-field amf-checkbox">
                    <label>
                        <input type="checkbox" name="map_meta_cap" value="1" <?php checked($defaults['map_meta_cap']); ?>>
                        <?php esc_html_e('Map Meta Cap', 'amf'); ?>
                    </label>
                    <p class="description"><?php esc_html_e('Map primitive caps to meta caps for this post type.', 'amf'); ?></p>
                </div>

                <div class="amf-form-field amf-checkbox">
                    <label>
                        <input type="checkbox" name="delete_with_user" value="1" <?php checked($defaults['delete_with_user']); ?>>
                        <?php esc_html_e('Delete with User', 'amf'); ?>
                    </label>
                    <p class="description"><?php esc_html_e('Delete posts when the author is deleted.', 'amf'); ?></p>
                </div>
            </div>
        </div>

        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Register Post Type', 'amf'); ?>">
            <a href="<?php echo esc_url(admin_url('admin.php?page=amf-post-types')); ?>" class="button">
                <?php esc_html_e('Cancel', 'amf'); ?>
            </a>
        </p>
    </form>
</div>

<style>
    .amf-post-type-form h1 {
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

    .amf-form-field select option {
        display: flex;
        align-items: center;
        gap: 8px;
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
