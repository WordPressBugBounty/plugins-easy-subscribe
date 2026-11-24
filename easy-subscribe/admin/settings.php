<?php

namespace Devnet\EasySubscribe\Admin;

use Devnet\EasySubscribe\Includes\Helper;


if (!defined('ABSPATH')) {
    exit;
}


class Settings
{

    private $settings_api;


    public function __construct()
    {
        $this->settings_api = new Settings_API('devnet_esub');

        $this->admin_init();
    }

    public function admin_init()
    {

        $page        = isset($_REQUEST['page']) ? sanitize_text_field($_REQUEST['page']) : '';
        $option_page = isset($_REQUEST['option_page']) ? sanitize_text_field($_REQUEST['option_page']) : '';

        $is_settings_page = $page === 'easy-subscribe';

        // When saving options.
        $is_option_page = !empty($option_page) && strpos($option_page, 'devnet_esub') === 0;

        if (!$is_settings_page && !$is_option_page) {
            return;
        }

        //set the settings
        $this->settings_api->set_sections($this->get_settings_sections());
        $this->settings_api->set_fields($this->get_settings_fields());

        //initialize settings
        $this->settings_api->admin_init();

        // Calling it from here to avoid unnecessary code execution.
        add_action('devnet_esub_form_top', [$this, 'panel_description']);
        add_action('devnet_esub_form_header', [$this, 'form_header']);
    }

    public function get_settings_sections()
    {

        $sections = [];

        if (Options::general()) {
            $sections[] = [
                'id'    => 'devnet_esub_general',
                'title' => esc_html__('General', 'easy-subscribe')
            ];
        }

        $form_counter = 1;
        $multiple_forms = count(DEVNET_ESUB_OPTIONS['forms']) > 1;

        if ($multiple_forms) {
            $sections[] = [
                'id'    => 'devnet_esub_forms',
                'title' => esc_html__('Forms', 'easy-subscribe'),
                'tabs'  => true,
            ];
        }

        foreach (DEVNET_ESUB_OPTIONS['forms'] as $form_id => $form_options) {

            $form_name_suffix = $form_counter > 1 ? ' ' . $form_counter : '';

            $form_section = [
                'id'    => $form_id,
                'title' => esc_html__('Form', 'easy-subscribe') . $form_name_suffix
            ];

            if ($multiple_forms) {
                $form_section['parent'] = 'devnet_esub_forms';
            }

            $sections[] = $form_section;

            $form_counter++;
        }

        $sections = apply_filters('esub_settings_sections', $sections);

        // Every form gets the Form / Style / Subscription / Popup layout, including forms added through
        // the filter (e.g. the WooCommerce Product Form); other sections keep the plain table.
        foreach ($sections as &$section) {
            if (empty($section['tabs']) && preg_match('/^devnet_esub_form(_|$)/', $section['id'])) {
                $section['layout'] = $section['layout'] ?? Options::form_layout();
            }
        }
        unset($section);

        return $sections;
    }

    /**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
    public function get_settings_fields()
    {
        $settings_fields = [];

        $settings_fields['devnet_esub_general'] = Options::general();

        foreach (DEVNET_ESUB_OPTIONS['forms'] as $form_id => $form_options) {
            $settings_fields[$form_id] = Options::form();
        }

        $settings_fields = apply_filters('esub_settings_fields', $settings_fields);

        // Custom CSS is deprecated in favour of theme CSS (::part); only forms with saved CSS keep the editor.
        foreach ($settings_fields as $section_id => &$fields) {
            $saved = get_option($section_id);

            if (strpos($section_id, 'devnet_esub_form') === 0 && trim((string) ($saved['custom_css'] ?? '')) === '') {
                $fields = array_values(array_filter($fields, function ($field) {
                    return !in_array($field['name'], ['custom_css', 'custom_css__disabled'], true);
                }));
            }
        }
        unset($fields);

        return $settings_fields;
    }

    public function settings_page()
    {
        echo '<div class="easy-subscribe-wrap devnet-plugin-settings-page" data-id="devnet_esub">';

        printf(
            '<h1 class="esub-page-title"><span class="esub-page-mark dashicons dashicons-email-alt" aria-hidden="true"></span>%1$s <span class="esub-page-version">%2$s</span></h1>',
            esc_html__('Easy Subscribe', 'easy-subscribe'),
            esc_html(DEVNET_ESUB_VERSION)
        );

        $this->settings_api->show_navigation();
        echo '<hr class="wp-header-end">';
        $this->panel_description('all');
        $this->settings_api->show_forms();

        echo '</div>';
    }

    /**
     * Get all the pages
     *
     * @return array page names with key value pairs
     */
    public function get_pages()
    {
        $pages         = get_pages();
        $pages_options = [];
        if ($pages) {
            foreach ($pages as $page) {
                $pages_options[$page->ID] = $page->post_title;
            }
        }

        return $pages_options;
    }

