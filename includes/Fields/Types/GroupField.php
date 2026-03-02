<?php

declare(strict_types=1);

namespace AMF\Fields\Types;

use AMF\Fields\FieldAbstract;

/**
 * Group Field - Field grouping
 */
class GroupField extends FieldAbstract
{
    protected string $type = 'group';

    protected array $defaults = [
        'id' => '',
        'name' => '',
        'desc' => '',
        'required' => false,
        'class' => '',
        'sanitize' => 'text',
        'validate' => [],
        'save_field' => true,
        'fields' => [],
        'collapsible' => false,
        'cloneable' => false,
        'min' => 0,
        'max' => 0,
        'sortable' => true,
    ];

    public function render(array $options): void
    {
        $options = $this->getOptions($options);
        $value = $this->getFieldValue($options);
        $field_id = esc_attr($options['id']);

        // Ensure value is array
        if (!is_array($value)) {
            $value = [];
        }

        // For cloneable groups, value should be array of arrays
        if ($options['cloneable']) {
            $this->renderCloneable($options, $value);
        } else {
            $this->renderSingle($options, $value);
        }
    }

    /**
     * Render single group
     *
     * @param array $options
     * @param array $value
     * @return void
     */
    private function renderSingle(array $options, array $value): void
    {
        echo '<div class="amf-group amf-group-single">';

        if ($options['collapsible'] ?? false) {
            echo '<div class="amf-group-header">';
            echo '<span class="amf-group-toggle dashicons dashicons-arrow-down"></span>';
            echo '<span class="amf-group-title">' . esc_html($options['name'] ?: __('Group', 'amf')) . '</span>';
            echo '</div>';
        }

        echo '<div class="amf-group-content' . ($options['collapsible'] ? ' amf-group-collapsed' : '') . '">';

        foreach ($options['fields'] as $field) {
            $field_name = $options['name'] . '[' . $field['id'] . ']';
            $field_value = $value[$field['id']] ?? ($field['std'] ?? '');

            $this->renderField($field, $field_name, $field_value);
        }

        echo '</div>';
        echo '</div>';
    }

    /**
     * Render cloneable group
     *
     * @param array $options
     * @param array $value
     * @return void
     */
    private function renderCloneable(array $options, array $value): void
    {
        $min = max(1, (int) $options['min']);
        $max = (int) $options['max'];

        // Ensure we have at least min items
        while (count($value) < $min) {
            $value[] = [];
        }

        echo '<div class="amf-group amf-group-cloneable" data-min="' . esc_attr($min) . '" data-max="' . esc_attr($max) . '">';

        if (!empty($options['name'])) {
            echo '<div class="amf-group-label">' . esc_html($options['name']) . '</div>';
        }

        echo '<div class="amf-group-clones">';

        foreach ($value as $index => $group_value) {
            echo '<div class="amf-group-clone">';
            echo '<div class="amf-group-clone-header">';
            echo '<span class="amf-group-clone-sort dashicons dashicons-menu"></span>';
            echo '<span class="amf-group-clone-title">' . sprintf(__('Group %d', 'amf'), $index + 1) . '</span>';
            echo '<div class="amf-group-clone-actions">';
            echo '<button type="button" class="button amf-clone-add" title="' . esc_attr__('Add', 'amf') . '"><span class="dashicons dashicons-plus"></span></button>';
            echo '<button type="button" class="button amf-clone-remove" title="' . esc_attr__('Remove', 'amf') . '"><span class="dashicons dashicons-trash"></span></button>';
            echo '</div>';
            echo '</div>';

            echo '<div class="amf-group-clone-content">';
            foreach ($options['fields'] as $field) {
                $field_name = $options['name'] . '[' . $index . '][' . $field['id'] . ']';
                $field_value = $group_value[$field['id']] ?? ($field['std'] ?? '');

                $this->renderField($field, $field_name, $field_value);
            }
            echo '</div>';
            echo '</div>';
        }

        echo '</div>';

        // Add button
        if ($max === 0 || count($value) < $max) {
            echo '<button type="button" class="button amf-clone-add-button">' . esc_html__('Add Group', 'amf') . '</button>';
        }

        echo '</div>';
    }

