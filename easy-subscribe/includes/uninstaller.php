<?php

namespace Devnet\EasySubscribe\Includes;


if (!defined('ABSPATH')) {
    exit;
}


class Uninstaller
{

    public static function cleanup()
    {
        $options = get_option('devnet_esub_general');

        $delete_plugin_data = $options['delete_plugin_data'] ?? false;

        if ($delete_plugin_data) {

            $all_plugins = get_plugins();

            $easy_subscribe_slug = 'easy-subscribe';

            $easy_subscribe_plugin = [$easy_subscribe_slug . '/' . $easy_subscribe_slug . '.php', $easy_subscribe_slug . '-pro/' . $easy_subscribe_slug . '.php',];

            // Ensure no data has ben deleted if both plugins are installed.
            if (!isset($all_plugins[$easy_subscribe_plugin[0]], $all_plugins[$easy_subscribe_plugin[1]])) {

                self::delete_options();
                self::remove_tables();
            }
        }
    }

    public static function delete_options()
    {

        delete_option('devnet_esub_general');

        $forms = (array)get_option('devnet_esub_forms');

        foreach ($forms as $form_group_id) {
            delete_option($form_group_id);
        }

        delete_option('devnet_esub_forms');
        delete_option('devnet_esub_form');
        delete_option('devnet_esub_messages');
        delete_option('devnet_esub_db_version');
    }


    public static function remove_tables()
    {
        global $wpdb;

        $tables = [
            'easy_subscribe_meta',
            'easy_subscribe',
        ];

        foreach ($tables as $table) {
            $table_name = $wpdb->prefix . $table;

            // Properly escape the table name
            $table_name_safe = esc_sql($table_name);

            // Directly use the escaped table name in the query
            $wpdb->query("DROP TABLE IF EXISTS `$table_name_safe`"); // Backticks to properly quote identifier
        }
    }
}
