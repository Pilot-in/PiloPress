<?php

if ( !class_exists( 'PIP_Layouts_Sync' ) ) {

    /**
     * Class PIP_Layouts_Sync
     */
    class PIP_Layouts_Sync {

        public function __construct() {

            // Save
            add_filter( 'acfe/settings/json_save/all', array( $this, 'save_path' ), 10, 2 );
            add_filter( 'acfe/settings/php_save/all', array( $this, 'save_path' ), 10, 2 );

            // Load
            add_filter( 'acfe/settings/json_load', array( $this, 'load_path' ) );
            add_filter( 'acfe/settings/php_load', array( $this, 'load_path' ) );

        }

        /**
         * Save path
         *
         * @param $path
         * @param $field_group
         *
         * @return string
         */
        public function save_path( $path, $field_group ) {

            if ( !pip_is_layout( $field_group ) ) {
                return $path;
            }

            return PIP_THEME_LAYOUTS_PATH . $field_group['_pip_layout_slug'];
        }

        /**
         * Load path
         *
         * @param $path
         *
         * @return array
         */
        public function load_path( $path ) {

            $path = (array) $path;

            // Get layouts dirs
            $layouts_dirs = glob( PIP_THEME_LAYOUTS_PATH . '*', GLOB_ONLYDIR );

            return array_merge( $path, $layouts_dirs );

        }

    }

    acf_new_instance( 'PIP_Layouts_Sync' );

}
