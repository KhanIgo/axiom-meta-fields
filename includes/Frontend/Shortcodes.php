<?php

declare(strict_types=1);

namespace AMF\Frontend;

use AMF\Traits\Hookable;

/**
 * Shortcodes
 */
class Shortcodes
{
    use Hookable;

    /**
     * Initialize
     *
     * @return void
     */
    public function init(): void
    {
        $this->addAction('init', [$this, 'registerShortcodes']);
    }

    /**
     * Register shortcodes
     *
     * @return void
     */
    public function registerShortcodes(): void
    {
        // Display single meta value
        add_shortcode('amf_meta', [$this, 'displayMeta']);

        // Display all meta for a post
        add_shortcode('amf_meta_all', [$this, 'displayAllMeta']);

        // Display field with custom formatting
        add_shortcode('amf_field', [$this, 'displayField']);

        // Display gallery
        add_shortcode('amf_gallery', [$this, 'displayGallery']);

        // Display relationship
        add_shortcode('amf_relationship', [$this, 'displayRelationship']);
    }

    /**
     * Display single meta value
     * Usage: [amf_meta key="price" post_id="123"]
     *
     * @param array $atts
     * @return string
     */
    public function displayMeta(array $atts): string
    {
        $atts = shortcode_atts([
            'key' => '',
            'post_id' => get_the_ID(),
            'default' => '',
            'format' => '',
        ], $atts);

        if (empty($atts['key'])) {
            return '';
        }

        $value = get_post_meta((int) $atts['post_id'], $atts['key'], true);

        if (empty($value) && !empty($atts['default'])) {
            $value = $atts['default'];
        }

        // Apply formatting
        if (!empty($atts['format'])) {
            $value = $this->formatValue($value, $atts['format']);
        }

        return esc_html($value);
    }

    /**
     * Display all meta for a post
     * Usage: [amf_meta_all post_id="123" template="list"]
     *
     * @param array $atts
     * @return string
     */
    public function displayAllMeta(array $atts): string
    {
        $atts = shortcode_atts([
            'post_id' => get_the_ID(),
            'template' => 'list',
            'exclude' => '',
            'include' => '',
        ], $atts);

        $post_id = (int) $atts['post_id'];
        $all_meta = get_post_meta($post_id);

        if (empty($all_meta)) {
            return '';
        }

        // Filter meta
        $exclude = !empty($atts['exclude']) ? explode(',', $atts['exclude']) : [];
        $include = !empty($atts['include']) ? explode(',', $atts['include']) : [];

        $output = '';

        if ($atts['template'] === 'table') {
            $output .= '<table class="amf-meta-table">';
            $output .= '<tbody>';

            foreach ($all_meta as $key => $values) {
                if (!empty($exclude) && in_array($key, $exclude, true)) {
                    continue;
                }

                if (!empty($include) && !in_array($key, $include, true)) {
                    continue;
                }

                // Skip WordPress internal meta
                if (strpos($key, '_') === 0) {
                    continue;
                }

                $value = maybe_unserialize($values[0]);
                $output .= '<tr>';
                $output .= '<th>' . esc_html(ucwords(str_replace('_', ' ', $key))) . '</th>';
                $output .= '<td>' . esc_html($this->formatValue($value, 'text')) . '</td>';
                $output .= '</tr>';
            }

            $output .= '</tbody>';
            $output .= '</table>';
        } else {
            $output .= '<ul class="amf-meta-list">';

            foreach ($all_meta as $key => $values) {
                if (!empty($exclude) && in_array($key, $exclude, true)) {
                    continue;
                }

                if (!empty($include) && !in_array($key, $include, true)) {
                    continue;
                }

                // Skip WordPress internal meta
                if (strpos($key, '_') === 0) {
                    continue;
                }

                $value = maybe_unserialize($values[0]);
                $output .= '<li>';
                $output .= '<strong>' . esc_html(ucwords(str_replace('_', ' ', $key))) . ':</strong> ';
                $output .= '<span>' . esc_html($this->formatValue($value, 'text')) . '</span>';
                $output .= '</li>';
            }

            $output .= '</ul>';
        }

        return $output;
    }