    /**
     * Output description text above panel title.
     *
     * @since   1.1.0
     */
    public function panel_description($form)
    {

        $id = isset($form['id']) ? $form['id'] : '';

        if ($form === 'all') {

            if (Helper::missing_esub_tables()) {

                echo '<div class="devnet-plugin-panel-description devnet-plugin-alert">';
                echo '<p>';

                echo wp_kses_post(__('<strong>Attention:</strong> It appears that some required tables are missing in the database.<br>
                Please click the button below to repair the database tables.', 'easy-subscribe'));

                echo '<a href="#" class="button button-primary easy-subscribe-button easy-subscribe-repair-tables">' . esc_html__('Repair', 'easy-subscribe') . '</a>';

                echo '</p>';
                echo '</div>';
            }
        }

        if (strpos($id, "devnet_esub_form") === 0) {
            echo '<div class="esub-preview-wrap" id="esub-preview-wrap--' . esc_attr($id) . '">';
            echo '<div class="esub-preview-bg-select">';
            foreach (['white' => __('White background', 'easy-subscribe'), 'darkgray' => __('Gray background', 'easy-subscribe'), 'black' => __('Black background', 'easy-subscribe')] as $color => $label) {
                echo '<button type="button" class="esub-preview-bg-color-box" style="background-color: ' . esc_attr($color) . '" aria-label="' . esc_attr($label) . '"></button>';
            }
            echo '</div>';
            echo '<button type="button" class="esub-preview-close" aria-label="' . esc_attr__('Close preview', 'easy-subscribe') . '">&times;</button>';
            echo '<div id="preview--' . esc_attr($id) . '" class="esub-preview">';
            echo '<div class="esub-form"></div>';
            echo '</div>';
            echo '</div>';
        }
    }

    /**
     * Form header utilities: disabled status, preview and shortcode.
     */
    public function form_header($form)
    {
        $id           = $form['id'];
        $shortcode_id = Helper::extract_form_id($id);
        $shortcode    = $shortcode_id === null ? '[easy-subscribe]' : '[easy-subscribe id="' . $shortcode_id . '"]';

        // CSS shows the one matching the Enable switch.
        echo '<span class="esub-form-status"><span class="esub-form-status__on">' . esc_html__('Enabled', 'easy-subscribe') . '</span><span class="esub-form-status__off">' . esc_html__('Disabled', 'easy-subscribe') . '</span></span>';
        echo '<button type="button" class="button esub-preview-button" data-id="' . esc_attr($id) . '" aria-controls="esub-preview-wrap--' . esc_attr($id) . '" aria-expanded="false">' . esc_html__('Preview', 'easy-subscribe') . '</button>';

        echo '<div class="esub-shortcode-field">';
        echo '<label for="esub-shortcode--' . esc_attr($id) . '">' . esc_html__('Shortcode', 'easy-subscribe') . '</label>';
        echo '<input type="text" id="esub-shortcode--' . esc_attr($id) . '" class="esub-shortcode" value="' . esc_attr($shortcode) . '" readonly>';
        echo '<button type="button" class="button esub-copy-shortcode" aria-controls="esub-shortcode--' . esc_attr($id) . '">' . esc_html__('Copy', 'easy-subscribe') . '</button>';
        echo '<span class="esub-copy-status" role="status"></span>';
        echo '</div>';
    }
}
