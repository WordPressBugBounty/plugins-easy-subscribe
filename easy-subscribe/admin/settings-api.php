<?php

namespace Devnet\EasySubscribe\Admin;


if (! defined('ABSPATH')) {
    exit;
}

/**
 * Settings API wrapper class.
 * 
 * @updated    2026-10-08
 */

class Settings_API
{

    /**
     * settings sections array
     *
     * @var array
     */
    protected $settings_sections = [];

    /**
     * Settings fields array
     *
     * @var array
     */
    protected $settings_fields = [];

    private $plugin_slug;

    public function __construct($plugin_slug = '')
    {
        $this->plugin_slug = $plugin_slug;
    }

    /**
     * Set settings sections
     *
     * @param array   $sections setting sections array
     */
    public function set_sections($sections)
    {
        $this->settings_sections = $sections;

        return $this;
    }

    /**
     * Add a single section
     *
     * @param array   $section
     */
    public function add_section($section)
    {
        $this->settings_sections[] = $section;

        return $this;
    }

    /**
     * Set settings fields
     *
     * @param array   $fields settings fields array
     */
    public function set_fields($fields)
    {
        $this->settings_fields = $fields;

        return $this;
    }

    public function add_field($section, $field)
    {
        $defaults = [
            'name'  => '',
            'label' => '',
            'desc'  => '',
            'type'  => 'text'
        ];

        $arg = wp_parse_args($field, $defaults);
        $this->settings_fields[$section][] = $arg;

        return $this;
    }

    /**
     * Initialize and registers the settings sections and fields to WordPress
     *
     * Usually this should be called at `admin_init` hook.
     *
     * This function gets the initiated settings sections and fields. Then
     * registers them to WordPress and ready for use.
     */
    public function admin_init()
    {
        //register settings sections
        foreach ($this->settings_sections as $section) {

            // If set tabs, skip adding options and settings sections. We'll just use it as a wrapper.
            // A `render` section (e.g. an upgrade notice) has no options either.
            if (filter_var($section['tabs'] ?? false, FILTER_VALIDATE_BOOL) || !empty($section['render'])) {
                continue;
            }

            if (false == get_option($section['id'])) {
                // Array default: add_option() runs this through sanitize_option_{$id}, and every
                // module's sanitize callback treats a non-array value as invalid settings.
                add_option($section['id'], []);
            }

            if (isset($section['desc']) && !empty($section['desc'])) {
                $callback = function () use ($section) {
                    echo '<div class="inside">' . wp_kses_post($section['desc']) . '</div>';
                };
            } elseif (isset($section['callback'])) {
                $callback = $section['callback'];
            } else {
                $callback = null;
            }

            add_settings_section($section['id'], $section['title'], $callback, $section['id']);
        }

        // Sections with a `layout` register each field into a per-group WP section (rendered by render_layout()).
        // Fields with an unknown group stay in the section itself, so they are still rendered and saved.
        $layout_groups = [];
        foreach ($this->settings_sections as $section) {
            foreach ($section['layout'] ?? [] as $panel) {
                $layout_groups[$section['id']] = ($layout_groups[$section['id']] ?? ['header' => []]) + $panel['groups'];
            }
        }

        //register settings fields
        foreach ($this->settings_fields as $section => $field) {
            foreach ($field as $option) {
                $name     = $option['name'] ?? '';
                $type     = $option['type'] ?? 'text';
                $label    = $option['label'] ?? '';
                $callback = $option['callback'] ?? [$this, 'callback_' . $type];

                $group    = $option['group'] ?? '';

                // Point the row label at the control the callback actually prints.
                $label_for = "{$section}[{$name}]";
                if ($type === 'checkbox' || $type === 'date') {
                    $label_for = "dvnt-{$section}[{$name}]";
                } elseif ($type === 'wysiwyg') {
                    $label_for = "{$section}-{$name}";
                }

                $args = [
                    'id'                => $name,
                    'class'             => $option['class'] ?? $name,
                    'label_for'         => $label_for,
                    'desc'              => $option['desc'] ?? '',
                    'name'              => $label,
                    'section'           => $section,
                    'size'              => $option['size'] ?? null,
                    'options'           => $option['options'] ?? '',
                    'optgroup'          => $option['optgroup'] ?? [],
                    'std'               => $option['default'] ?? '',
                    'sanitize_callback' => $option['sanitize_callback'] ?? '',
                    'type'              => $type,
                    'placeholder'       => $option['placeholder'] ?? '',
                    'min'               => $option['min'] ?? '',
                    'max'               => $option['max'] ?? '',
                    'step'              => $option['step'] ?? '',
                    'multiple'          => $option['multiple'] ?? '',
                    'unit'              => $option['unit'] ?? '',
                    'rows'              => $option['rows'] ?? '',
                    'cols'              => $option['cols'] ?? '',
                    'disabled'          => $option['disabled'] ?? '',
                    'pro_plan'          => $option['pro_plan'] ?? '',
                    'fields'            => $option['fields'] ?? [],
                    'repeatable'        => $option['repeatable'] ?? false,
                    'tab_id'            => $option['tab_id'] ?? '',
                    'tabs'              => $option['tabs'] ?? [],
                    'prefix'             => $option['prefix'] ?? false,
                    'toggle'            => $option['toggle'] ?? [],
                ];


                if (!empty($args['tab_id'])) {
                    $args['class'] .= ' ' . $section . '_' . $args['tab_id'];
                }

                if ($args['pro_plan']) {
                    $args['class'] .= ' ' . $args['pro_plan'];
                }

                $wp_section = isset($layout_groups[$section][$group]) ? "{$section}__{$group}" : $section;

                add_settings_field("{$section}[{$name}]", $label, $callback, $section, $wp_section, $args);
            }
        }

        // creates our settings in the options table
        foreach ($this->settings_sections as $section) {

            // If set tabs, skip registering the section..
            if (filter_var($section['tabs'] ?? false, FILTER_VALIDATE_BOOL) || !empty($section['render'])) {
                continue;
            }

            register_setting($section['id'], $section['id'], [$this, 'sanitize_options']);
        }
    }

