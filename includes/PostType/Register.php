<?php

declare(strict_types=1);

namespace AMF\PostType;

use AMF\Traits\Singleton;

/**
 * Post Type Registration
 */
class Register
{
    use Singleton;

    /**
     * @var array<string, array>
     */
    private array $postTypes = [];

    /**
     * Initialize
     *
     * @return void
     */
    public function init(): void
    {
        // Load saved configurations
        $this->loadConfigurations();
    }

    /**
     * Load saved post type configurations
     *
     * @return void
     */
    private function loadConfigurations(): void
    {
        $saved = get_option('amf_post_types', []);
        foreach ($saved as $config) {
            if (is_array($config) && isset($config['key'])) {
                $this->register($config);
            }
        }
    }

    /**
     * Register a post type
     *
     * @param array $config
     * @return self
     */
    public function register(array $config): self
    {
        $defaults = [
            'key' => '',
            'labels' => [],
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'rest_base' => '',
            'menu_position' => null,
            'menu_icon' => 'dashicons-admin-post',
            'supports' => ['title', 'editor'],
            'taxonomies' => [],
            'has_archive' => false,
            'rewrite' => true,
            'capability_type' => 'post',
            'map_meta_cap' => true,
            'hierarchical' => false,
            'show_in_nav_menus' => true,
            'can_export' => true,
            'delete_with_user' => false,
            'register_meta_box_cb' => null,
        ];

        $config = wp_parse_args($config, $defaults);

        if (empty($config['key'])) {
            _doing_it_wrong(
                __METHOD__,
                __('Post type key is required.', 'amf'),
                '1.0.0'
            );
            return $this;
        }

        $key = $config['key'];
        $this->postTypes[$key] = $config;

        // Register with WordPress
        add_action('init', function () use ($config) {
            $this->doRegister($config);
        }, 1);

        /**
         * Fires after a post type is registered
         *
         * @param string $key Post type key
         * @param array $config Post type configuration
         */
        do_action('amf_post_type_registered', $key, $config);

        return $this;
    }

    /**
     * Actually register the post type with WordPress
     *
     * @param array $config
     * @return void
     */
    private function doRegister(array $config): void
    {
        $key = $config['key'];
        $labels = $this->getLabels($config['labels'] ?? [], $key);

        $args = [
            'label' => $labels['name'] ?? $key,
            'labels' => $labels,
            'public' => $config['public'],
            'show_ui' => $config['show_ui'],
            'show_in_menu' => $config['show_in_menu'],
            'show_in_rest' => $config['show_in_rest'],
            'rest_base' => $config['rest_base'] ?: $key,
            'menu_position' => $config['menu_position'],
            'menu_icon' => $config['menu_icon'],
            'supports' => $config['supports'],
            'has_archive' => $config['has_archive'],
            'rewrite' => $config['rewrite'],
            'capability_type' => $config['capability_type'],
            'map_meta_cap' => $config['map_meta_cap'],
            'hierarchical' => $config['hierarchical'],
            'show_in_nav_menus' => $config['show_in_nav_menus'],
            'can_export' => $config['can_export'],
            'delete_with_user' => $config['delete_with_user'],
            'register_meta_box_cb' => $config['register_meta_box_cb'],
        ];

        // Handle rewrite
        if (is_array($config['rewrite'])) {
            $args['rewrite'] = wp_parse_args($config['rewrite'], [
                'slug' => $key,
                'with_front' => true,
                'feeds' => true,
                'pages' => true,
            ]);
        }

        register_post_type($key, $args);

        // Register associated taxonomies
        if (!empty($config['taxonomies'])) {
            foreach ($config['taxonomies'] as $taxonomy) {
                if (is_string($taxonomy)) {
                    register_taxonomy_for_object_type($taxonomy, $key);
                }
            }
        }
    }

    /**
     * Get labels
     *
     * @param array $labels
     * @param string $key
     * @return array
     */
    private function getLabels(array $labels, string $key): array
    {
        $defaults = [
            'name' => ucfirst($key) . 's',
            'singular_name' => ucfirst($key),
            'menu_name' => ucfirst($key) . 's',
            'add_new' => __('Add New', 'amf'),
            'add_new_item' => sprintf(__('Add New %s', 'amf'), ucfirst($key)),
            'edit_item' => sprintf(__('Edit %s', 'amf'), ucfirst($key)),
            'new_item' => sprintf(__('New %s', 'amf'), ucfirst($key)),
            'view_item' => sprintf(__('View %s', 'amf'), ucfirst($key)),
            'search_items' => sprintf(__('Search %s', 'amf'), ucfirst($key) . 's'),
            'not_found' => sprintf(__('No %s found', 'amf'), $key . 's'),
            'not_found_in_trash' => sprintf(__('No %s found in trash', 'amf'), $key . 's'),
            'all_items' => sprintf(__('All %s', 'amf'), ucfirst($key) . 's'),
            'archives' => sprintf(__('%s Archives', 'amf'), ucfirst($key)),
            'attributes' => sprintf(__('%s Attributes', 'amf'), ucfirst($key)),
            'insert_into_item' => sprintf(__('Insert into %s', 'amf'), $key),
            'uploaded_to_this_item' => sprintf(__('Uploaded to this %s', 'amf'), $key),
            'featured_image' => __('Featured Image', 'amf'),
            'set_featured_image' => __('Set featured image', 'amf'),
            'remove_featured_image' => __('Remove featured image', 'amf'),
            'use_featured_image' => __('Use as featured image', 'amf'),
        ];

        return wp_parse_args($labels, $defaults);
    }

    /**
     * Create a fluent post type builder
     *
     * @param string $key
     * @return PostTypeBuilder
     */
    public static function make(string $key): PostTypeBuilder
    {
        return new PostTypeBuilder($key);
    }

    /**
     * Get a registered post type
     *
     * @param string $key
     * @return array|null
     */
    public function get(string $key): ?array
    {
        return $this->postTypes[$key] ?? null;
    }

    /**
     * Get all registered post types
     *
     * @return array<string, array>
     */
    public function all(): array
    {
        return $this->postTypes;
    }

    /**
     * Unregister a post type
     *
     * @param string $key
     * @return void
     */
    public function unregister(string $key): void
    {
        unset($this->postTypes[$key]);
    }

    /**
     * Save configurations
     *
     * @return void
     */
    public function saveConfigurations(): void
    {
        update_option('amf_post_types', $this->postTypes);
    }
}
