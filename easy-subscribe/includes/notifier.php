<?php

namespace Devnet\EasySubscribe\Includes;


if (!defined('ABSPATH')) {
    exit;
}


class Notifier
{

    public function __construct() {}

    public static function send_confirmation_email($email, $token, $redirect_url = '')
    {
        if (!$token || empty($email)) {
            return;
        }

        if (!$redirect_url) {
            $redirect_url = get_site_url();
        }

        // Get site domain dynamically (handles subdomains)
        $site_url     = wp_parse_url(get_site_url(), PHP_URL_HOST);
        $domain_parts = explode('.', $site_url);

        // Remove subdomain if it's there
        if (count($domain_parts) > 2) {
            array_shift($domain_parts); // Removes first part (subdomain)
        }

        $domain     = implode('.', $domain_parts);
        $from_email = 'no-reply@' . $domain; // Ensures no-reply email from main domain

        // Generate confirmation URL (redirects back to the subscribing page)
        $verification_link = add_query_arg([
            'esub_confirm' => $token,
            'email'      => urlencode($email),
        ], esc_url($redirect_url));

        // Email subject
        $subject = __('Confirm Your Subscription', 'easy-subscribe');

        // Email message with HTML content
        $message = sprintf(
            /* translators: 1: verification url */
            __('<p>Thank you for subscribing! Please confirm your subscription by clicking the link below:</p>
            <p><a href="%s" style="background-color:#0073aa;color:#ffffff;padding:10px 15px;text-decoration:none;display:inline-block;font-weight:bold;border-radius:5px;">Confirm Subscription</a></p>
            <p>If you did not subscribe, you can ignore this email.</p>', 'easy-subscribe'),
            esc_url($verification_link)
        );

        // Set email headers
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: Easy Subscribe <' . sanitize_email($from_email) . '>',
            'Reply-To: ' . sanitize_email($from_email) // Ensures replies don’t go to an actual inbox
        ];

        // Send email
        wp_mail($email, $subject, $message, $headers);
    }
}