    public function kses_args($additional_args = [])
    {
        $args = [
            'br'     => [],
            'b'      => [],
            'em'     => [],
            'strong' => [],
            'p'      => [],
            'input' => [
                'type'     => [],
                'readonly' => [],
                'size'     => [],
                'value'    => []
            ],
            'a'     => [
                'href'   => [],
                'target' => [],
                'rel'    => []
            ],
            'div'   => [
                'class'  => []
            ],
        ];

        return $args + $additional_args;
    }

    /**
     * Get field description for display
     *
     * @param array   $args settings field args
     */
    public function get_field_description($args)
    {
        if (!empty($args['desc'])) {
            $desc = sprintf('<p class="description">%s</p>', wp_kses($args['desc'], $this->kses_args()));
        } else {
            $desc = '';
        }

        return $desc;
    }

    /**
     * Displays subtitle or description settings group
     *
     * @param array   $args settings field args
     */
    public function callback_info($args)
    {
        printf('<div class="info-description">%1$s</div>', wp_kses($args['desc'], $this->kses_args()));
    }

    /**
     * Displays a hidden field for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_hidden($args)
    {
        $auto_value = $args['value'] ?? null;
        $_value = $this->get_option($args['id'], $args['section'], $args['std']);

        $value = $auto_value !== null || $auto_value === 0 ? $auto_value : $_value;

        $type  = isset($args['type']) ? $args['type'] : 'hidden';

        printf(
            '<input type="%1$s" class="%2$s-hidden" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" />',
            esc_attr($type),
            esc_attr($args['section']),
            esc_attr($args['id']),
            esc_attr($value)
        );
    }

    /**
     * Displays a text field for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_text($args)
    {
        $value       = $this->get_option($args['id'], $args['section'], $args['std']);
        $size        = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
        $type        = isset($args['type']) ? $args['type'] : 'text';
        $placeholder = empty($args['placeholder']) ? '' : $args['placeholder'];
        $maxlength   = isset($args['max']) ? $args['max'] : '';
        $disabled    = isset($args['disabled']) && $args['disabled'] == true ? 'disabled' : '';
        $toggle      = $args['toggle'] ?? [];

        // A switch (or a select, with `options`) in the same row, before the input.
        if ($toggle) {
            echo '<div class="devnet-toggle-field">';

            if (isset($toggle['options'])) {
                $this->toggle_select($args['section'], $toggle, $disabled);
            } else {
                $this->toggle_switch($args['section'], $toggle, $disabled);
            }
        }

        printf(
            '<input type="%1$s" class="%2$s-text" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s" placeholder="%6$s" maxlength="%7$s" %8$s/>',
            esc_attr($type),
            esc_attr($size),
            esc_attr($args['section']),
            esc_attr($args['id']),
            esc_attr($value),
            esc_attr($placeholder),
            esc_attr($maxlength),
            esc_attr($disabled)
        );

        if ($toggle) {
            echo '</div>';
        }

        printf('<p class="description">%s</p>', wp_kses($args['desc'], $this->kses_args()));
    }

    /**
     * Switch for a field's `toggle` arg (`name`, `label`, `default`), saved as 1 or 0 like callback_checkbox().
     * The text input stays enabled: a disabled input isn't posted, so saving would delete its text.
     */
    private function toggle_switch($section, $toggle, $disabled = '')
    {
        $name  = $toggle['name'];
        $value = $this->get_option($name, $section, $toggle['default'] ?? 1);

        printf('<label for="dvnt-%1$s[%2$s]" class="switch">', esc_attr($section), esc_attr($name));
        printf('<input type="hidden" name="%1$s[%2$s]" value="0" />', esc_attr($section), esc_attr($name));
        printf(
            '<input type="checkbox" class="checkbox" id="dvnt-%1$s[%2$s]" name="%1$s[%2$s]" value="1" aria-label="%3$s" %4$s %5$s />',
            esc_attr($section),
            esc_attr($name),
            esc_attr($toggle['label'] ?? ''),
            checked(esc_attr($value), '1', false),
            esc_attr($disabled)
        );
        echo '<span class="devnet-switch devnet-switch--round"></span>';
        echo '</label>';
    }

