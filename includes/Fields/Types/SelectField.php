<?php

declare(strict_types=1);

namespace AMF\Fields\Types;

use AMF\Fields\FieldAbstract;

/**
 * Select Field
 */
class SelectField extends FieldAbstract
{
    protected string $type = 'select';

    protected array $defaults = [
        'id' => '',
        'name' => '',
        'desc' => '',
        'std' => '',
        'required' => false,
        'class' => '',
        'style' => '',
        'sanitize' => 'text',
        'validate' => [],
        'save_field' => true,
        'options' => [],
        'multiple' => false,
        'placeholder' => '',
        'ajax' => false,
        'allow_clear' => true,
    ];

    public function render(array $options): void
    {
        $options = $this->getOptions($options);
        $value = $this->getFieldValue($options);
        $name = $this->getFieldName($options);

        // Ensure value is array for multiple select
        if ($options['multiple'] && !is_array($value)) {
            $value = $value !== '' ? [$value] : [];
        }

        echo '<select';
        echo ' id="' . esc_attr($options['id']) . '"';

        if ($options['multiple']) {
            echo ' name="' . esc_attr($name) . '[]"';
            echo ' multiple="multiple"';
        } else {
            echo ' name="' . esc_attr($name) . '"';
        }

        if (!empty($options['class'])) {
            echo ' class="' . esc_attr($options['class']) . '"';
        }

        if (!empty($options['style'])) {
            echo ' style="' . esc_attr($options['style']) . '"';
        }

        $this->renderAttributes($options);
        echo '>';

        // Placeholder option
        if (!$options['multiple'] && !empty($options['placeholder'])) {
            echo '<option value="">' . esc_html($options['placeholder']) . '</option>';
        }

        // Options
        foreach ($options['options'] as $key => $label) {
            $selected = is_array($value) ? in_array((string) $key, $value, true) : (string) $key === (string) $value;

            echo '<option value="' . esc_attr($key) . '"';
            if ($selected) {
                echo ' selected="selected"';
            }
            echo '>' . esc_html($label) . '</option>';
        }

        echo '</select>';
    }
}

/**
 * Checkbox Field
 */
class CheckboxField extends FieldAbstract
{
    protected string $type = 'checkbox';

    protected array $defaults = [
        'id' => '',
        'name' => '',
        'desc' => '',
        'std' => 0,
        'required' => false,
        'class' => '',
        'sanitize' => 'bool',
        'validate' => [],
        'save_field' => true,
        'label' => '',
        'value' => '1',
    ];

    public function render(array $options): void
    {
        $options = $this->getOptions($options);
        $value = $this->getFieldValue($options);
        $name = $this->getFieldName($options);

        $checked = (bool) $value;

        echo '<label class="amf-checkbox-label">';
        echo '<input type="checkbox"';
        echo ' id="' . esc_attr($options['id']) . '"';
        echo ' name="' . esc_attr($name) . '"';
        echo ' value="' . esc_attr($options['value']) . '"';

        if ($checked) {
            echo ' checked="checked"';
        }

        if (!empty($options['class'])) {
            echo ' class="' . esc_attr($options['class']) . '"';
        }

        $this->renderAttributes($options);
        echo ' />';

        $label = !empty($options['label']) ? $options['label'] : $options['desc'];
        if (!empty($label)) {
            echo ' ' . esc_html($label);
        }

        echo '</label>';
    }
}

/**
 * CheckboxList Field
 */
class CheckboxListField extends FieldAbstract
{
    protected string $type = 'checkbox_list';

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
        'options' => [],
        'inline' => false,
        'select_all' => false,
    ];

    public function render(array $options): void
    {
        $options = $this->getOptions($options);
        $value = $this->getFieldValue($options);

        if (!is_array($value)) {
            $value = $value !== '' ? [$value] : [];
        }

        $name = $this->getFieldName($options);
        $class = $options['inline'] ? 'amf-checkbox-list-inline' : 'amf-checkbox-list';

        echo '<div class="' . esc_attr($class) . '">';

        if ($options['select_all'] ?? false) {
            echo '<label class="amf-select-all">';
            echo '<input type="checkbox" class="amf-select-all-checkbox" /> ';
            echo esc_html__('Select All', 'amf');
            echo '</label>';
        }

        foreach ($options['options'] as $key => $label) {
            $checked = in_array((string) $key, $value, true);

            echo '<label class="amf-checkbox-item">';
            echo '<input type="checkbox"';
            echo ' name="' . esc_attr($name) . '[]"';
            echo ' value="' . esc_attr($key) . '"';

            if ($checked) {
                echo ' checked="checked"';
            }

            echo ' /> ';
            echo esc_html($label);
            echo '</label>';
        }

        echo '</div>';
    }
}

