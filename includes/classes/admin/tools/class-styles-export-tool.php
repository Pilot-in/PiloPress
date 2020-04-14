<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

if ( !class_exists( 'PIP_Styles_Export_Tool' ) ) {
    class PIP_Styles_Export_Tool extends ACF_Admin_Tool {

        /** @var string View context */
        var $view = '';

        /** @var array Export data */
        var $json = '';

        /**
         *  Initialize
         */
        public function initialize() {
            $this->name  = 'pilopress_tool_styles_export';
            $this->title = __( 'Export styles settings', 'pilopress' );
        }

        /**
         * Generate HTML
         */
        public function html() {
            // Export JSON
            if ( !$this->is_active() ) {
                $this->html_archive();
            }

        }

        /**
         * HTML for archive page
         */
        public function html_archive() {
            // Get styles options
            $styles_options = new PIP_Admin_Options_Page();

            // Get choices
            $choices = array();
            if ( $styles_options->pages ) {
                foreach ( $styles_options->pages as $key => $style_option ) {
                    // Store choice
                    $choices[ $style_option['post_id'] ] = esc_html( $style_option['page_title'] );
                }
            }
            $selected = $this->get_selected_keys();

            // If no choice, disabled action
            $disabled = '';
            if ( empty( $choices ) ) {
                $disabled = 'disabled="disabled"';
            }

            ?>
            <div class="acf-fields">
                <?php

                if ( !empty( $choices ) ) {

                    // Render
                    acf_render_field_wrap( array(
                        'label'   => __( 'Select style pages', 'pilopress' ),
                        'type'    => 'checkbox',
                        'name'    => 'keys',
                        'prefix'  => false,
                        'value'   => $selected,
                        'toggle'  => true,
                        'choices' => $choices,
                    ) );

                } else {

                    // No choice
                    echo '<div style="padding:15px 12px;">';
                    _e( 'No style option available.' );
                    echo '</div>';

                }

                ?>
            </div>
            <p class="acf-submit">
                <button type="submit" name="action" class="button button-primary"
                        value="download" <?php echo $disabled; ?>><?php _e( 'Export File' ); ?></button>
            </p>
            <?php
        }

        /**
         * Submit action
         */
        public function submit() {
            // Get action
            $action = acf_maybe_get_POST( 'action' );

            // Download action
            if ( $action === 'download' ) {
                $this->submit_download();
            }

        }

        /**
         * Download styles data
         * @return ACF_Admin_Notice
         */
        public function submit_download() {
            // Get selected keys
            $keys = $this->get_selected_keys();

            // If no keys, show warning message
            if ( $keys === false ) {
                return acf_add_admin_notice( __( 'No style page selected', 'pilopress' ), 'warning' );
            }

            // Get data
            $data = $this->get_data_for_export( $keys );

            // If no data, show error message
            if ( !$data ) {
                return acf_add_admin_notice( __( 'An error appended. Please try again later.', 'pilopress' ), 'error' );
            }

            // File headers
            $file_name = 'acf-styles-export-' . date( 'Y-m-d' ) . '.json';
            header( "Content-Description: File Transfer" );
            header( "Content-Disposition: attachment; filename={$file_name}" );
            header( "Content-Type: application/json; charset=utf-8" );

            // Return
            echo acf_json_encode( $data );
            die;
        }

        /**
         * Format data for export
         *
         * @param $post_ids
         *
         * @return array
         */
        public function get_data_for_export( $post_ids ) {
            $data = array();

            // Browse selected options
            foreach ( $post_ids as $post_id ) {

                // Get sub fields
                $sub_fields = get_field_objects( $post_id, false );
                if ( !$sub_fields ) {
                    continue;
                }

                // Format values
                $values = array();
                foreach ( $sub_fields as $sub_field ) {
                    $values[ $sub_field['key'] ] = $sub_field['value'];
                }

                // Store data
                $data[] = array(
                    'post_id' => $post_id,
                    'data'    => $values,
                );

            }

            return $data;
        }

        /**
         * Get selected keys
         * @return array|bool
         */
        public function get_selected_keys() {
            // Check $_POST
            if ( $keys = acf_maybe_get_POST( 'keys' ) ) {
                return (array) $keys;
            }

            // Check $_GET
            if ( $keys = acf_maybe_get_GET( 'keys' ) ) {
                $keys = str_replace( ' ', '+', $keys );

                return explode( '+', $keys );
            }

            return false;
        }

        /**
         * Load action
         */
        public function load() {
            // If not active, return
            if ( !$this->is_active() ) {
                return;
            }

            // Get selected keys
            $selected = $this->get_selected_keys();

            // If no keys, return
            if ( !$selected ) {
                return;
            }

            // Add notice
            $count = count( $selected );
            $text  = sprintf( _n( 'Exported 1 style settings.', 'Exported %s styles settings.', $count, 'pilopress' ), $count );
            acf_add_admin_notice( $text, 'success' );
        }

    }

    // Initialize
    acf_register_admin_tool( 'PIP_Styles_Export_Tool' );
}
