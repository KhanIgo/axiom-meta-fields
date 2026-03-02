<?php

declare(strict_types=1);

namespace AMF\Providers;

use AMF\Core\Container;
use AMF\Traits\Hookable;

/**
 * Admin Service Provider
 */
class AdminServiceProvider
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
        $this->container->singleton('amf.admin.menu', function () {
            return new \AMF\Admin\Menu();
        });

        $this->container->singleton('amf.admin.settings', function () {
            return new \AMF\Admin\Settings();
        });

        $this->container->singleton('amf.admin.notices', function () {
            return new \AMF\Admin\Notices();
        });
    }

    /**
     * Boot services
     *
     * @return void
     */
    public function boot(): void
    {
        if (!is_admin()) {
            return;
        }

        $menu = $this->container->get('amf.admin.menu');
        $settings = $this->container->get('amf.admin.settings');
        $notices = $this->container->get('amf.admin.notices');

        // Initialize admin menu
        $menu->init();

        // Initialize settings
        $settings->init();

        // Initialize notices
        $notices->init();

        // Enqueue admin scripts and styles
        $this->addAction('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook
     * @return void
     */
    public function enqueueAdminAssets(string $hook): void
    {
        // Only load on plugin pages
        if (strpos($hook, 'amf-') === false) {
            return;
        }

        // Styles
        wp_enqueue_style(
            'amf-admin',
            AMF_PLUGIN_URL . 'assets/css/admin.css',
            [],
            AMF_VERSION
        );

        // Scripts
        wp_enqueue_script(
            'amf-admin',
            AMF_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery', 'jquery-ui-sortable'],
            AMF_VERSION,
            true
        );

        // Localize script
        wp_localize_script('amf-admin', 'cfpAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('amf_admin'),
            'strings' => [
                'confirmDelete' => __('Are you sure you want to delete this item?', 'amf'),
                'error' => __('An error occurred. Please try again.', 'amf'),
            ],
        ]);
    }
}
