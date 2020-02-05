<?php

if ( !class_exists( 'PIP_Field_Groups_Layouts' ) ) {
    class PIP_Field_Groups_Layouts {

        public function __construct() {
            // WP hooks
            add_action( 'current_screen', array( $this, 'current_screen' ) );
        }

        /**
         * Fire actions on acf field groups page
         */
        public function current_screen() {
            // If not ACF field group single, return
            if ( acf_is_screen( 'acf-field-group' ) ) {
                return;
            }

            add_action( 'acf/field_group/admin_head', array( $this, 'layout_meta_boxes' ) );
        }

        /**
         * Pilo'Press meta boxes
         */
        public function layout_meta_boxes() {
            // Get current field group
            global $field_group;

            // If mirror flexible page, don't register meta boxes
            if ( $field_group['key'] === PIP_Field_Groups_Flexible_Mirror::get_flexible_mirror_group_key() ) {
                return;
            }

            // Is current field group a layout ?
            $is_layout = acf_maybe_get( $field_group, '_pip_is_layout' );
            if ( acf_maybe_get_GET( 'layout' ) ) {
                $is_layout = 1;
            }

            // Meta box: Layout settings
            if ( $is_layout ) {
                add_meta_box( 'pip_layout_settings', __( "Pilo'Press: Flexible Layout settings", 'pilopress' ), array(
                    $this,
                    'render_meta_box_main',
                ), 'acf-field-group', 'normal', 'high', array( 'field_group' => $field_group ) );
            }
        }

        /**
         *  Meta box: Main
         *
         * @param $post
         * @param $meta_box
         */
        public function render_meta_box_main( $post, $meta_box ) {
            $field_group = $meta_box['args']['field_group'];

            // Layout settings
            acf_render_field_wrap( array(
                'label'        => '',
                'name'         => '_pip_is_layout',
                'prefix'       => 'acf_field_group',
                'type'         => 'acfe_hidden',
                'instructions' => '',
                'value'        => 1,
                'required'     => false,
            ) );

            // Layout
            $layout_name        = sanitize_title( str_replace( 'Layout: ', '', $field_group['title'] ) );
            $layout_path_prefix = str_replace( home_url() . '/wp-content/themes/', '', _PIP_THEME_STYLE_URL ) . '/layouts/' . $layout_name . '/';

            // Category
            acf_render_field_wrap( array(
                'label'         => __( 'Catégorie', 'pilopress' ),
                'instructions'  => __( 'Nom de catégorie du layout', 'pilopress' ),
                'type'          => 'text',
                'name'          => '_pip_category',
                'prefix'        => 'acf_field_group',
                'default_value' => 'classic',
                'value'         => isset( $field_group['_pip_category'] ) ? $field_group['_pip_category'] : 'Classic',
            ) );

            // Layout
            acf_render_field_wrap( array(
                'label'         => __( 'Layout', 'pilopress' ),
                'instructions'  => __( 'Nom du fichier de layout', 'pilopress' ),
                'type'          => 'text',
                'name'          => '_pip_render_layout',
                'prefix'        => 'acf_field_group',
                'placeholder'   => $layout_name . '.php',
                'default_value' => $layout_name . '.php',
                'prepend'       => $layout_path_prefix,
                'value'         => isset( $field_group['_pip_render_layout'] ) ? $field_group['_pip_render_layout'] : '',
            ) );

            // Style
            acf_render_field_wrap( array(
                'label'         => __( 'Style', 'pilopress' ),
                'instructions'  => __( 'Nom du fichier de style', 'pilopress' ),
                'type'          => 'text',
                'name'          => '_pip_render_style',
                'prefix'        => 'acf_field_group',
                'placeholder'   => $layout_name . '.css',
                'default_value' => $layout_name . '.css',
                'prepend'       => $layout_path_prefix,
                'value'         => isset( $field_group['_pip_render_style'] ) ? $field_group['_pip_render_style'] : '',
            ) );

            // Script
            acf_render_field_wrap( array(
                'label'         => __( 'Script', 'pilopress' ),
                'instructions'  => __( 'Nom du fichier de script', 'pilopress' ),
                'type'          => 'text',
                'name'          => '_pip_render_script',
                'prefix'        => 'acf_field_group',
                'placeholder'   => $layout_name . '.js',
                'default_value' => $layout_name . '.js',
                'prepend'       => $layout_path_prefix,
                'value'         => isset( $field_group['_pip_render_script'] ) ? $field_group['_pip_render_script'] : '',
            ) );

            // Get layouts for configuration field
            $choices = array();
            foreach ( PIP_Field_Groups_Flexible_Mirror::get_layout_group_keys() as $layout_group_key ) {
                // Get current field group
                $group = acf_get_field_group( $layout_group_key );

                // Save title
                $choices[ $group['key'] ] = $group['title'];
            }

            // Configuration
            acf_render_field_wrap( array(
                'label'         => __( 'Configuration', 'pilopress' ),
                'instructions'  => __( 'Clones de configuration', 'pilopress' ),
                'type'          => 'select',
                'name'          => '_pip_configuration',
                'prefix'        => 'acf_field_group',
                'value'         => ( isset( $field_group['_pip_configuration'] ) ? $field_group['_pip_configuration'] : '' ),
                'choices'       => $choices,
                'allow_null'    => 1,
                'multiple'      => 1,
                'ui'            => 1,
                'ajax'          => 0,
                'return_format' => 0,
            ) );

            // Miniature
            acf_render_field_wrap( array(
                'label'         => __( 'Thumbnail', 'pilopress' ),
                'instructions'  => __( 'Aperçu du layout', 'pilopress' ),
                'name'          => '_pip_thumbnail',
                'type'          => 'image',
                'class'         => '',
                'prefix'        => 'acf_field_group',
                'value'         => ( isset( $field_group['_pip_thumbnail'] ) ? $field_group['_pip_thumbnail'] : '' ),
                'return_format' => 'array',
                'preview_size'  => 'thumbnail',
                'library'       => 'all',
            ) );

            // Script for admin style
            ?>
            <script type="text/javascript">
              if (typeof acf !== 'undefined') {
                acf.postbox.render({
                  'id': 'pip_layout_settings',
                  'label': 'left'
                })
              }
            </script>
            <?php
        }
    }

    // Instantiate class
    new PIP_Field_Groups_Layouts();
}