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
            // WP hooks
            add_action( 'pip_delete_layouts_zip', array( $this, 'delete_old_zip' ) );
        }

        /**
         * Delete old ZIP action
         */
        public function delete_old_zip() {

            // Get all layouts sub-folders
            $layouts_folder = scandir( PIP_THEME_LAYOUTS_PATH );

            // If no layout folder, return
            if ( !$layouts_folder ) {
                return;
            }

            // Scan all layouts folder
            foreach ( $layouts_folder as $layout_folder ) {

                // Generate layout path from folder name
                $layout_folder_path = PIP_THEME_LAYOUTS_PATH . $layout_folder;

                // If there's a ZIP file, delete it
                preg_match( '/^.*\.(zip)$/', $layout_folder_path, $folder_zip );
                if ( $folder_zip && $folder_zip[1] === 'zip' ) {
                    unlink( $layout_folder_path );
                }

                // Skip folders
                if ( $layout_folder === '.' || $layout_folder === '..' || $layout_folder === '.gitkeep' || !is_dir( $layout_folder_path ) ) {
                    continue;
                }

                // List files in each layout folder
                $sub_folders = scandir( $layout_folder_path );
                if ( !$sub_folders ) {
                    continue;
                }

                foreach ( $sub_folders as $key => $file ) {

                    // Check if there's a ZIP file
                    preg_match( '/^.*\.(zip)$/', $file, $sub_folder_zip );
                    if ( !$sub_folder_zip ) {
                        continue;
                    }

                    // If ZIP file, delete it
                    if ( $sub_folder_zip[1] === 'zip' ) {
                        unlink( PIP_THEME_LAYOUTS_PATH . $layout_folder . '/' . scandir( $layout_folder_path )[ $key ] );
                    }
                }
            }
        }

    }


    // Instantiate
    new PIP_Cron();
}
