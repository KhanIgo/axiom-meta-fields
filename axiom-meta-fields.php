<?php
/**
 * Plugin Name: Axiom Meta Fields
 * Plugin URI: https://github.com/KhanIgo/axiom-meta-fields
 * Description: Custom fields and meta boxes for WordPress
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://khxn.ru
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: amf
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 8.0
 */

if (!defined('ABSPATH')) {
    exit;
}

define('AMF_VERSION', '1.0.0');
define('AMF_PLUGIN_FILE', __FILE__);
define('AMF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AMF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AMF_PLUGIN_BASENAME', plugin_basename(__FILE__));

// PSR-11 Container Interface autoloader
spl_autoload_register(function ($class) {
    if ($class === 'Psr\\Container\\ContainerInterface') {
        $file = AMF_PLUGIN_DIR . 'includes/Core/Psr/Container/ContainerInterface.php';
        if (file_exists($file)) {
            require $file;
        }
    }
});

// AMF autoloader
spl_autoload_register(function ($class) {
    if (strpos($class, 'AMF\\') !== 0) {
        return;
    }
    $relative_class = substr($class, 4);
    $file = AMF_PLUGIN_DIR . 'includes/' . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

require_once AMF_PLUGIN_DIR . 'includes/Helpers/functions.php';

function amf_bootstrap()
{
    if (!amf_check_requirements()) {
        add_action('admin_notices', 'amf_requirements_notice');
        return;
    }

    $bootstrap = new \AMF\Core\Bootstrap();
    $bootstrap->run();
}

function amf_check_requirements(): bool
{
    if (version_compare(PHP_VERSION, '8.0', '<')) {
        return false;
    }

    global $wp_version;
    if (version_compare($wp_version, '5.8', '<')) {
        return false;
    }

    return true;
}

function amf_requirements_notice()
{
    global $wp_version;
    $php_version = PHP_VERSION;

    $messages = [];

    if (version_compare($php_version, '8.0', '<')) {
        $messages[] = sprintf(
            /* translators: %s: PHP version */
            __('Axiom Meta Fields requires PHP %s or higher. Your server is running PHP %s.', 'amf'),
            '8.0',
            $php_version
        );
    }

    if (version_compare($wp_version, '5.8', '<')) {
        $messages[] = sprintf(
            /* translators: %s: WordPress version */
            __('Axiom Meta Fields requires WordPress %s or higher. Your site is running WordPress %s.', 'amf'),
            '5.8',
            $wp_version
        );
    }

    if (!empty($messages)) {
        echo '<div class="notice notice-error"><p>' . implode(' ', $messages) . '</p></div>';
    }
}

add_action('plugins_loaded', 'amf_bootstrap');
register_activation_hook(__FILE__, ['AMF\\Core\\Activation', 'activate']);
register_deactivation_hook(__FILE__, ['AMF\\Core\\Activation', 'deactivate']);
register_uninstall_hook(__FILE__, 'amf_uninstall');

function amf_uninstall()
{
    if (!defined('AMF_UNINSTALL')) {
        define('AMF_UNINSTALL', true);
    }

    require_once AMF_PLUGIN_DIR . 'uninstall.php';
}
