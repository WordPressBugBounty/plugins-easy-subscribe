<?php

namespace Devnet\EasySubscribe\Frontend;

use Devnet\EasySubscribe\Includes\Helper;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class EasySubscribe_Public {
    private $plugin_name;

    private $version;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        add_action( 'wp_enqueue_scripts', [$this, 'enqueue_styles'] );
        add_action( 'wp_enqueue_scripts', [$this, 'enqueue_scripts'] );
        add_action( 'wp_footer', [$this, 'handle_email_confirmation'] );
        add_shortcode( 'easy-subscribe', [$this, 'form_shortcode'] );
    }

    public function enqueue_styles() {
        wp_register_style(
            $this->plugin_name,
            plugin_dir_url( __DIR__ ) . 'assets/build/public.css',
            [],
            $this->version,
            'all'
        );
    }

    public function enqueue_scripts() {
        $script_asset_path = plugin_dir_path( __DIR__ ) . 'assets/build/public.asset.php';
        $script_info = ( file_exists( $script_asset_path ) ? include $script_asset_path : [
            'dependencies' => ['wp-element'],
            'version'      => $this->version,
        ] );
        wp_register_script(
            $this->plugin_name,
            plugin_dir_url( __DIR__ ) . 'assets/build/public.js',
            $script_info['dependencies'],
            $script_info['version'],
            true
        );
    }

    private function get_form_html( $form ) {
        $shortcode = '[easy-subscribe id="' . esc_attr( $form['id'] ?? '' ) . '"]';
        // Return rendered form
        return do_shortcode( $shortcode );
    }

    public function script_data( $custom_data = [] ) {
        $script_data = [
            'ajaxurl'            => admin_url( 'admin-ajax.php' ),
            'nonce'              => wp_create_nonce( 'easy-subscribe-nonce' ),
            'subscribe_endpoint' => esc_url_raw( rest_url( 'easy-subscribe/v1/subscribe' ) ),
        ];
        $script_data = array_merge( $script_data, $custom_data );
        $script_data = apply_filters(
            'esub_script_data',
            $script_data,
            'public',
            []
        );
        return $script_data;
    }

    public function form_shortcode( $atts ) {
        $atts = shortcode_atts( [
            'id'   => null,
            'list' => '',
        ], $atts, 'easy-subscribe' );
        $id = ( $atts['id'] ? esc_attr( $atts['id'] ) : '' );
        $list = ( $atts['list'] ? esc_attr( $atts['list'] ) : '' );
        $form_id = apply_filters( 'esub_form_id', 'devnet_esub_form', $id );
        $wrapper_id = apply_filters( 'esub_wrapper_id', 'easy-subscribe', $id );
        $wrapper_id = str_replace( 'devnet_esub_form', 'easy-subscribe', $form_id );
        $wrapper_id = str_replace( '_', '-', $wrapper_id );
        $script_data = $this->script_data();
        $options = Helper::get_all_options();
        // Assign options to script data
        $script_data['options'] = $options;
        $form = $options['forms'][$form_id] ?? null;
        if ( !$form ) {
            return;
        }
        $is_enabled = $form['enable_form'] ?? false;
        if ( !$is_enabled ) {
            return;
        }
        $only_logged_in = $form['only_logged_in'] ?? false;
        if ( $only_logged_in && !is_user_logged_in() ) {
            return;
        }
        $script_data = apply_filters(
            'esub_script_data',
            $script_data,
            'public',
            [
                'list' => $list,
            ]
        );
        wp_enqueue_script( $this->plugin_name );
        wp_localize_script( $this->plugin_name, 'devnet_esub_script', $script_data );
        $content_before_form = $form['content_before_form'] ?? '';
        $form_html = '<div class="easy-subscribe ' . esc_attr( $wrapper_id ) . '" data-wrapper-id="' . esc_attr( $wrapper_id ) . '">';
        if ( $content_before_form ) {
            $form_html .= '<div class="esub-before-form">' . wp_kses_post( $content_before_form ) . '</div>';
        }
        $form_html .= '<div class="esub-form"></div>';
        $form_html .= '</div>';
        return $form_html;
    }

    public function handle_email_confirmation() {
        if ( !isset( $_GET['esub_confirm'] ) || !isset( $_GET['email'] ) ) {
            return;
        }
        $email = sanitize_email( $_GET['email'] );
        $token = sanitize_text_field( $_GET['esub_confirm'] );
        $is_confirmed = Helper::email_confirmation( $email, $token );
        if ( $is_confirmed ) {
            $options = Helper::get_all_options();
            $success_message = $options['messages']['success'] ?? __( 'Email confirmed!', 'easy-subscribe' );
            echo '<script>alert("' . esc_html( $success_message ) . '");</script>';
        }
    }

}
