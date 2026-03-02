<?php

declare(strict_types=1);

namespace AMF\MetaBox;

use AMF\Traits\Hookable;

/**
 * MetaBox Save Handler
 */
class Save
{
    use Hookable;

    /**
     * Save meta box data
     *
     * @param int $post_id
     * @param \WP_Post $post
     * @return int|void
     */
    public function saveMeta(int $post_id, \WP_Post $post)
    {
        // Verify nonce
        if (!isset($_POST['amf_metabox_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['amf_metabox_nonce'])), 'amf_metabox_save')) {
            return $post_id;
        }

        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }

        // Check user permissions
        if (!current_user_can('edit_post', $post_id)) {
            return $post_id;
        }

        // Check revision
        if (wp_is_post_revision($post_id)) {
            return $post_id;
        }

        // Get registered meta boxes
        $register = Register::getInstance();
        $metaBoxes = $register->getByPostType($post->post_type);

        foreach ($metaBoxes as $id => $config) {
            // Check if save is enabled
            if (!($config['save_post'] ?? true)) {
                continue;
            }

            // Save fields
            $this->saveFields($config['fields'] ?? [], $post_id);
        }
    }

    /**
     * Save fields data
     *
     * @param array $fields
     * @param int $post_id
     * @return void
     */
    private function saveFields(array $fields, int $post_id): void
    {
        // Check if amf_meta is in POST data
        if (!isset($_POST['amf_meta']) || !is_array($_POST['amf_meta'])) {
            return;
        }

        $meta_data = $_POST['amf_meta']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

        foreach ($fields as $field) {
            $field_id = $field['id'] ?? '';

            if (empty($field_id)) {
                continue;
            }

            // Check if field should be saved
            if (!($field['save_field'] ?? true)) {
                continue;
            }

            // Get value
            $value = $meta_data[$field_id] ?? null;

            // Sanitize value
            $value = $this->sanitizeValue($value, $field);

            // Validate value
            if (!$this->validateValue($value, $field, $post_id)) {
                continue;
            }

            // Save or delete
            if ($value === null || $value === '') {
                delete_post_meta($post_id, $field_id);
            } else {
                update_post_meta($post_id, $field_id, $value);
            }

            /**
             * Fires after a field value is saved
             *
             * @param mixed $value Field value
             * @param int $post_id Post ID
             * @param array $field Field configuration
             */
            do_action('amf_field_saved', $field_id, $value, $post_id, $field);
        }
    }

    /**
     * Sanitize field value
     *
     * @param mixed $value
     * @param array $field
     * @return mixed
     */
    private function sanitizeValue($value, array $field)
    {
        $sanitize = $field['sanitize'] ?? null;

        if ($sanitize === null) {
            // Default sanitization based on field type
            return $this->defaultSanitize($value, $field['type'] ?? 'text');
        }

        if (is_callable($sanitize)) {
            return call_user_func($sanitize, $value, $field);
        }

        // Built-in sanitizers
        switch ($sanitize) {
            case 'text':
                return sanitize_text_field($value);
            case 'textarea':
                return sanitize_textarea_field($value);
            case 'html':
                return wp_kses_post($value);
            case 'url':
                return esc_url_raw($value);
            case 'email':
                return sanitize_email($value);
            case 'int':
                return intval($value);
            case 'float':
                return floatval($value);
            case 'bool':
                return (bool) $value;
            case 'json':
                return is_array($value) ? $value : json_decode($value, true);
            default:
                return $value;
        }
    }

    /**
     * Default sanitization based on field type
     *
     * @param mixed $value
     * @param string $type
     * @return mixed
     */
    private function defaultSanitize($value, string $type)
    {
        switch ($type) {
            case 'text':
            case 'email':
            case 'url':
            case 'phone':
            case 'password':
                return sanitize_text_field($value);
            case 'textarea':
            case 'wysiwyg':
                return wp_kses_post($value);
            case 'number':
            case 'range':
            case 'slider':
                return floatval($value);
            case 'checkbox':
            case 'switch':
                return (bool) $value;
            case 'color':
                return sanitize_hex_color($value);
            case 'date':
            case 'time':
            case 'datetime':
                return sanitize_text_field($value);
            case 'select':
            case 'radio':
            case 'checkbox_list':
                return is_array($value) ? array_map('sanitize_text_field', $value) : sanitize_text_field($value);
            case 'file':
            case 'image':
                return absint($value);
            case 'gallery':
                return is_array($value) ? array_map('absint', $value) : [];
            default:
                return $value;
        }
    }

    /**
     * Validate field value
     *
     * @param mixed $value
     * @param array $field
     * @param int $post_id
     * @return bool
     */
    private function validateValue($value, array $field, int $post_id): bool
    {
        $validate = $field['validate'] ?? [];

        if (empty($validate)) {
            // Check if required
            if ($field['required'] ?? false) {
                if ($value === null || $value === '') {
                    add_action('admin_notices', function () use ($field) {
                        echo '<div class="notice notice-error"><p>' . sprintf(
                            /* translators: %s: field name */
                            __('Field "%s" is required.', 'amf'),
                            esc_html($field['name'] ?? '')
                        ) . '</p></div>';
                    });
                    return false;
                }
            }
            return true;
        }

        if (!is_array($validate)) {
            $validate = [$validate];
        }

        foreach ($validate as $callback) {
            if (is_callable($callback)) {
                $result = call_user_func($callback, $value, $field, $post_id);
                if ($result !== true) {
                    if (is_string($result)) {
                        add_action('admin_notices', function () use ($result) {
                            echo '<div class="notice notice-error"><p>' . esc_html($result) . '</p></div>';
                        });
                    }
                    return false;
                }
            }
        }

        return true;
    }
}
