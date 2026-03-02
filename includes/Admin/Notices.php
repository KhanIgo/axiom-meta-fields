<?php

declare(strict_types=1);

namespace AMF\Admin;

use AMF\Traits\Hookable;

/**
 * Admin Notices
 */
class Notices
{
    use Hookable;

    /**
     * @var array
     */
    private array $notices = [];

    /**
     * Initialize
     *
     * @return void
     */
    public function init(): void
    {
        $this->addAction('admin_notices', [$this, 'displayNotices']);
        $this->addAction('network_admin_notices', [$this, 'displayNotices']);

        // Check for dismissible notices
        $this->addAction('admin_init', [$this, 'handleNoticeDismissals']);
    }

    /**
     * Add a notice
     *
     * @param string $id
     * @param string $message
     * @param string $type
     * @param bool $dismissible
     * @return void
     */
    public function add(string $id, string $message, string $type = 'info', bool $dismissible = true): void
    {
        $this->notices[$id] = [
            'message' => $message,
            'type' => $type,
            'dismissible' => $dismissible,
        ];
    }

    /**
     * Display notices
     *
     * @return void
     */
    public function displayNotices(): void
    {
        // Check for dismissed notices
        $dismissed = get_user_meta(get_current_user_id(), 'amf_dismissed_notices', true);
        if (!is_array($dismissed)) {
            $dismissed = [];
        }

        foreach ($this->notices as $id => $notice) {
            // Skip dismissed notices
            if ($notice['dismissible'] && in_array($id, $dismissed, true)) {
                continue;
            }

            $this->renderNotice($id, $notice);
        }

        // Display dynamic notices
        $this->displayDynamicNotices();
    }

    /**
     * Render a notice
     *
     * @param string $id
     * @param array $notice
     * @return void
     */
    private function renderNotice(string $id, array $notice): void
    {
        $class = 'notice notice-' . esc_attr($notice['type']);
        $dismiss_class = $notice['dismissible'] ? ' is-dismissible' : '';
        $dismiss_attr = $notice['dismissible'] ? ' data-notice-id="' . esc_attr($id) . '"' : '';

        echo '<div class="' . esc_attr($class . $dismiss_class) . '"' . $dismiss_attr . '>';
        echo $notice['message'];
        echo '</div>';
    }

    /**
     * Handle notice dismissals
     *
     * @return void
     */
    public function handleNoticeDismissals(): void
    {
        if (!isset($_GET['amf_dismiss_notice'])) {
            return;
        }

        $notice_id = sanitize_text_field(wp_unslash($_GET['amf_dismiss_notice']));
        $user_id = get_current_user_id();

        $dismissed = get_user_meta($user_id, 'amf_dismissed_notices', true);
        if (!is_array($dismissed)) {
            $dismissed = [];
        }

        if (!in_array($notice_id, $dismissed, true)) {
            $dismissed[] = $notice_id;
            update_user_meta($user_id, 'amf_dismissed_notices', $dismissed);
        }

        wp_safe_redirect(remove_query_arg('amf_dismiss_notice'));
        exit;
    }

    /**
     * Display dynamic notices
     *
     * @return void
     */
    private function displayDynamicNotices(): void
    {
        // Check PHP version
        if (version_compare(PHP_VERSION, '8.0', '<')) {
            $this->add(
                'php-version',
                sprintf(
                    /* translators: %s: Required PHP version */
                    __('Axiom Meta Fields requires PHP 8.0 or higher. Your server is running PHP %s. Please upgrade PHP to use this plugin.', 'amf'),
                    PHP_VERSION
                ),
                'error',
                false
            );
        }

        // Check WordPress version
        global $wp_version;
        if (version_compare($wp_version, '5.8', '<')) {
            $this->add(
                'wp-version',
                sprintf(
                    /* translators: %s: Required WordPress version */
                    __('Axiom Meta Fields requires WordPress 5.8 or higher. Your site is running WordPress %s. Please upgrade WordPress to use this plugin.', 'amf'),
                    $wp_version
                ),
                'error',
                false
            );
        }

        // Show welcome notice on first activation
        $activated = get_option('amf_activated');
        $welcome_shown = get_option('amf_welcome_shown');

        if ($activated && !$welcome_shown) {
            $this->add(
                'welcome',
                sprintf(
                    /* translators: %1$s: Opening link tag, %2$s: Closing link tag */
                    __('Welcome to Axiom Meta Fields! %1$sGet started%2$s by creating your first meta box.', 'amf'),
                    '<a href="' . esc_url(admin_url('admin.php?page=amf-meta-boxes')) . '">',
                    '</a>'
                ),
                'success',
                true
            );
            update_option('amf_welcome_shown', true);
        }
    }
}
