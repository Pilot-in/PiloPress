<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'PIP_Layouts_Import_Tool' ) ) {

    /**
     * Class PIP_Layouts_Import_Tool
     */
    class PIP_Layouts_Import_Tool extends ACF_Admin_Tool {

        /**
         * Initialize
         */
        public function initialize() {

            $this->name  = 'pilopress_tool_layouts_import';
            $this->title = __( 'Import layout', 'pilopress' );

        }

        /**
         * Generate HTML
         */
        public function html() {

            ?>
            <div class="acf-fields">
                <?php
                acf_render_field_wrap(
                    array(
                        'label'    => __( 'Select File', 'acf' ),
                        'type'     => 'file',
                        'name'     => 'acf_import_layouts',
                        'value'    => false,
                        'uploader' => 'basic',
                    )
                );
                ?>
            </div>
            <p class="acf-submit">
                <input type="submit" class="button button-primary" value="<?php _e( 'Import File', 'acf' ); ?>"/>
            </p>
            <?php
        }

        /**
         * Import ZIP
         *
         * @return ACF_Admin_Notice
         */
        public function submit() {

            // Check file size
            if ( empty( $_FILES['acf_import_layouts']['size'] ) ) {
                return acf_add_admin_notice( __( 'No file selected', 'pilopress' ), 'warning' );
            }

            // Get file data
            $file = $_FILES['acf_import_layouts'];

            // Check errors
            if ( $file['error'] ) {
                return acf_add_admin_notice( __( 'Error uploading file. Please try again', 'acf' ), 'warning' );
            }

            // Check file type
            if ( pathinfo( $file['name'], PATHINFO_EXTENSION ) !== 'zip' ) {
                return acf_add_admin_notice( __( 'Incorrect file type', 'acf' ), 'warning' );
            }

            // File data
            $filename        = $file['name'];
            $file_tmp_folder = $file['tmp_name'];
            $layout_slug     = str_replace( '.zip', '/', $filename );
            $layout_exists   = PIP_THEME_LAYOUTS_PATH . $layout_slug;
            $several_layouts = preg_match( '/layouts-\d{10}.zip/', $filename, $test );

            // Check if current layout folder already exists
            if ( realpath( $layout_exists ) && is_dir( $layout_exists ) ) {
                return acf_add_admin_notice( __( 'A layout with this slug already exists.', 'pilopress' ), 'error' );
            }

            // Maybe create tmp folder
            $path = PIP_THEME_LAYOUTS_PATH . 'tmp/';
            if ( !is_dir( $path ) ) {
                mkdir( $path );
            }

            // Move ZIP from local tmp folder to tmp folder inside layouts folder
            $location  = $path . $filename;
            $zip_moved = move_uploaded_file( $file_tmp_folder, $location );
            if ( !$zip_moved ) {
                return acf_add_admin_notice( __( 'An error occurred, please try again later.', 'acf' ), 'error' );
            }

            $extract_to = $several_layouts ? $path : $path . $layout_slug;

            // Unzip
            $zip = new ZipArchive();
            if ( $zip->open( $location ) ) {
                $zip->extractTo( $extract_to );
                $zip->close();
            }

            if ( $several_layouts ) {
                $already_exists_layouts = array();
                $imported_layouts       = 0;
                unlink( $location );
                $sub_folders = scandir( $extract_to );
                $sub_folders = array_diff( $sub_folders, array( '.', '..' ) );

                if ( $sub_folders ) {
                    foreach ( $sub_folders as $sub_folder ) {
                        // Check if current layout folder already exists
                        if ( realpath( PIP_THEME_LAYOUTS_PATH . $sub_folder ) && is_dir( PIP_THEME_LAYOUTS_PATH . $sub_folder ) ) {
                            $already_exists_layouts[] = $sub_folder;
                            $this->rmdir_recursive( $extract_to . $sub_folder );
                            continue;
                        }

                        $imported_layouts ++;
                        rename( $extract_to . $sub_folder, PIP_THEME_LAYOUTS_PATH . $sub_folder );
                    }
                    rmdir( $path );
                }
            } else {
                // Move unzip layout folder from tmp to layouts folder
                rename( $extract_to, $layout_exists );
                unlink( $location );
                rmdir( $path );
            }

            if ( $several_layouts ) {
                if ( count( $already_exists_layouts ) > 0 ) {
                    // Error notice
                    $text = sprintf(
                    // translators: number of layouts which already exists
                        _n(
                            'This layout already exists: %s.',
                            'These layouts already exists: %s.',
                            count( $already_exists_layouts ),
                            'pilopress'
                        ),
                        implode( ', ', $already_exists_layouts )
                    );

                    // Warning notice for layouts already existing
                    acf_add_admin_notice( $text, 'warning' );
                }

                // translators: number of layouts imported
                $success_text = sprintf( _n( '%s layout has been imported.', '%s layouts has been imported.', $imported_layouts, 'pilopress' ), $imported_layouts );

                // Success notice
                return acf_add_admin_notice( $success_text, 'success' );
            } else {
                // Success notice
                return acf_add_admin_notice( __( 'Layout has been imported.', 'acf' ), 'success' );
            }
        }

        /**
         * Remove folder with files and/or sub-folders inside
         *
         * @param $dir
         */
        private function rmdir_recursive( $dir ) {
            foreach ( scandir( $dir ) as $file ) {
                if ( '.' === $file || '..' === $file ) {
                    continue;
                }
                if ( is_dir( "$dir/$file" ) ) {
                    $this->rmdir_recursive( "$dir/$file" );
                } else {
                    unlink( "$dir/$file" );
                }
            }
            rmdir( $dir );
        }

    }

// Initialize
    acf_register_admin_tool( 'PIP_Layouts_Import_Tool' );
}
