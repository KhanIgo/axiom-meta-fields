<?php

declare(strict_types=1);

namespace AMF\Core;

/**
 * Loader - manages loading of plugin components
 */
class Loader
{
    /**
     * @var Container
     */
    private Container $container;

    /**
     * @var array<string, array<int, callable>>
     */
    private array $actions = [];

    /**
     * @var array<string, array<int, callable>>
     */
    private array $filters = [];

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
     * Add an action hook
     *
     * @param string $hook
     * @param callable $callable
     * @param int $priority
     * @param int $accepted_args
     * @return void
     */
    public function addAction(
        string $hook,
        callable $callable,
        int $priority = 10,
        int $accepted_args = 1
    ): void {
        $this->addHook($this->actions, $hook, $callable, $priority, $accepted_args);
    }

    /**
     * Add a filter hook
     *
     * @param string $hook
     * @param callable $callable
     * @param int $priority
     * @param int $accepted_args
     * @return void
     */
    public function addFilter(
        string $hook,
        callable $callable,
        int $priority = 10,
        int $accepted_args = 1
    ): void {
        $this->addHook($this->filters, $hook, $callable, $priority, $accepted_args);
    }

    /**
     * Internal method to add hooks
     *
     * @param array<string, array<int, callable>> $hooks
     * @param string $hook
     * @param callable $callable
     * @param int $priority
     * @param int $accepted_args
     * @return void
     */
    private function addHook(
        array &$hooks,
        string $hook,
        callable $callable,
        int $priority,
        int $accepted_args
    ): void {
        $index = $hook . '_' . $priority;

        if (!isset($hooks[$index])) {
            $hooks[$index] = [];
        }

        $hooks[$index][] = [
            'callable' => $callable,
            'priority' => $priority,
            'accepted_args' => $accepted_args,
        ];

        // Register with WordPress
        add_filter(
            $hook,
            function (...$args) use ($callable, $accepted_args) {
                $args = array_slice($args, 0, $accepted_args);
                return call_user_func_array($callable, $args);
            },
            $priority,
            $accepted_args
        );
    }

    /**
     * Run all registered actions for a hook
     *
     * @param string $hook
     * @param mixed ...$args
     * @return void
     */
    public function runActions(string $hook, ...$args): void
    {
        if (isset($this->actions[$hook])) {
            foreach ($this->actions[$hook] as $action) {
                call_user_func_array($action['callable'], $args);
            }
        }
    }

    /**
     * Run all registered filters for a hook
     *
     * @param string $hook
     * @param mixed $value
     * @param mixed ...$args
     * @return mixed
     */
    public function runFilters(string $hook, $value, ...$args)
    {
        if (isset($this->filters[$hook])) {
            foreach ($this->filters[$hook] as $filter) {
                $value = call_user_func_array($filter['callable'], array_merge([$value], $args));
            }
        }

        return $value;
    }
}
