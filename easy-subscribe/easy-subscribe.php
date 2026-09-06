<?php

/**
 * Plugin Name:       Easy Subscribe
 * Description:       Easy way to integrate the form and collect subscribers.
 * Version:           1.5.7
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            Devnet
 * Author URI:        https://devnet.hr
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       easy-subscribe
 * Domain Path:       /languages
 */
use Devnet\EasySubscribe\Includes\Activator;
use Devnet\EasySubscribe\Includes\Deactivator;
use Devnet\EasySubscribe\Includes\Plugin;
use Devnet\EasySubscribe\Includes\Uninstaller;
// If this file is called directly, abort.
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( function_exists( 'devnet_esub_fs' ) ) {
    devnet_esub_fs()->set_basename( false, __FILE__ );
} else {
    if ( !function_exists( 'devnet_esub_fs' ) ) {
        // Create a helper function for easy SDK access.
        function devnet_esub_fs() {
            global $devnet_esub_fs;
            if ( !isset( $devnet_esub_fs ) ) {
                // Include Freemius SDK.
                require_once __DIR__ . '/vendor/freemius/wordpress-sdk/start.php';
                $devnet_esub_fs = fs_dynamic_init( [
                    'id'               => '14904',
                    'slug'             => 'easy-subscribe',
                    'premium_slug'     => 'easy-subscribe-pro',
                    'type'             => 'plugin',
                    'public_key'       => 'pk_fb60e997a9379fa986e287711e169',
                    'is_premium'       => false,
                    'premium_suffix'   => '(Pro)',
                    'has_addons'       => true,
                    'has_paid_plans'   => true,
                    'is_org_compliant' => true,
                    'trial'            => [
                        'days'               => 7,
                        'is_require_payment' => true,
                    ],
                    'menu'             => [
                        'slug'       => 'easy-subscribe',
                        'first-path' => 'admin.php?page=easy-subscribe',
                    ],
                    'is_live'          => true,
                ] );
            }
            return $devnet_esub_fs;
        }

        // Init Freemius.
        devnet_esub_fs();
        // Signal that SDK was initiated.
        do_action( 'devnet_esub_fs_loaded' );
    }
    /*
     * Show the contact submenu item only when the user have a valid non-expired license.
     *
     * @param $is_visible The filtered value. Whether the submenu item should be visible or not.
     * @param $menu_id    The ID of the submenu item.
     *
     * @return bool If true, the menu item should be visible.
     */
    if ( !function_exists( 'devnet_esub_is_submenu_visible' ) ) {
        function devnet_esub_is_submenu_visible(  $is_visible, $menu_id  ) {
            if ( 'contact' != $menu_id ) {
                return $is_visible;
            }
            return devnet_esub_fs()->can_use_premium_code();
        }

    }
    /*
     * TODO: do uninstall logic.
     */
    if ( !function_exists( 'devnet_esub_fs_uninstall_cleanup' ) ) {
        function devnet_esub_fs_uninstall_cleanup() {
            require_once plugin_dir_path( __FILE__ ) . 'includes/uninstaller.php';
            Uninstaller::cleanup();
        }

    }
    if ( !function_exists( 'devnet_esub_fs_custom_icon' ) ) {
        function devnet_esub_fs_custom_icon() {
            return dirname( __FILE__ ) . '/assets/images/logo.png';
        }

    }
    /*
     * Run Freemius actions and filters.
     */
    if ( function_exists( 'devnet_esub_fs' ) ) {
        devnet_esub_fs()->add_filter(
            'is_submenu_visible',
            'devnet_esub_is_submenu_visible',
            10,
            2
        );
        devnet_esub_fs()->add_action( 'after_uninstall', 'devnet_esub_fs_uninstall_cleanup' );
        devnet_esub_fs()->add_filter( 'plugin_icon', 'devnet_esub_fs_custom_icon' );
    }
    function devnet_esub_options() {
        $options = [
            'general'  => get_option( 'devnet_esub_general' ),
            'forms'    => [
                'devnet_esub_form' => get_option( 'devnet_esub_form' ),
            ],
            'messages' => get_option( 'devnet_esub_messages' ),
        ];
        return $options;
    }

    function activate_plugin_easy_subscribe() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/activator.php';
        Activator::activate();
    }

    function deactivate_plugin_easy_subscribe() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/deactivator.php';
        Deactivator::deactivate();
    }

    register_activation_hook( __FILE__, 'activate_plugin_easy_subscribe' );
    register_deactivation_hook( __FILE__, 'deactivate_plugin_easy_subscribe' );
    function run_devnet_easy_subscribe_update() {
        $db_version = (int) get_option( 'devnet_esub_db_version' );
        if ( !$db_version || $db_version < DEVNET_ESUB_DB_VERSION ) {
            require_once plugin_dir_path( __FILE__ ) . 'includes/activator.php';
            Activator::update_table();
        }
    }

    function run_devnet_easy_subscribe() {
        require plugin_dir_path( __FILE__ ) . 'includes/plugin.php';
        $plugin = new Plugin();
        $plugin->run();
    }

    define( 'DEVNET_ESUB_VERSION', '1.5.7' );
    define( 'DEVNET_ESUB_DB_VERSION', 3 );
    define( 'DEVNET_ESUB_NAME', 'Easy Subscribe' );
    define( 'DEVNET_ESUB_SLUG', plugin_basename( __FILE__ ) );
    define( 'DEVNET_ESUB_OPTIONS', devnet_esub_options() );
    run_devnet_easy_subscribe_update();
    run_devnet_easy_subscribe();
}