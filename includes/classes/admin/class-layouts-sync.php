<?php

if ( !class_exists( 'PIP_Layouts_Sync' ) ) {

    /**
     * Class PIP_Layouts_Sync
     */
    class PIP_Layouts_Sync {
        public function __construct() {
            // ACF hooks
            add_filter( 'acf/settings/save_json', array( $this, 'change_save_path' ), 9999 );
            add_filter( 'acf/settings/load_json', array( $this, 'add_layouts_paths' ), 9999 );

            // ACFE hooks
            add_filter( 'acf/settings/acfe/php_save', array( $this, 'change_save_path' ) );
            add_filter( 'acf/settings/acfe/php_load', array( $this, 'add_layouts_paths' ) );
        }

        /**
         * Save to custom path
         *
         * @param $path
         *
         * @return string
         */
        public function change_save_path( $path ) {
            $post_id = acf_maybe_get_POST( 'post_ID' );
            $post    = get_post( $post_id );
            if ( !$post ) {
                return $path;
            }

            // Get field group
            $field_group = acf_get_field_group( $post->post_name );

            // If no group, return
            if ( !$field_group ) {
                return $path;
            }

            // If not a layout, return
            if ( !PIP_Layouts::is_layout( $field_group ) ) {
                return $path;
            }

            // If no slug, return
            if ( !acf_maybe_get( $field_group, '_pip_layout_slug' ) ) {
                return $path;
            }

            // If layout folder doesn't exists, return
            if ( !file_exists( PIP_THEME_LAYOUTS_PATH . $field_group['_pip_layout_slug'] ) ) {
                return false;
            }

            return PIP_THEME_LAYOUTS_PATH . $field_group['_pip_layout_slug'];
        }

        /**
         * Add layouts paths
         *
         * @param $paths
         *
         * @return array
         */
        public function add_layouts_paths( $paths ) {
            $paths = is_array( $paths ) ? $paths : array();

            // Get layouts dirs
            $layouts_dirs = glob( PIP_THEME_LAYOUTS_PATH . '*', GLOB_ONLYDIR );
            $paths        = array_merge( $paths, $layouts_dirs );

            return $paths;
        }
    }

    // Instantiate class
    new PIP_Layouts_Sync();
}
