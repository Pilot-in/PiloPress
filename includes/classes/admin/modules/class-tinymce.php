<?php

if ( !class_exists( 'PIP_TinyMCE' ) ) {

    /**
     * Class PIP_TinyMCE
     */
    class PIP_TinyMCE {

        public function __construct() {

            // Check if module is enable
            $modules = pip_get_modules();
            if ( !acf_maybe_get( $modules, 'tinymce' ) ) {
                return;
            }

            // WP hooks
            add_action( 'wp_enqueue_scripts', array( $this, 'custom_fonts_stylesheets' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'custom_fonts_stylesheets' ) );
            add_action( 'admin_init', array( $this, 'add_custom_fonts_to_editor' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'localize_data' ) );
            add_filter( 'mce_external_plugins', array( $this, 'editor_button_script' ) );
            add_filter( 'mce_css', array( $this, 'editor_style' ) );
            add_filter( 'tiny_mce_before_init', array( $this, 'remove_tiny_mce_style' ) );

            // ACF hooks
            add_filter( 'acf/fields/wysiwyg/toolbars', array( $this, 'customize_toolbar' ), 99, 1 ); // Late to be after 3rd party plugins, but not PHP_INT_MAX to allow modifications
            add_filter( 'acfe/load_fields', array( $this, 'load_fields_dark_mode' ) );
            add_filter( 'acf/pre_render_fields', array( $this, 'load_fields_dark_mode' ) );
            add_filter( 'acf/load_field/type=wysiwyg', array( $this, 'load_field_dark_mode' ) );
            add_action( 'acf/render_field_settings/type=wysiwyg', array( $this, 'add_dark_mode_option' ) );
        }

        /**
         * Add dark mode option to WYSIWYG fields
         *
         * @param $field
         */
        public function add_dark_mode_option( $field ) {
            // Dark mode
            acf_render_field_setting(
                $field,
                array(
                    'label'         => __( 'Dark mode' ),
                    'name'          => 'pip_dark_mode_default',
                    'key'           => 'pip_dark_mode_default',
                    'instructions'  => __( 'Activate dark mode by default.' ),
                    'type'          => 'true_false',
                    'message'       => '',
                    'default_value' => false,
                    'ui'            => true,
                )
            );
        }

        /**
         * Enqueue custom TinyMCE script and add variables to it
         */
        public function localize_data() {
            $pip_tailwind    = acf_get_instance( 'PIP_Tailwind' );
            $tailwind_prefix = $pip_tailwind->get_prefix();

            acf_localize_data(
                array(
                    'custom_fonts'   => $this->get_custom_fonts(),
                    'custom_styles'  => $this->get_custom_typography(),
                    'custom_colors'  => $this->get_custom_colors(),
                    'custom_buttons' => $this->get_custom_buttons(),
                    'image_sizes'    => $this->get_all_image_sizes(),
                    'tw_prefix'      => $tailwind_prefix,
                )
            );
        }

        /**
         * Get custom fonts
         *
         * @return array
         */
        public function get_custom_fonts() {

            $fonts = array();

            // Get custom fonts
            if ( have_rows( 'pip_fonts', 'pip_styles_fonts' ) ) {
                while ( have_rows( 'pip_fonts', 'pip_styles_fonts' ) ) {
                    the_row();

                    switch ( get_row_layout() ) {

                        case 'google_font':
                            // Get font name
                            $label         = get_sub_field( 'name' );
                            $url           = get_sub_field( 'url' );
                            $enqueue       = get_sub_field( 'enqueue' );
                            $class_name    = get_sub_field( 'class_name' );
                            $fallback      = get_sub_field( 'fallback' );
                            $add_to_editor = get_sub_field( 'add_to_editor' );

                            // Update class name
                            if ( !$class_name ) {
                                $class_name = sanitize_title( $label );
                                update_sub_field( 'class_name', $class_name, 'pip_styles_fonts' );
                            }

                            // Add custom font
                            $fonts[ sanitize_title( $label ) ] = array(
                                'name'          => $label,
                                'class_name'    => $class_name,
                                'url'           => $url,
                                'enqueue'       => $enqueue,
                                'fallback'      => $fallback,
                                'add_to_editor' => $add_to_editor,
                            );
                            break;

                        case 'custom_font':
                            // Get font name
                            $label         = get_sub_field( 'name' );
                            $class_name    = get_sub_field( 'class_name' );
                            $fallback      = get_sub_field( 'fallback' );
                            $add_to_editor = get_sub_field( 'add_to_editor' );
                            $multiple      = get_sub_field( 'multiple_weight_and_style' );

                            if ( $multiple && have_rows( 'variations' ) ) {

                                while ( have_rows( 'variations' ) ) {
                                    the_row();

                                    $files   = get_sub_field( 'files' );
                                    $weight  = get_sub_field( 'weight' );
                                    $style   = get_sub_field( 'style' );
                                    $display = get_sub_field( 'display' );

                                    // Add custom font
                                    $fonts[ sanitize_title( $label ) ] = array(
                                        'name'          => $label,
                                        'class_name'    => $class_name,
                                        'fallback'      => $fallback,
                                        'add_to_editor' => $add_to_editor,
                                        'files'         => $files,
                                        'weight'        => $weight,
                                        'style'         => $style,
                                        'display'       => $display,
                                    );
                                }
                            } else {
                                // Update class name
                                if ( !$class_name ) {
                                    $class_name = sanitize_title( $label );
                                    update_sub_field( 'class_name', $class_name, 'pip_styles_fonts' );
                                }

                                $files   = get_sub_field( 'files' );
                                $weight  = get_sub_field( 'weight' );
                                $style   = get_sub_field( 'style' );
                                $display = get_sub_field( 'display' );

                                // Add custom font
                                $fonts[ sanitize_title( $label ) ] = array(
                                    'name'          => $label,
                                    'class_name'    => $class_name,
                                    'fallback'      => $fallback,
                                    'add_to_editor' => $add_to_editor,
                                    'files'         => $files,
                                    'weight'        => $weight,
                                    'style'         => $style,
                                    'display'       => $display,
                                );
                            }

                            break;
                    }
                }
            }

            return $fonts;
        }

        /**
         * Get custom typography
         *
         * @return array
         */
        public function get_custom_typography() {

            $custom_styles = array();

            // Get custom styles
            if ( have_rows( 'pip_typography', 'pip_styles_configuration' ) ) {
                while ( have_rows( 'pip_typography', 'pip_styles_configuration' ) ) {
                    the_row();

                    $label            = get_sub_field( 'label' );
                    $class_name       = get_sub_field( 'class_name' );
                    $classes_to_apply = get_sub_field( 'classes_to_apply' );
                    $add_to_editor    = get_sub_field( 'add_to_editor' );

                    // Add custom style
                    $custom_styles[ sanitize_title( $label ) ] = array(
                        'name'             => $label,
                        'class_name'       => $class_name,
                        'classes_to_apply' => $classes_to_apply,
                        'add_to_editor'    => $add_to_editor,
                    );
                }
            }

            return $custom_styles;
        }

        /**
         * Get custom colors
         *
         * @return array
         */
        public function get_custom_colors() {

            $colors           = array();
            $redefined_colors = array();

            // Get override colors option
            $override       = false;
            $override_group = get_field( 'pip_override_colors', 'pip_styles_configuration' );
            if ( $override_group ) {
                $override = acf_maybe_get( $override_group, 'override_colors' );
            }

            // Get simple colors
            if ( have_rows( 'pip_simple_colors', 'pip_styles_configuration' ) ) {
                while ( have_rows( 'pip_simple_colors', 'pip_styles_configuration' ) ) {
                    the_row();

                    $label            = get_sub_field( 'label' );
                    $name             = get_sub_field( 'name' );
                    $value            = get_sub_field( 'value' );
                    $classes_to_apply = get_sub_field( 'classes_to_apply' );
                    $add_to_editor    = get_sub_field( 'add_to_editor' );

                    // Add custom style
                    $colors[ sanitize_title( $label ) ] = array(
                        'name'             => $label,
                        'value'            => $value,
                        'class_name'       => $name,
                        'classes_to_apply' => $classes_to_apply,
                        'add_to_editor'    => $add_to_editor,
                    );
                }
            }

            // Get colors with shades
            if ( have_rows( 'pip_colors_shades', 'pip_styles_configuration' ) ) {
                while ( have_rows( 'pip_colors_shades', 'pip_styles_configuration' ) ) {
                    the_row();

                    // Get color name
                    $color_name = get_sub_field( 'color_name' );

                    // Store color name
                    $redefined_colors[] = $color_name;

                    // Get shades
                    if ( have_rows( 'shades' ) ) {
                        while ( have_rows( 'shades' ) ) {
                            the_row();

                            $label            = get_sub_field( 'label' );
                            $name             = get_sub_field( 'shade_name' );
                            $value            = get_sub_field( 'value' );
                            $classes_to_apply = get_sub_field( 'classes_to_apply' );
                            $add_to_editor    = get_sub_field( 'add_to_editor' );

                            // Add custom style
                            $colors[ sanitize_title( $color_name . '-' . $label ) ] = array(
                                'name'             => $label,
                                'value'            => $value,
                                'class_name'       => $color_name . '-' . $name,
                                'classes_to_apply' => $classes_to_apply,
                                'add_to_editor'    => $add_to_editor,
                            );

                        }
                    }
                }
            }

            // Native colors
            if ( !$override ) {
                $native_colors_in_editor = get_field( 'pip_native_colors_in_editor', 'pip_styles_configuration' );
                $native_colors           = pip_get_tailwind_native_colors();

                if ( $native_colors_in_editor ) {
                    foreach ( $native_colors as $key => $shades ) {

                        // If color has been redefined, skip
                        if ( in_array( $key, $redefined_colors, true ) ) {
                            continue;
                        }

                        // Browse shades
                        foreach ( $shades as $shade ) {

                            $shade_name  = acf_maybe_get( $shade, 'name' );
                            $shade_label = acf_maybe_get( $shade, 'label' );
                            $shade_value = acf_maybe_get( $shade, 'value' );

                            // If current shade is in select native colors
                            if ( in_array( $key . '-' . $shade_name, $native_colors_in_editor, true ) ) {

                                // Add custom style
                                $colors[ sanitize_title( $key . '-' . $shade_name ) ] = array(
                                    'name'             => $shade_label,
                                    'value'            => $shade_value,
                                    'class_name'       => $key . '-' . $shade_name,
                                    'classes_to_apply' => '',
                                    'add_to_editor'    => true,
                                );

                            }
                        }
                    }
                }
            }

            return $colors;
        }

        /**
         * Get custom buttons
         *
         * @return array
         */
        public function get_custom_buttons() {

            $buttons = array();

            // Get custom buttons
            if ( have_rows( 'pip_button', 'pip_styles_configuration' ) ) {
                while ( have_rows( 'pip_button', 'pip_styles_configuration' ) ) {
                    the_row();

                    $label            = get_sub_field( 'label' );
                    $class_name       = get_sub_field( 'class_name' );
                    $classes_to_apply = get_sub_field( 'classes_to_apply' );
                    $add_to_editor    = get_sub_field( 'add_to_editor' );

                    // Add custom button
                    $buttons[ sanitize_title( $label ) ] = array(
                        'name'             => $label,
                        'class_name'       => $class_name,
                        'classes_to_apply' => $classes_to_apply,
                        'add_to_editor'    => $add_to_editor,
                    );
                }
            }

            return $buttons;
        }

        /**
         * Get all image sizes
         *
         * @return array
         */
        public function get_all_image_sizes() {

            $image_sizes = array();

            // Get image sizes
            $default_image_sizes = get_intermediate_image_sizes();

            if ( $default_image_sizes ) {
                foreach ( $default_image_sizes as $size ) {
                    $image_sizes[ $size ] = array(
                        'width'  => intval( get_option( "{$size}_size_w" ) ),
                        'height' => intval( get_option( "{$size}_size_h" ) ),
                        'crop'   => intval( get_option( "{$size}_crop" ) ),
                    );
                }
            }

            if ( isset( $_wp_additional_image_sizes ) && count( $_wp_additional_image_sizes ) ) {
                $image_sizes = array_merge( $image_sizes, $_wp_additional_image_sizes );
            }

            return $image_sizes;
        }

        /**
         * Enqueue custom fonts
         */
        public function custom_fonts_stylesheets() {

            // Get custom fonts
            if ( have_rows( 'pip_fonts', 'pip_styles_fonts' ) ) {
                while ( have_rows( 'pip_fonts', 'pip_styles_fonts' ) ) {
                    the_row();

                    // If not google font, skip
                    if ( get_row_layout() !== 'google_font' ) {
                        continue;
                    }

                    // Get sub fields
                    $name    = get_sub_field( 'name' );
                    $enqueue = get_sub_field( 'enqueue' );
                    $url     = get_sub_field( 'url' );

                    // Auto enqueue to false
                    if ( !$enqueue ) {
                        continue;
                    }

                    // Add google font
                    wp_enqueue_style( 'google-font-' . sanitize_title( $name ), $url, false, pilopress()->version );
                }
            }
        }

        /**
         * Add custom fonts to editor
         */
        public function add_custom_fonts_to_editor() {

            // Get custom fonts
            if ( have_rows( 'pip_fonts', 'pip_styles_fonts' ) ) {
                while ( have_rows( 'pip_fonts', 'pip_styles_fonts' ) ) {
                    the_row();

                    // If not google font, skip
                    if ( get_row_layout() !== 'google_font' ) {
                        continue;
                    }

                    // Get sub fields
                    $enqueue = get_sub_field( 'enqueue' );
                    $url     = get_sub_field( 'url' );

                    // Auto enqueue to false
                    if ( !$enqueue ) {
                        continue;
                    }

                    // Enqueue google font
                    add_editor_style( str_replace( ',', '%2C', $url ) );
                }
            }
        }

        /**
         * Customize toolbars
         *
         * @param $toolbars
         *
         * @return mixed
         */
        public function customize_toolbar( $toolbars ) {

            // Remove basic toolbar
            unset( $toolbars['Basic'] );

            // Native TinyMCE buttons
            $native_buttons = array(
                'bold',
                'italic',
                'bullist',
                'numlist',
                'blockquote',
                'alignleft',
                'aligncenter',
                'alignright',
                'link',
                'wp_more',
                'spellchecker',
                'fullscreen',
                'wp_adv',
                'hr',
                'removeformat',
                'charmap',
                'outdent',
                'indent',
                'undo',
                'wp_help',
                'strikethrough',
                'forecolor',
                'pastetext',
                'redo',
                'formatselect',
            );

            // Check if there is 3rd party buttons added
            $non_native_buttons = array();
            $full_toolbar       = $toolbars['Full'];
            foreach ( $full_toolbar as $toolbar_line ) {
                $diff = array_diff( $toolbar_line, $native_buttons );

                if ( $diff ) {
                    $non_native_buttons = array_map( 'acf_unarray', $diff );
                }
            }

            // Toolbar line 1
            $toolbars['Full'][1] = array(
                'formatselect',
                'pip_styles',
                'pip_fonts',
                'pip_colors',
                'pip_shortcodes',
                'bold',
                'italic',
                'underline',
                'strikethrough',
                'bullist',
                'numlist',
                'alignleft',
                'aligncenter',
                'alignright',
                'alignjustify',
                'link',
                'wp_add_media',
                'pip_dark_mode',
                'wp_adv',
            );

            // Toolbar line 2
            $toolbars['Full'][2] = array(
                'blockquote',
                'hr',
                'forecolor',
                'backcolor',
                'pastetext',
                'removeformat',
                'charmap',
                'outdent',
                'indent',
                'subscript',
                'superscript',
                'fullscreen',
                'wp_help',
            );

            // Add 3rd party buttons
            $toolbars['Full'][2] = array_merge( $toolbars['Full'][2], $non_native_buttons );

            return $toolbars;
        }

        /**
         * Add editor options
         *
         * @param $scripts
         *
         * @return mixed
         */
        public function editor_button_script( $scripts ) {

            $scripts['pip_colors']     = PIP_URL . 'assets/js/tinymce-custom-styles.js';
            $scripts['pip_fonts']      = PIP_URL . 'assets/js/tinymce-custom-styles.js';
            $scripts['pip_styles']     = PIP_URL . 'assets/js/tinymce-custom-styles.js';
            $scripts['pip_shortcodes'] = PIP_URL . 'assets/js/tinymce-shortcodes.js';

            return $scripts;
        }

        /**
         * Add custom editor style and remove WP's one
         *
         * @param $stylesheets
         *
         * @return string
         */
        public function editor_style( $stylesheets ) {

            $stylesheets = explode( ',', $stylesheets );

            // Parse stylesheets to remove WP CSS
            foreach ( $stylesheets as $key => $stylesheet ) {
                if ( strstr( $stylesheet, 'wp-content.css' ) ) {
                    unset( $stylesheets[ $key ] );
                }
            }

            // Add custom stylesheet
            if ( file_exists( PIP_THEME_ASSETS_PATH . PIP_THEME_STYLE_FILENAME . '.min.css' ) ) {
                $stylesheets[] = PIP_THEME_ASSETS_URL . PIP_THEME_STYLE_FILENAME . '.min.css';
            }

            // Add custom stylesheet
            if ( file_exists( PIP_PATH . 'assets/css/pilopress-tinymce.css' ) ) {
                $stylesheets[] = PIP_URL . 'assets/css/pilopress-tinymce.css';
            }

            return implode( ',', $stylesheets );
        }

        /**
         * Get dark mode field data
         *
         * @param $field
         *
         * @return mixed
         */
        public function get_dark_mode_field( $field ) {

            // Clone field
            $new = $field;

            // Change values
            $new['type']          = 'acfe_hidden';
            $new['label']         = '';
            $new['key']           = $field['key'] . '_dark_mode';
            $new['name']          = $field['name'] . '_dark_mode';
            $new['_name']         = $field['_name'] . '_dark_mode';
            $new['append']        = '';
            $new['prepend']       = '';
            $new['minlength']     = '';
            $new['maxlength']     = '';
            $new['required']      = false;
            $new['default_value'] = acf_maybe_get( $field, 'pip_dark_mode_default' );

            // Remove useless values
            unset( $new['tabs'] );
            unset( $new['toolbar'] );
            unset( $new['media_upload'] );
            unset( $new['delay'] );
            unset( $new['id'] );
            unset( $new['class'] );
            unset( $new['_valid'] );

            return $new;
        }

        /**
         * Add dark mode field
         *
         * @param $fields
         *
         * @return mixed
         */
        public function load_fields_dark_mode( $fields ) {

            // If is ACF admin, return
            if ( acfe_is_admin_screen() ) {
                return $fields;
            }

            // Get keys
            $field_keys = wp_list_pluck( $fields, 'key' );

            $offset = 0;

            // Browse all fields
            foreach ( $fields as $key => $field ) {

                // If not a wysiwyg field, skip
                if ( $field['type'] !== 'wysiwyg' ) {
                    continue;
                }

                // Get filters data
                $new = $this->get_dark_mode_field( $field );

                // If dark mode field already added, return
                if ( is_array( $field_keys ) ) {
                    foreach ( $field_keys as $field_key ) {
                        if ( strstr( $field_key, $new['key'] ) ) {
                            break;
                        }
                    }
                }

                $offset ++;

                // Add dark mode field
                acf_add_local_field( $new, true );
                $acf_get_field = acf_get_field( $new['key'] );

                // Insert dark mode field after wysiwyg field
                array_splice( $fields, $key + $offset, 0, array( $acf_get_field ) );

            }

            return $fields;
        }

        /**
         * Add dark mode field
         *
         * @param $field
         *
         * @return mixed
         */
        public function load_field_dark_mode( $field ) {

            // Get filters data
            $new = $this->get_dark_mode_field( $field );

            // Add dark mode field
            acf_add_local_field( $new );

            return $field;
        }

        /**
         * Remove hard coded TinyMCE style
         *
         * @param $init
         *
         * @return mixed
         */
        public function remove_tiny_mce_style( $init ) {

            $init['init_instance_callback'] = ''
                                              . 'function(){'
                                              . '    jQuery(".acf-field-wysiwyg > .acf-input iframe").contents().find("link[href*=\'content.min.css\']").remove();'
                                              . '}';

            return $init;
        }

    }

    acf_new_instance( 'PIP_TinyMCE' );

}

/**
 * Get Pilo'Press fonts
 *
 * @return array
 */
function pip_get_fonts() {

    $pip_tinymce = acf_get_instance( 'PIP_TinyMCE' );

    return $pip_tinymce->get_custom_fonts();
}

/**
 * Get Pilo'Press colors
 *
 * @return array
 */
function pip_get_colors() {

    $pip_tinymce = acf_get_instance( 'PIP_TinyMCE' );

    return $pip_tinymce->get_custom_colors();
}

/**
 * Get Pilo'Press buttons
 *
 * @return array
 */
function pip_get_buttons() {

    $pip_tinymce = acf_get_instance( 'PIP_TinyMCE' );

    return $pip_tinymce->get_custom_buttons();
}

/**
 * Get Pilo'Press typography
 *
 * @return array
 */
function pip_get_typography() {

    $pip_tinymce = acf_get_instance( 'PIP_TinyMCE' );

    return $pip_tinymce->get_custom_typography();
}
