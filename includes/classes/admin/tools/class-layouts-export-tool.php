<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'PIP_Layouts_Export_Tool' ) ) {

    /**
     * Class PIP_Layouts_Export_Tool
     */
    class PIP_Layouts_Export_Tool extends ACF_Admin_Tool {

        /**
         * View context
         *
         * @var string
         */
        public $view = '';

        /**
         * Export data
         *
         * @var string
         */
        public $json = '';

        /**
         *  Initialize
         */
        public function initialize() {

            $this->name  = 'pilopress_tool_layouts_export';
            $this->title = __( 'Export layouts', 'pilopress' );

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

            $pip_layouts = acf_get_instance( 'PIP_Layouts' );

            // If no layouts, return
            if ( !$pip_layouts->get_layouts() ) {
                return;
            }

            // Store choices
            $choices = array();
            foreach ( $pip_layouts->get_layouts() as $layout ) {
                $auto_sync = acf_maybe_get( $layout, 'acfe_autosync' );

                // If layout is not sync via JSON, skip
                if ( !$auto_sync || !in_array( 'json', $auto_sync, true ) ) {
                    continue;
                }

                $choices[ $layout['key'] ] = esc_html( $layout['title'] );
            }

            // Get selected layouts
            $selected = $this->get_selected_layouts();

            // If no choice, disabled action
            $disabled = empty( $choices ) ? 'disabled="disabled"' : '';
            ?>
            <div class="acf-fields">
                <?php

                if ( !empty( $choices ) ) {

                    // Render
                    acf_render_field_wrap(
                        array(
                            'label'   => __( 'Select layouts', 'pilopress' ),
                            'type'    => 'checkbox',
                            'name'    => 'keys',
                            'prefix'  => false,
                            'value'   => $selected,
                            'toggle'  => true,
                            'choices' => $choices,
                        )
                    );

                } else {

                    // No choice
                    echo '<div style="padding:15px 12px;">';
                    _e( 'No layouts available.', 'pilopress' );
                    echo '</div>';

                }

                ?>
            </div>
            <p class="acf-submit">
                <button type="submit" name="action" class="button button-primary" value="download" <?php echo $disabled; ?>><?php _e( 'Export File', 'acf' ); ?></button>
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
         * Download layouts data
         *
         * @return ACF_Admin_Notice|void
         */
        public function submit_download() {

            // Get keys of selected layouts
            $keys = $this->get_selected_layouts();

            // If no keys, show warning message
            if ( !$keys ) {
                return acf_add_admin_notice( __( 'No layout selected', 'pilopress' ), 'warning' );
            }

            // Check if user has selected more than 1 layout
            $several_layouts = count( $keys ) > 1;

            $folder_to_zip = array();
            foreach ( $keys as $key ) {

                $file_to_search = $key . '.json';
                $layouts_folder = scandir( PIP_THEME_LAYOUTS_PATH );

                // If no layout folder, return
                if ( !$layouts_folder ) {
                    return;
                }

                // Scan all layouts folder
                foreach ( $layouts_folder as $key => $layout_folder ) {

                    // Generate layout path from folder name
                    $layout_folder_path = PIP_THEME_LAYOUTS_PATH . $layout_folder;

                    // If not a real folder, return
                    if ( $layout_folder === '.' || $layout_folder === '..' || $layout_folder === '.gitkeep' || !is_dir( $layout_folder_path ) ) {
                        continue;
                    }

                    // List files in each layout folder
                    foreach ( scandir( $layout_folder_path ) as $file ) {

                        // If no JSON file, skip
                        if ( !preg_match( '/^.*\.(json)$/', $file, $match ) ) {
                            continue;
                        }
                        $extension = acf_maybe_get( $match, 1 );

                        // If not a JSON file and the one we are looking for, skip
                        if ( $extension !== 'json' || $file !== $file_to_search ) {
                            continue;
                        }

                        // Store current layout
                        $folder_to_zip[ $key ] = array(
                            'folder_path' => dirname( $layout_folder_path . '/' . $file ),
                            'zip_url'     => PIP_THEME_LAYOUTS_URL . $layout_folder . '/' . $layout_folder . '.zip',
                            'zip_path'    => PIP_THEME_LAYOUTS_PATH . $layout_folder . '/' . $layout_folder . '.zip',
                            'zip_name'    => $layout_folder . '.zip',
                        );

                        // If several layouts to export, store current layout folder name
                        if ( $several_layouts ) {
                            $folder_to_zip[ $key ]['folder_slug'] = $layout_folder;
                        }
                    }
                }
            }

            // If no folder, return
            if ( !$folder_to_zip ) {
                return;
            }

            // New ZIP
            $zip = new ZipArchive();

            // Initialize variable for export if several layouts
            $temp_zip_path = '';
            $temp_zip_url  = '';

            // Generate ZIP
            if ( $several_layouts ) {
                // Create a dummy ZIP name
                $temp_zip_path = PIP_THEME_LAYOUTS_PATH . '/layouts-' . wp_date( 'U' ) . '.zip';
                $temp_zip_url  = PIP_THEME_LAYOUTS_URL . '/layouts-' . wp_date( 'U' ) . '.zip';
                $zip_created   = $zip->open( $temp_zip_path, ZipArchive::CREATE );
            } else {
                // Only one layout, reset array to get layout name
                $layout_to_export = acf_unarray( $folder_to_zip );
                $zip_created      = $zip->open( $layout_to_export['zip_path'], ZipArchive::CREATE );
            }

            // If ZIP not created, return
            if ( !$zip_created ) {
                return;
            }

            if ( $several_layouts ) {

                // Browse layouts to export
                foreach ( $folder_to_zip as $folder ) {

                    // Create sub-folder with layout name
                    $zip->addEmptyDir( $folder['folder_slug'] );

                    // Get layout files
                    $layout_files = glob( $folder['folder_path'] . '/*' );
                    if ( !$layout_files ) {
                        continue;
                    }

                    // Add each file in corresponding layout folder
                    foreach ( $layout_files as $layout_file ) {
                        $new_filename = substr( $layout_file, strrpos( $layout_file, '/' ) + 1 );
                        $zip->addFile( $layout_file, $folder['folder_slug'] . '/' . $new_filename );
                    }
                }
            } else {

                // Get layout files
                $layout_files = glob( $layout_to_export['folder_path'] . '/*' );
                if ( !$layout_files ) {
                    return;
                }

                // Add each file in folder
                foreach ( $layout_files as $layout_file ) {
                    $new_filename = substr( $layout_file, strrpos( $layout_file, '/' ) + 1 );
                    $zip->addFile( $layout_file, $new_filename );
                }
            }

            // Close archive
            $zip->close();

            // File headers
            if ( $several_layouts ) {
                if ( $temp_zip_path && $temp_zip_url && file_exists( $temp_zip_path ) ) {
                    header( 'Location: ' . $temp_zip_url );
                }
            } else {
                if ( file_exists( $layout_to_export['zip_path'] ) ) {
                    header( 'Location: ' . $layout_to_export['zip_url'] );
                }
            }
        }

        /**
         * Get keys of selected layouts
         *
         * @return array|bool
         */
        public function get_selected_layouts() {

            // Check $_POST
            $keys = acf_maybe_get_POST( 'keys' );
            if ( $keys ) {
                return (array) $keys;
            }

            // Check $_GET
            $keys = acf_maybe_get_GET( 'keys' );
            if ( $keys ) {
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
            $selected = $this->get_selected_layouts();

            // If no keys, return
            if ( !$selected ) {
                return;
            }

            // Add notice
            $count = count( $selected );

            // translators: Number of layouts exported
            $text = sprintf( _n( '%s layouts exported.', '%s layouts exported.', $count, 'pilopress' ), $count );

            acf_add_admin_notice( $text, 'success' );
        }

    }

    // Initialize
    acf_register_admin_tool( 'PIP_Layouts_Export_Tool' );
}
