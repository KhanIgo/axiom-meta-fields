<?php

declare(strict_types=1);

namespace AMF\Fields\Types;

use AMF\Fields\FieldAbstract;

/**
 * File Field
 */
class FileField extends FieldAbstract
{
    protected string $type = 'file';

    protected array $defaults = [
        'id' => '',
        'name' => '',
        'desc' => '',
        'std' => '',
        'required' => false,
        'class' => '',
        'sanitize' => 'int',
        'validate' => [],
        'save_field' => true,
        'mime_types' => [],
        'max_size' => '',
        'library_type' => 'all',
    ];

    public function render(array $options): void
    {
        $options = $this->getOptions($options);
        $value = $this->getFieldValue($options);
        $field_id = esc_attr($options['id']);
        $name = $this->getFieldName($options);

        $file_url = '';
        $file_name = '';

        if (!empty($value) && is_numeric($value)) {
            $file_url = wp_get_attachment_url((int) $value);
            $file_name = get_the_title((int) $value);
        }

        echo '<div class="amf-file-wrapper">';
        echo '<input type="hidden" id="' . $field_id . '" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '" class="amf-file-id" />';

        echo '<div class="amf-file-preview">';
        if (!empty($file_url)) {
            echo '<div class="amf-file-info">';
            echo '<span class="amf-file-name">' . esc_html($file_name) . '</span>';
            echo '<a href="' . esc_url($file_url) . '" target="_blank" class="amf-file-download">' . esc_html__('Download', 'amf') . '</a>';
            echo '</div>';
        } else {
            echo '<span class="amf-file-placeholder">' . esc_html__('No file selected', 'amf') . '</span>';
        }
        echo '</div>';

        echo '<div class="amf-file-actions">';
        echo '<button type="button" class="button amf-file-upload">' . esc_html__('Upload File', 'amf') . '</button>';
        echo '<button type="button" class="button amf-file-remove' . (empty($value) ? ' hidden' : '') . '">' . esc_html__('Remove', 'amf') . '</button>';
        echo '</div>';
        echo '</div>';
    }

    public function enqueueScripts(): void
    {
        wp_enqueue_media();
    }
}

/**
 * Image Field
 */
class ImageField extends FieldAbstract
{
    protected string $type = 'image';

    protected array $defaults = [
        'id' => '',
        'name' => '',
        'desc' => '',
        'std' => '',
        'required' => false,
        'class' => '',
        'sanitize' => 'int',
        'validate' => [],
        'save_field' => true,
        'size' => 'thumbnail',
        'thumbnail_size' => 'thumbnail',
        'preview' => true,
    ];

    public function render(array $options): void
    {
        $options = $this->getOptions($options);
        $value = $this->getFieldValue($options);
        $field_id = esc_attr($options['id']);
        $name = $this->getFieldName($options);

        $image_url = '';
        $image_alt = '';

        if (!empty($value) && is_numeric($value)) {
            $image_url = wp_get_attachment_image_url((int) $value, $options['thumbnail_size']);
            $image_alt = get_post_meta((int) $value, '_wp_attachment_image_alt', true);
        }

        echo '<div class="amf-image-wrapper">';
        echo '<input type="hidden" id="' . $field_id . '" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '" class="amf-image-id" />';

        echo '<div class="amf-image-preview"' . ($options['preview'] ? '' : ' style="display:none;"') . '>';
        if (!empty($image_url)) {
            echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($image_alt) . '" class="amf-image-thumbnail" />';
        } else {
            echo '<div class="amf-image-placeholder">' . esc_html__('No image selected', 'amf') . '</div>';
        }
        echo '</div>';

        echo '<div class="amf-image-actions">';
        echo '<button type="button" class="button amf-image-upload">' . esc_html__('Upload Image', 'amf') . '</button>';
        echo '<button type="button" class="button amf-image-remove' . (empty($value) ? ' hidden' : '') . '">' . esc_html__('Remove Image', 'amf') . '</button>';
        echo '</div>';
        echo '</div>';
    }

    public function enqueueScripts(): void
    {
        wp_enqueue_media();
    }
}

/**
 * Gallery Field
 */
class GalleryField extends FieldAbstract
{
    protected string $type = 'gallery';

    protected array $defaults = [
        'id' => '',
        'name' => '',
        'desc' => '',
        'std' => '',
        'required' => false,
        'class' => '',
        'sanitize' => 'text',
        'validate' => [],
        'save_field' => true,
        'max_files' => 0,
        'size' => 'medium',
        'thumbnail_size' => 'thumbnail',
    ];

