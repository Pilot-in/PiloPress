<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( !class_exists( 'PIP_Options_Pages' ) ) {

    /**
     * Class PIP_Options_Pages
     */
    class PIP_Options_Pages {

        public function __construct() {

            // WP hooks
            add_action( 'init', array( $this, 'custom_image_sizes' ) );
            add_filter( 'image_size_names_choose', array( $this, 'custom_image_sizes_names' ) );

            // ACF hooks
            add_filter( 'acf/load_value/name=pip_typography', array( $this, 'pre_populate_typography' ), 10, 3 );
            add_filter( 'acf/load_value/name=pip_screens', array( $this, 'pre_populate_screens' ), 10, 3 );
            add_filter( 'acf/load_field/name=pip_native_colors_in_editor', array( $this, 'pre_populate_native_colors_choices' ) );
            add_filter( 'acf/load_value/name=pip_native_colors_in_editor', array( $this, 'pre_populate_native_colors_values' ), 10, 3 );
            add_filter( 'acf/load_value/name=pip_wp_image_sizes', array( $this, 'pre_populate_wp_image_sizes' ), 10, 3 );
            add_filter( 'acf/prepare_field/name=pip_wp_image_sizes', array( $this, 'configure_wp_image_sizes' ) );
            add_action( 'acf/save_post', array( $this, 'save_wp_image_sizes' ), 20, 1 );
        }

        /**
         * Pre-populate repeater with h1 to h6 tags
         *
         * @param $value
         * @param $post_id
         * @param $field
         *
         * @return mixed
         */
        public function pre_populate_typography( $value, $post_id, $field ) {

            // If value has been modified, return
            if ( $value ) {
                return $value;
            }

            // Add default values
            $new_values = array();
            for ( $i = 1; $i <= 6; $i ++ ) {
                $new_values[] = array(
                    'field_typography_label'            => __( 'Title', 'pilopress' ) . ' ' . $i,
                    'field_typography_class_name'       => 'h' . $i,
                    'field_typography_classes_to_apply' => '',
                );
            }

            // Return default values
            return $new_values;
        }

        /**
         * Pre-populate repeater with default screens
         *
         * @param $value
         * @param $post_id
         * @param $field
         *
         * @return mixed
         */
        public function pre_populate_screens( $value, $post_id, $field ) {

            // If value has been modified, return
            if ( $value ) {
                return $value;
            }

            // Return default values
            return array(
                array(
                    'field_screen_name'  => 'sm',
                    'field_screen_value' => '640px',
                ),
                array(
                    'field_screen_name'  => 'md',
                    'field_screen_value' => '768px',
                ),
                array(
                    'field_screen_name'  => 'lg',
                    'field_screen_value' => '1024px',
                ),
                array(
                    'field_screen_name'  => 'xl',
                    'field_screen_value' => '1280px',
                ),
                array(
                    'field_screen_name'  => '2xl',
                    'field_screen_value' => '1536px',
                ),
            );
        }

        /**
         * Pre-populate select with all Tailwind native colors
         *
         * @param $field
         *
         * @return mixed
         */
        public function pre_populate_native_colors_choices( $field ) {
            // Reset choices
            $field['choices'] = array();

            // Add native tailwind colors as choices
            $field['choices'] = pip_get_tailwind_native_colors( true );

            return $field;
        }

        /**
         * Select all Tailwind native colors
         *
         * @param $value
         * @param $post_id
         * @param $field
         *
         * @return array|string[]
         */
        public function pre_populate_native_colors_values( $value, $post_id, $field ) {

            // If value has been modified, return
            if ( $value || empty( $value ) ) {
                return $value;
            }

            // Select all tailwind native colors
            return pip_get_tailwind_native_colors( false, true );
        }

        /**
         * Pre-populate repeater with WP native image sizes
         *
         * @param $value
         * @param $post_id
         * @param $field
         *
         * @return mixed
         */
        public function pre_populate_wp_image_sizes( $value, $post_id, $field ) {

            $pip_tinymce = acf_get_instance( 'PIP_TinyMCE' );

            $image_sizes = array();
            $fields      = array();
            $new_values  = array();

            // Get only WP image sizes
            $all_image_sizes        = $pip_tinymce->get_all_image_sizes();
            $additional_image_sizes = wp_get_additional_image_sizes();
            if ( $additional_image_sizes ) {
                foreach ( $additional_image_sizes as $key => $additional_image_size ) {
                    unset( $all_image_sizes[ $key ] );
                }
            }

            // Format image sizes array
            $i = 0;
            if ( !empty( $all_image_sizes ) ) {
                foreach ( $all_image_sizes as $key => $image_size ) {
                    $image_sizes[ $i ]['name']   = $key;
                    $image_sizes[ $i ]['width']  = $image_size['width'];
                    $image_sizes[ $i ]['height'] = $image_size['height'];
                    $image_sizes[ $i ]['crop']   = $image_size['crop'];
                    $i ++;
                }
            }

            // Get sub fields keys
            $sub_fields = acf_get_fields( $field );
            if ( $sub_fields ) {
                foreach ( $sub_fields as $sub_field ) {
                    $fields[ $sub_field['name'] ] = $sub_field['key'];
                }
            }

            // Set new values
            if ( $image_sizes ) {
                foreach ( $image_sizes as $image_key => $image_size ) {
                    if ( $image_size ) {
                        foreach ( $image_size as $key => $value ) {
                            $new_values[ $image_key ][ $fields[ $key ] ] = $value;
                        }
                    }
                }
            }

            return $new_values;
        }

        /**
         * Set max and min for wp_image_sizes field
         *
         * @param $field
         *
         * @return mixed
         */
        public function configure_wp_image_sizes( $field ) {

            $value = acf_maybe_get( $field, 'value' );
            // Set min and max for wp_image_sizes
            if ( $value ) {
                $field['min'] = count( $value );
                $field['max'] = count( $value );
            }

            return $field;
        }

        /**
         * Register custom image sizes
         */
        public function custom_image_sizes() {

            // Get custom sizes
            $custom_sizes = get_field( 'pip_image_sizes', 'pip_styles_image_sizes' );
            if ( !is_array( $custom_sizes ) ) {
                return;
            }

            // Register custom sizes
            foreach ( $custom_sizes as $size ) {
                add_image_size( $size['name'], $size['width'], $size['height'], $size['crop'] );
            }
        }

        /**
         * Add custom image sizes names
         *
         * @param $size_names
         *
         * @return mixed
         */
        public function custom_image_sizes_names( $size_names ) {

            // Get custom sizes
            $custom_sizes = get_field( 'pip_image_sizes', 'pip_styles_image_sizes' );
            if ( !is_array( $custom_sizes ) ) {
                return $size_names;
            }

            // Add custom sizes names
            foreach ( $custom_sizes as $size ) {
                $size_names[ $size['name'] ] = __( $size['name'], 'pilopress' );
            }

            return $size_names;
        }

        /**
         * Save WP image sizes
         *
         * @param $post_id
         */
        public function save_wp_image_sizes( $post_id ) {

            // If not on Styles admin page, return
            if ( !pip_str_starts( $post_id, 'pip_styles_' ) ) {
                return;
            }

            // Get posted values
            $posted_values = acf_maybe_get_POST( 'acf' );
            if ( !$posted_values ) {
                return;
            }

            // Browse values
            foreach ( $posted_values as $key => $posted_value ) {
                $field = acf_get_field( $key );

                // If not WP image sizes, continue
                if ( $field['name'] !== 'pip_wp_image_sizes' ) {
                    continue;
                }

                // If no value, return
                if ( !$posted_value ) {
                    continue;
                }

                // Browse each repeater values
                foreach ( $posted_value as $image_key => $image_size ) {

                    // Format posted value array
                    foreach ( $image_size as $field_key => $value ) {
                        $image_field = acf_get_field( $field_key );
                        unset( $image_size[ $field_key ] );
                        $image_size[ $image_field['name'] ] = $value;
                    }

                    // Update values
                    update_option( $image_size['name'] . '_size_w', $image_size['width'] );
                    update_option( $image_size['name'] . '_size_h', $image_size['height'] );
                    update_option( $image_size['name'] . '_crop', $image_size['crop'] );
                }
            }
        }

    }

    acf_new_instance( 'PIP_Options_Pages' );

}

/**
 * Get Pilo'Press modules
 *
 * @return mixed
 */
function pip_get_modules() {

    return get_field( 'pip_modules', 'pip_styles_modules' );
}
