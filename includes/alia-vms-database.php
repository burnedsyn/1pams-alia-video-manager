<?php
// alia-vms-plugin/includes/alia-vms-database.php

function alia_vms_create_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'alia_vms_videos';

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            vimeo_id varchar(255) NOT NULL,
            title varchar(255) NOT NULL,
            description text,
            seo_tags text,
            ovh_object_paths text,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

add_action('plugins_loaded', 'alia_vms_create_table');