    /**
     * Select for a `toggle` arg with `options` (`name`, `label`, `options`, `default`): a choice instead of on/off.
     * Its '' option is off.
     */
    private function toggle_select($section, $toggle, $disabled = '')
    {
        $name  = $toggle['name'];
        $value = $this->get_option($name, $section, $toggle['default'] ?? '');

        printf(
            '<select name="%1$s[%2$s]" aria-label="%3$s" %4$s>',
            esc_attr($section),
            esc_attr($name),
            esc_attr($toggle['label'] ?? ''),
            esc_attr($disabled)
        );

        foreach ($toggle['options'] as $key => $label) {
            printf('<option value="%s" %s>%s</option>', esc_attr($key), selected($value, $key, false), esc_html($label));
        }

        echo '</select>';
    }

    /**
     * Displays a url field for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_url($args)
    {
        $this->callback_text($args);
    }

    /**
     * Displays a number field for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_number($args)
    {
        $value        = $this->get_option($args['id'], $args['section'], $args['std']);
        $size         = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
        $type         = isset($args['type']) ? $args['type'] : 'number';
        $placeholder  = empty($args['placeholder']) ? '' : $args['placeholder'];
        $min          = $args['min'] ?? '';
        $max          = empty($args['max']) ? '' : $args['max'];
        $step         = empty($args['step']) ? '' : $args['step'];
        $has_unit_box = !empty($args['unit']) ? 'has-unit-box' : '';
        $toggle       = $args['toggle'] ?? [];

        // A switch in the same row, before the input, as callback_text().
        if ($toggle) {
            echo '<div class="devnet-toggle-field">';
            $this->toggle_switch($args['section'], $toggle);
        }

        printf(
            '<input type="%1$s" class="%2$s-number %10$s" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s" placeholder="%6$s" min="%7$s" max="%8$s" step="%9$s" />',

            esc_attr($type),
            esc_attr($size),
            esc_attr($args['section']),
            esc_attr($args['id']),
            esc_attr($value),
            esc_attr($placeholder),
            esc_attr($min),
            esc_attr($max),
            esc_attr($step),
            esc_attr($has_unit_box)
        );

        if ($has_unit_box) {
            echo '<div class="input-unit-box">' . esc_html($args['unit']) . '</div>';
        }

        if ($toggle) {
            echo '</div>';
        }

        printf('<p class="description">%s</p>', wp_kses($args['desc'], $this->kses_args()));
    }

    /**
     * Displays a checkbox for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_checkbox($args)
    {
        $disabled = isset($args['disabled']) && $args['disabled'] == true ? 'disabled' : '';
        $value    = $this->get_option($args['id'], $args['section'], $args['std']);

        echo '<fieldset>';
        printf('<label for="dvnt-%1$s[%2$s]" class="switch">', esc_attr($args['section']), esc_attr($args['id']));
        printf('<input type="hidden" name="%1$s[%2$s]" value="0" />', esc_attr($args['section']), esc_attr($args['id']));
        printf(
            '<input type="checkbox" class="checkbox" id="dvnt-%1$s[%2$s]" name="%1$s[%2$s]" value="1" %3$s  %4$s />',
            esc_attr($args['section']),
            esc_attr($args['id']),
            checked(esc_attr($value), '1', false),
            esc_attr($disabled)
        );
        echo '<span class="devnet-switch devnet-switch--round"></span>';
        echo '</label>';
        printf('<span class="description">%1$s</span>', wp_kses($args['desc'], $this->kses_args()));
        echo '</fieldset>';
    }

    /**
     * Displays a multicheckbox for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_multicheck($args)
    {

        $value = $this->get_option($args['id'], $args['section'], $args['std']);

        echo '<fieldset>';
        printf(
            '<input type="hidden" name="%1$s[%2$s]" value="" />',
            esc_attr($args['section']),
            esc_attr($args['id'])
        );
        foreach ($args['options'] as $key => $label) {
            $checked = isset($value[$key]) ? $value[$key] : '0';

            printf(
                '<label for="dvnt-%1$s[%2$s][%3$s]" class="switch">',
                esc_attr($args['section']),
                esc_attr($args['id']),
                esc_attr($key)
            );
            printf(
                '<input type="checkbox" class="checkbox" id="dvnt-%1$s[%2$s][%3$s]" name="%1$s[%2$s][%3$s]" value="%3$s" %4$s />',
                esc_attr($args['section']),
                esc_attr($args['id']),
                esc_attr($key),
                checked(esc_attr($checked), esc_attr($key), false)
            );
            echo '<span class="slider round"></span>';
            echo '</label>';
            printf('<span class="desc">%1$s</span><br>', esc_html($label));
        }

        printf('<p class="description">%s</p>', wp_kses($args['desc'], $this->kses_args()));
        echo '</fieldset>';
    }

    /**
     * Displays a radio button for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_radio($args)
    {
        $value = $this->get_option($args['id'], $args['section'], $args['std']);

        echo '<fieldset>';

        foreach ($args['options'] as $key => $label) {
            printf(
                '<label for="dvnt-%1$s[%2$s][%3$s]">',
                esc_attr($args['section']),
                esc_attr($args['id']),
                esc_attr($key)
            );
            printf(
                '<input type="radio" class="radio" id="dvnt-%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s" %4$s />',
                esc_attr($args['section']),
                esc_attr($args['id']),
                esc_attr($key),
                checked(esc_attr($value), esc_attr($key), false)
            );
            printf('%1$s</label><br>', esc_html($label));
        }

        printf('<p class="description">%s</p>', wp_kses($args['desc'], $this->kses_args()));
        echo '</fieldset>';
    }

    /**
     * Displays a radio images as buttons for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_radio_image($args)
    {
        $value = $this->get_option($args['id'], $args['section'], $args['std']);

        echo '<fieldset class="radio-images">';

        foreach ($args['options'] as $key => $label) {
            printf(
                '<label for="dvnt-%1$s[%2$s][%3$s]">',
                esc_attr($args['section']),
                esc_attr($args['id']),
                esc_attr($key)
            );
            printf(
                '<input type="radio" class="radio" id="dvnt-%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s" %4$s />',
                esc_attr($args['section']),
                esc_attr($args['id']),
                esc_attr($key),
                checked(esc_attr($value), esc_attr($key), false)
            );
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $label is a controlled internal option value (not user input).
            printf('<span class="img">%1$s</span></label><br>', $label);
        }

        printf('<p class="description">%s</p>', wp_kses($args['desc'], $this->kses_args()));
        echo '</fieldset>';
    }

    /**
     * Displays a selectbox for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_select($args)
    {
        $value = $this->get_option($args['id'], $args['section'], $args['std']);
        $size  = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
        $optgroup = !empty($args['optgroup']) ? $args['optgroup'] : null;

        printf(
            '<select class="%1$s" name="%2$s[%3$s]" id="%2$s[%3$s]">',
            esc_attr($size),
            esc_attr($args['section']),
            esc_attr($args['id'])
        );

        foreach ($args['options'] as $key => $label) {

            $is_disabled = strpos($key, '_disabled') === 0 ? 'disabled' : '';

            printf(
                '<option value="%s" %s %s>%s</option>',
                esc_attr($key),
                esc_attr($is_disabled),
                selected(esc_attr($value), esc_attr($key), false),
                esc_attr($label)
            );
        }

        if ($optgroup) {
            foreach ($optgroup as $group) {

                $disabled = $group['disabled'] ?? '';
                $is_disabled = filter_var($disabled, FILTER_VALIDATE_BOOLEAN) ? 'disabled' : '';

                echo '<optgroup label="' . esc_attr($group['label']) . '" ' . esc_attr($is_disabled) . '>';

                foreach ($group['options'] as $key => $label) {

                    $is_disabled = strpos($key, '_disabled') === 0 ? 'disabled' : '';

                    printf(
                        '<option value="%s" %s %s>%s</option>',
                        esc_attr($key),
                        esc_attr($is_disabled),
                        selected(esc_attr($value), esc_attr($key), false),
                        esc_attr($label)
                    );
                }
                echo '</optgroup>';
            }
        }

        printf('</select>');
        printf('<p class="description">%s</p>', wp_kses($args['desc'], $this->kses_args()));
    }


    /**
     * Displays a select2 selectbox for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_select2($args)
    {
        $value               = $this->get_option($args['id'], $args['section'], $args['std']);
        $size                = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
        $multiple            = isset($args['multiple']) && $args['multiple'] ? 'multiple' : '';
        $disabled            = isset($args['disabled']) && $args['disabled'] == true ? 'disabled' : '';
        $multiple_collection = $multiple ? '[]' : '';

        $options = [];

        if ($value) {
            $options = is_array($value) ? $value : [$value];
        }

        printf(
            '<select class="%1$s" name="%2$s[%3$s]%4$s" id="%2$s[%3$s]" %5$s %6$s>',
            esc_attr($size),
            esc_attr($args['section']),
            esc_attr($args['id']),
            esc_attr($multiple_collection),
            esc_attr($multiple),
            esc_attr($disabled)
        );

        foreach ((array) $options as $key => $label) {

            $key   = $label;
            $parts = explode('___', $label);
            $label = $parts[1] ?? $label;

            // all options are always selected.
            printf('<option value="%s" selected>%s</option>', esc_attr($key), esc_html($label));
        }

        printf('</select>');
        printf('<p class="description">%s</p>', wp_kses($args['desc'], $this->kses_args()));
    }

    /**
     * Displays a textarea for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_textarea($args)
    {
        $value       = $this->get_option($args['id'], $args['section'], $args['std']);
        $size        = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
        $rows        = isset($args['rows']) && !empty($args['rows']) ? $args['rows'] : 5;
        $cols        = isset($args['cols']) && !empty($args['cols']) ? $args['cols'] : 55;
        $placeholder = empty($args['placeholder']) ? '' : $args['placeholder'];
        $disabled    = isset($args['disabled']) && $args['disabled'] == true ? 'disabled' : '';

        $toggle = $args['toggle'] ?? [];

        if ($toggle) {
            echo '<div class="devnet-toggle-field">';
            $this->toggle_switch($args['section'], $toggle, $disabled);
        }

        printf(
            '<textarea rows="%1$s" cols="%2$s" class="%3$s-text" id="%4$s[%5$s]" name="%4$s[%5$s]" placeholder="%6$s" %7$s>%8$s</textarea>',
            esc_attr($rows),
            esc_attr($cols),
            esc_attr($size),
            esc_attr($args['section']),
            esc_attr($args['id']),
            esc_attr($placeholder),
            esc_attr($disabled),
            esc_textarea($value)
        );

        if ($toggle) {
            echo '</div>';
        }

        printf('<p class="description">%s</p>', wp_kses($args['desc'], $this->kses_args()));
    }

    /**
     * Displays the html for a settings field
     *
     * @param array   $args settings field args
     * @return string
     */
    public function callback_html($args)
    {
        printf('<p class="description">%s</p>', wp_kses($args['desc'], $this->kses_args()));
    }

