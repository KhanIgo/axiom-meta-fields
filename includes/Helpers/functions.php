<?php

if (!defined('ABSPATH')) {
    exit;
}

function amf_app(): \AMF\Core\Bootstrap
{
    static $instance = null;
    if ($instance === null) {
        $instance = new \AMF\Core\Bootstrap();
    }
    return $instance;
}

function amf_container(): \AMF\Core\Container
{
    return amf_app()->getContainer();
}

function amf_register_meta_box(array $config): \AMF\MetaBox\Register
{
    return \AMF\MetaBox\Register::getInstance()->register($config);
}

function amf_meta_box(string $id): \AMF\MetaBox\MetaBoxBuilder
{
    return \AMF\MetaBox\Register::make($id);
}

function amf_register_post_type(array $config): \AMF\PostType\Register
{
    return \AMF\PostType\Register::getInstance()->register($config);
}

function amf_post_type(string $key): \AMF\PostType\PostTypeBuilder
{
    return \AMF\PostType\Register::make($key);
}

function amf_register_taxonomy(array $config): \AMF\Taxonomy\Register
{
    return \AMF\Taxonomy\Register::getInstance()->register($config);
}

function amf_taxonomy(string $key): \AMF\Taxonomy\TaxonomyBuilder
{
    return \AMF\Taxonomy\Register::make($key);
}

function amf_get_meta(string $key, ?int $post_id = null, bool $single = true)
{
    return \AMF\Frontend\amf_get_meta($key, $post_id, $single);
}

function amf_the_meta(string $key, ?int $post_id = null, string $default = ''): void
{
    \AMF\Frontend\amf_the_meta($key, $post_id, $default);
}

function amf_get_field_value(string $field_id, ?int $post_id = null)
{
    return \AMF\Frontend\amf_get_field_value($field_id, $post_id);
}

function amf_the_field(string $field_id, ?int $post_id = null, string $default = ''): void
{
    \AMF\Frontend\amf_the_field($field_id, $post_id, $default);
}

function amf_version(): string
{
    return AMF_VERSION;
}

function amf_path(string $path = ''): string
{
    return AMF_PLUGIN_DIR . ltrim($path, '/');
}

function amf_url(string $url = ''): string
{
    return AMF_PLUGIN_URL . ltrim($url, '/');
}

function amf_template(string $template, array $args = [], string $template_path = ''): void
{
    if (empty($template_path)) {
        $template_path = AMF_PLUGIN_DIR . 'templates/';
    }

    $template_file = $template_path . $template . '.php';

    if (file_exists($template_file)) {
        extract($args);
        include $template_file;
    }
}

function amf_log($data, string $label = ''): void
{
    $settings = get_option('amf_settings', []);

    if (!($settings['debug_mode'] ?? false)) {
        return;
    }

    if (!empty($label)) {
        error_log('[AMF ' . $label . ']');
    }

    error_log(print_r($data, true));
}

function amf_is_debug(): bool
{
    $settings = get_option('amf_settings', []);
    return $settings['debug_mode'] ?? false;
}

function amf_get_setting(string $key, $default = null)
{
    $settings = get_option('amf_settings', []);
    return $settings[$key] ?? $default;
}

function amf_is_enabled(string $feature): bool
{
    return amf_get_setting($feature, true);
}

function amf_format_value($value, string $type = 'text')
{
    return \AMF\Frontend\amf_format_value($value, $type);
}

function amf_sanitize_value($value, string $type = 'text')
{
    $sanitizers = [
        'text' => 'sanitize_text_field',
        'textarea' => 'sanitize_textarea_field',
        'html' => 'wp_kses_post',
        'url' => 'esc_url_raw',
        'email' => 'sanitize_email',
        'int' => 'intval',
        'float' => 'floatval',
        'bool' => 'boolval',
        'hex_color' => 'sanitize_hex_color',
    ];

    $callback = $sanitizers[$type] ?? null;

    if ($callback && is_callable($callback)) {
        return call_user_func($callback, $value);
    }

    return $value;
}

function amf_validate_required($value): bool
{
    if (is_array($value)) {
        return !empty($value);
    }

    return $value !== '' && $value !== null;
}

function amf_get_field_types(): array
{
    $factory = \AMF\Fields\FieldFactory::getInstance();
    return $factory->getTypes();
}

function amf_field_type_exists(string $type): bool
{
    $factory = \AMF\Fields\FieldFactory::getInstance();
    return $factory->exists($type);
}

function amf_create_field(string $type): ?\AMF\Fields\FieldInterface
{
    $factory = \AMF\Fields\FieldFactory::getInstance();
    return $factory->create($type);
}

function amf_get_meta_boxes(): array
{
    $register = \AMF\MetaBox\Register::getInstance();
    return $register->all();
}

function amf_get_meta_boxes_for_post_type(string $post_type): array
{
    $register = \AMF\MetaBox\Register::getInstance();
    return $register->getByPostType($post_type);
}

function amf_get_post_types(): array
{
    $register = \AMF\PostType\Register::getInstance();
    return $register->all();
}

function amf_get_taxonomies(): array
{
    $register = \AMF\Taxonomy\Register::getInstance();
    return $register->all();
}

function amf_clear_cache(): bool
{
    return wp_cache_flush();
}

function amf_get_cache(string $key, string $group = 'amf')
{
    return wp_cache_get($key, $group);
}

function amf_set_cache(string $key, $value, int $ttl = 3600, string $group = 'amf'): bool
{
    return wp_cache_set($key, $value, $group, $ttl);
}

function amf_delete_cache(string $key, string $group = 'amf'): bool
{
    return wp_cache_delete($key, $group);
}
