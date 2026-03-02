<?php

declare(strict_types=1);

namespace AMF\Traits;

/**
 * Singleton trait
 */
trait Singleton
{
    /**
     * @var static|null
     */
    private static ?self $instance = null;

    /**
     * Get singleton instance
     *
     * @return static
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Prevent cloning
     */
    private function __clone()
    {
    }

    /**
     * Prevent unserialization
     *
     * @throws \Exception
     */
    public function __wakeup()
    {
        throw new \Exception('Cannot unserialize singleton');
    }
}
