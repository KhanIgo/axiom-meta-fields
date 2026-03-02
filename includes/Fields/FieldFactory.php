<?php

declare(strict_types=1);

namespace AMF\Fields;

use AMF\Core\Container;
use AMF\Traits\Singleton;

/**
 * Field Factory - creates field instances
 */
class FieldFactory
{
    use Singleton;

    /**
     * @var Container|null
     */
    private ?Container $container = null;

    /**
     * @var array<string, string>
     */
    private array $fieldTypes = [];

    /**
     * Set container
     *
     * @param Container $container
     * @return void
     */
    public function setContainer(Container $container): void
    {
        $this->container = $container;
    }

    /**
     * Register a field type
     *
     * @param string $type
     * @param string $class
     * @return void
     */
    public function register(string $type, string $class): void
    {
        $this->fieldTypes[$type] = $class;
    }

    /**
     * Create a field instance
     *
     * @param string $type
     * @return FieldInterface|null
     */
    public function create(string $type): ?FieldInterface
    {
        // Try to get from container
        if ($this->container && $this->container->has("amf.field.{$type}")) {
            $field = $this->container->get("amf.field.{$type}");
            if ($field instanceof FieldInterface) {
                return $field;
            }
        }

        // Try to create directly
        if (isset($this->fieldTypes[$type])) {
            $class = $this->fieldTypes[$type];
            if (class_exists($class)) {
                $field = new $class();
                if ($field instanceof FieldInterface) {
                    return $field;
                }
            }
        }

        return null;
    }

    /**
     * Get all registered field types
     *
     * @return array<string>
     */
    public function getTypes(): array
    {
        return array_keys($this->fieldTypes);
    }

    /**
     * Check if a field type exists
     *
     * @param string $type
     * @return bool
     */
    public function exists(string $type): bool
    {
        return isset($this->fieldTypes[$type]) ||
            ($this->container && $this->container->has("amf.field.{$type}"));
    }
}
