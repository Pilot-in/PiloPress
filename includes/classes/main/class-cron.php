<?php

defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'PIP_Cron' ) ) {

    /**
     * Class PIP_Cron
     */
    class PIP_Cron {

        /**
         * PIP_Cron constructor.
         */
        public function __construct() {
            add_action( 'pip_delete_layouts_zip', array( $this, 'delete_old_zip' ) );
        }

        public function delete_old_zip() {

            acf_log( 'go' );

            $layouts_folder = scandir( PIP_THEME_LAYOUTS_PATH );

            if ( !$layouts_folder ) {
                acf_log( 'Pilo\'Press --> Can\'t find the layouts folder' );

                return;
            }

            // Scan all layouts folder
            foreach ( $layouts_folder as $layout_folder ) {

                // Generate layout path from folder name
                $layout_folder_path = PIP_THEME_LAYOUTS_PATH . $layout_folder;

                // Delete old .zip archive
                if ( preg_match( '/^.*\.(zip)$/', $layout_folder_path, $match ) ) {
                    if ( $match[1] === 'zip' ) {
                        unlink( $layout_folder_path );
                    }
                }

                // Skip Folders
                if ( $layout_folder === '.' || $layout_folder === '..' || $layout_folder === '.gitkeep' || !is_dir( $layout_folder_path ) ) {
                    continue;
                }

                // List files in each layout folder
                foreach ( scandir( $layout_folder_path ) as $k => $file ) {
                    // Focus on .json files
                    if ( preg_match( '/^.*\.(zip)$/', $file, $match ) ) {
                        if ( $match[1] === 'zip' ) {
                            unlink( PIP_THEME_LAYOUTS_PATH . $layout_folder . '/' . scandir( $layout_folder_path )[ $k ] );
                        }
                    }
                }
            }
        }

    }


    // Instantiate
    new PIP_Cron();
}
