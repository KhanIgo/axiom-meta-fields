<?php

declare(strict_types=1);

namespace AMF\Admin;

use AMF\Traits\Hookable;

/**
 * Admin Settings
 */
class Settings
{
    use Hookable;

    /**
     * @var string
     */
    private string $optionName = 'amf_settings';

    /**
     * Initialize
     *
     * @return void
     */
    public function init(): void
    {
        $this->addAction('admin_init', [$this, 'registerSettings']);
    }

    /**
     * Register settings
     *
     * @return void
     */
    public function registerSettings(): void
    {
        // Register setting
        register_setting(
            'amf_settings_group',
            $this->optionName,
            [$this, 'sanitizeSettings']
        );

        // General section
        add_settings_section(
            'amf_section_general',
            __('General Settings', 'amf'),
            [$this, 'renderGeneralSection'],
            'amf-settings'
        );

        // Features section
        add_settings_section(
            'amf_section_features',
            __('Features', 'amf'),
            [$this, 'renderFeaturesSection'],
            'amf-settings'
        );

        // Performance section
        add_settings_section(
            'amf_section_performance',
            __('Performance', 'amf'),
            [$this, 'renderPerformanceSection'],
            'amf-settings'
        );

        // General fields
        add_settings_field(
            'amf_enable_gutenberg',
            __('Gutenberg Support', 'amf'),
            [$this, 'renderGutenbergField'],
            'amf-settings',
            'amf_section_features'
        );

        add_settings_field(
            'amf_enable_rest_api',
            __('REST API', 'amf'),
            [$this, 'renderRestApiField'],
            'amf-settings',
            'amf_section_features'
        );

        add_settings_field(
            'amf_cache_enabled',
            __('Enable Caching', 'amf'),
            [$this, 'renderCacheField'],
            'amf-settings',
            'amf_section_performance'
        );

        add_settings_field(
            'amf_cache_ttl',
            __('Cache TTL (seconds)', 'amf'),
            [$this, 'renderCacheTtlField'],
            'amf-settings',
            'amf_section_performance'
        );

        add_settings_field(
            'amf_debug_mode',
            __('Debug Mode', 'amf'),
            [$this, 'renderDebugField'],
            'amf-settings',
            'amf_section_general'
        );
    }

    /**
     * Sanitize settings
     *
     * @param array $input
     * @return array
     */
    public function sanitizeSettings(array $input): array
    {
        $sanitized = [];

        $sanitized['enable_gutenberg'] = isset($input['enable_gutenberg']) ? (bool) $input['enable_gutenberg'] : true;
        $sanitized['enable_rest_api'] = isset($input['enable_rest_api']) ? (bool) $input['enable_rest_api'] : true;
        $sanitized['enable_graphql'] = isset($input['enable_graphql']) ? (bool) $input['enable_graphql'] : false;
        $sanitized['cache_enabled'] = isset($input['cache_enabled']) ? (bool) $input['cache_enabled'] : true;
        $sanitized['cache_ttl'] = absint($input['cache_ttl'] ?? 3600);
        $sanitized['debug_mode'] = isset($input['debug_mode']) ? (bool) $input['debug_mode'] : false;

        return $sanitized;
    }

    /**
     * Render general section
     *
     * @return void
     */
    public function renderGeneralSection(): void
    {
        echo '<p>' . __('Configure general plugin settings.', 'amf') . '</p>';
    }

    /**
     * Render features section
     *
     * @return void
     */
    public function renderFeaturesSection(): void
    {
        echo '<p>' . __('Enable or disable specific features.', 'amf') . '</p>';
    }

    /**
     * Render performance section
     *
     * @return void
     */
    public function renderPerformanceSection(): void
    {
        echo '<p>' . __('Configure performance-related settings.', 'amf') . '</p>';
    }

    /**
     * Render Gutenberg field
     *
     * @return void
     */
    public function renderGutenbergField(): void
    {
        $options = get_option($this->optionName, []);
        $value = $options['enable_gutenberg'] ?? true;

        echo '<label>';
        echo '<input type="checkbox" name="' . esc_attr($this->optionName) . '[enable_gutenberg]" value="1" ' . checked($value, true, false) . ' />';
        echo ' ' . esc_html__('Enable Gutenberg/Block Editor integration', 'amf');
        echo '</label>';
    }

    /**
     * Render REST API field
     *
     * @return void
     */
    public function renderRestApiField(): void
    {
        $options = get_option($this->optionName, []);
        $value = $options['enable_rest_api'] ?? true;

        echo '<label>';
        echo '<input type="checkbox" name="' . esc_attr($this->optionName) . '[enable_rest_api]" value="1" ' . checked($value, true, false) . ' />';
        echo ' ' . esc_html__('Enable REST API endpoints', 'amf');
        echo '</label>';
    }

    /**
     * Render cache field
     *
     * @return void
     */
    public function renderCacheField(): void
    {
        $options = get_option($this->optionName, []);
        $value = $options['cache_enabled'] ?? true;

        echo '<label>';
        echo '<input type="checkbox" name="' . esc_attr($this->optionName) . '[cache_enabled]" value="1" ' . checked($value, true, false) . ' />';
        echo ' ' . esc_html__('Enable meta caching for improved performance', 'amf');
        echo '</label>';
    }

    /**
     * Render cache TTL field
     *
     * @return void
     */
    public function renderCacheTtlField(): void
    {
        $options = get_option($this->optionName, []);
        $value = $options['cache_ttl'] ?? 3600;

        echo '<input type="number" name="' . esc_attr($this->optionName) . '[cache_ttl]" value="' . esc_attr($value) . '" class="small-text" min="60" step="60" />';
        echo ' <span class="description">' . esc_html__('seconds', 'amf') . '</span>';
    }

    /**
     * Render debug field
     *
     * @return void
     */
    public function renderDebugField(): void
    {
        $options = get_option($this->optionName, []);
        $value = $options['debug_mode'] ?? false;

        echo '<label>';
        echo '<input type="checkbox" name="' . esc_attr($this->optionName) . '[debug_mode]" value="1" ' . checked($value, true, false) . ' />';
        echo ' ' . esc_html__('Enable debug mode (logs additional information)', 'amf');
        echo '</label>';
    }

    /**
     * Get settings value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $options = get_option($this->optionName, []);
        return $options[$key] ?? $default;
    }
}