    /**
     * Displays a rich text textarea for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_wysiwyg($args)
    {
        $value = $this->get_option($args['id'], $args['section'], $args['std']);
        $size  = isset($args['size']) && !is_null($args['size']) ? $args['size'] : '500px';

        echo '<div style="max-width: ' . esc_attr($size) . ';">';

        $editor_settings = [
            'teeny'         => false,
            'textarea_name' => $args['section'] . '[' . $args['id'] . ']',
            'textarea_rows' => 10
        ];

        if (isset($args['options']) && is_array($args['options'])) {
            $editor_settings = array_merge($editor_settings, $args['options']);
        }

        wp_editor($value, $args['section'] . '-' . $args['id'], $editor_settings);

        echo '</div>';

        printf('<p class="description">%s</p>', wp_kses($args['desc'], $this->kses_args()));
    }

    /**
     * Displays a file upload field for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_file($args)
    {
        $value = $this->get_option($args['id'], $args['section'], $args['std']);
        $size  = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
        $id    = $args['section'] . '[' . $args['id'] . ']';
        $label = isset($args['options']['button_label']) ? $args['options']['button_label'] : 'Choose File';

        printf(
            '<input type="text" class="%1$s-text dvnt-f-url" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>',
            esc_attr($size),
            esc_attr($args['section']),
            esc_attr($args['id']),
            esc_attr($value)
        );
        echo '<input type="button" class="button dvnt-f-browse" value="' . esc_attr($label) . '" />';

        // Image by file extension, without requesting the URL: a slow or unreachable host would block this page.
        if ($value && filter_var($value, FILTER_VALIDATE_URL) && strpos($value, 'http') === 0) {
            $filetype = wp_check_filetype((string) wp_parse_url($value, PHP_URL_PATH));

            if (strpos((string) $filetype['type'], 'image/') === 0) {
                // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage -- Using external image URL.
                echo '<img src="' . esc_url($value) . '" class="dvnt-f-preview" width="75" height="75"/>';
            }
        }

        printf('<p class="description">%s</p>', wp_kses($args['desc'], $this->kses_args()));
    }

    /**
     * Displays a password field for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_password($args)
    {
        $value = $this->get_option($args['id'], $args['section'], $args['std']);
        $size  = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';

        printf(
            '<input type="password" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>',
            esc_attr($size),
            esc_attr($args['section']),
            esc_attr($args['id']),
            esc_attr($value)
        );

        printf('<p class="description">%s</p>', wp_kses($args['desc'], $this->kses_args()));
    }

    /**
     * Displays a color picker field for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_color($args)
    {
        $value = $this->get_option($args['id'], $args['section'], $args['std']);
        $size  = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';

        printf(
            '<input type="text" class="%1$s-text wp-color-picker-field color-picker" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" data-default-color="%5$s" data-alpha-enabled="true" />',
            esc_attr($size),
            esc_attr($args['section']),
            esc_attr($args['id']),
            esc_attr($value),
            esc_attr($args['std'])
        );

        printf('<p class="description">%s</p>', wp_kses($args['desc'], $this->kses_args()));
    }

    /**
     * Displays a repeatable for a settings field.
     *
     * @param array   $args settings field args
     */
    public function callback_group($args)
    {
        $fields = isset($args['fields']) ? $args['fields'] : [];

        $group_data = $this->get_option($args['id'], $args['section'], $args['std']) ?? [];

        $repeatable = isset($args['repeatable']) && $args['repeatable'];

        if (!$repeatable) {
            $group_data = [$group_data];
        }

        if (!is_array($fields) || empty($fields)) return;

        echo '<div class="dvnt-groups" data-repeatable="' . esc_attr($repeatable) . '">';


        foreach ((array)$group_data as $group_index => $group_values) {

            echo '<div class="dvnt-group">';

            foreach ($fields as $index => $field) {

                $callback = 'callback_' . $field['type'];


                $repeatable_index = $repeatable ? '[' . $group_index . ']' : '';

                /**
                 * to simplify life, we are building id upon callback functions structure.
                 * name attributes are build like: name="section[id]"
                 * that's why we have closing "]" first and no closing "]" at the end.
                 * $external id will be inserted on the place of $id in the callback function inside "[]"
                 *
                 */
                $extended_id = $args['id'] . ']' . $repeatable_index . '[' . $field['name'];


                $std = isset($field['std']) ? $field['std'] : '';

                $field_value = isset($group_values[$field['name']]) ? $group_values[$field['name']] : $std;

                $description = isset($field['desc']) ? $field['desc'] : '';

                $callback_args = [
                    'id'      => $extended_id,
                    'section' => $args['section'],
                    'std'     => $field_value,
                    'desc'    => $description,
                    'group_index' => $group_index
                ] + $field;


                echo '<div class="dvnt-group-field field-type-' . esc_attr($field['type']) . '">';
                echo '<label>' . esc_html($field['label']) . '</label>';
                call_user_func([$this, $callback], $callback_args);
                echo '</div>';
            }

            if ($repeatable) {

                echo '<a href="#" class="dvnt-remove-group">Remove</a>';
            }

            echo '</div>';
        }

        if ($repeatable) {

            echo '<button class="dvnt-repeat-group" title="Add new">+</button>';
        }

        echo '</div>';

        printf('<p class="description">%s</p>', wp_kses($args['desc'], $this->kses_args()));
    }

