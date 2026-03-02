<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Check user permissions
if (!current_user_can('activate_plugins')) {
    exit;
}

global $wpdb;

function amf_uninstall_cleanup()
{
    global $wpdb;

    // Drop custom tables
    $tables = [
        'amf_configurations',
        'amf_field_groups',
        'amf_relationships',
        'amf_meta_cache',
    ];

    foreach ($tables as $table) {
        $table_name = $wpdb->prefix . $table;
        $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
    }
}

function amf_uninstall_options()
{
    $options = [
        'amf_version',
        'amf_settings',
        'amf_field_groups',
        'amf_post_types',
        'amf_taxonomies',
        'amf_meta_boxes',
        'amf_activated',
        'amf_welcome_shown',
        'amf_db_version',
    ];

    foreach ($options as $option) {
        delete_option($option);
    }

    if (is_multisite()) {
        foreach ($options as $option) {
            delete_site_option($option);
        }
    }
}

function amf_uninstall_user_meta()
{
    $users = get_users([
        'fields' => 'ID',
    ]);

    foreach ($users as $user_id) {
        delete_user_meta($user_id, 'amf_dismissed_notices');
    }
}

function amf_uninstall_post_meta()
{
    if (!defined('AMF_CLEANUP_POST_META') || !AMF_CLEANUP_POST_META) {
        return;
    }

    $post_types = get_post_types([], 'names');

    foreach ($post_types as $post_type) {
        $posts = get_posts([
            'post_type' => $post_type,
            'posts_per_page' => -1,
            'post_status' => 'any',
            'fields' => 'ids',
        ]);

        foreach ($posts as $post_id) {
            // remove amf meta from posts
        }
    }
}

function amf_uninstall_cron()
{
    wp_clear_scheduled_hook('amf_cleanup_cache');
}

amf_uninstall_cleanup();
amf_uninstall_options();
amf_uninstall_user_meta();
amf_uninstall_post_meta();
amf_uninstall_cron();
do_action('amf_uninstalled');
