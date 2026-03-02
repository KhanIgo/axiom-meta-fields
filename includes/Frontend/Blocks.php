<?php

declare(strict_types=1);

namespace AMF\Frontend;

use AMF\Traits\Hookable;

/**
 * Gutenberg Blocks
 */
class Blocks
{
    use Hookable;

    /**
     * Initialize
     *
     * @return void
     */
    public function init(): void
    {
        // Check if Gutenberg is enabled
        $settings = get_option('amf_settings', []);
        if (!($settings['enable_gutenberg'] ?? true)) {
            return;
        }

        $this->addAction('init', [$this, 'registerBlocks']);
        $this->addAction('enqueue_block_editor_assets', [$this, 'enqueueEditorAssets']);
    }

    /**
     * Register blocks
     *
     * @return void
     */
    public function registerBlocks(): void
    {
        // Register meta display block
        register_block_type('amf/meta-display', [
            'editor_script' => 'amf-blocks-editor',
            'editor_style' => 'amf-blocks-editor',
            'style' => 'amf-blocks-frontend',
            'render_callback' => [$this, 'renderMetaBlock'],
            'attributes' => [
                'metaKey' => [
                    'type' => 'string',
                    'default' => '',
                ],
                'postId' => [
                    'type' => 'integer',
                    'default' => null,
                ],
                'format' => [
                    'type' => 'string',
                    'default' => 'text',
                ],
                'before' => [
                    'type' => 'string',
                    'default' => '',
                ],
                'after' => [
                    'type' => 'string',
                    'default' => '',
                ],
                'default' => [
                    'type' => 'string',
                    'default' => '',
                ],
                'tagName' => [
                    'type' => 'string',
                    'default' => 'div',
                ],
                'className' => [
                    'type' => 'string',
                    'default' => '',
                ],
            ],
        ]);

        // Register gallery block
        register_block_type('amf/gallery', [
            'editor_script' => 'amf-blocks-editor',
            'editor_style' => 'amf-blocks-editor',
            'style' => 'amf-blocks-frontend',
            'render_callback' => [$this, 'renderGalleryBlock'],
            'attributes' => [
                'metaKey' => [
                    'type' => 'string',
                    'default' => 'gallery',
                ],
                'postId' => [
                    'type' => 'integer',
                    'default' => null,
                ],
                'size' => [
                    'type' => 'string',
                    'default' => 'thumbnail',
                ],
                'columns' => [
                    'type' => 'integer',
                    'default' => 3,
                ],
                'link' => [
                    'type' => 'string',
                    'default' => 'file',
                ],
                'className' => [
                    'type' => 'string',
                    'default' => '',
                ],
            ],
        ]);

        // Register relationship block
        register_block_type('amf/relationship', [
            'editor_script' => 'amf-blocks-editor',
            'editor_style' => 'amf-blocks-editor',
            'style' => 'amf-blocks-frontend',
            'render_callback' => [$this, 'renderRelationshipBlock'],
            'attributes' => [
                'metaKey' => [
                    'type' => 'string',
                    'default' => '',
                ],
                'postId' => [
                    'type' => 'integer',
                    'default' => null,
                ],
                'display' => [
                    'type' => 'string',
                    'default' => 'title',
                ],
                'link' => [
                    'type' => 'boolean',
                    'default' => true,
                ],
                'separator' => [
                    'type' => 'string',
                    'default' => ', ',
                ],
                'className' => [
                    'type' => 'string',
                    'default' => '',
                ],
            ],
        ]);

        // Register all meta block
        register_block_type('amf/all-meta', [
            'editor_script' => 'amf-blocks-editor',
            'editor_style' => 'amf-blocks-editor',
            'style' => 'amf-blocks-frontend',
            'render_callback' => [$this, 'renderAllMetaBlock'],
            'attributes' => [
                'postId' => [
                    'type' => 'integer',
                    'default' => null,
                ],
                'template' => [
                    'type' => 'string',
                    'default' => 'list',
                ],
                'exclude' => [
                    'type' => 'string',
                    'default' => '',
                ],
                'include' => [
                    'type' => 'string',
                    'default' => '',
                ],
                'className' => [
                    'type' => 'string',
                    'default' => '',
                ],
            ],
        ]);
    }

    /**
     * Render meta display block
     *
     * @param array $attributes
     * @return string
     */
    public function renderMetaBlock(array $attributes): string
    {
        $post_id = $attributes['postId'] ?? get_the_ID();
        $meta_key = $attributes['metaKey'] ?? '';

        if (empty($meta_key)) {
            return '<!-- AMF Meta Block: Missing meta key -->';
        }

        $value = get_post_meta($post_id, $meta_key, true);

        if (empty($value) && !empty($attributes['default'])) {
            $value = $attributes['default'];
        }

        // Apply formatting
        if (!empty($attributes['format'])) {
            $value = $this->formatValue($value, $attributes['format']);
        }

        $output = esc_html($attributes['before']);
        $output .= esc_html($value);
        $output .= esc_html($attributes['after']);

        $tag = $attributes['tagName'] ?? 'div';
        $class = 'amf-meta-block ' . ($attributes['className'] ?? '');

        return '<' . $tag . ' class="' . esc_attr($class) . '">' . $output . '</' . $tag . '>';
    }

