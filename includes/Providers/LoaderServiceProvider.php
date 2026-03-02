<?php

declare(strict_types=1);

namespace AMF\Providers;

use AMF\Core\Container;

/**
 * Loader Service Provider - registers core loaders and initializers
 */
class LoaderServiceProvider
{
    /**
     * @var Container
     */
    private Container $container;

    /**
     * Constructor
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Register services
     *
     * @return void
     */
    public function register(): void
    {
        // Register field types
        $this->registerFieldTypes();
    }

    /**
     * Boot services
     *
     * @return void
     */
    public function boot(): void
    {
        // Initialize field factory
        $this->initializeFieldFactory();
    }

    /**
     * Register all field types
     *
     * @return void
     */
    private function registerFieldTypes(): void
    {
        $field_types = [
            // Basic fields
            'text' => \AMF\Fields\Types\TextField::class,
            'textarea' => \AMF\Fields\Types\TextareaField::class,
            'wysiwyg' => \AMF\Fields\Types\WysiwygField::class,
            'number' => \AMF\Fields\Types\NumberField::class,
            'email' => \AMF\Fields\Types\EmailField::class,
            'url' => \AMF\Fields\Types\UrlField::class,
            'phone' => \AMF\Fields\Types\PhoneField::class,
            'password' => \AMF\Fields\Types\PasswordField::class,
            'color' => \AMF\Fields\Types\ColorField::class,
            'hidden' => \AMF\Fields\Types\HiddenField::class,

            // Date & Time fields
            'date' => \AMF\Fields\Types\DateField::class,
            'time' => \AMF\Fields\Types\TimeField::class,
            'datetime' => \AMF\Fields\Types\DatetimeField::class,
            'date_range' => \AMF\Fields\Types\DateRangeField::class,

            // Selection fields
            'select' => \AMF\Fields\Types\SelectField::class,
            'checkbox' => \AMF\Fields\Types\CheckboxField::class,
            'checkbox_list' => \AMF\Fields\Types\CheckboxListField::class,
            'radio' => \AMF\Fields\Types\RadioField::class,
            'radio_list' => \AMF\Fields\Types\RadioListField::class,
            'switch' => \AMF\Fields\Types\SwitchField::class,
            'slider' => \AMF\Fields\Types\SliderField::class,

            // Media fields
            'file' => \AMF\Fields\Types\FileField::class,
            'image' => \AMF\Fields\Types\ImageField::class,
            'gallery' => \AMF\Fields\Types\GalleryField::class,
            'video' => \AMF\Fields\Types\VideoField::class,
            'audio' => \AMF\Fields\Types\AudioField::class,

            // Relationship fields
            'post' => \AMF\Fields\Types\PostField::class,
            'taxonomy' => \AMF\Fields\Types\TaxonomyField::class,
            'user' => \AMF\Fields\Types\UserField::class,
            'relationship' => \AMF\Fields\Types\RelationshipField::class,

            // Complex fields
            'group' => \AMF\Fields\Types\GroupField::class,
            'repeater' => \AMF\Fields\Types\RepeaterField::class,
            'tab' => \AMF\Fields\Types\TabField::class,
            'divider' => \AMF\Fields\Types\DividerField::class,
            'heading' => \AMF\Fields\Types\HeadingField::class,
            'code' => \AMF\Fields\Types\CodeField::class,
            'map' => \AMF\Fields\Types\MapField::class,
            'range' => \AMF\Fields\Types\RangeField::class,
        ];

        foreach ($field_types as $type => $class) {
            if (class_exists($class)) {
                $this->container->set("amf.field.{$type}", fn() => new $class());
            }
        }
    }

    /**
     * Initialize field factory
     *
     * @return void
     */
    private function initializeFieldFactory(): void
    {
        $factory = \AMF\Fields\FieldFactory::getInstance();
        $factory->setContainer($this->container);
    }
}
