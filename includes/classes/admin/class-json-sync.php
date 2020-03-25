<?php

if ( !class_exists( 'PIP_Json_Sync' ) ) {
    class PIP_Json_Sync {
        public function __construct() {
            // ACF hooks
            add_filter( 'acf/settings/save_json', array( $this, 'change_save_path' ), 9999 );
            add_filter( 'acf/settings/load_json', array( $this, 'add_layouts_paths' ), 9999 );
        }

        /**
         * Save to custom path
         *
         * @param $path
         *
         * @return string
         */
        public function change_save_path( $path ) {
            $post = get_post();
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
            // Get layouts dirs
            $layouts_dirs = glob( PIP_THEME_LAYOUTS_PATH . '*', GLOB_ONLYDIR );
            $paths        = array_merge( $paths, $layouts_dirs );

            return $paths;
        }
    }

    // Instantiate class
    new PIP_Json_Sync();
}
