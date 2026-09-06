<?php

namespace Devnet\EasySubscribe\Admin;

use Devnet\EasySubscribe\Includes\Defaults;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Options for settings panel
 *
 */
class Options {
    public static function general() {
        $general = [[
            'type'    => 'checkbox',
            'name'    => 'multilingual',
            'label'   => esc_html__( 'Multilingual', 'easy-subscribe' ),
            'desc'    => esc_html__( 'Use your own translated strings.', 'easy-subscribe' ),
            'default' => Defaults::general( 'multilingual' ),
        ], [
            'type'    => 'select',
            'name'    => 'delete_old_data__disabled',
            'label'   => esc_html__( 'Delete entries older than', 'easy-subscribe' ),
            'options' => [
                '' => esc_html__( '-- Select --', 'easy-subscribe' ),
            ],
            'default' => Defaults::general( 'delete_old_data' ),
        ], [
            'type'    => 'checkbox',
            'name'    => 'delete_plugin_data',
            'label'   => esc_html__( 'Delete all plugin data on uninstall', 'easy-subscribe' ),
            'desc'    => esc_html__( 'Enabling this option will ensure that all data associated with the plugin, including settings and all subscribers, will be completely removed from the database upon uninstallation of the plugin.', 'easy-subscribe' ),
            'default' => Defaults::general( 'delete_plugin_data' ),
        ]];
        return apply_filters( 'esub_settings_general', $general );
    }

