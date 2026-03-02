<?php

declare(strict_types=1);

namespace AMF\Fields\Types;

use AMF\Fields\FieldAbstract;

/**
 * Textarea Field
 */
class TextareaField extends FieldAbstract
{
    protected string $type = 'textarea';

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
        'sanitize' => 'textarea',
        'validate' => [],
        'save_field' => true,
        'rows' => 4,
        'cols' => 50,
        'maxlength' => '',
    ];

    public function render(array $options): void
    {
        $options = $this->getOptions($options);
        $value = $this->getFieldValue($options);
        $name = $this->getFieldName($options);

        echo '<textarea';
        echo ' id="' . esc_attr($options['id']) . '"';
        echo ' name="' . esc_attr($name) . '"';
        echo ' rows="' . esc_attr($options['rows']) . '"';
        echo ' cols="' . esc_attr($options['cols']) . '"';

        if (!empty($options['maxlength'])) {
            echo ' maxlength="' . esc_attr($options['maxlength']) . '"';
        }

        if (!empty($options['class'])) {
            echo ' class="' . esc_attr($options['class']) . '"';
        }

        if (!empty($options['style'])) {
            echo ' style="' . esc_attr($options['style']) . '"';
        }

        if (!empty($options['placeholder'])) {
            echo ' placeholder="' . esc_attr($options['placeholder']) . '"';
        }

        $this->renderAttributes($options);
        echo '>' . esc_textarea($value) . '</textarea>';
    }
}

/**
 * WYSIWYG Field
 */
class WysiwygField extends FieldAbstract
{
    protected string $type = 'wysiwyg';

    protected array $defaults = [
        'id' => '',
        'name' => '',
        'desc' => '',
        'std' => '',
        'required' => false,
        'class' => '',
        'sanitize' => 'html',
        'validate' => [],
        'save_field' => true,
        'editor_settings' => [],
        'media_buttons' => true,
        'textarea_rows' => 10,
        'teeny' => false,
    ];

    public function render(array $options): void
    {
        $options = $this->getOptions($options);
        $value = $this->getFieldValue($options);
        $editor_id = esc_attr($options['id']);

        $settings = wp_parse_args($options['editor_settings'], [
            'media_buttons' => $options['media_buttons'],
            'textarea_rows' => $options['textarea_rows'],
            'teeny' => $options['teeny'],
            'textarea_name' => $this->getFieldName($options),
        ]);

        wp_editor($value, $editor_id, $settings);
    }
}

/**
 * Number Field
 */
class NumberField extends FieldAbstract
{
    protected string $type = 'number';

    protected array $defaults = [
        'id' => '',
        'name' => '',
        'desc' => '',
        'std' => 0,
        'placeholder' => '',
        'required' => false,
        'class' => '',
        'style' => '',
        'attributes' => [],
        'sanitize' => 'float',
        'validate' => [],
        'save_field' => true,
        'min' => '',
        'max' => '',
        'step' => 'any',
        'prefix' => '',
        'suffix' => '',
    ];

    public function render(array $options): void
    {
        $options = $this->getOptions($options);
        $value = $this->getFieldValue($options);
        $name = $this->getFieldName($options);

        echo '<div class="amf-number-wrapper">';

        if (!empty($options['prefix'])) {
            echo '<span class="amf-field-prefix">' . esc_html($options['prefix']) . '</span>';
        }

        echo '<input type="number"';
        echo ' id="' . esc_attr($options['id']) . '"';
        echo ' name="' . esc_attr($name) . '"';
        echo ' value="' . esc_attr($value) . '"';

        if ($options['min'] !== '') {
            echo ' min="' . esc_attr($options['min']) . '"';
        }

        if ($options['max'] !== '') {
            echo ' max="' . esc_attr($options['max']) . '"';
        }

        echo ' step="' . esc_attr($options['step']) . '"';

        if (!empty($options['class'])) {
            echo ' class="' . esc_attr($options['class']) . '"';
        }

        if (!empty($options['style'])) {
            echo ' style="' . esc_attr($options['style']) . '"';
        }

        if (!empty($options['placeholder'])) {
            echo ' placeholder="' . esc_attr($options['placeholder']) . '"';
        }

        $this->renderAttributes($options);
        echo ' />';

        if (!empty($options['suffix'])) {
            echo '<span class="amf-field-suffix">' . esc_html($options['suffix']) . '</span>';
        }

        echo '</div>';
    }
}

/**
 * Email Field
 */
class EmailField extends FieldAbstract
{
    protected string $type = 'email';

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
        'sanitize' => 'email',
        'validate' => [],
        'save_field' => true,
        'multiple' => false,
    ];

    public function render(array $options): void
    {
        $options = $this->getOptions($options);
        $value = $this->getFieldValue($options);
        $name = $this->getFieldName($options);

        echo '<input type="email"';
        echo ' id="' . esc_attr($options['id']) . '"';
        echo ' name="' . esc_attr($name) . '"';
        echo ' value="' . $this->escapeValue($value) . '"';

        if ($options['multiple'] ?? false) {
            echo ' multiple="multiple"';
        }

        if (!empty($options['class'])) {
            echo ' class="' . esc_attr($options['class']) . '"';
        }

        if (!empty($options['style'])) {
            echo ' style="' . esc_attr($options['style']) . '"';
        }

        if (!empty($options['placeholder'])) {
            echo ' placeholder="' . esc_attr($options['placeholder']) . '"';
        }

        $this->renderAttributes($options);
        echo ' />';
    }
}

