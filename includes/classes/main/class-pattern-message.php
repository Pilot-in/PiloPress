<?php

if ( !class_exists( 'PIP_Pattern_Message' ) ) {
    class PIP_Pattern_Message {
        public function __construct() {
            add_action( 'init', array( $this, 'add_local_field_group' ) );
            add_action( 'acf/render_field/name=pip_flexible_pattern_message', array( $this, 'pattern_message' ) );
        }

        /**
         * Add local field group
         */
        public function add_local_field_group() {
            // Message flexible content field group
            acf_add_local_field_group( array(
                'key'                   => 'group_pip_flexible_pattern_message',
                'title'                 => 'Message',
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
            $header = acf_get_field_group( PIP_Flexible_Header::get_flexible_header_field_name() );
            $footer = acf_get_field_group( PIP_Flexible_Footer::get_flexible_footer_field_name() );

            // No header and no footer
            if ( !$header && !$footer ) {
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

            // Echo default content
            echo '<div class="border border-dark px-3 py-5 rounded text-center">
                    <p class="text-uppercase font-weight-bold text-monospace">Website content here</p>
                </div>';
        }
    }

    // Instantiate class
    new PIP_Pattern_Message();
}