    /**
     * Render a field within the group
     *
     * @param array $field
     * @param string $name
     * @param mixed $value
     * @return void
     */
    private function renderField(array $field, string $name, $value): void
    {
        $field_type = $field['type'] ?? 'text';
        $field_instance = \AMF\Fields\FieldFactory::getInstance()->create($field_type);

        if (!$field_instance) {
            return;
        }

        $field['name'] = $name;
        $field['value'] = $value;

        echo '<div class="amf-field amf-field-' . esc_attr($field_type) . '">';

        if (!empty($field['name'])) {
            echo '<label class="amf-field-label">' . esc_html($field['name']) . '</label>';
        }

        echo '<div class="amf-field-input">';
        $field_instance->render($field);
        echo '</div>';

        if (!empty($field['desc'])) {
            echo '<p class="amf-field-description">' . esc_html($field['desc']) . '</p>';
        }

        echo '</div>';
    }

    public function enqueueScripts(): void
    {
        wp_enqueue_script('jquery-ui-sortable');
    }
}

/**
 * Repeater Field - Repeatable field group
 */
class RepeaterField extends FieldAbstract
{
    protected string $type = 'repeater';

    protected array $defaults = [
        'id' => '',
        'name' => '',
        'desc' => '',
        'required' => false,
        'class' => '',
        'sanitize' => 'text',
        'validate' => [],
        'save_field' => true,
        'fields' => [],
        'min' => 0,
        'max' => 0,
        'sortable' => true,
        'cloneable' => true,
        'add_label' => 'Add Row',
        'row_label' => 'Row',
    ];

    public function render(array $options): void
    {
        $options = $this->getOptions($options);
        $value = $this->getFieldValue($options);
        $field_id = esc_attr($options['id']);

        // Ensure value is array of arrays
        if (!is_array($value)) {
            $value = [];
        }

        $min = max(0, (int) $options['min']);
        $max = (int) $options['max'];

        // Ensure we have at least min items
        while (count($value) < $min) {
            $value[] = [];
        }

        echo '<div class="amf-repeater" data-min="' . esc_attr($min) . '" data-max="' . esc_attr($max) . '">';

        if (!empty($options['name'])) {
            echo '<div class="amf-repeater-label">' . esc_html($options['name']) . '</div>';
        }

        echo '<div class="amf-repeater-rows">';

        foreach ($value as $index => $row_value) {
            echo '<div class="amf-repeater-row">';
            echo '<div class="amf-repeater-row-header">';
            echo '<span class="amf-repeater-sort dashicons dashicons-menu"></span>';
            echo '<span class="amf-repeater-row-title">' . esc_html($options['row_label'] . ' ' . ($index + 1)) . '</span>';
            echo '<div class="amf-repeater-row-actions">';
            echo '<button type="button" class="button amf-repeater-remove" title="' . esc_attr__('Remove', 'amf') . '"><span class="dashicons dashicons-trash"></span></button>';
            echo '</div>';
            echo '</div>';

            echo '<div class="amf-repeater-row-content">';
            foreach ($options['fields'] as $field) {
                $field_name = $options['id'] . '[' . $index . '][' . $field['id'] . ']';
                $field_value = $row_value[$field['id']] ?? ($field['std'] ?? '');

                $this->renderField($field, $field_name, $field_value);
            }
            echo '</div>';
            echo '</div>';
        }

        echo '</div>';

        // Add button
        if ($max === 0 || count($value) < $max) {
            echo '<button type="button" class="button amf-repeater-add">' . esc_html($options['add_label']) . '</button>';
        }

        echo '</div>';
    }

    /**
     * Render a field within the repeater
     *
     * @param array $field
     * @param string $name
     * @param mixed $value
     * @return void
     */
    private function renderField(array $field, string $name, $value): void
    {
        $field_type = $field['type'] ?? 'text';
        $field_instance = \AMF\Fields\FieldFactory::getInstance()->create($field_type);

        if (!$field_instance) {
            return;
        }

        $field['name'] = $name;
        $field['value'] = $value;

        echo '<div class="amf-field amf-field-' . esc_attr($field_type) . '">';

        if (!empty($field['name'])) {
            echo '<label class="amf-field-label">' . esc_html($field['name']) . '</label>';
        }

        echo '<div class="amf-field-input">';
        $field_instance->render($field);
        echo '</div>';

        if (!empty($field['desc'])) {
            echo '<p class="amf-field-description">' . esc_html($field['desc']) . '</p>';
        }

        echo '</div>';
    }

    public function enqueueScripts(): void
    {
        wp_enqueue_script('jquery-ui-sortable');
    }
}

/**
 * Tab Field - Tabbed interface
 */
class TabField extends FieldAbstract
{
    protected string $type = 'tab';

    protected array $defaults = [
        'id' => '',
        'name' => '',
        'fields' => [],
        'active_tab' => 0,
    ];

