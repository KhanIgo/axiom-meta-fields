<?php

declare(strict_types=1);

namespace AMF\Core;

use Psr\Container\ContainerInterface as PsrContainerInterface;

/**
 * PSR-11 compliant Dependency Injection Container
 */
class Container implements PsrContainerInterface
{
    /**
     * @var array<string, callable>
     */
    private array $definitions = [];

    /**
     * @var array<string, mixed>
     */
    private array $instances = [];

    /**
     * @var array<string, bool>
     */
    private array $singletons = [];

    /**
     * Get a service from the container
     *
     * @param string $id
     * @return mixed
     * @throws \Exception
     */
    public function get(string $id)
    {
        if (!$this->has($id)) {
            throw new \Exception(sprintf('Service "%s" not found', $id));
        }

        // Return cached instance for singletons
        if (isset($this->singletons[$id]) && isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        $definition = $this->definitions[$id];
        $instance = $definition($this);

        // Cache singleton instances
        if ($this->singletons[$id] ?? false) {
            $this->instances[$id] = $instance;
        }

        return $instance;
    }

    /**
     * Check if a service exists in the container
     *
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->definitions[$id]);
    }

    /**
     * Set a service definition
     *
     * @param string $id
     * @param callable $concrete
     * @return void
     */
    public function set(string $id, callable $concrete): void
    {
        $this->definitions[$id] = $concrete;
        $this->singletons[$id] = false;
    }

    /**
     * Set a singleton service definition
     *
     * @param string $id
     * @param callable $concrete
     * @return void
     */
    public function singleton(string $id, callable $concrete): void
    {
        $this->definitions[$id] = $concrete;
        $this->singletons[$id] = true;
    }

    /**
     * Remove a service definition
     *
     * @param string $id
     * @return void
     */
    public function remove(string $id): void
    {
        unset($this->definitions[$id], $this->instances[$id], $this->singletons[$id]);
    }

    /**
     * Build an instance using the container
     *
     * @param string $class
     * @return mixed
     * @throws \ReflectionException
     */
    public function build(string $class)
    {
        $reflector = new \ReflectionClass($class);
        $constructor = $reflector->getConstructor();

        if ($constructor === null) {
            return new $class();
        }

        $parameters = $constructor->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            if ($type && $type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                $dependencies[] = $this->get($type->getName());
            } elseif ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
            } else {
                throw new \Exception(sprintf('Cannot resolve parameter "%s"', $parameter->getName()));
            }
        }

        return $reflector->newInstanceArgs($dependencies);
    }
}
