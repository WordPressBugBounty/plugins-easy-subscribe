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
    }

    public function get_settings_sections()
    {

        $sections[] = [
            'id'    => 'devnet_esub_general',
            'title' => esc_html__('General', 'easy-subscribe')
        ];

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

        $sections[] = [
            'id'    => 'devnet_esub_messages',
            'title' => esc_html__('Messages', 'easy-subscribe')
        ];

        return apply_filters('esub_settings_sections', $sections);
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

        $settings_fields['devnet_esub_messages'] = Options::messages();


        return apply_filters('esub_settings_fields', $settings_fields);
    }

    public function settings_page()
    {
        echo '<div class="easy-subscribe-wrap devnet-plugin-settings-page" data-id="devnet_esub">';

        $this->settings_api->show_navigation();
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
            echo '<div class="esub-preview-button" data-id="' . esc_attr($id) . '">' . esc_html__('Preview Form', 'easy-subscribe') . '</div>';
            echo '<div class="esub-preview-wrap">';
            echo '<div class="esub-preview-bg-select">
            <div class="esub-preview-bg-color-box" style="background-color: white"></div>
            <div class="esub-preview-bg-color-box" style="background-color: darkgray"></div>
            <div class="esub-preview-bg-color-box" style="background-color: black"></div>
           </div>';
            echo '<span class="esub-preview-close">&times</span>';
            echo '<div id="preview--' . esc_attr($id) . '" class="esub-preview">';
            echo '<div class="esub-form"></div>';
            echo '</div>';
            echo '</div>';
        }
    }
}