    /**
     * Displays a date picker for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_date($args)
    {
        $value = $this->get_option($args['id'], $args['section'], $args['std']);

        printf(
            '<input type="date" class="date" id="dvnt-%1$s[%2$s]" name="%1$s[%2$s]" value="%3$s" />',
            esc_attr($args['section']),
            esc_attr($args['id']),
            esc_attr($value),
        );

        printf('<p class="description">%s</p>', wp_kses($args['desc'], $this->kses_args()));
    }




    /**
     * Displays a select box for creating the pages select box
     *
     * @param array   $args settings field args
     */
    public function callback_pages($args)
    {
        $dropdown_args = [
            'selected' => esc_attr($this->get_option($args['id'], $args['section'], $args['std'])),
            'name'     => esc_attr($args['section'] . '[' . $args['id'] . ']'),
            'id'       => esc_attr($args['section'] . '[' . $args['id'] . ']'),
            'echo'     => 0
        ];

        $kses_args = [
            'select' => [
                'name'   => [],
                'id'     => [],
            ],
            'option' => [
                'class' => [],
                'value' => [],
            ],
        ];

        echo wp_kses(wp_dropdown_pages($dropdown_args), $kses_args);
    }

    /**
     * Sanitize callback for Settings API
     *
     * @return mixed
     */
    public function sanitize_options($options)
    {
        if (!$options) {
            return $options;
        }

        foreach ($options as $option_slug => $option_value) {
            $sanitize_callback = $this->get_sanitize_callback($option_slug);

            // If callback is set, call it
            if ($sanitize_callback) {

                $options[$option_slug] = call_user_func($sanitize_callback, $option_value);
                continue;
            }
        }

        return $options;
    }

