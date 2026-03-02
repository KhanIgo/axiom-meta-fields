<?php

declare(strict_types=1);

namespace AMF\Core;

class Activation
{
    public static function activate(): void
    {
        // Check user capabilities
        if (!current_user_can('activate_plugins')) {
            return;
        }

        self::createTables();
        self::setDefaults();
        self::scheduleEvents();
        flush_rewrite_rules();
        update_option('amf_activated', time());
    }

    public static function deactivate(): void
    {
        // Check user capabilities
        if (!current_user_can('activate_plugins')) {
            return;
        }

        self::unscheduleEvents();
        flush_rewrite_rules();
    }

    private static function createTables(): void
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_configurations = $wpdb->prefix . 'amf_configurations';
        $sql_configurations = "CREATE TABLE $table_configurations (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            config_key VARCHAR(100) NOT NULL,
            config_type VARCHAR(50) NOT NULL,
            config_data LONGTEXT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY config_key (config_key),
            KEY config_type (config_type)
        ) ENGINE=InnoDB $charset_collate;";
        $table_field_groups = $wpdb->prefix . 'amf_field_groups';
        $sql_field_groups = "CREATE TABLE $table_field_groups (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            group_key VARCHAR(100) NOT NULL,
            group_title VARCHAR(255) NOT NULL,
            group_config LONGTEXT NOT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT '1',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY group_key (group_key),
            KEY is_active (is_active)
        ) ENGINE=InnoDB $charset_collate;";
        $table_relationships = $wpdb->prefix . 'amf_relationships';
        $sql_relationships = "CREATE TABLE $table_relationships (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            from_post_id BIGINT(20) UNSIGNED NOT NULL,
            to_post_id BIGINT(20) UNSIGNED NOT NULL,
            field_key VARCHAR(100) NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY relationship (from_post_id, to_post_id, field_key),
            KEY to_post_id (to_post_id),
            KEY field_key (field_key)
        ) ENGINE=InnoDB $charset_collate;";
        $table_meta_cache = $wpdb->prefix . 'amf_meta_cache';
        $sql_meta_cache = "CREATE TABLE $table_meta_cache (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            object_id BIGINT(20) UNSIGNED NOT NULL,
            object_type VARCHAR(50) NOT NULL,
            meta_key VARCHAR(255) NOT NULL,
            meta_value LONGTEXT,
            cache_hash VARCHAR(64) NOT NULL,
            expires_at DATETIME DEFAULT NULL,
            PRIMARY KEY (id),
            KEY object_lookup (object_type, object_id, meta_key),
            KEY cache_hash (cache_hash),
            KEY expires_at (expires_at)
        ) ENGINE=InnoDB $charset_collate;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta($sql_configurations);
        dbDelta($sql_field_groups);
        dbDelta($sql_relationships);
        dbDelta($sql_meta_cache);
        update_option('amf_db_version', AMF_VERSION);
    }

    private static function setDefaults(): void
    {
        $defaults = [
            'amf_version' => AMF_VERSION,
            'amf_settings' => [
                'enable_gutenberg' => true,
                'enable_rest_api' => true,
                'enable_graphql' => false,
                'cache_enabled' => true,
                'cache_ttl' => 3600,
                'debug_mode' => false,
            ],
            'amf_field_groups' => [],
            'amf_post_types' => [],
            'amf_taxonomies' => [],
        ];

        foreach ($defaults as $option => $value) {
            if (!get_option($option)) {
                add_option($option, $value);
            }
        }
    }

    private static function scheduleEvents(): void
    {
        if (!wp_next_scheduled('amf_cleanup_cache')) {
            wp_schedule_event(time(), 'daily', 'amf_cleanup_cache');
        }
    }

    private static function unscheduleEvents(): void
    {
        wp_clear_scheduled_hook('amf_cleanup_cache');
    }
}