    /**
     * Display field with custom formatting
     * Usage: [amf_field key="price" format="currency" before="$"]
     *
     * @param array $atts
     * @return string
     */
    public function displayField(array $atts): string
    {
        $atts = shortcode_atts([
            'key' => '',
            'post_id' => get_the_ID(),
            'format' => '',
            'before' => '',
            'after' => '',
            'default' => '',
        ], $atts);

        if (empty($atts['key'])) {
            return '';
        }

        $value = get_post_meta((int) $atts['post_id'], $atts['key'], true);

        if (empty($value) && !empty($atts['default'])) {
            $value = $atts['default'];
        }

        // Apply formatting
        if (!empty($atts['format'])) {
            $value = $this->formatValue($value, $atts['format']);
        }

        $output = esc_html($atts['before']);
        $output .= esc_html($value);
        $output .= esc_html($atts['after']);

        return $output;
    }

    /**
     * Display gallery
     * Usage: [amf_gallery key="gallery_images" size="thumbnail" columns="3"]
     *
     * @param array $atts
     * @return string
     */
    public function displayGallery(array $atts): string
    {
        $atts = shortcode_atts([
            'key' => 'gallery',
            'post_id' => get_the_ID(),
            'size' => 'thumbnail',
            'columns' => 3,
            'link' => 'file',
        ], $atts);

        $post_id = (int) $atts['post_id'];
        $value = get_post_meta($post_id, $atts['key'], true);

        if (empty($value)) {
            return '';
        }

        // Parse gallery IDs
        $ids = is_array($value) ? $value : explode(',', $value);
        $ids = array_filter(array_map('absint', $ids));

        if (empty($ids)) {
            return '';
        }

        $output = '<div class="amf-gallery amf-gallery-columns-' . esc_attr($atts['columns']) . '">';

        foreach ($ids as $attachment_id) {
            $output .= '<div class="amf-gallery-item">';

            if ($atts['link'] === 'file') {
                $full_url = wp_get_attachment_url($attachment_id);
                $output .= '<a href="' . esc_url($full_url) . '">';
            } elseif ($atts['link'] === 'post') {
                $post_url = get_permalink($attachment_id);
                $output .= '<a href="' . esc_url($post_url) . '">';
            }

            $output .= wp_get_attachment_image($attachment_id, $atts['size']);

            if ($atts['link'] !== 'none') {
                $output .= '</a>';
            }

            $output .= '</div>';
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Display relationship
     * Usage: [amf_relationship key="related_posts" display="title"]
     *
     * @param array $atts
     * @return string
     */
    public function displayRelationship(array $atts): string
    {
        $atts = shortcode_atts([
            'key' => '',
            'post_id' => get_the_ID(),
            'display' => 'title',
            'link' => 'true',
            'separator' => ', ',
        ], $atts);

        if (empty($atts['key'])) {
            return '';
        }

        $post_id = (int) $atts['post_id'];
        $value = get_post_meta($post_id, $atts['key'], true);

        if (empty($value)) {
            return '';
        }

        // Parse IDs
        $ids = is_array($value) ? $value : explode(',', $value);
        $ids = array_filter(array_map('absint', $ids));

        if (empty($ids)) {
            return '';
        }

        $items = [];

        foreach ($ids as $id) {
            $post = get_post($id);
            if (!$post) {
                continue;
            }

            $item = '';

            if ($atts['link'] === 'true') {
                $item .= '<a href="' . esc_url(get_permalink($id)) . '">';
            }

            if ($atts['display'] === 'title') {
                $item .= get_the_title($id);
            } elseif ($atts['display'] === 'excerpt') {
                $item .= get_the_excerpt($id);
            } elseif ($atts['display'] === 'thumbnail') {
                $item .= get_the_post_thumbnail($id, 'thumbnail');
            }

            if ($atts['link'] === 'true') {
                $item .= '</a>';
            }

            $items[] = $item;
        }

        return implode($atts['separator'], $items);
    }

    /**
     * Format value
     *
     * @param mixed $value
     * @param string $format
     * @return mixed
     */
    private function formatValue($value, string $format)
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
            case 'nl2br':
                return nl2br($value);
            case 'wpautop':
                return wpautop($value);
            case 'text':
            default:
                if (is_array($value) || is_object($value)) {
                    return json_encode($value);
                }
                return $value;
        }
    }
}
