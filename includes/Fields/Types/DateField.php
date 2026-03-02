<?php

declare(strict_types=1);

namespace AMF\Fields\Types;

use AMF\Fields\FieldAbstract;

/**
 * Date Field
 */
class DateField extends FieldAbstract
{
    protected string $type = 'date';

    protected array $defaults = [
        'id' => '',
        'name' => '',
        'desc' => '',
        'std' => '',
        'placeholder' => '',
        'required' => false,
        'class' => '',
        'style' => '',
        'sanitize' => 'text',
        'validate' => [],
        'save_field' => true,
        'format' => 'yy-mm-dd',
        'min_date' => '',
        'max_date' => '',
        'inline' => false,
    ];

    public function render(array $options): void
    {
        $options = $this->getOptions($options);
        $value = $this->getFieldValue($options);
        $name = $this->getFieldName($options);

        echo '<input type="text"';
        echo ' id="' . esc_attr($options['id']) . '"';
        echo ' name="' . esc_attr($name) . '"';
        echo ' value="' . esc_attr($value) . '"';
        echo ' class="amf-date-picker ' . esc_attr($options['class']) . '"';
        echo ' data-format="' . esc_attr($options['format']) . '"';

        if (!empty($options['min_date'])) {
            echo ' data-min-date="' . esc_attr($options['min_date']) . '"';
        }

        if (!empty($options['max_date'])) {
            echo ' data-max-date="' . esc_attr($options['max_date']) . '"';
        }

        if (!empty($options['style'])) {
            echo ' style="' . esc_attr($options['style']) . '"';
        }

        if (!empty($options['placeholder'])) {
            echo ' placeholder="' . esc_attr($options['placeholder']) . '"';
        }

        $this->renderAttributes($options);
        echo ' />';

        if ($options['inline'] ?? false) {
            echo '<div id="' . esc_attr($options['id']) . '-inline" class="amf-datepicker-inline"></div>';
        }
    }

    public function enqueueScripts(): void
    {
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style('jquery-ui-css');
    }
}

/**
 * Time Field
 */
class TimeField extends FieldAbstract
{
    protected string $type = 'time';

    protected array $defaults = [
        'id' => '',
        'name' => '',
        'desc' => '',
        'std' => '',
        'placeholder' => '',
        'required' => false,
        'class' => '',
        'style' => '',
        'sanitize' => 'text',
        'validate' => [],
        'save_field' => true,
        'format' => 'HH:mm',
        'time_24hr' => false,
        'step' => 5,
    ];

    public function render(array $options): void
    {
        $options = $this->getOptions($options);
        $value = $this->getFieldValue($options);
        $name = $this->getFieldName($options);

        echo '<input type="text"';
        echo ' id="' . esc_attr($options['id']) . '"';
        echo ' name="' . esc_attr($name) . '"';
        echo ' value="' . esc_attr($value) . '"';
        echo ' class="amf-time-picker ' . esc_attr($options['class']) . '"';
        echo ' data-format="' . esc_attr($options['format']) . '"';
        echo ' data-24hr="' . ($options['time_24hr'] ? '1' : '0') . '"';
        echo ' data-step="' . esc_attr($options['step']) . '"';

        if (!empty($options['style'])) {
            echo ' style="' . esc_attr($options['style']) . '"';
        }

        if (!empty($options['placeholder'])) {
            echo ' placeholder="' . esc_attr($options['placeholder']) . '"';
        }

        $this->renderAttributes($options);
        echo ' />';
    }

    public function enqueueScripts(): void
    {
        wp_enqueue_script('jquery-ui-timepicker');
    }
}

/**
 * DateTime Field
 */
class DatetimeField extends FieldAbstract
{
    protected string $type = 'datetime';

    protected array $defaults = [
        'id' => '',
        'name' => '',
        'desc' => '',
        'std' => '',
        'placeholder' => '',
        'required' => false,
        'class' => '',
        'style' => '',
        'sanitize' => 'text',
        'validate' => [],
        'save_field' => true,
        'format' => 'yy-mm-dd HH:mm',
        'show_time' => true,
        'show_date' => true,
    ];

    public function render(array $options): void
    {
        $options = $this->getOptions($options);
        $value = $this->getFieldValue($options);
        $name = $this->getFieldName($options);

        echo '<input type="text"';
        echo ' id="' . esc_attr($options['id']) . '"';
        echo ' name="' . esc_attr($name) . '"';
        echo ' value="' . esc_attr($value) . '"';
        echo ' class="amf-datetime-picker ' . esc_attr($options['class']) . '"';
        echo ' data-format="' . esc_attr($options['format']) . '"';

        if (!empty($options['style'])) {
            echo ' style="' . esc_attr($options['style']) . '"';
        }

        if (!empty($options['placeholder'])) {
            echo ' placeholder="' . esc_attr($options['placeholder']) . '"';
        }

        $this->renderAttributes($options);
        echo ' />';
    }

    public function enqueueScripts(): void
    {
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('jquery-ui-timepicker');
    }
}

/**
 * DateRange Field
 */
class DateRangeField extends FieldAbstract
{
    protected string $type = 'date_range';

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
        'format' => 'yy-mm-dd',
        'separator' => ' - ',
        'min_range' => 0,
    ];

    public function render(array $options): void
    {
        $options = $this->getOptions($options);
        $value = $this->getFieldValue($options);
        $field_id = esc_attr($options['id']);

        // Parse value
        $start = '';
        $end = '';
        if (is_array($value) && isset($value['start'], $value['end'])) {
            $start = $value['start'];
            $end = $value['end'];
        }

        echo '<div class="amf-date-range-wrapper">';
        echo '<span class="amf-date-range-label">' . esc_html__('From', 'amf') . ':</span>';
        echo '<input type="text"';
        echo ' id="' . $field_id . '_start"';
        echo ' name="' . esc_attr($options['name']) . '[start]"';
        echo ' value="' . esc_attr($start) . '"';
        echo ' class="amf-date-range-start ' . esc_attr($options['class']) . '"';
        echo ' data-format="' . esc_attr($options['format']) . '"';
        echo ' />';

        echo '<span class="amf-date-range-separator">' . esc_html($options['separator']) . '</span>';

        echo '<span class="amf-date-range-label">' . esc_html__('To', 'amf') . ':</span>';
        echo '<input type="text"';
        echo ' id="' . $field_id . '_end"';
        echo ' name="' . esc_attr($options['name']) . '[end]"';
        echo ' value="' . esc_attr($end) . '"';
        echo ' class="amf-date-range-end ' . esc_attr($options['class']) . '"';
        echo ' data-format="' . esc_attr($options['format']) . '"';
        echo ' />';
        echo '</div>';
    }

    public function enqueueScripts(): void
    {
        wp_enqueue_script('jquery-ui-datepicker');
    }
}
