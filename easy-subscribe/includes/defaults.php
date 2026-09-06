<?php

namespace Devnet\EasySubscribe\Includes;

if (!defined('ABSPATH')) {
    exit;
}

class Defaults
{

    public static function general($option_name = '')
    {
        $options = [
            'multilingual'       => 0,
            'delete_old_data'    => '',
            'delete_plugin_data' => 0
        ];

        $output = $options;

        if ($option_name) {
            $output = $options[$option_name] ?? null;
        }

        return $output;
    }

    public static function form($option_name = '')
    {
        $options = [
            'enable_form'           => 0,
            'only_logged_in'        => 0,
            'list'                  => 'subscribers',
            'honeypot'              => 1,
            'confirmation'          => 'message',
            'confirmation_url'      => '',
            'auto_position_posts'   => '',
            'double_opt_in'         => 0,
            'name_field'            => 1,
            'name_field_label'      => esc_html__('Name', 'easy-subscribe'),
            'last_name_field'       => 0,
            'last_name_field_label' => esc_html__('Last name', 'easy-subscribe'),
            'gdpr_field'            => 0,
            'gdpr_text'             => sprintf(
                /* translators: 1: site name */
                esc_html__('I hereby give consent for my personal data (first name, last name, email address) to be used for marketing purposes (receiving newsletters and catalogs) by the online store %1$s. The store will use them solely for these purposes and will not disclose them to third parties, using the data until the withdrawal of consent. I am aware of the right to request access to personal data, correction, deletion of data, processing limitation, the right to object to processing, the right to data portability, the right to lodge a complaint with the competent authority (Personal Data Protection Agency), and to ask the data protection officer if I believe there has been any violation in the processing of personal data.', 'easy-subscribe'),
                get_bloginfo('name')
            ),
            'email_field_label'     => esc_html__('Email', 'easy-subscribe'),
            'button_label'          => esc_html__('Submit', 'easy-subscribe'),
            'layout'                => 'inline',
            'style'                 => '',
            'field_size'            => 'medium',
            'primary_color'         => '#673ab7',
            'lighten_fields'        => 0,
            'field_bg_color'        => '',
            'text_color'            => '',
            'form_width'            => 0,
            'form_alignment'        => 'center',
            'button_width'          => 0,
            'button_alignment'      => 'center',
            'custom_breakpoint'     => 0,
            'breakpoint_1'          => '795',
            'breakpoint_2'          => '565',
            'breakpoint_3'          => '415',
            'content_before_form'   => '',
            'custom_css'            => ''
        ];

        $output = $options;

        if ($option_name) {
            $output = $options[$option_name] ?? null;
        }

        return $output;
    }

    public static function messages($option_name = '')
    {
        $options = [
            'success'              => esc_html__('Successfully subscribed!', 'easy-subscribe'),
            'exists'               => esc_html__('This email is already subscribed.', 'easy-subscribe'),
            'error'                => esc_html__('Oops! It seems there was an error while submitting. Please refresh the page and try again. If the issue persists, feel free to contact us for assistance. Thank you!', 'easy-subscribe'),
            'spam'                 => esc_html__('Error - possible spam detected.', 'easy-subscribe'),
            'pending_confirmation' => esc_html__('Please check your email to confirm your subscription.', 'easy-subscribe')
        ];

        $output = $options;

        if ($option_name) {
            $output = $options[$option_name] ?? null;
        }

        return $output;
    }
}
