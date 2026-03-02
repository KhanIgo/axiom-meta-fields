<?php

declare(strict_types=1);

namespace AMF\Frontend;

/**
 * Template Tags - Helper functions for theme templates
 *
 * These functions are available globally in theme templates
 */
class TemplateTags
{
    /**
     * Initialize - register global functions
     *
     * @return void
     */
    public function init(): void
    {
        // Template tags are loaded globally via action
        add_action('after_setup_theme', [$this, 'loadTemplateTags']);
    }

    /**
     * Load template tags into global scope
     *
     * @return void
     */
    public function loadTemplateTags(): void
    {
        // Template tag functions are defined as global functions
        // This class just initializes the system
    }
}

/**
 * Get meta value for a post
 *
 * @param string $key Meta key
 * @param int|null $post_id Post ID (defaults to current post)
 * @param bool $single Return single value or array
 * @return mixed
 */
function amf_get_meta(string $key, ?int $post_id = null, bool $single = true)
{
    $post_id = $post_id ?? get_the_ID();
    return get_post_meta($post_id, $key, $single);
}

/**
 * Display meta value for a post
 *
 * @param string $key Meta key
 * @param int|null $post_id Post ID (defaults to current post)
 * @param string $default Default value if empty
 * @return void
 */
function amf_the_meta(string $key, ?int $post_id = null, string $default = ''): void
{
    $value = amf_get_meta($key, $post_id);

    if (empty($value) && !empty($default)) {
        $value = $default;
    }

    echo esc_html($value);
}

/**
 * Get all meta for a post (excluding WordPress internal meta)
 *
 * @param int|null $post_id Post ID
 * @return array
 */
function amf_get_all_meta(?int $post_id = null): array
{
    $post_id = $post_id ?? get_the_ID();
    $all_meta = get_post_meta($post_id);

    $amf_meta = [];
    foreach ($all_meta as $key => $values) {
        // Skip WordPress internal meta
        if (strpos($key, '_') === 0) {
            continue;
        }
        $amf_meta[$key] = maybe_unserialize($values[0]);
    }

    return $amf_meta;
}

/**
 * Check if post has meta
 *
 * @param string $key Meta key
 * @param int|null $post_id Post ID
 * @return bool
 */
function amf_has_meta(string $key, ?int $post_id = null): bool
{
    $post_id = $post_id ?? get_the_ID();
    $value = get_post_meta($post_id, $key, true);
    return !empty($value);
}

/**
 * Get formatted meta value
 *
 * @param string $key Meta key
 * @param string $format Format type (currency, date, datetime, etc.)
 * @param int|null $post_id Post ID
 * @return mixed
 */
function amf_get_formatted_meta(string $key, string $format = 'text', ?int $post_id = null)
{
    $value = amf_get_meta($key, $post_id);

    return amf_format_value($value, $format);
}

/**
 * Format a value
 *
 * @param mixed $value
 * @param string $format
 * @return mixed
 */
function amf_format_value($value, string $format)
{
    switch ($format) {
        case 'currency':
            return number_format((float) $value, 2);
        case 'date':
            return date_i18n(get_option('date_format'), strtotime($value));
        case 'datetime':
            return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($value));
        case 'uppercase':
            return strtoupper($value);
        case 'lowercase':
            return strtolower($value);
        case 'capitalize':
            return ucwords($value);
        default:
            return $value;
    }
}

/**
 * Display gallery field
 *
 * @param string $key Meta key containing gallery IDs
 * @param array $args Arguments
 * @return void
 */
function amf_gallery(string $key = 'gallery', array $args = []): void
{
    $defaults = [
        'size' => 'thumbnail',
        'columns' => 3,
        'link' => 'file',
        'post_id' => get_the_ID(),
    ];

    $args = wp_parse_args($args, $defaults);

    $post_id = (int) $args['post_id'];
    $value = get_post_meta($post_id, $key, true);

    if (empty($value)) {
        return;
    }

    $ids = is_array($value) ? $value : explode(',', $value);
    $ids = array_filter(array_map('absint', $ids));

    if (empty($ids)) {
        return;
    }

    echo '<div class="amf-gallery amf-gallery-columns-' . esc_attr($args['columns']) . '">';

    foreach ($ids as $attachment_id) {
        echo '<div class="amf-gallery-item">';

        if ($args['link'] === 'file') {
            $full_url = wp_get_attachment_url($attachment_id);
            echo '<a href="' . esc_url($full_url) . '">';
        } elseif ($args['link'] === 'post') {
            $post_url = get_permalink($attachment_id);
            echo '<a href="' . esc_url($post_url) . '">';
        }

        echo wp_get_attachment_image($attachment_id, $args['size']);

        if ($args['link'] !== 'none') {
            echo '</a>';
        }

        echo '</div>';
    }

    echo '</div>';
}

