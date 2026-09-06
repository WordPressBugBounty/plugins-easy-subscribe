<?php

namespace Devnet\EasySubscribe\Includes;

use Devnet\EasySubscribe\Includes\Defaults;


if (!defined('ABSPATH')) {
    exit;
}


class Helper
{

    public static function get_table_name($table = 'esub')
    {
        global $wpdb;

        $tables = [
            'esub' => $wpdb->prefix . 'easy_subscribe',
            'meta' => $wpdb->prefix . 'easy_subscribe_meta',
        ];

        if (empty($table)) {
            return array_values($tables);
        }

        return $tables[$table] ?? null;
    }


    /**
     * Create entry in the easy_subscribe_subscribers table.
     *
     * @since     1.0.0
     */
    public static function create_entry($data = [])
    {
        if (empty($data)) {
            return;
        }

        $data = apply_filters('esub_data_before_insert', $data);

        $name              = $data['name'] ?? '';
        $last_name         = $data['last_name'] ?? '';
        $email             = $data['email'] ?? '';
        $list              = $data['list'] ?? '';
        $custom_input      = $data['custom_input'] ?? '';
        $custom_data       = $data['custom_data'] ?? '';
        $notification_date  = null;
        $date_created      = $data['date_created'] ?? current_time('mysql');
        $origin_url        = $data['origin_url'] ?? '';
        $form_id          = $data['form_id'] ?? '';
        $double_opt_in  = filter_var($data['double_opt_in'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $status         = $double_opt_in ? 'unconfirmed' : '';
        $opt_in         = $double_opt_in ? 0 : null;
        $security_token = $double_opt_in ? wp_generate_password(32, false) : null;


        $current_user = wp_get_current_user();

        if ($current_user->ID && $current_user->user_email === $email) {

            // Auto-confirm if the user is logged in and subscribing with their own email
            $opt_in = 1;
            $status = 'confirmed';
            $security_token = null;

            if (empty($name)) {
                $name = $current_user->display_name;
            }
        }


        global $wpdb;

        $table_name = self::get_table_name('esub');

        $affected_row_id = null;
        $response = '';

        // Run the SQL statement with INSERT IGNORE
        $entry = $wpdb->query(
            $wpdb->prepare(
                "INSERT IGNORE INTO $table_name
                (name, last_name, email, list, opt_in, security_token, status, custom_input, custom_data, notification_date, date_created)
                VALUES (%s, %s, %s, %s, %d, %s, %s, %s, %s, %s, %s)",
                $name, // string value
                $last_name, // string value
                $email, // string value
                $list, // string value
                $opt_in, // boolean - tinyint
                $security_token, // string value
                $status, // string value
                $custom_input, // string value
                $custom_data, // string value
                $notification_date, // string value
                $date_created // current datetime value
            )
        );

        $affected_row_id = $wpdb->insert_id;


        $pending_confirmation = false;

        if ($affected_row_id) {
            $response = 'created';

            // Send confirmation email only if double opt-in is enabled and the user is not auto-confirmed
            if ($double_opt_in && $status === 'unconfirmed') {
                Notifier::send_confirmation_email($email, $security_token, $origin_url);

                // TODO: check if email is sent.
                $pending_confirmation = true;
            }
        } else {
            // Check if there is an existing entry
            $existing_entry = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$table_name} WHERE list = %s AND email = %s",
                    $list,
                    $email
                )
            );

            $response = $existing_entry ? 'exists' : 'error';
        }

        do_action('esub_after_insert', $affected_row_id, $data);