    public function render(array $options): void
    {
        $options = $this->getOptions($options);
        $field_id = esc_attr($options['id']);

        echo '<div class="amf-tabs" data-active="' . esc_attr($options['active_tab']) . '">';
        echo '<ul class="amf-tab-nav">';

        foreach ($options['fields'] as $index => $tab) {
            $tab_id = $field_id . '-tab-' . $index;
            $active = ($index === (int) $options['active_tab']) ? ' active' : '';

            echo '<li class="amf-tab-item' . $active . '">';
            echo '<a href="#' . esc_attr($tab_id) . '" class="amf-tab-link" data-tab="' . esc_attr($index) . '">';
            echo esc_html($tab['name'] ?? __('Tab', 'amf'));
            echo '</a>';
            echo '</li>';
        }

        echo '</ul>';

        echo '<div class="amf-tab-panels">';

        foreach ($options['fields'] as $index => $tab) {
            $tab_id = $field_id . '-tab-' . $index;
            $active = ($index === (int) $options['active_tab']) ? ' active' : '';

            echo '<div id="' . esc_attr($tab_id) . '" class="amf-tab-panel' . $active . '">';

            // Render tab fields
            if (isset($tab['fields']) && is_array($tab['fields'])) {
                foreach ($tab['fields'] as $field) {
                    $this->renderField($field);
                }
            }

            echo '</div>';
        }

        echo '</div>';
        echo '</div>';
    }

    /**
     * Render a field within the tab
     *
     * @param array $field
     * @return void
     */
    private function renderField(array $field): void
    {
        $field_type = $field['type'] ?? 'text';
        $field_instance = \AMF\Fields\FieldFactory::getInstance()->create($field_type);

        if (!$field_instance) {
            return;
        }

        echo '<div class="amf-field amf-field-' . esc_attr($field_type) . '">';

        if (!empty($field['name'])) {
            echo '<label class="amf-field-label">' . esc_html($field['name']) . '</label>';
        }

        echo '<div class="amf-field-input">';
        $field_instance->render($field);
        echo '</div>';

        if (!empty($field['desc'])) {
            echo '<p class="amf-field-description">' . esc_html($field['desc']) . '</p>';
        }

        echo '</div>';
    }
}

/**
 * Divider Field - Visual separator
 */
class DividerField extends FieldAbstract
{
    protected string $type = 'divider';

    protected array $defaults = [
        'id' => '',
        'label' => '',
        'icon' => '',
    ];

    public function render(array $options): void
    {
        $options = $this->getOptions($options);

        echo '<div class="amf-divider">';

        if (!empty($options['icon'])) {
            echo '<span class="amf-divider-icon ' . esc_attr($options['icon']) . '"></span>';
        }

        if (!empty($options['label'])) {
            echo '<span class="amf-divider-label">' . esc_html($options['label']) . '</span>';
        }

        echo '</div>';
    }
}

/**
 * Heading Field - Section heading
 */
class HeadingField extends FieldAbstract
{
    protected string $type = 'heading';

    protected array $defaults = [
        'id' => '',
        'name' => '',
        'desc' => '',
        'icon' => '',
        'level' => 'h3',
    ];

    public function render(array $options): void
    {
        $options = $this->getOptions($options);
        $tag = in_array($options['level'], ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'], true) ? $options['level'] : 'h3';

        echo '<div class="amf-heading">';

        if (!empty($options['icon'])) {
            echo '<span class="amf-heading-icon ' . esc_attr($options['icon']) . '"></span>';
        }

        echo '<' . $tag . ' class="amf-heading-title">' . esc_html($options['name']) . '</' . $tag . '>';

        if (!empty($options['desc'])) {
            echo '<p class="amf-heading-description">' . esc_html($options['desc']) . '</p>';
        }

        echo '</div>';
    }
}

/**
 * Code Field - Code editor
 */
class CodeField extends FieldAbstract
{
    protected string $type = 'code';

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
        'language' => 'javascript',
        'indent_size' => 4,
        'line_numbers' => true,
        'height' => 300,
    ];

    public function render(array $options): void
    {
        $options = $this->getOptions($options);
        $value = $this->getFieldValue($options);
        $name = $this->getFieldName($options);
        $field_id = esc_attr($options['id']);

        echo '<textarea';
        echo ' id="' . $field_id . '"';
        echo ' name="' . esc_attr($name) . '"';
        echo ' class="amf-code-editor ' . esc_attr($options['class']) . '"';
        echo ' data-language="' . esc_attr($options['language']) . '"';
        echo ' data-indent-size="' . esc_attr($options['indent_size']) . '"';
        echo ' data-line-numbers="' . ($options['line_numbers'] ? '1' : '0') . '"';
        echo ' data-height="' . esc_attr($options['height']) . '"';
        echo '>';
        echo esc_textarea($value);
        echo '</textarea>';
    }

