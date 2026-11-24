<?php

namespace Devnet\EasySubscribe\Includes;


if (!defined('ABSPATH')) {
	exit;
}


class Activator
{

	public static function activate()
	{
		self::setup_db_tables();
		self::set_db_version();
	}

	public static function set_db_version()
	{
		update_option('devnet_esub_db_version', DEVNET_ESUB_DB_VERSION);
	}

	/**
	 * The Multilingual option forced translated defaults for these texts; an empty text
	 * now means the same. Empty them once, keeping each site's output, then drop the flag.
	 */
	public static function retire_multilingual()
	{
		$general = get_option('devnet_esub_general');

		if (empty($general['multilingual'])) {
			return;
		}

		$fields = ['name_field_label', 'last_name_field_label', 'gdpr_text', 'email_field_label', 'button_label', 'content_before_form', 'success', 'exists', 'spam'];
		$groups = array_merge(['devnet_esub_form', 'devnet_esub_messages'], (array) get_option('devnet_esub_forms', []));

		foreach ($groups as $group) {
			$values = get_option($group);

			if (!is_array($values)) {
				continue;
			}

			foreach ($fields as $field) {
				if (isset($values[$field])) {
					$values[$field] = '';
				}
			}

			update_option($group, $values);
		}

		unset($general['multilingual']);
		update_option('devnet_esub_general', $general);
	}

	/** Option names holding one form each: the base form, added forms and an existing Product Form. */
	private static function form_groups()
	{
		$groups = array_merge(['devnet_esub_form'], (array) get_option('devnet_esub_forms', []));
		if (is_array(get_option('devnet_esub_form_product'))) {
			$groups[] = 'devnet_esub_form_product';
		}
		return array_unique($groups);
	}

