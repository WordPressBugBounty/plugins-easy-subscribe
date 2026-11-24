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
        // No built-in general settings remain; the tab shows only when a filter adds fields.
        return apply_filters( 'esub_settings_general', [] );
    }

    public static function form() {
        // `group` places each field in a section of the layout (see form_layout()). Labels describe what
        // visitors see; option names, defaults and sanitizers never change.
        $form = [
            [
                'type'    => 'checkbox',
                'name'    => 'enable_form',
                'label'   => esc_html__( 'Enable Form', 'easy-subscribe' ),
                'default' => Defaults::form( 'enable_form' ),
                'group'   => 'header',
            ],
            [
                'type'    => 'wysiwyg',
                'name'    => 'content_before_form',
                'label'   => esc_html__( 'Text above the form', 'easy-subscribe' ),
                'desc'    => esc_html__( 'Optional. For example, a heading and a sentence about what subscribers get.', 'easy-subscribe' ),
                'default' => Defaults::form( 'content_before_form' ),
                'options' => [
                    'textarea_rows' => 5,
                ],
                'group'   => 'intro',
            ],
            [
                'type'              => 'text',
                'name'              => 'email_field_label',
                'label'             => esc_html__( 'Email', 'easy-subscribe' ),
                'default'           => Defaults::form( 'email_field_label' ),
                'sanitize_callback' => 'sanitize_text_field',
                'group'             => 'fields',
            ],
            [
                'type'              => 'text',
                'name'              => 'name_field_label',
                'label'             => esc_html__( 'Name', 'easy-subscribe' ),
                'default'           => Defaults::form( 'name_field_label' ),
                'sanitize_callback' => 'sanitize_text_field',
                'group'             => 'fields',
                'toggle'            => [
                    'name'    => 'name_field',
                    'label'   => esc_html__( 'Show the name field', 'easy-subscribe' ),
                    'default' => Defaults::form( 'name_field' ),
                ],
            ],
            [
                'type'              => 'text',
                'name'              => 'last_name_field_label__disabled',
                'label'             => esc_html__( 'Last name', 'easy-subscribe' ),
                'default'           => Defaults::form( 'last_name_field_label' ),
                'sanitize_callback' => 'sanitize_text_field',
                'group'             => 'fields',
                'toggle'            => [
                    'name'    => 'last_name_field__disabled',
                    'label'   => esc_html__( 'Show the last name field', 'easy-subscribe' ),
                    'default' => Defaults::form( 'last_name_field' ),
                ],
            ],
            [
                'type'              => 'textarea',
                'name'              => 'gdpr_text',
                'label'             => esc_html__( 'Consent checkbox', 'easy-subscribe' ),
                'default'           => Defaults::form( 'gdpr_text' ),
                'sanitize_callback' => 'sanitize_textarea_field',
                'group'             => 'fields',
                'toggle'            => [
                    'name'    => 'gdpr_field',
                    'label'   => esc_html__( 'Show the consent checkbox (GDPR)', 'easy-subscribe' ),
                    'default' => Defaults::form( 'gdpr_field' ),
                ],
            ],
            [
                'type'              => 'text',
                'name'              => 'button_label',
                'label'             => esc_html__( 'Button', 'easy-subscribe' ),
                'default'           => Defaults::form( 'button_label' ),
                'sanitize_callback' => 'sanitize_text_field',
                'group'             => 'fields',
            ],
            [
                'type'    => 'select',
                'name'    => 'auto_position_posts',
                'label'   => esc_html__( 'Blog posts', 'easy-subscribe' ),
                'options' => [
                    ''            => esc_html__( "Don’t add automatically", 'easy-subscribe' ),
                    '_disabled_1' => esc_html__( 'Before the post content - (PRO)', 'easy-subscribe' ),
                    '_disabled_2' => esc_html__( 'After the post content - (PRO)', 'easy-subscribe' ),
                ],
                'desc'    => esc_html__( 'Add this form to every blog post automatically.', 'easy-subscribe' ),
                'default' => Defaults::form( 'auto_position_posts' ),
                'private' => true,
                'group'   => 'placement',
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'only_logged_in__disabled',
                'label'   => esc_html__( 'Logged-in visitors only', 'easy-subscribe' ),
                'desc'    => esc_html__( 'Hide this form from visitors who are not logged in.', 'easy-subscribe' ),
                'default' => Defaults::form( 'only_logged_in' ),
                'group'   => 'placement',
            ],
            [
                'type'    => 'select',
                'name'    => 'layout',
                'label'   => esc_html__( 'Layout', 'easy-subscribe' ),
                'options' => [
                    'inline'      => esc_html__( 'Inline: all fields in one row', 'easy-subscribe' ),
                    'vertical'    => esc_html__( 'Stacked: one field per row', 'easy-subscribe' ),
                    'combination' => esc_html__( 'Combined: names in one row, email below', 'easy-subscribe' ),
                ],
                'default' => Defaults::form( 'layout' ),
                'group'   => 'style',
            ],
            [
                'type'    => 'select',
                'name'    => 'style',
                'label'   => esc_html__( 'Field style', 'easy-subscribe' ),
                'options' => [
                    ''         => esc_html__( 'Default', 'easy-subscribe' ),
                    'standard' => esc_html__( 'Underlined', 'easy-subscribe' ),
                    'outlined' => esc_html__( 'Outlined', 'easy-subscribe' ),
                    'filled'   => esc_html__( 'Filled', 'easy-subscribe' ),
                ],
                'default' => Defaults::form( 'style' ),
                'group'   => 'style',
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
                'group'   => 'style',
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'field_icons__disabled',
                'label'   => esc_html__( 'Icons in fields', 'easy-subscribe' ),
                'desc'    => esc_html__( 'Show a small icon at the start of the name and email fields.', 'easy-subscribe' ),
                'default' => Defaults::form( 'field_icons' ),
                'group'   => 'style',
            ],
            [
                'type'    => 'color',
                'name'    => 'primary_color',
                'label'   => esc_html__( 'Main color', 'easy-subscribe' ),
                'desc'    => esc_html__( 'Button and highlighted field borders.', 'easy-subscribe' ),
                'default' => Defaults::form( 'primary_color' ),
                'group'   => 'colors',
            ],
            [
                'type'    => 'color',
                'name'    => 'text_color__disabled',
                'label'   => esc_html__( 'Text color', 'easy-subscribe' ),
                'default' => Defaults::form( 'text_color' ),
                'group'   => 'colors',
            ],
            [
                'type'    => 'color',
                'name'    => 'field_bg_color__disabled',
                'label'   => esc_html__( 'Field background', 'easy-subscribe' ),
                'default' => Defaults::form( 'field_bg_color' ),
                'group'   => 'colors',
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'lighten_fields',
                'label'   => esc_html__( 'Lighter fields', 'easy-subscribe' ),
                'desc'    => esc_html__( 'Makes the fields easier to see on a dark background.', 'easy-subscribe' ),
                'default' => Defaults::form( 'lighten_fields' ),
                'group'   => 'colors',
            ],
            [
                'type'              => 'number',
                'name'              => 'form_width__disabled',
                'label'             => esc_html__( 'Form width', 'easy-subscribe' ),
                'desc'              => esc_html__( '0 = full width', 'easy-subscribe' ),
                'unit'              => 'px',
                'min'               => 0,
                'step'              => '1',
                'sanitize_callback' => 'absint',
                'default'           => Defaults::form( 'form_width' ),
                'group'             => 'sizing',
            ],
            [
                'type'    => 'select',
                'name'    => 'form_alignment__disabled',
                'label'   => esc_html__( 'Form alignment', 'easy-subscribe' ),
                'options' => [
                    'left'   => esc_html__( 'Left', 'easy-subscribe' ),
                    'center' => esc_html__( 'Center', 'easy-subscribe' ),
                    'right'  => esc_html__( 'Right', 'easy-subscribe' ),
                ],
                'default' => Defaults::form( 'form_alignment' ),
                'group'   => 'sizing',
            ],
            [
                'type'              => 'number',
                'name'              => 'button_width',
                'class'             => 'button_width button_width__disabled',
                'label'             => esc_html__( 'Button width', 'easy-subscribe' ),
                'desc'              => esc_html__( 'Used when the button sits on its own row. 0 = full width', 'easy-subscribe' ),
                'unit'              => 'px',
                'min'               => 0,
                'step'              => '1',
                'sanitize_callback' => 'absint',
                'default'           => Defaults::form( 'button_width' ),
                'group'             => 'sizing',
            ],
            [
                'type'    => 'select',
                'name'    => 'button_alignment',
                'class'   => 'button_alignment button_alignment__disabled',
                'label'   => esc_html__( 'Button alignment', 'easy-subscribe' ),
                'options' => [
                    'left'   => esc_html__( 'Left', 'easy-subscribe' ),
                    'center' => esc_html__( 'Center', 'easy-subscribe' ),
                    'right'  => esc_html__( 'Right', 'easy-subscribe' ),
                ],
                'default' => Defaults::form( 'button_alignment' ),
                'group'   => 'sizing',
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'custom_breakpoint',
                'label'   => esc_html__( 'Custom stacking width', 'easy-subscribe' ),
                'desc'    => esc_html__( 'When the form gets narrow, its fields move onto separate rows automatically. Turn this on to choose that width yourself.', 'easy-subscribe' ),
                'default' => Defaults::form( 'custom_breakpoint' ),
                'group'   => 'responsive',
            ],
            // Only the breakpoint that matches the layout and enabled fields is shown (admin.js).
            [
                'type'              => 'number',
                'name'              => 'breakpoint_1',
                'label'             => esc_html__( 'Stack fields below', 'easy-subscribe' ),
                'desc'              => esc_html__( 'Form width at which the fields move onto separate rows.', 'easy-subscribe' ),
                'unit'              => 'px',
                'min'               => 320,
                'step'              => '1',
                'sanitize_callback' => 'absint',
                'default'           => Defaults::form( 'breakpoint_1' ),
                'group'             => 'responsive',
            ],
            [
                'type'              => 'number',
                'name'              => 'breakpoint_2',
                'label'             => esc_html__( 'Stack fields below', 'easy-subscribe' ),
                'desc'              => esc_html__( 'Form width at which the fields move onto separate rows.', 'easy-subscribe' ),
                'unit'              => 'px',
                'min'               => 320,
                'step'              => '1',
                'sanitize_callback' => 'absint',
                'default'           => Defaults::form( 'breakpoint_2' ),
                'group'             => 'responsive',
            ],
            [
                'type'              => 'number',
                'name'              => 'breakpoint_3',
                'label'             => esc_html__( 'Stack fields below', 'easy-subscribe' ),
                'desc'              => esc_html__( 'Form width at which the fields move onto separate rows.', 'easy-subscribe' ),
                'unit'              => 'px',
                'min'               => 320,
                'step'              => '1',
                'sanitize_callback' => 'absint',
                'default'           => Defaults::form( 'breakpoint_3' ),
                'group'             => 'responsive',
            ],
            [
                'type'              => 'textarea',
                'name'              => 'custom_css__disabled',
                'label'             => esc_html__( 'Custom CSS', 'easy-subscribe' ),
                'desc'              => esc_html__( 'Deprecated: style forms with your theme CSS or Additional CSS using ::part() (see the plugin FAQ). Empty this field to remove it.', 'easy-subscribe' ),
                'sanitize_callback' => 'sanitize_textarea_field',
                'default'           => Defaults::form( 'custom_css' ),
                'group'             => 'css',
            ],
            [
                'type'    => 'select',
                'name'    => 'confirmation',
                'label'   => esc_html__( 'When someone subscribes', 'easy-subscribe' ),
                'options' => [
                    'message'     => esc_html__( 'Show a message', 'easy-subscribe' ),
                    '_disabled_1' => esc_html__( 'Go to another page - (PRO)', 'easy-subscribe' ),
                ],
                'default' => Defaults::form( 'confirmation' ),
                'group'   => 'subscription',
            ],
            [
                'type'              => 'url',
                'name'              => 'confirmation_url__disabled',
                'label'             => esc_html__( 'Page address (URL)', 'easy-subscribe' ),
                'default'           => Defaults::form( 'confirmation_url' ),
                'sanitize_callback' => 'sanitize_text_field',
                'group'             => 'subscription',
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'double_opt_in__disabled',
                'label'   => esc_html__( 'Double opt-in', 'easy-subscribe' ),
                'desc'    => esc_html__( 'Send a confirmation email. The subscription is confirmed when the link in it is clicked.', 'easy-subscribe' ),
                'default' => Defaults::form( 'double_opt_in' ),
                'group'   => 'subscription',
            ],
            [
                'type'              => 'text',
                'name'              => 'list__disabled',
                'label'             => esc_html__( 'Subscriber list', 'easy-subscribe' ),
                'desc'              => esc_html__( 'Subscribers from this form are saved to this list.', 'easy-subscribe' ),
                'default'           => Defaults::form( 'list' ),
                'sanitize_callback' => 'sanitize_text_field',
                'group'             => 'subscription',
            ],
        ];
        $form = self::text_defaults( $form );
        foreach ( self::messages() as $message ) {
            $message['group'] = 'messages';
            $form[] = $message;
        }
        return apply_filters( 'esub_settings_form', $form );
    }

    /**
     * Form settings layout: panels and their field groups, matched by each field's `group`.
     * Fields in the `header` group render in the form header instead of a panel. Groups are never
     * collapsed; a group whose fields don't apply is hidden by the admin script instead.
     */
    public static function form_layout() {
        $default_text = esc_html__( 'Leave a text empty to use the default, translated automatically.', 'easy-subscribe' );
        return [
            'form'         => [
                'title'  => esc_html__( 'Form', 'easy-subscribe' ),
                'groups' => [
                    'intro'     => [
                        'title' => esc_html__( 'Introduction', 'easy-subscribe' ),
                    ],
                    'fields'    => [
                        'title'       => esc_html__( 'Fields', 'easy-subscribe' ),
                        'description' => esc_html__( 'Turn fields on or off and change the text visitors see.', 'easy-subscribe' ) . ' ' . $default_text,
                    ],
                    'placement' => [
                        'title'       => esc_html__( 'Where it appears', 'easy-subscribe' ),
                        'description' => esc_html__( 'Paste the shortcode from the top of this page into any page, post or widget, or add the form automatically.', 'easy-subscribe' ),
                    ],
                ],
            ],
            'style'        => [
                'title'  => esc_html__( 'Style', 'easy-subscribe' ),
                'groups' => [
                    'style'      => [
                        'title' => esc_html__( 'Layout', 'easy-subscribe' ),
                    ],
                    'colors'     => [
                        'title' => esc_html__( 'Colors', 'easy-subscribe' ),
                    ],
                    'sizing'     => [
                        'title' => esc_html__( 'Width and alignment', 'easy-subscribe' ),
                    ],
                    'responsive' => [
                        'title' => esc_html__( 'Small screens', 'easy-subscribe' ),
                    ],
                    'css'        => [
                        'title' => esc_html__( 'Custom CSS', 'easy-subscribe' ),
                    ],
                ],
            ],
            'subscription' => [
                'title'  => esc_html__( 'Subscription', 'easy-subscribe' ),
                'groups' => [
                    'subscription' => [
                        'title' => esc_html__( 'After signup', 'easy-subscribe' ),
                    ],
                    'messages'     => [
                        'title'       => esc_html__( 'Messages', 'easy-subscribe' ),
                        'description' => esc_html__( 'Shown to visitors after they submit the form.', 'easy-subscribe' ) . ' ' . $default_text,
                    ],
                ],
            ],
            'popup'        => [
                'title'  => esc_html__( 'Popup', 'easy-subscribe' ),
                'groups' => [
                    'popup'           => [
                        'title' => esc_html__( 'Popup', 'easy-subscribe' ),
                    ],
                    'popup_design'    => [
                        'title' => esc_html__( 'Design', 'easy-subscribe' ),
                    ],
                    'popup_customize' => [
                        'title'       => esc_html__( 'Colors and size', 'easy-subscribe' ),
                        'description' => esc_html__( 'Leave a setting empty to use the design’s default.', 'easy-subscribe' ),
                    ],
                    'popup_intro'     => [
                        'title' => esc_html__( 'Popup text', 'easy-subscribe' ),
                    ],
                    'popup_when'      => [
                        'title' => esc_html__( 'When to open', 'easy-subscribe' ),
                    ],
                    'popup_where'     => [
                        'title' => esc_html__( 'Where to open', 'easy-subscribe' ),
                    ],
                    'popup_repeat'    => [
                        'title' => esc_html__( 'How often', 'easy-subscribe' ),
                    ],
                ],
            ],
        ];
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
                'name'              => 'pending_confirmation',
                'label'             => esc_html__( 'Confirmation email sent', 'easy-subscribe' ),
                'default'           => Defaults::messages( 'pending_confirmation' ),
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'type'              => 'text',
                'name'              => 'exists',
                'label'             => esc_html__( 'Already subscribed', 'easy-subscribe' ),
                'default'           => Defaults::messages( 'exists' ),
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'type'              => 'text',
                'name'              => 'error',
                'label'             => esc_html__( 'Something went wrong', 'easy-subscribe' ),
                'default'           => Defaults::messages( 'error' ),
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'type'              => 'text',
                'name'              => 'spam',
                'label'             => esc_html__( 'Blocked as spam', 'easy-subscribe' ),
                'default'           => Defaults::messages( 'spam' ),
                'sanitize_callback' => 'sanitize_text_field',
            ]
        ];
        return apply_filters( 'esub_settings_messages', self::text_defaults( $messages ) );
    }

    /**
     * Visitor-facing texts start empty and show the translated default as placeholder,
     * so saving the form never freezes the default in the admin's language. Their groups explain this once.
     */
    private static function text_defaults( $options ) {
        foreach ( $options as &$option ) {
            if ( in_array( $option['name'], Defaults::TEXT_FIELDS, true ) ) {
                $option['placeholder'] = $option['default'];
                $option['default'] = '';
            }
        }
        unset($option);
        return $options;
    }

}