    public function render(array $options): void
    {
        $options = $this->getOptions($options);
        $value = $this->getFieldValue($options);
        $field_id = esc_attr($options['id']);
        $name = $this->getFieldName($options);

        if (!is_array($value)) {
            $value = !empty($value) ? explode(',', $value) : [];
        }

        echo '<div class="amf-gallery-wrapper">';
        echo '<input type="hidden" id="' . $field_id . '" name="' . esc_attr($name) . '" value="' . esc_attr(implode(',', $value)) . '" class="amf-gallery-ids" />';

        echo '<ul class="amf-gallery-list">';
        foreach ($value as $attachment_id) {
            if (empty($attachment_id)) {
                continue;
            }
            $image_url = wp_get_attachment_image_url((int) $attachment_id, $options['thumbnail_size']);
            if ($image_url) {
                echo '<li class="amf-gallery-item" data-id="' . esc_attr($attachment_id) . '">';
                echo '<img src="' . esc_url($image_url) . '" alt="" />';
                echo '<button type="button" class="amf-gallery-remove dashicons dashicons-no"></button>';
                echo '</li>';
            }
        }
        echo '</ul>';

        echo '<div class="amf-gallery-actions">';
        echo '<button type="button" class="button amf-gallery-add">' . esc_html__('Add to Gallery', 'amf') . '</button>';
        echo '</div>';
        echo '</div>';
    }

    public function enqueueScripts(): void
    {
        wp_enqueue_media();
    }
}

/**
 * Video Field
 */
class VideoField extends FieldAbstract
{
    protected string $type = 'video';

    protected array $defaults = [
        'id' => '',
        'name' => '',
        'desc' => '',
        'std' => '',
        'required' => false,
        'class' => '',
        'sanitize' => 'text',
        'validate' => [],
        'save_field' => true,
        'mime_types' => ['mp4', 'webm', 'ogg'],
        'embed_support' => true,
    ];

    public function render(array $options): void
    {
        $options = $this->getOptions($options);
        $value = $this->getFieldValue($options);
        $field_id = esc_attr($options['id']);
        $name = $this->getFieldName($options);

        $is_embed = false;
        $video_url = '';

        if (!empty($value)) {
            // Check if it's an embed URL
            if (filter_var($value, FILTER_VALIDATE_URL)) {
                $is_embed = $this->isEmbedUrl($value);
                $video_url = $value;
            } elseif (is_numeric($value)) {
                $video_url = wp_get_attachment_url((int) $value);
            }
        }

        echo '<div class="amf-video-wrapper">';
        echo '<input type="hidden" id="' . $field_id . '" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '" class="amf-video-value" />';

        if (!empty($video_url)) {
            echo '<div class="amf-video-preview">';
            if ($is_embed) {
                echo wp_oembed_get($video_url);
            } else {
                echo do_shortcode('[video src="' . esc_url($video_url) . '"]');
            }
            echo '</div>';
        }

        echo '<div class="amf-video-actions">';
        echo '<button type="button" class="button amf-video-upload">' . esc_html__('Upload/Embed Video', 'amf') . '</button>';
        echo '<button type="button" class="button amf-video-remove' . (empty($value) ? ' hidden' : '') . '">' . esc_html__('Remove', 'amf') . '</button>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * Check if URL is an embeddable video URL
     *
     * @param string $url
     * @return bool
     */
    private function isEmbedUrl(string $url): bool
    {
        $embed_hosts = ['youtube.com', 'youtu.be', 'vimeo.com', 'dailymotion.com'];

        $host = parse_url($url, PHP_URL_HOST);
        return in_array($host, $embed_hosts, true);
    }

    public function enqueueScripts(): void
    {
        wp_enqueue_media();
    }
}

/**
 * Audio Field
 */
class AudioField extends FieldAbstract
{
    protected string $type = 'audio';

    protected array $defaults = [
        'id' => '',
        'name' => '',
        'desc' => '',
        'std' => '',
        'required' => false,
        'class' => '',
        'sanitize' => 'text',
        'validate' => [],
        'save_field' => true,
        'mime_types' => ['mp3', 'wav', 'ogg'],
        'embed_support' => true,
    ];

    public function render(array $options): void
    {
        $options = $this->getOptions($options);
        $value = $this->getFieldValue($options);
        $field_id = esc_attr($options['id']);
        $name = $this->getFieldName($options);

        $audio_url = '';

        if (!empty($value)) {
            if (is_numeric($value)) {
                $audio_url = wp_get_attachment_url((int) $value);
            } else {
                $audio_url = $value;
            }
        }

        echo '<div class="amf-audio-wrapper">';
        echo '<input type="hidden" id="' . $field_id . '" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '" class="amf-audio-value" />';

        if (!empty($audio_url)) {
            echo '<div class="amf-audio-preview">';
            echo do_shortcode('[audio src="' . esc_url($audio_url) . '"]');
            echo '</div>';
        }

        echo '<div class="amf-audio-actions">';
        echo '<button type="button" class="button amf-audio-upload">' . esc_html__('Upload/Embed Audio', 'amf') . '</button>';
        echo '<button type="button" class="button amf-audio-remove' . (empty($value) ? ' hidden' : '') . '">' . esc_html__('Remove', 'amf') . '</button>';
        echo '</div>';
        echo '</div>';
    }

    public function enqueueScripts(): void
    {
        wp_enqueue_media();
    }
}
