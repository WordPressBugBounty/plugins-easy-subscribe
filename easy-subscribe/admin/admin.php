<?php

namespace Devnet\EasySubscribe\Admin;

use Devnet\EasySubscribe\Includes\Activator;
use Devnet\EasySubscribe\Includes\Helper;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class EasySubscribe_Admin {
    private $plugin_name;

    private $version;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $page = ( isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : null );
        add_action( 'admin_enqueue_scripts', [$this, 'enqueue_styles'] );
        add_action( 'admin_enqueue_scripts', [$this, 'enqueue_scripts'] );
        add_action( 'admin_menu', [$this, 'admin_menu'], 100 );
        add_action( 'admin_menu', [$this, 'more_free_plugins_submenu'], PHP_INT_MAX );
        add_filter( 'plugin_action_links_' . DEVNET_ESUB_SLUG, [$this, 'plugin_action_links'] );
        add_action( 'wp_ajax_esub_repair_tables', [$this, 'repair_tables'] );
        add_action( 'rest_api_init', [$this, 'easy_subscribe_endpoint'] );
        if ( $page === 'easy-subscribe' || $page === 'easy-subscribe-subscribers' ) {
            add_filter( 'admin_footer_text', [$this, 'admin_credits'] );
        }
        add_action( 'wp_ajax_esub_get_list_count', [$this, 'get_list_count'] );
    }

    public function enqueue_styles( $hook ) {
        $sufix = '';
        $load = false;
        if ( $hook === 'toplevel_page_easy-subscribe' ) {
            wp_enqueue_style( 'wp-color-picker' );
            $load = true;
        }
        if ( $hook === 'easy-subscribe_page_easy-subscribe-subscribers' ) {
            $sufix = '-subscribers';
            $load = true;
        }
        if ( $load ) {
            wp_enqueue_style(
                $this->plugin_name . $sufix,
                plugin_dir_url( __DIR__ ) . 'assets/build/admin' . $sufix . '.css',
                [],
                $this->version,
                'all'
            );
        }
        if ( $hook === 'plugin-install.php' && isset( $_GET['tab'], $_GET['user'] ) && $_GET['tab'] === 'favorites' && $_GET['user'] === 'devnethr' ) {
            add_action( 'admin_head', function () {
                echo '<style>
						.plugin-install-php #the-list { gap:1rem; }
						.plugin-install-php .plugin-card { order:100; margin:0 !important; }
						.plugin-install-php .plugin-card.plugin-card-free-shipping-label { order:1; }
						.plugin-install-php .plugin-card.plugin-card-product-price-history { order:2; }
						.plugin-install-php .plugin-card.plugin-card-easy-subscribe { order:3; }
						.plugin-install-php .plugin-card.plugin-card-pingvid { order:4; }
						.plugin-install-php .plugin-card.plugin-card-price-alerts { order:5; }
						.plugin-install-php .plugin-card.plugin-card-easy-booking-calendar { order:6; }
						.plugin-install-php .plugin-card.plugin-card-snap-blocks { order:7; }
						.plugin-install-php .plugin-card.plugin-card-biznotes { order:8; }
					</style>';
            } );
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     */
    public function enqueue_scripts( $hook ) {
        $sufix = '';
        $localized_obj_name = '';
        $script_data = [];
        $load = false;
        if ( $hook === 'toplevel_page_easy-subscribe' ) {
            $load = true;
            $localized_obj_name = 'devnet_esub_script';
            wp_enqueue_media();
            wp_enqueue_script(
                'wp-color-picker-alpha',
                plugin_dir_url( __DIR__ ) . 'assets/color-picker/wp-color-picker-alpha.min.js',
                ['wp-color-picker'],
                $this->version,
                true
            );
            $script_asset_path = plugin_dir_path( __DIR__ ) . 'assets/build/preview.asset.php';
            $script_info = ( file_exists( $script_asset_path ) ? include $script_asset_path : [
                'dependencies' => ['wp-element'],
                'version'      => $this->version,
            ] );
            $script_info['dependencies'][] = 'wp-color-picker';
            wp_enqueue_script(
                $this->plugin_name . '-preview',
                plugin_dir_url( __DIR__ ) . 'assets/build/preview.js',
                $script_info['dependencies'],
                $script_info['version'],
                true
            );
            $all_options = Helper::get_all_options();
            $preview_script_data = [
                'options' => $all_options,
                'preview' => true,
            ];
            $preview_script_data = apply_filters( 'esub_preview_script_data', $preview_script_data );
            /* translators: %1$s: number of deleted entries */
            $text_delete_success = __( '%s old entries has been successfully deleted.', 'easy-subscribe' );
            $script_data = [
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'easy-subscribe-nonce' ),
                'text'    => [
                    '30_days'        => esc_html__( '30 days', 'easy-subscribe' ),
                    '3_months'       => esc_html__( '3 months', 'easy-subscribe' ),
                    '6_months'       => esc_html__( '6 months', 'easy-subscribe' ),
                    '12_months'      => esc_html__( '12 months', 'easy-subscribe' ),
                    'delete_confirm' => esc_html__( 'Are you sure you want to proceed? This action will irreversibly delete old data.', 'easy-subscribe' ),
                    'delete_success' => $text_delete_success,
                    'delete_none'    => esc_html__( 'No old data was found or deleted.', 'easy-subscribe' ),
                ],
                'options' => $all_options,
            ];
            $script_data = apply_filters(
                'esub_script_data',
                $script_data,
                'admin',
                []
            );
            wp_localize_script( $this->plugin_name . '-preview', $localized_obj_name, $preview_script_data );
        }
        if ( $hook === 'easy-subscribe_page_easy-subscribe-subscribers' ) {
            $load = true;
            $localized_obj_name = 'devnet_esub_subscribers_script';
            $sufix = '-subscribers';
            $script_data = [
                'ajaxurl'            => admin_url( 'admin-ajax.php' ),
                'nonce'              => wp_create_nonce( 'wp_rest' ),
                'text'               => [
                    'select_csv_fields' => esc_html__( 'Please select at least one field.', 'easy-subscribe' ),
                ],
                'additional_columns' => apply_filters( 'esub_table_additional_columns', [] ),
            ];
        }
        if ( $load ) {
            $script_asset_path = plugin_dir_url( __DIR__ ) . 'assets/build/admin' . $sufix . '.asset.php';
            $script_info = ( file_exists( $script_asset_path ) ? include $script_asset_path : [
                'dependencies' => ['jquery', 'wp-element'],
                'version'      => $this->version,
            ] );
            wp_enqueue_script(
                $this->plugin_name,
                plugin_dir_url( __DIR__ ) . 'assets/build/admin' . $sufix . '.js',
                $script_info['dependencies'],
                $script_info['version'],
                true
            );
            wp_localize_script( $this->plugin_name, $localized_obj_name, $script_data );
        }
    }

    /**
     * Add admin menu page.
     *
     */
    public function admin_menu() {
        $plugin_settings = new Settings();
        $menu_slug = 'easy-subscribe';
        add_menu_page(
            esc_html__( 'Easy Subscribe', 'easy-subscribe' ),
            esc_html__( 'Easy Subscribe', 'easy-subscribe' ),
            'edit_posts',
            $menu_slug,
            [$plugin_settings, 'settings_page'],
            'dashicons-email-alt',
            56
        );
        add_submenu_page(
            $menu_slug,
            esc_html__( 'Settings', 'easy-subscribe' ),
            esc_html__( 'Settings', 'easy-subscribe' ),
            'edit_posts',
            $menu_slug
        );
        add_submenu_page(
            $menu_slug,
            esc_html__( 'Subscribers', 'easy-subscribe' ),
            esc_html__( 'Subscribers', 'easy-subscribe' ),
            'edit_posts',
            $menu_slug . '-subscribers',
            [$this, 'page_subscribers']
        );
    }

    /**
     * Add the "More Free Plugins" submenu after Freemius adds its submenu items.
     *
     */
    public function more_free_plugins_submenu() {
        $menu_slug = 'easy-subscribe';
        add_submenu_page(
            $menu_slug,
            esc_html__( 'More Free Plugins', 'easy-subscribe' ),
            esc_html__( 'More Free Plugins', 'easy-subscribe' ),
            'edit_posts',
            $menu_slug . '-devnet-plugins',
            function () {
                $url = add_query_arg( [
                    'tab'      => 'favorites',
                    'user'     => 'devnethr',
                    '_wpnonce' => wp_create_nonce( 'save_wporg_username_' . get_current_user_id() ),
                ], admin_url( 'plugin-install.php' ) );
                wp_safe_redirect( $url );
                exit;
            }
        );
    }

    /**
     * Callback function for admin menu page.
     *
     */
    public function page_subscribers() {
        include_once plugin_dir_path( __FILE__ ) . '/partials/subscribers-page.php';
    }

    /**
     * Add plugin action link.
     *
     */
    public function plugin_action_links( $links ) {
        $custom_links = [];
        $custom_links[] = '<a href="' . esc_url( get_admin_url( null, 'admin.php?page=easy-subscribe' ) ) . '">' . esc_html__( 'Settings', 'easy-subscribe' ) . '</a>';
        return array_merge( $custom_links, $links );
    }

    /**
     * Ajax action for creating missing tables.
     *
     */
    public function repair_tables() {
        check_ajax_referer( 'easy-subscribe-nonce', 'security' );
        require_once plugin_dir_path( __DIR__ ) . 'includes/activator.php';
        Activator::activate();
        wp_send_json( 'ok' );
        wp_die();
    }

    /**
     * Ajax action for getting lists total entries count.
     *
     */
    public function get_list_count() {
        check_ajax_referer( 'wp_rest', 'security' );
        $lists = Helper::get_lists( true );
        wp_send_json( $lists );
        wp_die();
    }

    /**
     * The callback is fired before the main callback to check if the current user can access the endpoint.
     *
     */
    public function get_data_permission_check_callback( $request ) {
        // Restrict endpoint to only users who have the edit_posts capability.
        if ( !current_user_can( 'edit_posts' ) ) {
            return new \WP_Error('rest_forbidden', esc_html__( 'OMG you can not view private data.', 'easy-subscribe' ), [
                'status' => 401,
            ]);
        }
        // This is a black-listing approach. You could alternatively do this via white-listing, by returning false here and changing the permissions check.
        return true;
    }

    /**
     * Easy Subscribe endpoint callback
     *
     */
    public function get_esub_data_callback( $request ) {
        $args = [
            'list'   => $request->get_param( 'list' ),
            'opt_in' => $request->get_param( 'opt_in' ),
            'date'   => $request->get_param( 'date' ),
            'limit'  => $request->get_param( 'limit' ),
        ];
        // Return data as JSON
        return rest_ensure_response( Helper::get_subscribers( $args ) );
    }

    /**
     * Easy Subscribe endpoint callback
     *
     */
    public function post_esub_data_callback( $request ) {
        $args = [
            'name'          => $request->get_param( 'name' ),
            'last_name'     => $request->get_param( 'last_name' ),
            'email'         => $request->get_param( 'email' ),
            'list'          => $request->get_param( 'list' ),
            'double_opt_in' => $request->get_param( 'double_opt_in' ),
            'origin_url'    => $request->get_param( 'origin_url' ),
            'form_id'       => $request->get_param( 'form_id' ),
        ];
        // Return data as JSON
        return rest_ensure_response( Helper::create_entry( $args ) );
    }

    /**
     * Easy Subscribe endpoint
     *
     */
    public function easy_subscribe_endpoint() {
        register_rest_route( 'easy-subscribe/v1', '/data', [
            'methods'             => \WP_REST_Server::READABLE,
            'callback'            => [$this, 'get_esub_data_callback'],
            'permission_callback' => [$this, 'get_data_permission_check_callback'],
            'args'                => [
                'list'   => [
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => 'rest_validate_request_arg',
                    'required'          => false,
                ],
                'opt_in' => [
                    'type'              => 'boolean',
                    'sanitize_callback' => 'rest_sanitize_boolean',
                    'validate_callback' => 'rest_validate_request_arg',
                    'required'          => false,
                ],
                'date'   => [
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => 'rest_validate_request_arg',
                    'required'          => false,
                ],
                'limit'  => [
                    'type'              => 'number',
                    'sanitize_callback' => 'absint',
                    'validate_callback' => 'rest_validate_request_arg',
                    'required'          => false,
                ],
            ],
        ] );
        register_rest_route( 'easy-subscribe/v1', '/subscribe', [
            'methods'             => \WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'post_esub_data_callback'],
            'permission_callback' => '__return_true',
            'args'                => [
                'name'          => [
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => 'rest_validate_request_arg',
                    'required'          => false,
                ],
                'last_name'     => [
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => 'rest_validate_request_arg',
                    'required'          => false,
                ],
                'email'         => [
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_email',
                    'validate_callback' => 'rest_validate_request_arg',
                    'required'          => true,
                ],
                'list'          => [
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => 'rest_validate_request_arg',
                    'required'          => false,
                ],
                'double_opt_in' => [
                    'type'              => 'boolean',
                    'sanitize_callback' => 'rest_sanitize_boolean',
                    'validate_callback' => 'rest_validate_request_arg',
                    'required'          => false,
                ],
                'origin_url'    => [
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_url',
                    'validate_callback' => 'rest_validate_request_arg',
                    'required'          => false,
                ],
                'form_id'       => [
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => 'rest_validate_request_arg',
                    'required'          => false,
                ],
            ],
        ] );
    }

    /**
     * Modifies the admin credits.
     * 
     */
    public function admin_credits( $footer_text ) {
        $footer_text = '';
        $footer_text .= '<div class="esub-admin-footer">';
        $footer_text .= '<div class="esub-footer-message">';
        $footer_text .= 'Please rate <strong>Easy Subscribe</strong> <a href="https://wordpress.org/support/plugin/easy-subscribe/reviews/" target="_blank">★★★★★</a> on <a href="https://wordpress.org/support/plugin/easy-subscribe/reviews/" target="_blank">WordPress.org</a> to help us spread the word. Thank you from the <a href="https://devnet.hr/" target="_blank">Devnet</a> team!';
        $footer_text .= '</div>';
        $footer_text .= '</div>';
        $footer_text .= '</div>';
        return $footer_text;
    }

}
