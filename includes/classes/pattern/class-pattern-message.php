<?php

if ( !class_exists( 'PIP_Pattern_Message' ) ) {

    /**
     * Class PIP_Pattern_Message
     */
    class PIP_Pattern_Message {

        public function __construct() {

            // WP hooks
            add_action( 'init', array( $this, 'add_local_field_group' ) );

            // ACF hooks
            add_action( 'acf/prepare_field/name=pip_flexible_pattern_message', array( $this, 'pattern_message' ), 99 );
        }

        /**
         * Add local field group
         */
        public function add_local_field_group() {

            $pip_pattern = acf_get_instance( 'PIP_Pattern' );

            // Message flexible content field group
            acf_add_local_field_group(
                array(
                    'key'                   => 'group_pip_flexible_pattern_message',
                    'title'                 => __( 'Message', 'pilopress' ),
                    'fields'                => array(
                        array(
                            'key'               => 'field_pip_flexible_pattern_message',
                            'label'             => '',
                            'name'              => 'pip_flexible_pattern_message',
                            'type'              => 'acfe_dynamic_message',
                            'instructions'      => '',
                            'required'          => 0,
                            'conditional_logic' => 0,
                            'wrapper'           => array(
                                'width' => '',
                                'class' => '',
                                'id'    => '',
                            ),
                            'acfe_permissions'  => '',
                        ),
                    ),
                    'location'              => array(
                        array(
                            array(
                                'param'    => 'options_page',
                                'operator' => '==',
                                'value'    => $pip_pattern->menu_slug,
                            ),
                        ),
                    ),
                    'menu_order'            => 1,
                    'position'              => 'normal',
                    'style'                 => 'seamless',
                    'label_placement'       => 'top',
                    'instruction_placement' => 'label',
                    'hide_on_screen'        => '',
                    'active'                => true,
                    'description'           => '',
                    'acfe_display_title'    => '',
                    'acfe_autosync'         => '',
                    'acfe_permissions'      => '',
                    'acfe_form'             => 0,
                    'acfe_meta'             => '',
                    'acfe_note'             => '',
                )
            );
        }

        /**
         * Message content or alert if no layout
         */
        public function pattern_message() {

            $pip_flexible_header = acf_get_instance( 'PIP_Flexible_Header' );
            $pip_flexible_footer = acf_get_instance( 'PIP_Flexible_Footer' );
            $pip_layouts         = acf_get_instance( 'PIP_Layouts' );

            $header_layouts = $pip_layouts->get_layouts_by_location(
                array(
                    'pip-pattern' => $pip_flexible_header->get_flexible_header_field_name(),
                )
            );

            $footer_layouts = $pip_layouts->get_layouts_by_location(
                array(
                    'pip-pattern' => $pip_flexible_footer->get_flexible_footer_field_name(),
                )
            );

            // No layout for header and footer
            if ( !$header_layouts && !$footer_layouts ) {

                // Display message
                $url = add_query_arg(
                    array(
                        'layouts'   => 1,
                        'post_type' => 'acf-field-group',
                    ),
                    admin_url( 'edit.php' )
                );
                ?>

                <div class="inside acf-fields -top">
                    <p>
                        <?php _e( 'Please assign a layout to Header Pattern and/or Footer Pattern in order to use this functionality.', 'pilopress' ); ?>
                    </p>
                    <a href="<?php echo $url; ?>" class="button button-secondary">
                        <?php _e( 'Go to Layouts', 'pilopress' ); ?>
                    </a>
                </div>

                <?php

                return;
            }

            ?>
            <div class="inside acf-fields -top">
                <div class="-preview">

                    <div style="padding: 80px 20px;text-align: center;">
                        <em style="color:#aaa;"><?php _e( 'Website content', 'pilopress' ); ?></em>
                    </div>

                </div>
            </div>
            <?php

        }

    }

    acf_new_instance( 'PIP_Pattern_Message' );

}
