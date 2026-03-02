<?php

declare(strict_types=1);

namespace AMF\Admin;

use AMF\Traits\Hookable;

/**
 * Admin Menu
 */
class Menu
{
    use Hookable;

    /**
     * Initialize
     *
     * @return void
     */
    public function init(): void
    {
        $this->addAction('admin_menu', [$this, 'addMenus']);
    }

    /**
     * Add admin menus
     *
     * @return void
     */
    public function addMenus(): void
    {
        // Main menu
        add_menu_page(
            __('Axiom Meta Fields', 'amf'),
            __('Custom Fields', 'amf'),
            'manage_options',
            'amf-dashboard',
            [$this, 'renderDashboard'],
            'dashicons-admin-custom-fields',
            80
        );

        // Dashboard submenu
        add_submenu_page(
            'amf-dashboard',
            __('Dashboard', 'amf'),
            __('Dashboard', 'amf'),
            'manage_options',
            'amf-dashboard',
            [$this, 'renderDashboard']
        );

        // Meta Boxes submenu
        add_submenu_page(
            'amf-dashboard',
            __('Meta Boxes', 'amf'),
            __('Meta Boxes', 'amf'),
            'manage_options',
            'amf-meta-boxes',
            [$this, 'renderMetaBoxes']
        );

        // Post Types submenu
        add_submenu_page(
            'amf-dashboard',
            __('Post Types', 'amf'),
            __('Post Types', 'amf'),
            'manage_options',
            'amf-post-types',
            [$this, 'renderPostTypes']
        );

        // Taxonomies submenu
        add_submenu_page(
            'amf-dashboard',
            __('Taxonomies', 'amf'),
            __('Taxonomies', 'amf'),
            'manage_options',
            'amf-taxonomies',
            [$this, 'renderTaxonomies']
        );

        // Settings submenu
        add_submenu_page(
            'amf-dashboard',
            __('Settings', 'amf'),
            __('Settings', 'amf'),
            'manage_options',
            'amf-settings',
            [$this, 'renderSettings']
        );

        // Tools submenu
        add_submenu_page(
            'amf-dashboard',
            __('Tools', 'amf'),
            __('Tools', 'amf'),
            'manage_options',
            'amf-tools',
            [$this, 'renderTools']
        );
    }

    /**
     * Render dashboard page
     *
     * @return void
     */
    public function renderDashboard(): void
    {
        include AMF_PLUGIN_DIR . 'templates/admin/dashboard.php';
    }

    /**
     * Render meta boxes page
     *
     * @return void
     */
    public function renderMetaBoxes(): void
    {
        include AMF_PLUGIN_DIR . 'templates/admin/meta-boxes.php';
    }

    /**
     * Render post types page
     *
     * @return void
     */
    public function renderPostTypes(): void
    {
        include AMF_PLUGIN_DIR . 'templates/admin/post-types.php';
    }

    /**
     * Render taxonomies page
     *
     * @return void
     */
    public function renderTaxonomies(): void
    {
        include AMF_PLUGIN_DIR . 'templates/admin/taxonomies.php';
    }

    /**
     * Render settings page
     *
     * @return void
     */
    public function renderSettings(): void
    {
        include AMF_PLUGIN_DIR . 'templates/admin/settings.php';
    }

    /**
     * Render tools page
     *
     * @return void
     */
    public function renderTools(): void
    {
        include AMF_PLUGIN_DIR . 'templates/admin/tools.php';
    }
}
