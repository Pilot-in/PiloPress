<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

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

            // Get choices
            $choices = array();
            if ( $pip_layouts->get_layouts() ) {
                foreach ( $pip_layouts->get_layouts() as $key => $layout ) {
                    // Skip _layout_model
                    if ($layout['title'] === '_layout_model'):
                        continue;
                    endif;
                    // Store choice
                    $choices[ $layout['key'] ] = esc_html( $layout['title'] );
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
                    _e( 'No layouts available.' );
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
         * @return ACF_Admin_Notice
         */
        public function submit_download() {

            // Get selected keys
            $keys = $this->get_selected_keys();

            // If no keys, show warning message
            if ( $keys === false ) {
                return acf_add_admin_notice( __( 'No layout selected', 'pilopress' ), 'warning' );
            }

            if ( count( $keys ) === 1 ) :
                foreach ( $keys as $key ) :
                    $file_to_search = $key . '.json';
                    $layouts_folder = scandir( PIP_THEME_LAYOUTS_PATH );

                    if ( !$layouts_folder ) :
                        acf_log( 'Pilo\'Press --> Can\'t find the layouts folder' );
                        return;
                    endif;

                    // Scan all layouts folder
                    $folder_to_zip = false;
                    foreach ( $layouts_folder as $layout_folder ) :

                        // Generate layout path from folder name
                        $layout_folder_path = PIP_THEME_LAYOUTS_PATH . $layout_folder;

                        if ( $layout_folder === '.' || $layout_folder === '..' || $layout_folder === '.gitkeep' || !is_dir( $layout_folder_path ) ) :
                            continue;
                        endif;

                        // List files in each layout folder
                        foreach ( scandir( $layout_folder_path ) as $file ) :
                            // Focus on .json files
                            if ( preg_match( '/^.*\.(json)$/', $file, $match ) ) :
                                if ( $match[1] === 'json' && $file === $file_to_search ) :
                                    $folder_to_zip = array(
                                        'folder_path' => dirname( $layout_folder_path . '/' . $file ),
                                        'zip_url'     => PIP_THEME_LAYOUTS_URL . $layout_folder . '/' . $layout_folder . '.zip',
                                        'zip_path'    => PIP_THEME_LAYOUTS_PATH . $layout_folder . '/' . $layout_folder . '.zip',
                                        'zip_name'    => $layout_folder . '.zip',
                                    );
                                endif;
                            endif;
                        endforeach;

                    endforeach;

                    $zip         = new ZipArchive();
                    $zip_created = $zip->open( $folder_to_zip['zip_path'], ZipArchive::CREATE );
                    if ( !$zip_created ) :
                        acf_log( 'Pilo\'Press --> Can\'t generate .zip layout folder' );
                        return;
                    endif;

                    // Add each file in folder
                    foreach ( glob( $folder_to_zip['folder_path'] . '/*' ) as $file ) :
                        $new_filename = substr( $file, strrpos( $file, '/' ) + 1 );
                        $zip->addFile( $file, $new_filename );
                    endforeach;

                    // Close archive
                    $zip->close();

                    // File headers
                    if ( file_exists( $folder_to_zip['zip_path'] ) ) :
                        header( 'Location: ' . $folder_to_zip['zip_url'] );
                    endif;
                endforeach;
            else :
                $folder_to_zip = array();
                foreach ( $keys as $key ) :

                    $file_to_search = $key . '.json';
                    $layouts_folder = scandir( PIP_THEME_LAYOUTS_PATH );

                    if ( !$layouts_folder ) :
                        acf_log( 'Pilo\'Press --> Can\'t find the layouts folder' );
                        return;
                    endif;

                    // Scan all layouts folder
                    foreach ( $layouts_folder as $layout_folder ) :

                        // Generate layout path from folder name
                        $layout_folder_path = PIP_THEME_LAYOUTS_PATH . $layout_folder;

                        if ( $layout_folder === '.' || $layout_folder === '..' || $layout_folder === '.gitkeep' || !is_dir( $layout_folder_path ) ) :
                            continue;
                        endif;

                        // List files in each layout folder
                        foreach ( scandir( $layout_folder_path ) as $file ) :
                            // Focus on .json files
                            if ( preg_match( '/^.*\.(json)$/', $file, $match ) ) :
                                if ( $match[1] === 'json' && $file === $file_to_search ) :
                                    $folder_to_zip[] = array(
                                        'folder_path' => dirname( $layout_folder_path . '/' . $file ),
                                        'folder_slug' => $layout_folder,
                                        'zip_url'     => PIP_THEME_LAYOUTS_URL . $layout_folder . '/' . $layout_folder . '.zip',
                                        'zip_path'    => PIP_THEME_LAYOUTS_PATH . $layout_folder . '/' . $layout_folder . '.zip',
                                        'zip_name'    => $layout_folder . '.zip',
                                    );
                                endif;
                            endif;
                        endforeach;

                    endforeach;
                endforeach;
                if ( $folder_to_zip ) :
                    $zip           = new ZipArchive();
                    $temp_zip_path = PIP_THEME_LAYOUTS_PATH . '/layouts-' . wp_date('U') . '.zip';
                    $temp_zip_url  = PIP_THEME_LAYOUTS_URL . '/layouts-' . wp_date('U') . '.zip';
                    $zip_created   = $zip->open( $temp_zip_path, ZipArchive::CREATE );
                    if ( !$zip_created ) :
                        acf_log( 'Pilo\'Press --> Can\'t generate .zip layout folder' );
                        return;
                    endif;
                    foreach ( $folder_to_zip as $folder ) :
                        $zip->addEmptyDir( $folder['folder_slug'] );

                        // Add each file in folder
                        foreach ( glob( $folder['folder_path'] . '/*' ) as $file ) :
                            $new_filename = substr( $file, strrpos( $file, '/' ) + 1 );
                            $zip->addFile( $file, $folder['folder_slug'] . '/' . $new_filename );
                        endforeach;
                    endforeach;

                    // Close archive
                    $zip->close();

                    // File headers
                    if ( file_exists( $temp_zip_path ) ) :
                        header( 'Location: ' . $temp_zip_url );
                    endif;
                endif;

            endif;

        }

        /**
         * Get selected keys
         *
         * @return array|bool
         */
        public function get_selected_keys() {

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
            $selected = $this->get_selected_keys();

            // If no keys, return
            if ( !$selected ) {
                return;
            }

            // Add notice
            $count = count( $selected );
            // translators: Number of layouts exported
            $text = sprintf( _n( 'Exported %s layouts.', 'Exported %s layouts.', $count, 'pilopress' ), $count );
            acf_add_admin_notice( $text, 'success' );
        }

    }

    // Initialize
    acf_register_admin_tool( 'PIP_Layouts_Export_Tool' );
}
