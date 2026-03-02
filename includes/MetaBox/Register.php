<?php

declare(strict_types=1);

namespace AMF\MetaBox;

use AMF\Traits\Singleton;

/**
 * MetaBox Registration
 */
class Register
{
    use Singleton;

    /**
     * Registered meta boxes
     *
     * @var array<string, array>
     */
    private array $metaBoxes = [];

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
     * Load saved meta box configurations
     *
     * @return void
     */
    private function loadConfigurations(): void
    {
        $saved = get_option('amf_meta_boxes', []);
        foreach ($saved as $config) {
            if (is_array($config) && isset($config['id'])) {
                $this->register($config);
            }
        }
    }

    /**
     * Register a meta box
     *
     * @param array $config
     * @return self
     */
    public function register(array $config): self
    {
        $defaults = [
            'id' => '',
            'title' => '',
            'post_types' => ['post'],
            'context' => 'normal',
            'priority' => 'default',
            'capability' => 'edit_post',
            'visible' => true,
            'fields' => [],
            'save_post' => true,
            'autosave' => false,
            'revision' => false,
            'style' => 'default',
            'class' => '',
        ];

        $config = wp_parse_args($config, $defaults);

        if (empty($config['id'])) {
            _doing_it_wrong(
                __METHOD__,
                __('Meta box ID is required.', 'amf'),
                '1.0.0'
            );
            return $this;
        }

        $this->metaBoxes[$config['id']] = $config;

        /**
         * Fires after a meta box is registered
         *
         * @param string $id Meta box ID
         * @param array $config Meta box configuration
         */
        do_action('amf_metabox_registered', $config['id'], $config);

        return $this;
    }

    /**
     * Create a fluent meta box builder
     *
     * @param string $id
     * @return MetaBoxBuilder
     */
    public static function make(string $id): MetaBoxBuilder
    {
        return new MetaBoxBuilder($id);
    }

    /**
     * Get a registered meta box
     *
     * @param string $id
     * @return array|null
     */
    public function get(string $id): ?array
    {
        return $this->metaBoxes[$id] ?? null;
    }

    /**
     * Get all registered meta boxes
     *
     * @return array<string, array>
     */
    public function all(): array
    {
        return $this->metaBoxes;
    }

    /**
     * Get meta boxes for a post type
     *
     * @param string $post_type
     * @return array
     */
    public function getByPostType(string $post_type): array
    {
        $result = [];

        foreach ($this->metaBoxes as $id => $config) {
            if (in_array($post_type, $config['post_types'], true)) {
                $result[$id] = $config;
            }
        }

        return $result;
    }

    /**
     * Unregister a meta box
     *
     * @param string $id
     * @return void
     */
    public function unregister(string $id): void
    {
        unset($this->metaBoxes[$id]);
    }

    /**
     * Save configurations
     *
     * @return void
     */
    public function saveConfigurations(): void
    {
        update_option('amf_meta_boxes', $this->metaBoxes);
    }
}
