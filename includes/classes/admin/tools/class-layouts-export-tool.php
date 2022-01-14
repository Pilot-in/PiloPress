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
            $this->title = __( 'Export Layouts', 'pilopress' );

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
         * Get layouts
         *
         * @return int[]|WP_Post[]|null
         */
        private function get_layouts() {
            $args = array(
                'post_type'        => 'acf-field-group',
                'posts_per_page'   => - 1,
                'pip_post_content' => array(
                    'compare' => 'LIKE',
                    'value'   => 's:14:"_pip_is_layout";i:1',
                ),
            );

            $query = new WP_Query( $args );

            return $query->have_posts() ? $query->get_posts() : null;
        }

        /**
         * HTML for archive page
         */
        public function html_archive() {

            // If no layouts, return
            if ( !$this->get_layouts() ) { ?>

                <div class="acf-fields">
                    <div style="padding:15px 12px;">
                        <?php _e( 'No layouts available.', 'pilopress' ); ?>
                    </div>
                </div>

                <?php
                return;
            }

            // Store choices
            $choices = array();
            foreach ( $this->get_layouts() as $layout ) {
                $layout      = acf_get_field_group( $layout );
                $auto_sync   = pip_maybe_get( $layout, 'acfe_autosync' );
                $layout_slug = pip_maybe_get( $layout, '_pip_layout_slug' );

                // If no layout folder, skip
                if ( !file_exists( PIP_THEME_LAYOUTS_PATH . $layout_slug ) ) {
                    continue;
                }

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

                } else { // No choice ?>

                    <div class="acf-fields">
                        <div style="padding:15px 12px;">
                            <?php _e( 'No layouts available.', 'pilopress' ); ?>
                        </div>
                    </div>

                    <?php
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
                            'folder_slug' => $layout_folder,
                        );
                    }
                }
            }

            // If no folder, return
            if ( !$folder_to_zip ) {
                return;
            }

            // New ZIP
            $zip = new ZipArchive();

            // Generate ZIP
            $temp_zip_path = PIP_THEME_LAYOUTS_PATH . '/pilopress-layouts-' . wp_date( 'Y-m-d' ) . '.zip';
            $temp_zip_url  = PIP_THEME_LAYOUTS_URL . '/pilopress-layouts-' . wp_date( 'Y-m-d' ) . '.zip';
            $zip_created   = $zip->open( $temp_zip_path, ZipArchive::CREATE );

            // If ZIP not created, return
            if ( !$zip_created ) {
                return;
            }

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

            // Close archive
            $zip->close();

            // File headers
            if ( $temp_zip_path && $temp_zip_url && file_exists( $temp_zip_path ) ) {
                header( 'Location: ' . $temp_zip_url );
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
