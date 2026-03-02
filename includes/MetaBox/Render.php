<?php

declare(strict_types=1);

namespace AMF\MetaBox;

use AMF\Traits\Hookable;

/**
 * MetaBox Rendering
 */
class Render
{
    use Hookable;

    /**
     * Add meta boxes to WordPress
     *
     * @param string $post_type
     * @return void
     */
    public function addMetaBoxes(string $post_type): void
    {
        $register = Register::getInstance();
        $metaBoxes = $register->getByPostType($post_type);

        foreach ($metaBoxes as $id => $config) {
            // Check visibility
            if (!$this->isVisible($config)) {
                continue;
            }

            add_meta_box(
                $id,
                $config['title'],
                [$this, 'renderMetaBox'],
                $post_type,
                $config['context'],
                $config['priority'],
                ['config' => $config]
            );
        }
    }

    /**
     * Render a meta box
     *
     * @param \WP_Post $post
     * @param array $args
     * @return void
     */
    public function renderMetaBox(\WP_Post $post, array $args): void
    {
        $config = $args['args']['config'] ?? [];
        $fields = $config['fields'] ?? [];

        if (empty($fields)) {
            echo '<p>' . __('No fields configured for this meta box.', 'amf') . '</p>';
            return;
        }

        // Add nonce for security
        wp_nonce_field('amf_metabox_save', 'amf_metabox_nonce');

        echo '<div class="amf-meta-box-content">';

        foreach ($fields as $field) {
            $this->renderField($field, $post->ID);
        }

        echo '</div>';
    }

    /**
     * Render a single field
     *
     * @param array $field
     * @param int $post_id
     * @return void
     */
    private function renderField(array $field, int $post_id): void
    {
        $field_type = $field['type'] ?? 'text';
        $field_id = $field['id'] ?? '';
        $field_name = 'amf_meta[' . $field_id . ']';
        $field_label = $field['name'] ?? '';
        $field_desc = $field['desc'] ?? '';

        // Get saved value
        $value = get_post_meta($post_id, $field_id, true);

        // Get field instance
        $field_instance = \AMF\Fields\FieldFactory::getInstance()->create($field_type);

        if (!$field_instance) {
            echo '<p>' . sprintf(
                /* translators: %s: field type */
                __('Field type "%s" not found.', 'amf'),
                esc_html($field_type)
            ) . '</p>';
            return;
        }

        // Prepare field options
        $options = array_merge($field, [
            'name' => $field_name,
            'value' => $value,
        ]);

        // Render field wrapper
        $field_class = 'amf-field amf-field-' . esc_attr($field_type);
        if ($field['class'] ?? false) {
            $field_class .= ' ' . esc_attr($field['class']);
        }

        echo '<div class="' . esc_attr($field_class) . '" style="' . esc_attr($field['style'] ?? '') . '">';

        // Render label
        if (!empty($field_label)) {
            echo '<label class="amf-field-label">';
            echo esc_html($field_label);

            if ($field['required'] ?? false) {
                echo '<span class="amf-required">*</span>';
            }

            echo '</label>';
        }

        // Render field
        echo '<div class="amf-field-input">';
        $field_instance->render($options);
        echo '</div>';

        // Render description
        if (!empty($field_desc)) {
            echo '<p class="amf-field-description">' . esc_html($field_desc) . '</p>';
        }

        echo '</div>';
    }

    /**
     * Check if meta box is visible
     *
     * @param array $config
     * @return bool
     */
    private function isVisible(array $config): bool
    {
        $visible = $config['visible'] ?? true;

        // If it's a callable, call it
        if (is_callable($visible)) {
            return (bool) call_user_func($visible);
        }

        // If it's an array of conditions, evaluate them
        if (is_array($visible) && isset($visible['relation'])) {
            return $this->evaluateConditions($visible);
        }

        return (bool) $visible;
    }

    /**
     * Evaluate visibility conditions
     *
     * @param array $conditions
     * @return bool
     */
    private function evaluateConditions(array $conditions): bool
    {
        $relation = $conditions['relation'] ?? 'AND';
        $results = [];

        foreach ($conditions as $condition) {
            if (!is_array($condition) || !isset($condition['key'])) {
                continue;
            }

            $results[] = $this->evaluateCondition($condition);
        }

        if (empty($results)) {
            return true;
        }

        if ($relation === 'OR') {
            return in_array(true, $results, true);
        }

        return !in_array(false, $results, true);
    }

    /**
     * Evaluate a single condition
     *
     * @param array $condition
     * @return bool
     */
    private function evaluateCondition(array $condition): bool
    {
        $key = $condition['key'] ?? '';
        $value = $condition['value'] ?? '';
        $operator = $condition['operator'] ?? '=';

        // Get current value (from post, user, etc.)
        $current_value = $this->getCurrentValue($key);

        return $this->compare($current_value, $value, $operator);
    }

    /**
     * Get current value for condition
     *
     * @param string $key
     * @return mixed
     */
    private function getCurrentValue(string $key)
    {
        global $post;

        if (!$post) {
            return null;
        }

        // Check post meta
        $meta = get_post_meta($post->ID, $key, true);
        if ($meta !== '') {
            return $meta;
        }

        // Check post properties
        if (property_exists($post, $key)) {
            return $post->$key;
        }

        return null;
    }

    /**
     * Compare values
     *
     * @param mixed $current
     * @param mixed $expected
     * @param string $operator
     * @return bool
     */
    private function compare($current, $expected, string $operator): bool
    {
        switch ($operator) {
            case '=':
            case '==':
                return $current == $expected;
            case '===':
                return $current === $expected;
            case '!=':
            case '<>':
                return $current != $expected;
            case '!==':
                return $current !== $expected;
            case '>':
                return $current > $expected;
            case '>=':
                return $current >= $expected;
            case '<':
                return $current < $expected;
            case '<=':
                return $current <= $expected;
            case 'IN':
                return is_array($expected) && in_array($current, $expected, true);
            case 'NOT IN':
                return is_array($expected) && !in_array($current, $expected, true);
            case 'CONTAINS':
                return is_string($current) && strpos($current, (string) $expected) !== false;
            default:
                return false;
        }
    }

    /**
     * Enqueue scripts and styles for meta boxes
     *
     * @param string $hook
     * @return void
     */
    public function enqueueScripts(string $hook): void
    {
        if ($hook !== 'post.php' && $hook !== 'post-new.php') {
            return;
        }

        // Field-specific scripts
        wp_enqueue_script(
            'amf-fields',
            AMF_PLUGIN_URL . 'assets/js/fields.js',
            ['jquery'],
            AMF_VERSION,
            true
        );

        // Localize
        wp_localize_script('amf-fields', 'cfpFields', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('amf_fields'),
        ]);
    }
}