	/**
	 * Whether the one-time updates have work left: the old Multilingual flag, messages never copied, or a form
	 * without its own messages (1.5.x saved it again after a downgrade). Cheap: the options are autoloaded.
	 */
	public static function needs_migration()
	{
		$general = get_option('devnet_esub_general');
		if (!empty($general['multilingual']) || !get_option('devnet_esub_form_messages_migrated')) {
			return true;
		}
		foreach (self::form_groups() as $group) {
			$saved = get_option($group);
			if (is_array($saved) && !array_key_exists('success', $saved)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Runs the one-time updates in one request at a time. They read and write whole options, so the first visits
	 * after an update, arriving together, would overwrite each other's work. INSERT IGNORE on the unique option
	 * name lets one request in (add_option() wouldn't: it updates an existing row); the others skip, and see the
	 * result on their next request. A lock older than 5 minutes (a request that died) is taken over.
	 */
	public static function migrate()
	{
		global $wpdb;

		$lock = 'devnet_esub_migration_lock';
		$now  = time();

		// phpcs:disable WordPress.DB.DirectDatabaseQuery -- An atomic lock: no options API call inserts without updating.
		$locked = $wpdb->query($wpdb->prepare("INSERT IGNORE INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, 'no')", $lock, $now))
			|| $wpdb->query($wpdb->prepare("UPDATE {$wpdb->options} SET option_value = %s WHERE option_name = %s AND option_value < %d", $now, $lock, $now - 300));
		// phpcs:enable WordPress.DB.DirectDatabaseQuery

		if (!$locked) {
			return false;
		}

		// Another request may have finished since this one loaded the options: read them again, not from the cache.
		foreach (['alloptions', 'notoptions', 'devnet_esub_general', 'devnet_esub_messages', 'devnet_esub_forms', 'devnet_esub_form_product', 'devnet_esub_form_messages_migrated'] as $key) {
			wp_cache_delete($key, 'options');
		}
		foreach (self::form_groups() as $group) {
			wp_cache_delete($group, 'options');
		}

		self::retire_multilingual();
		$done = self::migrate_form_messages();

		delete_option($lock);

		return $done;
	}

	/**
	 * Copy the shared messages into every form that has none, preserving overrides and the legacy option for
	 * rollback. Safe to repeat: forms that have their messages are left alone.
	 */
	public static function migrate_form_messages()
	{
		$messages = (array) get_option('devnet_esub_messages', []);
		$keys = ['success', 'exists', 'error', 'spam', 'pending_confirmation'];
		foreach (self::form_groups() as $group) {
			$saved = (array) get_option($group, []);
			$values = $saved;
			foreach ($keys as $key) {
				if (!array_key_exists($key, $values)) {
					$values[$key] = $messages[$key] ?? '';
				}
			}
			if ($values !== $saved && !update_option($group, $values)) {
				error_log('Easy Subscribe: form message migration failed for ' . $group . '; retrying on the next request.');
				return false;
			}
		}
		// update_option() is false when the value is already stored: a repeat run is still a success.
		return get_option('devnet_esub_form_messages_migrated') || update_option('devnet_esub_form_messages_migrated', 1);
	}

	/**
	 * Create custom tables.
	 *  
	 */
	private static function setup_db_tables()
	{
		self::setup_esub_db_table();
		self::setup_esub_meta_db_table();
	}

	/**
	 * Create custom table.
	 *  
	 */
	private static function setup_esub_db_table()
	{
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		$table_name = $wpdb->prefix . 'easy_subscribe';

		$sql = "CREATE TABLE $table_name (
			  id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,        
			  name VARCHAR(120) NULL,
			  last_name VARCHAR(120) NULL,
			  email VARCHAR(120) NOT NULL,
			  list VARCHAR(55) NULL,			  		
			  opt_in TINYINT(1) UNSIGNED NULL,
			  security_token VARCHAR(255) NULL,		
			  status varchar(20) NULL,
			  custom_input VARCHAR(255) NULL,
			  custom_data TEXT NULL,
			  notification_date datetime NULL,
			  date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  PRIMARY KEY  (id),
			  INDEX (email),
			  INDEX (list),
			  CONSTRAINT email_list_unique UNIQUE (email, list)
			) $charset_collate;";

		if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) !== $table_name) {
			dbDelta($sql);
		}
	}

	/**
	 * Create custom meta table.
	 *  
	 */
	private static function setup_esub_meta_db_table()
	{
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		$table_name = $wpdb->prefix . 'easy_subscribe_meta';

		$sql = "CREATE TABLE $table_name (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			subscribe_id BIGINT(20) UNSIGNED NOT NULL,
			meta_key VARCHAR(100) NOT NULL,
			meta_value TEXT NOT NULL,
			PRIMARY KEY (id),
			INDEX (subscribe_id),
			FOREIGN KEY (subscribe_id) REFERENCES {$wpdb->prefix}easy_subscribe(id) ON DELETE CASCADE
		) $charset_collate;";


		if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) !== $table_name) {
			dbDelta($sql);
		}
	}


	/**
	 * Update table.
	 * 
	 */
	public static function update_table()
	{

		global $wpdb;

		$table_name = $wpdb->prefix . 'easy_subscribe';

		// Query to check if the table exists
		$table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));

		// Check the result
		if ($table_exists !== $table_name) {
			return;
		}

		// Unique (email, list)
		self::add_unique_constraint($wpdb, $table_name);

		// Alter the list column to VARCHAR(55)
		$wpdb->query("ALTER TABLE $table_name MODIFY list VARCHAR(55);");

		// Setup meta table
		self::setup_esub_meta_db_table();

		self::set_db_version();
	}

	/**
	 * Check for duplicates "email-list" entries and delete them.
	 * 
	 */
	public static function handle_duplicate_entries($wpdb, $table_name)
	{
		$table_name = esc_sql($table_name);

		// Identify Duplicate Entries
		$query = "SELECT email, list, MIN(id) AS keep_id
              FROM $table_name
              GROUP BY email, list
              HAVING COUNT(*) > 1";
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared	  
		$duplicate_entries = $wpdb->get_results($query);

		if ($duplicate_entries) {
			// Handle Duplicate Entries
			foreach ($duplicate_entries as $entry) {
				$email = $entry->email;
				$list = $entry->list;
				$keep_id = $entry->keep_id;

				// Delete duplicates except for the one with the smallest id (keep_id)
				$delete_query = $wpdb->prepare("
                DELETE FROM $table_name
                WHERE email = %s AND list = %s AND id != %d
            ", $email, $list, $keep_id);

				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query($delete_query);
			}
		}
	}

	/**
	 * Check for duplicates "email-list" entries and delete them.
	 * 
	 */
	public static function add_unique_constraint($wpdb, $table_name)
	{
		// Step 1: Clean up duplicate entries before applying unique constraints
		self::handle_duplicate_entries($wpdb, $table_name);

		// Step 2: Check if the named constraint already exists
		$has_named_constraint = $wpdb->get_var(
			$wpdb->prepare("SHOW INDEX FROM $table_name WHERE Key_name = %s", 'email_list_unique')
		);

		// Step 3: If the named constraint doesn't exist, add it
		if (!$has_named_constraint) {
			$wpdb->query("ALTER TABLE $table_name ADD CONSTRAINT email_list_unique UNIQUE (email, list)");
		}

		// Step 4: Check if the auto-generated email_2 index exists
		$has_email_2 = $wpdb->get_var(
			$wpdb->prepare("SHOW INDEX FROM $table_name WHERE Key_name = %s", 'email_2')
		);

		// Step 5: If both exist, drop the redundant email_2 index
		if ($has_named_constraint && $has_email_2) {
			$wpdb->query("ALTER TABLE $table_name DROP INDEX email_2");
		}
	}
}