    public function enqueueScripts(): void
    {
        // CodeMirror or similar library would be enqueued here
        wp_enqueue_script('code-editor');
        wp_enqueue_style('code-editor');
    }
}

/**
 * Map Field - Google/OpenStreetMap
 */
class MapField extends FieldAbstract
{
    protected string $type = 'map';

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
        'center' => [0, 0],
        'zoom' => 13,
        'api_key' => '',
        'provider' => 'google',
        'height' => 400,
    ];

    public function render(array $options): void
    {
        $options = $this->getOptions($options);
        $value = $this->getFieldValue($options);
        $field_id = esc_attr($options['id']);

        // Parse value
        $lat = 0;
        $lng = 0;
        $address = '';

        if (is_array($value)) {
            $lat = $value['lat'] ?? 0;
            $lng = $value['lng'] ?? 0;
            $address = $value['address'] ?? '';
        }

        echo '<div class="amf-map-wrapper">';
        echo '<input type="hidden" id="' . $field_id . '" name="' . esc_attr($options['name']) . '" value="' . esc_attr(json_encode(['lat' => $lat, 'lng' => $lng])) . '" class="amf-map-value" />';

        echo '<div class="amf-map-search">';
        echo '<input type="text" class="amf-map-search-input" placeholder="' . esc_attr__('Search address...', 'amf') . '" value="' . esc_attr($address) . '" />';
        echo '<button type="button" class="button amf-map-search-btn">' . esc_html__('Search', 'amf') . '</button>';
        echo '</div>';

        echo '<div id="' . $field_id . '-map" class="amf-map" style="height: ' . esc_attr($options['height']) . 'px;" data-lat="' . esc_attr($lat) . '" data-lng="' . esc_attr($lng) . '" data-zoom="' . esc_attr($options['zoom']) . '"></div>';

        echo '<div class="amf-map-coords">';
        echo '<span>' . esc_html__('Latitude:', 'amf') . ' <input type="text" class="amf-map-lat" value="' . esc_attr($lat) . '" readonly /></span>';
        echo '<span>' . esc_html__('Longitude:', 'amf') . ' <input type="text" class="amf-map-lng" value="' . esc_attr($lng) . '" readonly /></span>';
        echo '</div>';

        echo '</div>';
    }

    public function enqueueScripts(): void
    {
        // Google Maps or Leaflet would be enqueued here
        $api_key = $this->defaults['api_key'];
        if (!empty($api_key)) {
            wp_enqueue_script('google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . $api_key, [], null, true);
        }
    }
}

/**
 * Range Field - Range slider
 */
class RangeField extends FieldAbstract
{
    protected string $type = 'range';

    protected array $defaults = [
        'id' => '',
        'name' => '',
        'desc' => '',
        'std' => 0,
        'required' => false,
        'class' => '',
        'sanitize' => 'int',
        'validate' => [],
        'save_field' => true,
        'min' => 0,
        'max' => 100,
        'step' => 1,
        'prefix' => '',
        'suffix' => '',
        'show_value' => true,
    ];

    public function render(array $options): void
    {
        $options = $this->getOptions($options);
        $value = $this->getFieldValue($options);
        $name = $this->getFieldName($options);

        echo '<div class="amf-range-wrapper">';

        if ($options['show_value']) {
            echo '<div class="amf-range-value">';
            if (!empty($options['prefix'])) {
                echo '<span class="amf-range-prefix">' . esc_html($options['prefix']) . '</span>';
            }
            echo '<span class="amf-range-number">' . esc_html($value) . '</span>';
            if (!empty($options['suffix'])) {
                echo '<span class="amf-range-suffix">' . esc_html($options['suffix']) . '</span>';
            }
            echo '</div>';
        }

        echo '<input type="range"';
        echo ' id="' . esc_attr($options['id']) . '"';
        echo ' name="' . esc_attr($name) . '"';
        echo ' value="' . esc_attr($value) . '"';
        echo ' min="' . esc_attr($options['min']) . '"';
        echo ' max="' . esc_attr($options['max']) . '"';
        echo ' step="' . esc_attr($options['step']) . '"';
        echo ' class="amf-range-input ' . esc_attr($options['class']) . '"';
        echo ' />';

        echo '</div>';
    }
}