    /**
     * Get sanitization callback for given option slug
     *
     * @param string $slug option slug
     *
     * @return mixed string or bool false
     */
    public function get_sanitize_callback($slug = '')
    {
        if (empty($slug)) {
            return false;
        }

        // Iterate over registered fields and see if we can find proper callback
        foreach ($this->settings_fields as $section => $options) {
            foreach ($options as $option) {
                if ($option['name'] != $slug) {
                    continue;
                }

                // Return the callback name
                return isset($option['sanitize_callback']) && is_callable($option['sanitize_callback']) ? $option['sanitize_callback'] : false;
            }
        }

        return false;
    }

    /**
     * Get the value of a settings field
     *
     * @param string  $option  settings field name
     * @param string  $section the section name this field belongs to
     * @param string  $default default text if it's not found
     * @return string
     */
    public function get_option($option, $section, $default = '')
    {

        $options = get_option($section);

        if (isset($options[$option])) {
            return $options[$option];
        }

        return $default;
    }

    /**
     * Show navigations as tab
     *
     * Shows all the settings section labels as tab
     */
    public function show_navigation()
    {

        $count = count($this->settings_sections);

        // don't show the navigation if only one section exists
        if ($count === 1) {
            return;
        }

        echo '<h2 class="nav-tab-wrapper">';

        foreach ($this->settings_sections as $tab) {

            $is_inner_tab = $tab['parent'] ?? null;

            // Skip rendering top nav tabs for inner navigation.
            if ($is_inner_tab) {
                continue;
            }

            $has_tabs = (filter_var($tab['tabs'] ?? false, FILTER_VALIDATE_BOOL));
            $classes = 'nav-tab';

            $classes = $has_tabs ? $classes . ' has-tabs' : $classes;

            printf('<a href="#%1$s" class="%2$s" id="%1$s-tab">%3$s</a>', esc_attr($tab['id']), esc_attr($classes), esc_html($tab['title']));
        }

        echo '</h2>';
    }

