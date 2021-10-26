<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

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

            function folder_exist( $folder ) {
                $path = realpath( $folder );
                return ( $path !== false and is_dir( $path ) ) ? $path : false;
            }

            // File informations
            $filename        = $file['name'];
            $file_tmp_folder = $file['tmp_name'];
            $layout_slug     = str_replace( '.zip', '/', $filename );
            $layout_exist    = PIP_THEME_LAYOUTS_PATH . $layout_slug;

            // Check if layout name exist already
            if ( realpath( $layout_exist ) !== false ) :
                if ( is_dir( $layout_exist ) ) :
                    return acf_add_admin_notice( __( 'This layout already exist. Please try another one', 'acf' ), 'error' );
                endif;
            endif;

            $path = PIP_THEME_LAYOUTS_PATH . 'tmp/';
            if ( !is_dir( $path ) ) :
                mkdir( $path );
            endif;
            $location = $path . $filename;
            if ( move_uploaded_file( $file_tmp_folder, $location ) ) :
                $zip = new ZipArchive();
                if ( $zip->open( $location ) ) :
                    $zip->extractTo( $path );
                    $zip->close();
                endif;
                rename( $path . $layout_slug, $layout_exist );
                unlink( $location );
                rmdir( $path );
                return acf_add_admin_notice( __( 'Layout has been imported', 'acf' ), 'success' );
            endif;
        }

    }

    // Initialize
    acf_register_admin_tool( 'PIP_Layouts_Import_Tool' );
}
