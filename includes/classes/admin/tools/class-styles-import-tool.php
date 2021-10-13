<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

if ( !class_exists( 'PIP_Styles_Import_Tool' ) ) {

    /**
     * Class PIP_Styles_Import_Tool
     */
    class PIP_Styles_Import_Tool extends ACF_Admin_Tool {

        /**
         * Initialize
         */
        public function initialize() {

            $this->name  = 'pilopress_tool_styles_import';
            $this->title = __( 'Import styles settings', 'pilopress' );
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
                        'name'     => 'acf_import_styles_configuration',
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
         * Import JSON
         *
         * @return ACF_Admin_Notice
         */
        public function submit() {

            // Check file size
            if ( empty( $_FILES['acf_import_styles_configuration']['size'] ) ) {
                return acf_add_admin_notice( __( 'No file selected', 'pilopress' ), 'warning' );
            }

            // Get file data
            $file = $_FILES['acf_import_styles_configuration'];

            // Check errors
            if ( $file['error'] ) {
                return acf_add_admin_notice( __( 'Error uploading file. Please try again', 'acf' ), 'warning' );
            }

            // Check file type
            if ( pathinfo( $file['name'], PATHINFO_EXTENSION ) !== 'json' ) {
                return acf_add_admin_notice( __( 'Incorrect file type', 'acf' ), 'warning' );
            }

            // Read JSON
            $filesystem = PIP_Main::get_wp_filesystem();
            $json       = $filesystem->get_contents( $file['tmp_name'] );
            $json       = json_decode( $json, true );

            // Check if empty
            if ( !$json || !is_array( $json ) ) {
                return acf_add_admin_notice( __( 'Import file empty', 'acf' ), 'warning' );
            }

            // Update pages
            foreach ( $json as $option_page ) {
                acf_enable_filter( 'local' );
                acf_update_values( $option_page['data'], $option_page['post_id'] );
                acf_disable_filter( 'local' );
            }

            // Count number of imported styles
            $total = count( $json );

            // Add notice
            // translators: Number of style settings imported
            acf_add_admin_notice( sprintf( _n( 'Imported %s style settings.', 'Imported %s styles settings.', $total, 'pilopress' ), $total ), 'success' );
        }

    }

    // Initialize
    acf_register_admin_tool( 'PIP_Styles_Import_Tool' );
}