/**
 * URL Field
 */
class UrlField extends FieldAbstract
{
    protected string $type = 'url';

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
        'sanitize' => 'url',
        'validate' => [],
        'save_field' => true,
        'protocols' => ['http', 'https'],
    ];

    public function render(array $options): void
    {
        $options = $this->getOptions($options);
        $value = $this->getFieldValue($options);
        $name = $this->getFieldName($options);

        echo '<input type="url"';
        echo ' id="' . esc_attr($options['id']) . '"';
        echo ' name="' . esc_attr($name) . '"';
        echo ' value="' . esc_url($value) . '"';

        if (!empty($options['class'])) {
            echo ' class="' . esc_attr($options['class']) . '"';
        }

        if (!empty($options['style'])) {
            echo ' style="' . esc_attr($options['style']) . '"';
        }

        if (!empty($options['placeholder'])) {
            echo ' placeholder="' . esc_attr($options['placeholder']) . '"';
        }

        $this->renderAttributes($options);
        echo ' />';
    }
}

/**
 * Phone Field
 */
class PhoneField extends FieldAbstract
{
    protected string $type = 'phone';

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
        'sanitize' => 'text',
        'validate' => [],
        'save_field' => true,
        'pattern' => '[0-9\-\+\(\)\s]*',
    ];

    public function render(array $options): void
    {
        $options = $this->getOptions($options);
        $value = $this->getFieldValue($options);
        $name = $this->getFieldName($options);

        echo '<input type="tel"';
        echo ' id="' . esc_attr($options['id']) . '"';
        echo ' name="' . esc_attr($name) . '"';
        echo ' value="' . $this->escapeValue($value) . '"';

        if (!empty($options['pattern'])) {
            echo ' pattern="' . esc_attr($options['pattern']) . '"';
        }

        if (!empty($options['class'])) {
            echo ' class="' . esc_attr($options['class']) . '"';
        }

        if (!empty($options['style'])) {
            echo ' style="' . esc_attr($options['style']) . '"';
        }

        if (!empty($options['placeholder'])) {
            echo ' placeholder="' . esc_attr($options['placeholder']) . '"';
        }

        $this->renderAttributes($options);
        echo ' />';
    }
}

/**
 * Password Field
 */
class PasswordField extends FieldAbstract
{
    protected string $type = 'password';

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
        'sanitize' => 'text',
        'validate' => [],
        'save_field' => true,
        'autocomplete' => '',
        'strength_meter' => false,
    ];

    public function render(array $options): void
    {
        $options = $this->getOptions($options);
        $value = $this->getFieldValue($options);
        $name = $this->getFieldName($options);

        echo '<input type="password"';
        echo ' id="' . esc_attr($options['id']) . '"';
        echo ' name="' . esc_attr($name) . '"';
        echo ' value="' . $this->escapeValue($value) . '"';

        if (!empty($options['autocomplete'])) {
            echo ' autocomplete="' . esc_attr($options['autocomplete']) . '"';
        }

        if (!empty($options['class'])) {
            echo ' class="' . esc_attr($options['class']) . '"';
        }

        if (!empty($options['style'])) {
            echo ' style="' . esc_attr($options['style']) . '"';
        }

        if (!empty($options['placeholder'])) {
            echo ' placeholder="' . esc_attr($options['placeholder']) . '"';
        }

        $this->renderAttributes($options);
        echo ' />';

        if ($options['strength_meter'] ?? false) {
            echo '<div id="' . esc_attr($options['id']) . '-strength" class="password-strength"></div>';
        }
    }
}

/**
 * Color Field
 */
class ColorField extends FieldAbstract
{
    protected string $type = 'color';

    protected array $defaults = [
        'id' => '',
        'name' => '',
        'desc' => '',
        'std' => '#ffffff',
        'required' => false,
        'class' => '',
        'sanitize' => 'hex_color',
        'validate' => [],
        'save_field' => true,
        'alpha_channel' => false,
    ];

    public function render(array $options): void
    {
        $options = $this->getOptions($options);
        $value = $this->getFieldValue($options);
        $name = $this->getFieldName($options);

        // Default to white if empty
        if (empty($value)) {
            $value = '#ffffff';
        }

        echo '<input type="text"';
        echo ' id="' . esc_attr($options['id']) . '"';
        echo ' name="' . esc_attr($name) . '"';
        echo ' value="' . esc_attr($value) . '"';
        echo ' class="amf-color-picker ' . esc_attr($options['class']) . '"';
        echo ' data-alpha-enabled="' . ($options['alpha_channel'] ? 'true' : 'false') . '"';
        echo ' />';
    }

    public function enqueueScripts(): void
    {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');

        wp_add_inline_script('wp-color-picker', "
            jQuery(document).ready(function($) {
                $('.amf-color-picker').wpColorPicker();
            });
        ");
    }
}

/**
 * Hidden Field
 */
class HiddenField extends FieldAbstract
{
    protected string $type = 'hidden';

    protected array $defaults = [
        'id' => '',
        'name' => '',
        'std' => '',
        'sanitize' => 'text',
        'validate' => [],
        'save_field' => true,
    ];

    public function render(array $options): void
    {
        $options = $this->getOptions($options);
        $value = $this->getFieldValue($options);
        $name = $this->getFieldName($options);

        echo '<input type="hidden"';
        echo ' id="' . esc_attr($options['id']) . '"';
        echo ' name="' . esc_attr($name) . '"';
        echo ' value="' . $this->escapeValue($value) . '"';
        echo ' />';
    }
}
