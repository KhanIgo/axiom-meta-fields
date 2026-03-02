<?php

declare(strict_types=1);

namespace AMF\Fields\Types;

use AMF\Fields\FieldAbstract;

/**
 * Text Field
 */
class TextField extends FieldAbstract
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
        'sanitize' => 'text',
        'validate' => [],
        'save_field' => true,
        'column' => false,
        'search' => false,
        'maxlength' => '',
        'prepend' => '',
        'append' => '',
        'size' => 40,
    ];

    /**
     * Render the field
     *
     * @param array $options
     * @return void
     */
    public function render(array $options): void
    {
        $options = $this->getOptions($options);
        $value = $this->getFieldValue($options);
        $name = $this->getFieldName($options);

        echo '<div class="amf-text-wrapper">';

        if (!empty($options['prepend'])) {
            echo '<span class="amf-field-prepend">' . esc_html($options['prepend']) . '</span>';
        }

        echo '<input type="text"';
        echo ' id="' . esc_attr($options['id']) . '"';
        echo ' name="' . esc_attr($name) . '"';
        echo ' value="' . $this->escapeValue($value) . '"';
        echo ' size="' . esc_attr($options['size']) . '"';

        if (!empty($options['maxlength'])) {
            echo ' maxlength="' . esc_attr($options['maxlength']) . '"';
        }

        if (!empty($options['class'])) {
            echo ' class="' . esc_attr($options['class']) . '"';
        }

        if (!empty($options['style'])) {
            echo ' style="' . esc_attr($options['style']) . '"';
        }

        $this->renderAttributes($options);
        echo ' />';

        if (!empty($options['append'])) {
            echo '<span class="amf-field-append">' . esc_html($options['append']) . '</span>';
        }

        echo '</div>';
    }
}