        return [
            'id'       => $affected_row_id,
            'response' => $response,
            'pending_confirmation' => $pending_confirmation,

        ];
    }

    public static function email_confirmation($email, $token)
    {
        global $wpdb;

        $table_name = self::get_table_name('esub');

        $pending_subscriber = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE email = %s AND security_token = %s",
            $email,
            $token
        ));

        $is_confirmed = false;

        if ($pending_subscriber) {

            // Update subscriber to confirmed
            $is_confirmed = $wpdb->update(
                $table_name,
                ['status' => 'confirmed', 'opt_in' => 1, 'security_token' => null],
                ['email' => $email, 'security_token' => $token]

            );
        }

        return $is_confirmed;
    }


    /**
     * Get the entry by the ID and the Email.
     * 
     * @since    1.0.0
     * 
     */
    public static function get_entry_by_id_and_email($email, $id)
    {

        global $wpdb;

        $table_name = self::get_table_name('esub');

        $entry = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE id = %d AND user_email = %s",
                $id,
                $email
            )
        );

        return $entry;
    }


    /**
     * Get all list names.
     * 
     * @since    1.0.0
     * 
     */
    public static function get_lists($with_count = false)
    {

        global $wpdb;

        $table_name = self::get_table_name('esub');

        $unique_list_names = $wpdb->get_col("SELECT DISTINCT list FROM {$table_name} ORDER BY list");

        $output = $unique_list_names;

        if ($with_count) {
            $output = [];

            foreach ($unique_list_names as $list) {

                $total_records = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $table_name WHERE list = %s",
                    $list
                ));

                $output[$list] = $total_records;
            }
        }

        return $output;
    }


    /**
     * Update entry status.
     * 
     * @since    1.0.0
     * 
     */
    public static function update_status($id, $status)
    {

        global $wpdb;

        $table_name = self::get_table_name('esub');

        $result = $wpdb->update(
            $table_name,
            [
                'status' => $status,
            ],
            [
                'id' => $id,
            ]
        );

        if ($result !== false) {
            // Update successful
            return true;
        } else {
            // Update failed
            return false;
        }
    }

    /**
     * User has opt-in, confirm emails.
     * 
     * @since    1.0.0
     * 
     */
    public static function user_opted_in($id)
    {

        global $wpdb;

        $table_name = self::get_table_name('esub');

        $result = $wpdb->update(
            $table_name,
            [
                'opt_in' => 1,
            ],
            [
                'id' => $id,
            ]
        );

        if ($result !== false) {
            // Update successful
            return true;
        } else {
            // Update failed
            return false;
        }
    }


    /**
     * Check if user email is confirmed.
     * 
     * @since    1.0.0
     * 
     */
    public static function is_confirmed_email($id)
    {

        global $wpdb;

        $table_name = self::get_table_name('esub');

        $entry = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT opt_in FROM {$table_name} WHERE id = %d",
                $id
            )
        );

        return $entry;
    }



    /**
     * Easy Subscribe - Subscribers data
     * 
     * @since    1.0.0
     * 
     */
    public static function get_subscribers($args = [])
    {

        global $wpdb;

        $table_name = self::get_table_name('esub');

        // $query = "SELECT esub.* FROM {$table_name} esub";

        // Allow addons to inject JOINs or extra SELECT fields
        $select_fields = ['esub.*'];
        $joins = [];

        /** @var array ['fields' => [], 'joins' => []] */
        $addon_modifiers = apply_filters('esub_get_subscribers_query', [
            'fields' => [],
            'joins' => [],
        ], $args);

        if (!empty($addon_modifiers['fields'])) {
            $select_fields = array_merge($select_fields, $addon_modifiers['fields']);
        }

        if (!empty($addon_modifiers['joins'])) {
            $joins = array_merge($joins, $addon_modifiers['joins']);
        }

        // Build SELECT and JOIN clauses
        $query = "SELECT " . implode(', ', $select_fields) . " FROM {$table_name} esub";
        $query .= ' ' . implode(' ', $joins);

        // Add WHERE clause based on the presence of $args
        $where_clauses = [];
        $params        = [];
        $limit_clause  = '';

        if (isset($args['list'])) {
            if ($args['list'] !== '---') {
                $where_clauses[] = 'esub.list = %s';
                $params[] = $args['list'];
            } else {
                $where_clauses[] = 'esub.list = "" OR esub.list IS NULL';
            }
        }

        if (isset($args['opt_in'])) {
            if ($args['opt_in']) {
                $where_clauses[] = 'esub.opt_in = 1';
            }
        }

        if (isset($args['status'])) {
            $where_clauses[] = 'esub.status = %s';
            $params[] = $args['status'];
        }

        // Add date filter
        if (isset($args['date'])) {
            if (strpos($args['date'], ' to ') !== false) {
                // Date range
                list($start_date, $end_date) = explode(' to ', $args['date']);
                $where_clauses[] = 'esub.date_created BETWEEN %s AND %s';
                $params[] = $start_date;
                $params[] = $end_date;
            } else {
                // Single date
                $where_clauses[] = 'DATE(esub.date_created) = %s';
                $params[] = $args['date'];
            }
        }

        if (isset($args['limit'])) {
            $limit_clause = 'LIMIT %d';
            $params[] = $args['limit'];
        }

        if (!empty($where_clauses)) {
            $query .= ' WHERE ' . implode(' AND ', $where_clauses);
        }


        $query .= " ORDER BY esub.date_created DESC {$limit_clause}";

        // Prepare and execute the SQL query
        $data = $wpdb->get_results(
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $wpdb->prepare($query, $params)
        );

        return $data;
    }


    /**
     * Delete entries from the easy_subscribe table.
     *
     * @since     1.0.0
     */
    public static function delete_entries($ids = [])
    {
        global $wpdb;

        $table_name = self::get_table_name('esub');

        $deleted = null;

        if (!empty($ids)) {

            $ids_placeholder = implode(', ', array_fill(0, count($ids), '%d'));

            $deleted = $wpdb->query(
                $wpdb->prepare("DELETE FROM {$table_name} WHERE id IN ({$ids_placeholder})", $ids)
            );
        }

        return $deleted;
    }



    /**
     * Delete old data from the easy_subscribe table.
     *
     * @since     1.0.0
     */
    public static function delete_old_data($older_than)
    {

        if (!$older_than) {
            return;
        }

        $date_interval  = "";
        $interval_value = "";

        // Extract the numeric value from the older_than using regex
        preg_match('/(\d+)_\w+/', $older_than, $matches);

        if (!empty($matches)) {
            $interval_value = intval($matches[1]);
            $date_interval  = preg_replace('/\d+_/', '', $older_than);
            $date_interval  = rtrim($date_interval, 's');
        }

        global $wpdb;

        $table_name = self::get_table_name('esub');

        $wpdb->get_results($wpdb->prepare(
            "DELETE FROM {$table_name} WHERE date_created < DATE_SUB(NOW(), INTERVAL %d $date_interval)",
            $interval_value
        ), ARRAY_A);

        return $wpdb->rows_affected;
    }

    /**
     * CHeck if easy_subscribe tables exist.
     *
     * @since     1.0.0
     */
    public static function missing_esub_tables(): bool
    {
        global $wpdb;

        $missing = false;

        // Get all esub-related table names
        $tables = self::get_table_name('');

        foreach ($tables as $table_name) {
            $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
            if ($exists !== $table_name) {
                $missing = true;
                break;
            }
        }

        return $missing;
    }


    /**
     * Get option or default.
     *
     * @since     1.0.0
     */
    public static function get_option($name, $group)
    {
        $options = DEVNET_ESUB_OPTIONS;

        $group_options = $options[$group] ?? [];

        $option = $group_options[$name] ?? Defaults::{$group}($name);

        return $option;
    }

    /**
     * Get all options or defaults.
     *
     * @since     1.1.0
     */
    public static function get_all_options()
    {
        $saved_options = DEVNET_ESUB_OPTIONS;
        $is_multilingual = $saved_options['general']['multilingual'] ?? false;

        $multilingual_fields = [
            'name_field_label',
            'last_name_field_label',
            'gdpr_text',
            'email_field_label',
            'button_label',
            'content_before_form',
            'success',
            'exists',
            'spam',
        ];

        $options = [];

        foreach ($saved_options as $group_name => $group_options) {
            if ($group_name === 'forms') {
                $forms = $saved_options[$group_name];
                $group_defaults = Defaults::form();

                foreach ($forms as $form_name => $form_options) {
                    foreach ($group_defaults as $name => $value) {
                        $option_value = $form_options[$name] ?? $value;

                        if ($is_multilingual && in_array($name, $multilingual_fields)) {
                            $option_value = $value;
                        }

                        $options[$group_name][$form_name][$name] = $option_value;
                    }
                }
            } else {
                $group_defaults = Defaults::{$group_name}();

                foreach ($group_defaults as $name => $value) {
                    $option_value = $group_options[$name] ?? $value;

                    if ($is_multilingual && in_array($name, $multilingual_fields)) {
                        $option_value = $value;
                    }

                    $options[$group_name][$name] = $option_value;
                }
            }
        }

        $options = apply_filters('esub_all_options', $options);

        return $options;
    }


    /**
     * Extracts the form ID from a string like 'devnet_esub_form' or 'devnet_esub_form_123'.
     *
     */
    public static function extract_form_id($key)
    {
        if ($key === 'devnet_esub_form') {
            return null;
        }

        $prefix = 'devnet_esub_form_';

        if (str_starts_with($key, $prefix)) {
            return substr($key, strlen($prefix));
        }

        return null;
    }


    /***
     * 
     * Handle meta data
     * 
     */
    /**
     * Add a meta entry.
     */
    public static function add_meta($subscribe_id, $key, $value)
    {
        global $wpdb;

        return $wpdb->insert(
            self::get_table_name('meta'),
            [
                'subscribe_id' => $subscribe_id,
                'meta_key'     => $key,
                'meta_value'   => maybe_serialize($value),
            ]
        );
    }

    /**
     * Update a meta entry.
     */
    public static function update_meta($subscribe_id, $key, $value)
    {
        global $wpdb;

        $table = self::get_table_name('meta');

        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE subscribe_id = %d AND meta_key = %s",
            $subscribe_id,
            $key
        ));

        if ($exists) {
            return $wpdb->update(
                $table,
                ['meta_value' => maybe_serialize($value)],
                ['subscribe_id' => $subscribe_id, 'meta_key' => $key]
            );
        } else {
            return self::add_meta($subscribe_id, $key, $value);
        }
    }

    /**
     * Get a meta value.
     */
    public static function get_meta($subscribe_id, $key, $default = null)
    {
        global $wpdb;

        $table = esc_sql(self::get_table_name('meta'));

        $value = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT meta_value FROM {$table} WHERE subscribe_id = %d AND meta_key = %s",
                $subscribe_id,
                $key
            )
        );

        return $value !== null ? maybe_unserialize($value) : $default;
    }

    /**
     * Delete a meta entry.
     */
    public static function delete_meta($subscribe_id, $key)
    {
        global $wpdb;

        return $wpdb->delete(
            self::get_table_name('meta'),
            [
                'subscribe_id' => $subscribe_id,
                'meta_key'     => $key,
            ]
        );
    }

    /**
     * Get all meta entries for a subscriber.
     */
    public static function get_all_meta($subscribe_id)
    {
        global $wpdb;

        $table = esc_sql(self::get_table_name('meta'));

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT meta_key, meta_value FROM {$table} WHERE subscribe_id = %d",
                $subscribe_id
            ),
            ARRAY_A
        );

        $meta = [];

        foreach ($results as $row) {
            $meta[$row['meta_key']] = maybe_unserialize($row['meta_value']);
        }

        return $meta;
    }
}