    /**
     * Show inner navigation if is set.
     */
    public function inner_navigation($form)
    {
        $form_id = $form['id'] ?? '';
        $title   = $form['title'] ?? '';

        $tab_items = '';

        foreach ($this->settings_sections as $tab) {

            $parent_id  = $tab['parent'] ?? '';

            if ($form_id !== $parent_id) continue;

            $tab_items .= sprintf('<a href="#%1$s" class="nav-tab inner-tab" id="%1$s-tab">%2$s</a>', esc_attr($tab['id']), esc_html($tab['title']));
        }

        if ($tab_items) {

            echo '<h2>' . esc_html($title) . '</h2>';
            echo '<div class="nav-tab-wrapper">' . wp_kses_post($tab_items) . '</div>';
        }
    }

    public function show_forms()
    {
        echo '<div class="metabox-holder">';

        foreach ($this->settings_sections as $form) {
            $is_tab = $form['parent'] ?? false;
            $has_tabs = filter_var($form['tabs'] ?? false, FILTER_VALIDATE_BOOL);

            // Skip inner tabs
            if ($is_tab) {
                continue;
            }

            $classes = 'group' . ($has_tabs ? ' has-tabs' : '');
            $form_id = esc_attr($form['id']);

            echo '<div id="' . esc_attr($form_id) . '" class="' . esc_attr($classes) . '" style="display: none;">';

            $this->inner_navigation($form);

            if (!empty($form['render'])) {
                call_user_func($form['render'], $form);
            } elseif ($has_tabs) {
                $this->render_inner_tabs($form['id']); // Call function to render inner tabs
            } else {
                $this->render_form($form);
            }

            echo '</div>'; // Close section div
        }

        echo '</div>';
    }

    /**
     * Function to render inner tabs of a parent section.
     */
    private function render_inner_tabs($parent_id)
    {
        foreach ($this->settings_sections as $tab) {
            if (($tab['parent'] ?? '') === $parent_id) {

                echo '<div id="' . esc_attr($tab['id']) . '" class="group" style="display: none;">';

                $this->render_form($tab);

                echo '</div>'; // Close section div
            }
        }
    }

