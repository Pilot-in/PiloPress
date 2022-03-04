<?php

if ( function_exists( 'acf_add_local_field_group' ) ) :

    acf_add_local_field_group(
        array(
            'key'                   => 'group_pip_locked_content',
            'title'                 => __( 'Locked content', 'pilopress' ),
            'fields'                => array(
                array(
                    'key'               => 'field_62222e6281fa6',
                    'label'             => 'Instructions',
                    'name'              => '',
                    'type'              => 'message',
                    'instructions'      => '',
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => array(
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ),
                    'acfe_permissions'  => '',
                    'message'           => '<h2>You must add <strong><code>Target content</code> layout</strong> if you don\'t want to override layouts used in your target content.</h2>',
                    'new_lines'         => 'wpautop',
                    'esc_html'          => 0,
                    'acfe_settings'     => '',
                ),
            ),
            'location'              => array(
                array(
                    array(
                        'param'    => 'post_type',
                        'operator' => '==',
                        'value'    => 'pip-locked-content',
                    ),
                ),
            ),
            'menu_order'            => 0,
            'position'              => 'acf_after_title',
            'style'                 => 'seamless',
            'label_placement'       => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen'        => '',
            'active'                => true,
            'description'           => '',
            'show_in_rest'          => 0,
            'acfe_display_title'    => '',
            'acfe_autosync'         => array(
                'json',
                'php',
            ),
            'acfe_permissions'      => '',
            'acfe_form'             => 1,
            'acfe_meta'             => '',
            'acfe_note'             => '',
        )
    );

endif;
