<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

// Check setting
if ( !acf_get_setting( 'pip/options/single_meta' ) ) {
    return;
}

// Register store
acf_register_store( 'pip/options/meta' )->prop( 'multisite', true );

if ( !class_exists( 'PIP_Options_Single_Meta' ) ) {

    /**
     * Class PIP_Options_Single_Meta
     */
    class PIP_Options_Single_Meta {

        public function __construct() {

            // Load
            add_filter( 'acf/load_value', array( $this, 'load_value' ), 0, 3 );
            add_filter( 'acf/pre_load_metadata', array( $this, 'load_reference' ), 10, 4 );

            // Save
            add_filter( 'acf/update_value', array( $this, 'update_value' ), 999, 3 );
            add_action( 'acf/save_post', array( $this, 'save_post' ), 999 );
        }

        /**
         * Update values in same option
         *
         * @param $value
         * @param $post_id
         * @param $field
         *
         * @return null
         */
        public function update_value( $value, $post_id, $field ) {

            // If not style options, return
            if ( !pip_str_starts( $post_id, 'pip_styles' ) ) {
                return $value;
            }

            $is_save_post = false;

            // Submitting acf/save_post
            if ( acf_maybe_get_POST( 'acf' ) ) {
                $is_save_post = true;
            }

            // Get store
            $store = acf_get_store( 'pip/options/meta' );

            if ( $store->has( "$post_id:pip_styles" ) ) {
                // Store found

                // Get Store: PIP meta
                $pip_styles = $store->get( "$post_id:pip_styles" );

            } else {
                // Store not found

                // Get PIP meta
                $pip_styles = get_option( 'pip_styles', array() );

                // Set Store: PIP meta
                $store->set( "$post_id:pip_styles", $pip_styles );

            }

            // Build pip_styles array
            $pip_styles[ $post_id ][ '_' . $field['name'] ] = $field['key'];
            $pip_styles[ $post_id ][ $field['name'] ]       = $value;

            // Single field update: Save to PIP meta
            if ( !$is_save_post ) {
                $pip_styles = wp_unslash( $pip_styles );
                $autoload   = (bool) acf_get_setting( 'autoload' );
                update_option( 'pip_styles', $pip_styles, $autoload );
            }

            // Set Store: PIP meta
            $store->set( "$post_id:pip_styles", $pip_styles );

            // Save individually
            if ( acf_maybe_get( $field, 'pip_save_meta' ) ) {
                return $value;
            }

            // Do not save meta
            return null;
        }

        /**
         * Save meta
         *
         * @param int $post_id
         */
        public function save_post( $post_id = 0 ) {

            // If not style options, return
            if ( !pip_str_starts( $post_id, 'pip_styles' ) ) {
                return;
            }

            // Check store.
            $store = acf_get_store( 'pip/options/meta' );

            // Store found
            if ( !$store->has( "$post_id:pip_styles" ) ) {
                return;
            }

            // Get Store: PIP meta
            $pip_styles = $store->get( "$post_id:pip_styles" );

            // Save to PIP meta
            $pip_styles = wp_unslash( $pip_styles );
            $autoload   = (bool) acf_get_setting( 'autoload' );
            update_option( 'pip_styles', $pip_styles, $autoload );
        }

        /**
         * Load values from custom meta
         *
         * @param $value
         * @param $post_id
         * @param $field
         *
         * @return mixed
         */
        public function load_value( $value, $post_id, $field ) {

            // If not style options, return
            if ( !pip_str_starts( $post_id, 'pip_styles' ) ) {
                return $value;
            }

            // Value already exists
            if ( ( !empty( $value ) || is_numeric( $value ) ) && acf_maybe_get( $field, 'default_value' ) !== $value ) {
                return $value;
            }

            // Check store.
            $store = acf_get_store( 'pip/options/meta' );

            if ( $store->has( "$post_id:pip_styles" ) ) {
                // Store found

                // Get Store: PIP meta
                $pip_styles = $store->get( "$post_id:pip_styles" );

            } else {
                // Store not found

                // Get PIP meta
                $pip_styles = get_option( 'pip_styles', array() );

                // Set Store: PIP meta
                $store->set( "$post_id:pip_styles", $pip_styles );

            }

            // If empty, return
            if ( empty( $pip_styles ) ) {
                return $value;
            }

            // Get field name
            $field_name = $field['name'];

            // If set, get value from custom meta
            if ( isset( $pip_styles[ $post_id ][ $field_name ] ) ) {
                $value = $pip_styles[ $post_id ][ $field_name ];
            }

            return $value;
        }

        /**
         * Load values from custom meta
         *
         * @param $value
         * @param $post_id
         * @param $name
         * @param $hidden
         *
         * @return mixed
         */
        public function load_reference( $value, $post_id, $name, $hidden ) {

            // If not style options, return
            if ( !pip_str_starts( $post_id, 'pip_styles' ) ) {
                return $value;
            }

            // If not hidden, return
            if ( !$hidden ) {
                return $value;
            }

            // Check store
            $store = acf_get_store( 'pip/options/meta' );

            if ( $store->has( "$post_id:pip_styles" ) ) {
                // Store found

                // Get Store: PIP meta
                $pip_styles = $store->get( "$post_id:pip_styles" );
            } else {
                // Store not found

                // Get PIP meta
                $pip_styles = get_option( 'pip_styles', array() );

                // Set Store: PIP meta
                $store->set( "$post_id:pip_styles", $pip_styles );
            }

            // If empty, return
            if ( empty( $pip_styles ) ) {
                return $value;
            }

            // If set, get value from custom meta
            if ( isset( $pip_styles[ $post_id ]["_{$name}"] ) ) {
                $value = $pip_styles[ $post_id ]["_{$name}"];
            }

            return $value;
        }

    }

    acf_new_instance( 'PIP_Options_Single_Meta' );

}
