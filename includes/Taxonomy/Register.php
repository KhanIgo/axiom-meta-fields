<?php

declare(strict_types=1);

namespace AMF\Taxonomy;

use AMF\Traits\Singleton;

/**
 * Taxonomy Registration
 */
class Register
{
    use Singleton;

    /**
     * @var array<string, array>
     */
    private array $taxonomies = [];

    /**
     * Initialize
     *
     * @return void
     */
    public function init(): void
    {
        $this->loadConfigurations();
    }

    /**
     * Load saved taxonomy configurations
     *
     * @return void
     */
    private function loadConfigurations(): void
    {
        $saved = get_option('amf_taxonomies', []);
        foreach ($saved as $config) {
            if (is_array($config) && isset($config['key'])) {
                $this->register($config);
            }
        }
    }

    /**
     * Register a taxonomy
     *
     * @param array $config
     * @return self
     */
    public function register(array $config): self
    {
        $defaults = [
            'key' => '',
            'labels' => [],
            'post_types' => ['post'],
            'public' => true,
            'show_ui' => true,
            'show_in_rest' => true,
            'rest_base' => '',
            'hierarchical' => true,
            'rewrite' => true,
            'show_admin_column' => true,
            'meta_box' => 'default',
            'show_in_nav_menus' => true,
            'show_tagcloud' => true,
            'show_in_quick_edit' => true,
        ];

        $config = wp_parse_args($config, $defaults);

        if (empty($config['key'])) {
            _doing_it_wrong(
                __METHOD__,
                __('Taxonomy key is required.', 'amf'),
                '1.0.0'
            );
            return $this;
        }

        $key = $config['key'];
        $this->taxonomies[$key] = $config;

        // Register with WordPress
        add_action('init', function () use ($config) {
            $this->doRegister($config);
        }, 1);

        do_action('amf_taxonomy_registered', $key, $config);

        return $this;
    }

    /**
     * Actually register the taxonomy with WordPress
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
            'show_in_rest' => $config['show_in_rest'],
            'rest_base' => $config['rest_base'] ?: $key,
            'hierarchical' => $config['hierarchical'],
            'show_admin_column' => $config['show_admin_column'],
            'meta_box_cb' => $config['meta_box'] === 'none' ? false : $config['meta_box'],
            'show_in_nav_menus' => $config['show_in_nav_menus'],
            'show_tagcloud' => $config['show_tagcloud'],
            'show_in_quick_edit' => $config['show_in_quick_edit'],
        ];

        // Handle rewrite
        if (is_array($config['rewrite'])) {
            $args['rewrite'] = wp_parse_args($config['rewrite'], [
                'slug' => $key,
                'with_front' => true,
            ]);
        }

        register_taxonomy($key, $config['post_types'], $args);
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
            'search_items' => sprintf(__('Search %s', 'amf'), ucfirst($key) . 's'),
            'popular_items' => sprintf(__('Popular %s', 'amf'), ucfirst($key) . 's'),
            'all_items' => sprintf(__('All %s', 'amf'), ucfirst($key) . 's'),
            'edit_item' => sprintf(__('Edit %s', 'amf'), ucfirst($key)),
            'update_item' => sprintf(__('Update %s', 'amf'), ucfirst($key)),
            'add_new_item' => sprintf(__('Add New %s', 'amf'), ucfirst($key)),
            'new_item_name' => sprintf(__('New %s Name', 'amf'), ucfirst($key)),
            'separate_items_with_commas' => sprintf(__('Separate %s with commas', 'amf'), strtolower($key) . 's'),
            'add_or_remove_items' => sprintf(__('Add or remove %s', 'amf'), strtolower($key) . 's'),
            'choose_from_most_used' => sprintf(__('Choose from the most used %s', 'amf'), strtolower($key) . 's'),
            'not_found' => sprintf(__('No %s found', 'amf'), strtolower($key) . 's'),
            'no_terms' => sprintf(__('No %s', 'amf'), strtolower($key) . 's'),
            'items_list_navigation' => sprintf(__('%s list navigation', 'amf'), ucfirst($key) . 's'),
            'items_list' => sprintf(__('%s list', 'amf'), ucfirst($key) . 's'),
            'back_to_items' => sprintf(__('&larr; Back to %s', 'amf'), strtolower($key) . 's'),
        ];

        return wp_parse_args($labels, $defaults);
    }

    /**
     * Create a fluent taxonomy builder
     *
     * @param string $key
     * @return TaxonomyBuilder
     */
    public static function make(string $key): TaxonomyBuilder
    {
        return new TaxonomyBuilder($key);
    }

    /**
     * Get a registered taxonomy
     *
     * @param string $key
     * @return array|null
     */
    public function get(string $key): ?array
    {
        return $this->taxonomies[$key] ?? null;
    }

    /**
     * Get all registered taxonomies
     *
     * @return array<string, array>
     */
    public function all(): array
    {
        return $this->taxonomies;
    }

    /**
     * Unregister a taxonomy
     *
     * @param string $key
     * @return void
     */
    public function unregister(string $key): void
    {
        unset($this->taxonomies[$key]);
    }

    /**
     * Save configurations
     *
     * @return void
     */
    public function saveConfigurations(): void
    {
        update_option('amf_taxonomies', $this->taxonomies);
    }
}
