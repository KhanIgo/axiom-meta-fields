<?php

declare(strict_types=1);

namespace AMF\Core;

class Bootstrap
{
    private Container $container;
    private bool $booted = false;

    public function __construct()
    {
        $this->container = new Container();
        $this->registerCoreServices();
    }

    public function run(): void
    {
        if ($this->booted) {
            return;
        }

        $this->registerCoreServices();
        $this->boot();

        $this->booted = true;
        do_action('amf_loaded', $this->container);
    }

    private function registerCoreServices(): void
    {
        $this->container->singleton(Container::class, fn() => $this->container);
        $this->container->singleton(Loader::class, function () {
            return new Loader($this->container);
        });

        // Register service providers
        $this->container->set('amf.provider.loader', function () {
            return new \AMF\Providers\LoaderServiceProvider($this->container);
        });

        $this->container->set('amf.provider.metabox', function () {
            return new \AMF\Providers\MetaBoxServiceProvider($this->container);
        });

        $this->container->set('amf.provider.posttype', function () {
            return new \AMF\Providers\PostTypeServiceProvider($this->container);
        });

        $this->container->set('amf.provider.taxonomy', function () {
            return new \AMF\Providers\TaxonomyServiceProvider($this->container);
        });

        $this->container->set('amf.provider.admin', function () {
            return new \AMF\Providers\AdminServiceProvider($this->container);
        });

        $this->container->set('amf.provider.api', function () {
            return new \AMF\Providers\APIServiceProvider($this->container);
        });

        $this->container->set('amf.provider.frontend', function () {
            return new \AMF\Providers\FrontendServiceProvider($this->container);
        });
    }

    private function boot(): void
    {
        $providers = [
            'amf.provider.loader',
            'amf.provider.metabox',
            'amf.provider.posttype',
            'amf.provider.taxonomy',
            'amf.provider.admin',
            'amf.provider.api',
            'amf.provider.frontend',
        ];

        foreach ($providers as $provider_id) {
            if ($this->container->has($provider_id)) {
                $provider = $this->container->get($provider_id);
                $provider->register();
            }
        }

        foreach ($providers as $provider_id) {
            if ($this->container->has($provider_id)) {
                $provider = $this->container->get($provider_id);
                if (method_exists($provider, 'boot')) {
                    $provider->boot();
                }
            }
        }
    }

    public function getContainer(): Container
    {
        return $this->container;
    }
}
