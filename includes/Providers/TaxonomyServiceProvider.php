<?php

declare(strict_types=1);

namespace AMF\Providers;

use AMF\Core\Container;
use AMF\Traits\Hookable;

/**
 * Taxonomy Service Provider
 */
class TaxonomyServiceProvider
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
        $this->container->singleton('amf.taxonomy.register', function () {
            return new \AMF\Taxonomy\Register();
        });
    }

    /**
     * Boot services
     *
     * @return void
     */
    public function boot(): void
    {
        $register = $this->container->get('amf.taxonomy.register');

        // Hook taxonomy registration
        $this->addAction('init', [$register, 'init'], 1);
    }
}
