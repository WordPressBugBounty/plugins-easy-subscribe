<?php

namespace Devnet\EasySubscribe\Admin;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shows what a plan adds where its settings would be: the Popup section without PRO,
 * the WooCommerce tab without the Ecommerce plan (only when WooCommerce is active).
 * Free code: keep it outside premium-only blocks.
 */
class Upsell
{
    public function __construct()
    {
        add_filter('esub_settings_fields', [$this, 'popup_fields'], 25);
        add_filter('esub_settings_sections', [$this, 'woocommerce_section']);
        add_action('admin_enqueue_scripts', [$this, 'assets']);
    }

    /** The popup module registers its own fields when the plan includes it. */
    private static function popup_locked()
    {
        return !class_exists('Devnet\EasySubscribe\Modules\Popup\Integration', false);
    }

    /** No Ecommerce module and no legacy WooCommerce add-on, but a store to sell it to. */
    private static function woocommerce_locked()
    {
        return class_exists('WooCommerce')
            && !class_exists('Devnet\EasySubscribe\Modules\WooCommerce\Module', false)
            && !defined('DEVNET_ESUB_WOO_VERSION') && !function_exists('devnet_esub_woo_fs');
    }

    /** Plans and features are on the product page; opens in a new tab. */
    const PRODUCT_URL = 'https://devnet.hr/plugins/easy-subscribe/';

    public function popup_fields($sections)
    {
        if (!self::popup_locked()) {
            return $sections;
        }

        foreach ($sections as $id => &$fields) {
            if ($id === 'devnet_esub_form' || strpos($id, 'devnet_esub_form_') === 0) {
                $fields[] = ['name' => 'popup_upsell', 'type' => 'html', 'label' => '', 'group' => 'popup', 'class' => 'esub-upsell-row', 'callback' => [$this, 'popup']];
            }
        }
        unset($fields);

        return $sections;
    }

    public function woocommerce_section($sections)
    {
        if (self::woocommerce_locked()) {
            $sections[] = ['id' => 'devnet_esub_woocommerce', 'title' => esc_html__('WooCommerce', 'easy-subscribe'), 'render' => [$this, 'woocommerce']];
        }

        return $sections;
    }

    public function assets($hook)
    {
        $css = dirname(__DIR__) . '/modules/popup/assets/build/admin.css';

        // Design thumbnails in the popup notice.
        if ($hook === 'toplevel_page_easy-subscribe' && self::popup_locked() && is_file($css)) {
            wp_enqueue_style('esub-popup-admin', plugins_url('modules/popup/assets/build/admin.css', __DIR__), [], (string) filemtime($css));
        }
    }

    public function popup()
    {
        $presets = dirname(__DIR__) . '/modules/popup/presets.php';
        $designs = '';

        if (is_file($presets)) {
            require_once $presets;

            foreach (\Devnet\EasySubscribe\Modules\Popup\Presets::catalog() as $id => $preset) {
                $colors = $id === 'legacy' ? ['background' => '#ffffff', 'text' => '#222222', 'button' => '#6b38c7', 'border' => '#dddddd'] : $preset;
                $designs .= sprintf(
                    '<span class="esub-preset-card"><span class="esub-preset-mini" data-preset="%1$s" style="--mini-bg:%2$s;--mini-text:%3$s;--mini-button:%4$s;--mini-border:%5$s"><span class="esub-preset-mini__popup"><span class="esub-preset-mini__media"></span><span class="esub-preset-mini__body"><i></i><b></b><em></em></span></span></span><strong>%6$s</strong></span>',
                    esc_attr($id),
                    esc_attr($colors['background']),
                    esc_attr($colors['text']),
                    esc_attr($colors['button']),
                    esc_attr($colors['border']),
                    esc_html($preset['label'])
                );
            }
        }

        self::notice(
            'PRO',
            __('Show this form in a popup', 'easy-subscribe'),
            [
                __('Ready-made designs, with your own colors and image.', 'easy-subscribe'),
                __('Open it after a delay, or when a visitor clicks a button or link.', 'easy-subscribe'),
                __('Choose the pages it opens on and how often.', 'easy-subscribe'),
            ],
            __('Upgrade to PRO', 'easy-subscribe'),
            $designs ? '<div class="esub-preset-cards esub-upsell__designs" aria-hidden="true">' . $designs . '</div>' : ''
        );
    }

    public function woocommerce()
    {
        echo '<div class="esub-upsell-card"><h3 class="esub-upsell-card__title">' . esc_html__('WooCommerce', 'easy-subscribe') . '</h3>';
        self::notice(
            __('Ecommerce plan', 'easy-subscribe'),
            __('Grow your list from your store', 'easy-subscribe'),
            [
                __('A subscribe form on product pages, inline or in a popup.', 'easy-subscribe'),
                __('Add customers to a list automatically when they order.', 'easy-subscribe'),
                __('Import email addresses from past orders.', 'easy-subscribe'),
                __('A marketing consent checkbox on the classic checkout.', 'easy-subscribe'),
            ],
            __('Upgrade to the Ecommerce plan', 'easy-subscribe')
        );

        echo '</div>';
    }

    private static function notice($badge, $title, $items, $button, $extra = '')
    {
        echo '<div class="esub-upsell">';
        printf('<p class="esub-upsell__title">%s <span class="esub-pro-badge">%s</span></p>', esc_html($title), esc_html($badge));
        echo '<ul class="esub-upsell__list">';
        foreach ($items as $item) {
            echo '<li>' . esc_html($item) . '</li>';
        }
        echo '</ul>';
        echo $extra; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped as it's built.
        printf(
            '<p><a class="button button-primary esub-upsell__button" href="%s" target="_blank" rel="noopener">%s <span class="screen-reader-text">%s</span><span class="dashicons dashicons-external" aria-hidden="true"></span></a></p>',
            esc_url(self::PRODUCT_URL),
            esc_html($button),
            esc_html__('(opens in a new tab)', 'easy-subscribe')
        );
        echo '</div>';
    }
}
