<?php

declare(strict_types=1);

namespace AMF\Traits;

/**
 * Hookable trait - provides WordPress hook registration helpers
 */
trait Hookable
{
    /**
     * Register an action hook
     *
     * @param string $hook
     * @param callable|array $callable
     * @param int $priority
     * @param int $accepted_args
     * @return void
     */
    protected function addAction(
        string $hook,
        callable|array $callable,
        int $priority = 10,
        int $accepted_args = 1
    ): void {
        add_action($hook, $callable, $priority, $accepted_args);
    }

    /**
     * Register a filter hook
     *
     * @param string $hook
     * @param callable|array $callable
     * @param int $priority
     * @param int $accepted_args
     * @return void
     */
    protected function addFilter(
        string $hook,
        callable|array $callable,
        int $priority = 10,
        int $accepted_args = 1
    ): void {
        add_filter($hook, $callable, $priority, $accepted_args);
    }

    /**
     * Register a method as an action
     *
     * @param string $hook
     * @param string $method
     * @param int $priority
     * @param int $accepted_args
     * @return void
     */
    protected function addActionMethod(
        string $hook,
        string $method,
        int $priority = 10,
        int $accepted_args = 1
    ): void {
        add_action($hook, [$this, $method], $priority, $accepted_args);
    }

    /**
     * Register a method as a filter
     *
     * @param string $hook
     * @param string $method
     * @param int $priority
     * @param int $accepted_args
     * @return void
     */
    protected function addFilterMethod(
        string $hook,
        string $method,
        int $priority = 10,
        int $accepted_args = 1
    ): void {
        add_filter($hook, [$this, $method], $priority, $accepted_args);
    }
}
