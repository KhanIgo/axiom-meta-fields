<?php

declare(strict_types=1);

namespace AMF\Providers;

use AMF\Core\Container;
use AMF\Traits\Hookable;

/**
 * MetaBox Service Provider
 */
class MetaBoxServiceProvider
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
        // Register meta box services
        $this->container->singleton('amf.metabox.register', function () {
            return new \AMF\MetaBox\Register();
        });

        $this->container->singleton('amf.metabox.render', function () {
            return new \AMF\MetaBox\Render();
        });

        $this->container->singleton('amf.metabox.save', function () {
            return new \AMF\MetaBox\Save();
        });
    }

    /**
     * Boot services
     *
     * @return void
     */
    public function boot(): void
    {
        $register = $this->container->get('amf.metabox.register');
        $render = $this->container->get('amf.metabox.render');
        $save = $this->container->get('amf.metabox.save');

        // Initialize meta box registration
        $register->init();

        // Hook meta box rendering
        $this->addAction('add_meta_boxes', [$render, 'addMetaBoxes']);
        $this->addAction('admin_enqueue_scripts', [$render, 'enqueueScripts']);

        // Hook meta box saving
        $this->addAction('save_post', [$save, 'saveMeta'], 10, 2);
    }
}
