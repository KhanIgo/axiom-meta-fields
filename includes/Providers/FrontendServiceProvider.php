<?php

declare(strict_types=1);

namespace AMF\Providers;

use AMF\Core\Container;
use AMF\Traits\Hookable;

/**
 * Frontend Service Provider
 */
class FrontendServiceProvider
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
        $this->container->singleton('amf.frontend.shortcodes', function () {
            return new \AMF\Frontend\Shortcodes();
        });

        $this->container->singleton('amf.frontend.template_tags', function () {
            return new \AMF\Frontend\TemplateTags();
        });

        $this->container->singleton('amf.frontend.blocks', function () {
            return new \AMF\Frontend\Blocks();
        });
    }

    /**
     * Boot services
     *
     * @return void
     */
    public function boot(): void
    {
        $shortcodes = $this->container->get('amf.frontend.shortcodes');
        $templateTags = $this->container->get('amf.frontend.template_tags');
        $blocks = $this->container->get('amf.frontend.blocks');

        // Initialize shortcodes
        $shortcodes->init();

        // Initialize template tags (auto-loaded)

        // Initialize Gutenberg blocks
        $blocks->init();

        // Enqueue frontend assets
        $this->addAction('wp_enqueue_scripts', [$this, 'enqueueFrontendAssets']);
    }

    /**
     * Enqueue frontend assets
     *
     * @return void
     */
    public function enqueueFrontendAssets(): void
    {
        // Styles
        wp_enqueue_style(
            'amf-frontend',
            AMF_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            AMF_VERSION
        );
    }
}