    /**
     * "Settings saved." or a field's error from the last save (options.php records them, get_settings_errors()),
     * beside the Save button. Not notices: WordPress would move them to the top of the page.
     *
     * @since	4.0.0
     */
    private function save_messages()
    {
        $messages = '';

        foreach (get_settings_errors() as $error) {
            $type = in_array($error['type'], ['error', 'warning', 'info'], true) ? $error['type'] : 'success';
            $icon = ['success' => 'yes-alt', 'info' => 'info-outline'][$type] ?? 'warning';

            $messages .= sprintf(
                '<p class="devnet-plugin-save-message devnet-plugin-save-message--%1$s"><span class="dashicons dashicons-%2$s" aria-hidden="true"></span> %3$s</p>',
                esc_attr($type),
                esc_attr($icon),
                wp_kses_post($error['message'])
            );
        }

        if ($messages) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped as it's built above.
            echo '<div class="devnet-plugin-save-messages" role="status">' . $messages . '</div>';
        }
    }

    /**
     * Function to render the settings form.
     */
    private function render_form($form)
    {
        $has_layout = !empty($form['layout']);

        echo '<form method="post" action="options.php"' . ($has_layout ? ' class="dvnt-layout-form"' : '') . '>';

        do_action($this->plugin_slug . '_form_top', $form);
        do_action($this->plugin_slug . '_form_top_' . $form['id'], $form);

        settings_fields($form['id']);

        if ($has_layout) {
            $this->render_layout($form);
        } else {
            do_settings_sections($form['id']);
        }

        do_action($this->plugin_slug . '_form_bottom_' . $form['id'], $form);
        do_action($this->plugin_slug . '_form_bottom', $form);

        if (isset($this->settings_fields[$form['id']])) {
            echo '<div class="submit-row" style="padding-left: 10px">';
            $this->save_messages();
            submit_button();
            echo '</div>';
        }

        echo '</form>';

        // Outside the options.php form: an HTML <form> cannot nest inside another, so any field
        // needing its own <form> (e.g. an admin-post.php action button) must hook in here instead.
        do_action($this->plugin_slug . '_form_after_' . $form['id'], $form);
    }

    /**
     * Render a section's fields as a header plus panels (tabs on wide screens, a select on narrow ones).
     * All panels stay inside the one options.php form, so they save together.
     */
    private function render_layout($form)
    {
        global $wp_settings_fields;

        $id     = $form['id'];
        $fields = $wp_settings_fields[$id] ?? [];

        // Skip empty groups/panels, e.g. Popup when the module is unavailable.
        $panels = [];
        foreach ($form['layout'] as $key => $panel) {
            $panel['groups'] = array_filter($panel['groups'], function ($group_id) use ($fields, $id) {
                return !empty($fields["{$id}__{$group_id}"]);
            }, ARRAY_FILTER_USE_KEY);

            if ($panel['groups'] || (!$panels && !empty($fields[$id]))) {
                $panels[$key] = $panel;
            }
        }

        echo '<div class="dvnt-form-header">';
        echo '<h2 class="dvnt-form-title">' . esc_html($form['title']) . '</h2>';

        if (!empty($fields["{$id}__header"])) {
            echo '<table class="form-table dvnt-form-header__fields" role="presentation">';
            do_settings_fields($id, "{$id}__header");
            echo '</table>';
        }

        do_action($this->plugin_slug . '_form_header', $form);
        echo '</div>';

        printf('<div class="dvnt-sections" data-section-id="%s">', esc_attr($id));

        printf('<div class="dvnt-sections__tabs" role="tablist" aria-orientation="vertical" aria-label="%s">', esc_attr__('Settings sections', 'easy-subscribe'));
        foreach ($panels as $key => $panel) {
            $first = $key === array_key_first($panels);
            printf(
                '<button type="button" role="tab" id="dvnt-tab-%1$s-%2$s" aria-controls="dvnt-panel-%1$s-%2$s" aria-selected="%3$s" tabindex="%4$s" data-section="%2$s">%5$s</button>',
                esc_attr($id),
                esc_attr($key),
                $first ? 'true' : 'false',
                $first ? '0' : '-1',
                esc_html($panel['title'])
            );
        }
        echo '</div>';

        // Narrow screens: CSS shows this instead of the tablist (never both).
        printf('<div class="dvnt-sections__select"><label for="dvnt-section-select-%1$s">%2$s</label><select id="dvnt-section-select-%1$s">', esc_attr($id), esc_html__('Settings section', 'easy-subscribe'));
        foreach ($panels as $key => $panel) {
            printf('<option value="%s">%s</option>', esc_attr($key), esc_html($panel['title']));
        }
        echo '</select></div>';

        foreach ($panels as $key => $panel) {
            $first = $key === array_key_first($panels);

            printf(
                '<div class="dvnt-section-panel" role="tabpanel" id="dvnt-panel-%1$s-%2$s" aria-labelledby="dvnt-tab-%1$s-%2$s" data-section="%2$s"%3$s>',
                esc_attr($id),
                esc_attr($key),
                $first ? '' : ' hidden'
            );
            echo '<h3 class="dvnt-section-title">' . esc_html($panel['title']) . '</h3>';

            foreach ($panel['groups'] as $group_id => $group) {
                printf('<div class="dvnt-settings-group dvnt-settings-group--%s"><div class="dvnt-settings-group__head"><h4 class="dvnt-settings-group__title">%s</h4>', esc_attr($group_id), esc_html($group['title']));
                if (!empty($group['description'])) {
                    echo '<p class="description">' . esc_html($group['description']) . '</p>';
                }
                echo '</div><table class="form-table" role="presentation">';
                do_settings_fields($id, "{$id}__{$group_id}");
                echo '</table></div>';
            }

            // Ungrouped fields (e.g. added by a filter) still render, and therefore still save.
            if ($first && !empty($fields[$id])) {
                echo '<div class="dvnt-settings-group"><table class="form-table" role="presentation">';
                do_settings_fields($id, $id);
                echo '</table></div>';
            }

            echo '</div>';
        }

        echo '</div>';
    }
}
