<?php

declare(strict_types=1);

namespace AMF\Providers;

use AMF\Core\Container;
use AMF\Traits\Hookable;

/**
 * API Service Provider
 */
class APIServiceProvider
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
        $this->container->singleton('amf.api.rest.meta', function () {
            return new \AMF\API\REST\MetaController();
        });

        $this->container->singleton('amf.api.rest.posttypes', function () {
            return new \AMF\API\REST\PostTypesController();
        });

        $this->container->singleton('amf.api.rest.taxonomies', function () {
            return new \AMF\API\REST\TaxonomiesController();
        });

        $this->container->singleton('amf.api.rest.fields', function () {
            return new \AMF\API\REST\FieldsController();
        });
    }

    /**
     * Boot services
     *
     * @return void
     */
    public function boot(): void
    {
        // Check if REST API is enabled
        $settings = get_option('amf_settings', []);
        if (!($settings['enable_rest_api'] ?? true)) {
            return;
        }

        // Register REST routes
        $this->addAction('rest_api_init', [$this, 'registerRoutes']);
    }

    /**
     * Register REST API routes
     *
     * @return void
     */
    public function registerRoutes(): void
    {
        $controllers = [
            'amf.api.rest.meta',
            'amf.api.rest.posttypes',
            'amf.api.rest.taxonomies',
            'amf.api.rest.fields',
        ];

        foreach ($controllers as $controller_id) {
            if ($this->container->has($controller_id)) {
                $controller = $this->container->get($controller_id);
                if (method_exists($controller, 'registerRoutes')) {
                    $controller->registerRoutes();
                }
            }
        }
    }
}
