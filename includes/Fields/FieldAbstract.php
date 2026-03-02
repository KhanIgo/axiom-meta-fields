<?php

declare(strict_types=1);

namespace AMF\Fields;

/**
 * Abstract Field class
 */
abstract class FieldAbstract implements FieldInterface
{
    /**
     * @var string
     */
    protected string $type = 'text';

    /**
     * @var array
     */
    protected array $defaults = [
        'id' => '',
        'name' => '',
        'desc' => '',
        'std' => '',
        'placeholder' => '',
        'required' => false,
        'class' => '',
        'style' => '',
        'attributes' => [],
        'sanitize' => null,
        'validate' => [],
        'save_field' => true,
        'column' => false,
        'search' => false,
    ];

    /**
     * Render the field
     *
     * @param array $options
     * @return void
     */
    abstract public function render(array $options): void;

    /**
     * Get field value
     *
     * @param int $object_id
     * @param bool $single
     * @return mixed
     */
    public function getValue(int $object_id, bool $single = true)
    {
        $field_id = $this->getFieldId($options = []);
        return get_post_meta($object_id, $field_id, $single);
    }

    /**
     * Update field value
     *
     * @param int $object_id
     * @param mixed $value
     * @return bool
     */
    public function updateValue(int $object_id, $value): bool
    {
        $field_id = $this->getFieldId([]);
        return update_post_meta($object_id, $field_id, $value);
    }

    /**
     * Delete field value
     *
     * @param int $object_id
     * @return bool
     */
    public function deleteValue(int $object_id): bool
    {
        $field_id = $this->getFieldId([]);
        return delete_post_meta($object_id, $field_id);
    }

    /**
     * Get field settings
     *
     * @return array
     */
    public function getSettings(): array
    {
        return $this->defaults;
    }

    /**
     * Enqueue field scripts
     *
     * @return void
     */
    public function enqueueScripts(): void
    {
        // Override in child class if needed
    }

    /**
     * Enqueue field styles
     *
     * @return void
     */
    public function enqueueStyles(): void
    {
        // Override in child class if needed
    }

    /**
     * Get merged options with defaults
     *
     * @param array $options
     * @return array
     */
    protected function getOptions(array $options): array
    {
        return wp_parse_args($options, $this->defaults);
    }

    /**
     * Get field ID
     *
     * @param array $options
     * @return string
     */
    protected function getFieldId(array $options): string
    {
        return $options['id'] ?? '';
    }

    /**
     * Get field name
     *
     * @param array $options
     * @return string
     */
    protected function getFieldName(array $options): string
    {
        return $options['name'] ?? 'amf_meta[' . $this->getFieldId($options) . ']';
    }

    /**
     * Get field value from options
     *
     * @param array $options
     * @return mixed
     */
    protected function getFieldValue(array $options)
    {
        $value = $options['value'] ?? null;

        if ($value === null || $value === '') {
            return $options['std'] ?? $this->defaults['std'];
        }

        return $value;
    }

    /**
     * Render field attributes
     *
     * @param array $options
     * @return void
     */
    protected function renderAttributes(array $options): void
    {
        $attributes = $options['attributes'] ?? [];

        // Add standard attributes
        if (!empty($options['placeholder'])) {
            $attributes['placeholder'] = $options['placeholder'];
        }

        if ($options['required'] ?? false) {
            $attributes['required'] = 'required';
        }

        if ($options['readonly'] ?? false) {
            $attributes['readonly'] = 'readonly';
        }

        if ($options['disabled'] ?? false) {
            $attributes['disabled'] = 'disabled';
        }

        // Render attributes
        foreach ($attributes as $key => $value) {
            echo ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
        }
    }

    /**
     * Escape value for display
     *
     * @param mixed $value
     * @return string
     */
    protected function escapeValue($value): string
    {
        if (is_array($value) || is_object($value)) {
            return esc_attr(json_encode($value));
        }

        return esc_attr($value);
    }
}