    public static function form() {
        $form = [
            [
                'type'    => 'checkbox',
                'name'    => 'enable_form',
                'label'   => esc_html__( 'Enable', 'easy-subscribe' ),
                'default' => Defaults::form( 'enable_form' ),
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'double_opt_in__disabled',
                'label'   => esc_html__( 'Double Opt-In', 'easy-subscribe' ),
                'default' => Defaults::form( 'double_opt_in' ),
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'only_logged_in__disabled',
                'label'   => esc_html__( 'Show only for logged in users', 'easy-subscribe' ),
                'default' => Defaults::form( 'only_logged_in' ),
            ],
            [
                'type'              => 'text',
                'name'              => 'list__disabled',
                'label'             => esc_html__( 'List name', 'easy-subscribe' ),
                'default'           => Defaults::form( 'list' ),
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'type'    => 'select',
                'name'    => 'confirmation',
                'label'   => esc_html__( 'Confirmation', 'easy-subscribe' ),
                'options' => [
                    'message'     => esc_html__( 'Message', 'easy-subscribe' ),
                    '_disabled_1' => esc_html__( 'Go to URL (redirect) - (PRO)', 'easy-subscribe' ),
                ],
                'default' => Defaults::form( 'confirmation' ),
            ],
            [
                'type'              => 'url',
                'name'              => 'confirmation_url__disabled',
                'label'             => esc_html__( 'Confirmation Redirect URL', 'easy-subscribe' ),
                'default'           => Defaults::form( 'confirmation_url' ),
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'type'    => 'info',
                'name'    => 'info-form-auto-placement',
                'label'   => esc_html__( 'Automatic Form Placement', 'easy-subscribe' ),
                'class'   => 'info',
                'private' => true,
            ],
            [
                'type'    => 'select',
                'name'    => 'auto_position_posts',
                'label'   => esc_html__( 'Form position in blog posts', 'easy-subscribe' ),
                'options' => [
                    ''            => esc_html__( "Don’t insert automatically", 'easy-subscribe' ),
                    '_disabled_1' => esc_html__( 'Before content - (PRO)', 'easy-subscribe' ),
                    '_disabled_2' => esc_html__( 'After content - (PRO)', 'easy-subscribe' ),
                ],
                'desc'    => esc_html__( 'Select where to automatically insert this form within blog post content.', 'easy-subscribe' ),
                'default' => Defaults::form( 'auto_position_posts' ),
                'private' => true,
            ],
            [
                'type'  => 'info',
                'name'  => 'info-form-fields',
                'label' => esc_html__( 'Fields', 'easy-subscribe' ),
                'class' => 'info',
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'name_field',
                'label'   => esc_html__( 'Name field', 'easy-subscribe' ),
                'default' => Defaults::form( 'name_field' ),
            ],
            [
                'type'              => 'text',
                'name'              => 'name_field_label',
                'label'             => esc_html__( 'Name label', 'easy-subscribe' ),
                'default'           => Defaults::form( 'name_field_label' ),
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'last_name_field__disabled',
                'label'   => esc_html__( 'Last name field', 'easy-subscribe' ),
                'default' => Defaults::form( 'last_name_field' ),
            ],
            [
                'type'              => 'text',
                'name'              => 'last_name_field_label__disabled',
                'label'             => esc_html__( 'Last name label', 'easy-subscribe' ),
                'default'           => Defaults::form( 'last_name_field_label' ),
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'gdpr_field',
                'label'   => esc_html__( 'GDPR confirmation', 'easy-subscribe' ),
                'default' => Defaults::form( 'gdpr_field' ),
            ],
            [
                'type'              => 'textarea',
                'name'              => 'gdpr_text',
                'label'             => esc_html__( 'GDPR text', 'easy-subscribe' ),
                'default'           => Defaults::form( 'gdpr_text' ),
                'sanitize_callback' => 'sanitize_textarea_field',
            ],
            [
                'type'              => 'text',
                'name'              => 'email_field_label',
                'label'             => esc_html__( 'Email label', 'easy-subscribe' ),
                'default'           => Defaults::form( 'email_field_label' ),
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'type'              => 'text',
                'name'              => 'button_label',
                'label'             => esc_html__( 'Button label', 'easy-subscribe' ),
                'default'           => Defaults::form( 'button_label' ),
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'honeypot',
                'label'   => esc_html__( 'Honeypot - Anti-Spam', 'easy-subscribe' ),
                'desc'    => esc_html__( "Silently blocks bots and reduces spam submissions with an invisible field that real users won't see. Recommended to keep this enabled for better protection.", 'easy-subscribe' ),
                'default' => Defaults::form( 'honeypot' ),
            ],
            [
                'type'  => 'info',
                'name'  => 'info-form-design',
                'label' => esc_html__( 'Design', 'easy-subscribe' ),
                'class' => 'info',
            ],
            [
                'type'    => 'select',
                'name'    => 'layout',
                'label'   => esc_html__( 'Layout', 'easy-subscribe' ),
                'options' => [
                    'inline'      => esc_html__( 'Inline', 'easy-subscribe' ),
                    'vertical'    => esc_html__( 'Vertical', 'easy-subscribe' ),
                    'combination' => esc_html__( 'Combination', 'easy-subscribe' ),
                ],
                'default' => Defaults::form( 'layout' ),
            ],
            [
                'type'    => 'select',
                'name'    => 'style',
                'label'   => esc_html__( 'Style', 'easy-subscribe' ),
                'options' => [
                    ''         => esc_html__( 'Default', 'easy-subscribe' ),
                    'standard' => esc_html__( 'Standard', 'easy-subscribe' ),
                    'outlined' => esc_html__( 'Outlined', 'easy-subscribe' ),
                    'filled'   => esc_html__( 'Filled', 'easy-subscribe' ),
                ],
                'default' => Defaults::form( 'style' ),
            ],
            [
                'type'    => 'select',
                'name'    => 'field_size',
                'label'   => esc_html__( 'Field size', 'easy-subscribe' ),
                'options' => [
                    'small'  => esc_html__( 'Small', 'easy-subscribe' ),
                    'medium' => esc_html__( 'Medium', 'easy-subscribe' ),
                    'large'  => esc_html__( 'Large', 'easy-subscribe' ),
                ],
                'default' => Defaults::form( 'field_size' ),
            ],
            [
                'type'              => 'number',
                'name'              => 'form_width__disabled',
                'label'             => esc_html__( 'Form width', 'easy-subscribe' ),
                'desc'              => esc_html__( 'Set 0 for full width', 'easy-subscribe' ),
                'unit'              => 'px',
                'min'               => 0,
                'step'              => '1',
                'sanitize_callback' => 'absint',
                'default'           => Defaults::form( 'form_width' ),
            ],
            [
                'type'    => 'select',
                'name'    => 'form_alignment__disabled',
                'label'   => esc_html__( 'Form alignment', 'easy-subscribe' ),
                'options' => [
                    'left'   => esc_html__( 'left', 'easy-subscribe' ),
                    'right'  => esc_html__( 'right', 'easy-subscribe' ),
                    'center' => esc_html__( 'center', 'easy-subscribe' ),
                ],
                'default' => Defaults::form( 'form_alignment' ),
            ],
            [
                'type'    => 'color',
                'name'    => 'primary_color',
                'label'   => esc_html__( 'Primary color', 'easy-subscribe' ),
                'default' => Defaults::form( 'primary_color' ),
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'lighten_fields',
                'label'   => esc_html__( 'Lighten Form Fields', 'easy-subscribe' ),
                'desc'    => esc_html__( 'Enable this setting to improve form visibility on darker backgrounds by making the fields lighter.', 'easy-subscribe' ),
                'default' => Defaults::form( 'lighten_fields' ),
            ],
            [
                'type'    => 'color',
                'name'    => 'field_bg_color__disabled',
                'label'   => esc_html__( 'Field background color', 'easy-subscribe' ),
                'default' => Defaults::form( 'field_bg_color' ),
            ],
            [
                'type'    => 'color',
                'name'    => 'text_color__disabled',
                'label'   => esc_html__( 'Text color', 'easy-subscribe' ),
                'default' => Defaults::form( 'text_color' ),
            ],
            [
                'type'              => 'number',
                'name'              => 'button_width',
                'label'             => esc_html__( 'Button width', 'easy-subscribe' ),
                'desc'              => sprintf( '%s <br> %s', esc_html__( 'Adjusts the width of the button when on a separate line, eg. vertical layout or after the breakpoint.', 'easy-subscribe' ), esc_html__( 'Set 0 for full width', 'easy-subscribe' ) ),
                'unit'              => 'px',
                'min'               => 0,
                'step'              => '1',
                'sanitize_callback' => 'absint',
                'default'           => Defaults::form( 'button_width' ),
            ],
            [
                'type'    => 'select',
                'name'    => 'button_alignment',
                'label'   => esc_html__( 'Button alignment', 'easy-subscribe' ),
                'options' => [
                    'left'   => esc_html__( 'left', 'easy-subscribe' ),
                    'right'  => esc_html__( 'right', 'easy-subscribe' ),
                    'center' => esc_html__( 'center', 'easy-subscribe' ),
                ],
                'desc'    => esc_html__( 'Sets the alignment of the button when on a separate line, eg. vertical layout or after the breakpoint.', 'easy-subscribe' ),
                'default' => Defaults::form( 'button_alignment' ),
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'custom_breakpoint',
                'label'   => esc_html__( 'Custom breakpoint', 'easy-subscribe' ),
                'desc'    => sprintf(
                    '%s <br><br> %s <br> %s',
                    esc_html__( 'Customize breakpoints for the "Inline" layout form.', 'easy-subscribe' ),
                    esc_html__( "Form elements rearrange based on the form's size. When the form's space is reduced, fields or buttons may shift to a new line to ensure the form remains easy to use and visually appealing.", 'easy-subscribe' ),
                    esc_html__( "Note: These are not traditional media queries but container queries.", 'easy-subscribe' )
                ),
                'default' => Defaults::form( 'custom_breakpoint' ),
            ],
            [
                'type'              => 'number',
                'name'              => 'breakpoint_1',
                'label'             => esc_html__( 'Breakpoint 1', 'easy-subscribe' ),
                'desc'              => esc_html__( "This is active only when both the 'Name' and 'Last Name' fields are enabled.", 'easy-subscribe' ),
                'unit'              => 'px',
                'min'               => 320,
                'step'              => '1',
                'sanitize_callback' => 'absint',
                'default'           => Defaults::form( 'breakpoint_1' ),
            ],
            [
                'type'              => 'number',
                'name'              => 'breakpoint_2',
                'label'             => esc_html__( 'Breakpoint 2', 'easy-subscribe' ),
                'desc'              => esc_html__( "This is active when either the 'Name' or 'Last Name' field is enabled.", 'easy-subscribe' ),
                'unit'              => 'px',
                'min'               => 320,
                'step'              => '1',
                'sanitize_callback' => 'absint',
                'default'           => Defaults::form( 'breakpoint_2' ),
            ],
            [
                'type'              => 'number',
                'name'              => 'breakpoint_3',
                'label'             => esc_html__( 'Breakpoint 3', 'easy-subscribe' ),
                'desc'              => esc_html__( "This is active only when the 'Email' field is enabled.", 'easy-subscribe' ),
                'unit'              => 'px',
                'min'               => 320,
                'step'              => '1',
                'sanitize_callback' => 'absint',
                'default'           => Defaults::form( 'breakpoint_3' ),
            ],
            [
                'type'  => 'info',
                'name'  => 'info-form-text',
                'label' => esc_html__( 'Text', 'easy-subscribe' ),
                'class' => 'info',
            ],
            [
                'type'    => 'wysiwyg',
                'name'    => 'content_before_form',
                'label'   => esc_html__( 'Before form', 'easy-subscribe' ),
                'default' => Defaults::form( 'content_before_form' ),
            ],
            [
                'type'  => 'info',
                'name'  => 'info-custom-css',
                'label' => esc_html__( 'Custom CSS', 'easy-subscribe' ),
                'class' => 'info custom-css',
            ],
            [
                'type'              => 'textarea',
                'name'              => 'custom_css__disabled',
                'label'             => '',
                'sanitize_callback' => 'sanitize_textarea_field',
                'default'           => Defaults::form( 'custom_css' ),
            ],
            [
                'type'  => 'info',
                'name'  => 'info-form-shortcode',
                'label' => esc_html__( 'Shortcode', 'easy-subscribe' ),
                'class' => 'info shortcode-info',
                'desc'  => esc_html( 'Copy the shortcode and integrate it into your content or template using your preferred editor.', 'easy-subscribe' ),
            ]
        ];
        return apply_filters( 'esub_settings_form', $form );
    }

    public static function messages() {
        $messages = [
            [
                'type'              => 'text',
                'name'              => 'success',
                'label'             => esc_html__( 'Subscribed', 'easy-subscribe' ),
                'default'           => Defaults::messages( 'success' ),
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'type'              => 'text',
                'name'              => 'exists',
                'label'             => esc_html__( 'Email already subscribed', 'easy-subscribe' ),
                'default'           => Defaults::messages( 'exists' ),
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'type'              => 'text',
                'name'              => 'error',
                'label'             => esc_html__( 'Error while submitting', 'easy-subscribe' ),
                'default'           => Defaults::messages( 'error' ),
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'type'              => 'text',
                'name'              => 'spam',
                'label'             => esc_html__( 'Spam', 'easy-subscribe' ),
                'default'           => Defaults::messages( 'spam' ),
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'type'              => 'text',
                'name'              => 'pending_confirmation',
                'label'             => esc_html__( 'Pending confirmation', 'easy-subscribe' ),
                'default'           => Defaults::messages( 'pending_confirmation' ),
                'sanitize_callback' => 'sanitize_text_field',
            ]
        ];
        return apply_filters( 'esub_settings_messages', $messages );
    }

}