    /**
     * Render gallery block
     *
     * @param array $attributes
     * @return string
     */
    public function renderGalleryBlock(array $attributes): string
    {
        $post_id = $attributes['postId'] ?? get_the_ID();
        $meta_key = $attributes['metaKey'] ?? 'gallery';

        $value = get_post_meta($post_id, $meta_key, true);

        if (empty($value)) {
            return '<!-- AMF Gallery Block: No images found -->';
        }

        $ids = is_array($value) ? $value : explode(',', $value);
        $ids = array_filter(array_map('absint', $ids));

        if (empty($ids)) {
            return '<!-- AMF Gallery Block: No valid image IDs -->';
        }

        $columns = $attributes['columns'] ?? 3;
        $size = $attributes['size'] ?? 'thumbnail';
        $link = $attributes['link'] ?? 'file';

        $output = '<div class="amf-gallery amf-gallery-block amf-gallery-columns-' . esc_attr($columns) . ' ' . esc_attr($attributes['className'] ?? '') . '">';

        foreach ($ids as $attachment_id) {
            $output .= '<div class="amf-gallery-item">';

            if ($link === 'file') {
                $full_url = wp_get_attachment_url($attachment_id);
                $output .= '<a href="' . esc_url($full_url) . '">';
            } elseif ($link === 'post') {
                $post_url = get_permalink($attachment_id);
                $output .= '<a href="' . esc_url($post_url) . '">';
            }

            $output .= wp_get_attachment_image($attachment_id, $size);

            if ($link !== 'none') {
                $output .= '</a>';
            }

            $output .= '</div>';
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Render relationship block
     *
     * @param array $attributes
     * @return string
     */
    public function renderRelationshipBlock(array $attributes): string
    {
        $post_id = $attributes['postId'] ?? get_the_ID();
        $meta_key = $attributes['metaKey'] ?? '';

        if (empty($meta_key)) {
            return '<!-- AMF Relationship Block: Missing meta key -->';
        }

        $value = get_post_meta($post_id, $meta_key, true);

        if (empty($value)) {
            return '<!-- AMF Relationship Block: No related items -->';
        }

        $ids = is_array($value) ? $value : explode(',', $value);
        $ids = array_filter(array_map('absint', $ids));

        if (empty($ids)) {
            return '<!-- AMF Relationship Block: No valid IDs -->';
        }

        $display = $attributes['display'] ?? 'title';
        $link = $attributes['link'] ?? true;
        $separator = $attributes['separator'] ?? ', ';

        $items = [];

        foreach ($ids as $id) {
            $post = get_post($id);
            if (!$post) {
                continue;
            }

            $item = '';

            if ($link) {
                $item .= '<a href="' . esc_url(get_permalink($id)) . '">';
            }

            if ($display === 'title') {
                $item .= get_the_title($id);
            } elseif ($display === 'excerpt') {
                $item .= get_the_excerpt($id);
            } elseif ($display === 'thumbnail') {
                $item .= get_the_post_thumbnail($id, 'thumbnail');
            }

            if ($link) {
                $item .= '</a>';
            }

            $items[] = $item;
        }

        $class = 'amf-relationship-block ' . ($attributes['className'] ?? '');

        return '<div class="' . esc_attr($class) . '">' . implode(esc_html($separator), $items) . '</div>';
    }

    /**
     * Render all meta block
     *
     * @param array $attributes
     * @return string
     */
    public function renderAllMetaBlock(array $attributes): string
    {
        $post_id = $attributes['postId'] ?? get_the_ID();
        $template = $attributes['template'] ?? 'list';

        $all_meta = get_post_meta($post_id);

        if (empty($all_meta)) {
            return '<!-- AMF All Meta Block: No meta found -->';
        }

        $exclude = !empty($attributes['exclude']) ? explode(',', $attributes['exclude']) : [];
        $include = !empty($attributes['include']) ? explode(',', $attributes['include']) : [];

        $output = '';

        if ($template === 'table') {
            $output .= '<table class="amf-meta-table amf-all-meta-block ' . esc_attr($attributes['className'] ?? '') . '">';
            $output .= '<tbody>';

            foreach ($all_meta as $key => $values) {
                if (!empty($exclude) && in_array($key, $exclude, true)) {
                    continue;
                }

                if (!empty($include) && !in_array($key, $include, true)) {
                    continue;
                }

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
            $output .= '<ul class="amf-meta-list amf-all-meta-block ' . esc_attr($attributes['className'] ?? '') . '">';

            foreach ($all_meta as $key => $values) {
                if (!empty($exclude) && in_array($key, $exclude, true)) {
                    continue;
                }

                if (!empty($include) && !in_array($key, $include, true)) {
                    continue;
                }

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
            default:
                return $value;
        }
    }

    /**
     * Enqueue editor assets
     *
     * @return void
     */
    public function enqueueEditorAssets(): void
    {
        // Register editor script
        wp_register_script(
            'amf-blocks-editor',
            AMF_PLUGIN_URL . 'assets/js/blocks/index.js',
            ['wp-blocks', 'wp-element', 'wp-components', 'wp-editor', 'wp-i18n'],
            AMF_VERSION,
            true
        );

        // Register editor style
        wp_register_style(
            'amf-blocks-editor',
            AMF_PLUGIN_URL . 'assets/css/blocks-editor.css',
            ['wp-edit-blocks'],
            AMF_VERSION
        );

        // Register frontend style
        wp_register_style(
            'amf-blocks-frontend',
            AMF_PLUGIN_URL . 'assets/css/blocks-frontend.css',
            [],
            AMF_VERSION
        );

        // Localize script
        wp_localize_script('amf-blocks-editor', 'cfpBlocks', [
            'pluginUrl' => AMF_PLUGIN_URL,
            'restUrl' => rest_url('amf/v1'),
            'restNonce' => wp_create_nonce('wp_rest'),
        ]);
    }
}
