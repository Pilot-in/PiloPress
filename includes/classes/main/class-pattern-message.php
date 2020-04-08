<?php

if ( !class_exists( 'PIP_Pattern_Message' ) ) {
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
            // Message flexible content field group
            acf_add_local_field_group( array(
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
                            'value'    => PIP_Pattern::get_pattern_option_page()['menu_slug'],
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
            ) );
        }

        /**
         * Message content or alert if no layout
         */
        public function pattern_message() {

            // No layout for header and footer
            if ( PIP_Pattern::$show_alert ) {

                // Display alert message
                echo '
                <script type="application/javascript">
                    (function ($) {

                        $(document).ready(function () {
                            alert("Please configure your first layout in order to use Patterns.\nGo to Pilo\'Press > Layouts menu.");
                        });

                    })(jQuery);
                </script>';

                return;
            }

            ?>
            <div class="inside acf-fields -top">
            <div class="-preview">

                <div style="padding: 120px 20px;text-align: center;">
                    <em style="color:#aaa;"><?php _e( 'Website content', 'pilopress' ); ?></em>
                </div>

            </div>
            </div>
            <?php

        }
    }

    // Instantiate class
    new PIP_Pattern_Message();
}