/**
 * Display relationship field
 *
 * @param string $key Meta key containing related post IDs
 * @param array $args Arguments
 * @return void
 */
function amf_relationship(string $key, array $args = []): void
{
    $defaults = [
        'display' => 'title',
        'link' => true,
        'separator' => ', ',
        'post_id' => get_the_ID(),
    ];

    $args = wp_parse_args($args, $defaults);

    $post_id = (int) $args['post_id'];
    $value = get_post_meta($post_id, $key, true);

    if (empty($value)) {
        return;
    }

    $ids = is_array($value) ? $value : explode(',', $value);
    $ids = array_filter(array_map('absint', $ids));

    if (empty($ids)) {
        return;
    }

    $items = [];

    foreach ($ids as $id) {
        $post = get_post($id);
        if (!$post) {
            continue;
        }

        $item = '';

        if ($args['link']) {
            $item .= '<a href="' . esc_url(get_permalink($id)) . '">';
        }

        if ($args['display'] === 'title') {
            $item .= get_the_title($id);
        } elseif ($args['display'] === 'excerpt') {
            $item .= get_the_excerpt($id);
        } elseif ($args['display'] === 'thumbnail') {
            $item .= get_the_post_thumbnail($id, 'thumbnail');
        }

        if ($args['link']) {
            $item .= '</a>';
        }

        $items[] = $item;
    }

    echo implode($args['separator'], $items);
}

/**
 * Get field object for a post
 *
 * @param string $field_id Field ID
 * @param int|null $post_id Post ID
 * @return array|null
 */
function amf_get_field(string $field_id, ?int $post_id = null): ?array
{
    $post_id = $post_id ?? get_the_ID();

    $register = \AMF\MetaBox\Register::getInstance();
    $metaBoxes = $register->getByPostType(get_post_type($post_id));

    foreach ($metaBoxes as $config) {
        foreach ($config['fields'] as $field) {
            if (($field['id'] ?? '') === $field_id) {
                $field['value'] = get_post_meta($post_id, $field_id, true);
                return $field;
            }
        }
    }

    return null;
}

/**
 * Get field value
 *
 * @param string $field_id Field ID
 * @param int|null $post_id Post ID
 * @return mixed
 */
function amf_get_field_value(string $field_id, ?int $post_id = null)
{
    $field = amf_get_field($field_id, $post_id);
    return $field['value'] ?? '';
}

/**
 * Display field value
 *
 * @param string $field_id Field ID
 * @param int|null $post_id Post ID
 * @param string $default Default value
 * @return void
 */
function amf_the_field(string $field_id, ?int $post_id = null, string $default = ''): void
{
    $value = amf_get_field_value($field_id, $post_id);

    if (empty($value) && !empty($default)) {
        $value = $default;
    }

    echo esc_html($value);
}

/**
 * Check if field has value
 *
 * @param string $field_id Field ID
 * @param int|null $post_id Post ID
 * @return bool
 */
function amf_have_field(string $field_id, ?int $post_id = null): bool
{
    $value = amf_get_field_value($field_id, $post_id);
    return !empty($value);
}

/**
 * Get meta box configuration
 *
 * @param string $meta_box_id Meta box ID
 * @return array|null
 */
function amf_get_meta_box(string $meta_box_id): ?array
{
    $register = \AMF\MetaBox\Register::getInstance();
    return $register->get($meta_box_id);
}

/**
 * Get all meta boxes for a post type
 *
 * @param string $post_type Post type
 * @return array
 */
function amf_get_meta_boxes(string $post_type): array
{
    $register = \AMF\MetaBox\Register::getInstance();
    return $register->getByPostType($post_type);
}
