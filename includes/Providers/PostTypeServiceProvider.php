<?php

declare(strict_types=1);

namespace AMF\Providers;

use AMF\Core\Container;
use AMF\Traits\Hookable;

/**
 * PostType Service Provider
 */
class PostTypeServiceProvider
{
    use Hookable;

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
        $this->container->singleton('amf.posttype.register', function () {
            return new \AMF\PostType\Register();
        });
    }

    /**
     * Boot services
     *
     * @return void
     */
    public function boot(): void
    {
        $register = $this->container->get('amf.posttype.register');

        // Hook post type registration
        $this->addAction('init', [$register, 'init'], 1);
    }
}