/**
 * Radio Field
 */
class RadioField extends FieldAbstract
{
    protected string $type = 'radio';

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
        'options' => [],
        'inline' => false,
    ];

    public function render(array $options): void
    {
        $options = $this->getOptions($options);
        $value = $this->getFieldValue($options);
        $name = $this->getFieldName($options);

        $class = $options['inline'] ? 'amf-radio-list-inline' : 'amf-radio-list';

        echo '<div class="' . esc_attr($class) . '">';

        foreach ($options['options'] as $key => $label) {
            $checked = (string) $key === (string) $value;

            echo '<label class="amf-radio-item">';
            echo '<input type="radio"';
            echo ' id="' . esc_attr($options['id']) . '-' . esc_attr($key) . '"';
            echo ' name="' . esc_attr($name) . '"';
            echo ' value="' . esc_attr($key) . '"';

            if ($checked) {
                echo ' checked="checked"';
            }

            echo ' /> ';
            echo esc_html($label);
            echo '</label>';
        }

        echo '</div>';
    }
}

/**
 * RadioList Field
 */
class RadioListField extends FieldAbstract
{
    protected string $type = 'radio_list';

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
        'options' => [],
        'inline' => false,
    ];

    public function render(array $options): void
    {
        // Same as Radio field
        $radio = new RadioField();
        $radio->render($options);
    }
}

/**
 * Switch Field
 */
class SwitchField extends FieldAbstract
{
    protected string $type = 'switch';

    protected array $defaults = [
        'id' => '',
        'name' => '',
        'desc' => '',
        'std' => 0,
        'required' => false,
        'class' => '',
        'sanitize' => 'bool',
        'validate' => [],
        'save_field' => true,
        'on_label' => 'On',
        'off_label' => 'Off',
    ];

    public function render(array $options): void
    {
        $options = $this->getOptions($options);
        $value = $this->getFieldValue($options);
        $name = $this->getFieldName($options);

        $checked = (bool) $value;

        echo '<div class="amf-switch-wrapper">';
        echo '<label class="amf-switch">';
        echo '<input type="checkbox"';
        echo ' id="' . esc_attr($options['id']) . '"';
        echo ' name="' . esc_attr($name) . '"';
        echo ' value="1"';

        if ($checked) {
            echo ' checked="checked"';
        }

        echo ' class="amf-switch-input" />';
        echo '<span class="amf-switch-slider"></span>';
        echo '</label>';

        echo '<span class="amf-switch-label">';
        echo $checked ? esc_html($options['on_label']) : esc_html($options['off_label']);
        echo '</span>';
        echo '</div>';
    }
}

/**
 * Slider Field
 */
class SliderField extends FieldAbstract
{
    protected string $type = 'slider';

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
    ];

    public function render(array $options): void
    {
        $options = $this->getOptions($options);
        $value = $this->getFieldValue($options);
        $name = $this->getFieldName($options);

        echo '<div class="amf-slider-wrapper">';

        if (!empty($options['prefix'])) {
            echo '<span class="amf-slider-prefix">' . esc_html($options['prefix']) . '</span>';
        }

        echo '<input type="text"';
        echo ' id="' . esc_attr($options['id']) . '"';
        echo ' name="' . esc_attr($name) . '"';
        echo ' value="' . esc_attr($value) . '"';
        echo ' class="amf-slider-input ' . esc_attr($options['class']) . '"';
        echo ' data-min="' . esc_attr($options['min']) . '"';
        echo ' data-max="' . esc_attr($options['max']) . '"';
        echo ' data-step="' . esc_attr($options['step']) . '"';
        echo ' readonly="readonly" />';

        if (!empty($options['suffix'])) {
            echo '<span class="amf-slider-suffix">' . esc_html($options['suffix']) . '</span>';
        }

        echo '<div id="' . esc_attr($options['id']) . '-slider" class="amf-slider"></div>';
        echo '</div>';
    }

    public function enqueueScripts(): void
    {
        wp_enqueue_script('jquery-ui-slider');
    }
}
