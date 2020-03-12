<?php

// Register "CSS" field group
acf_add_local_field_group( array(
    'key'                   => 'group_styles_css',
    'title'                 => 'CSS',
    'fields'                => array(
        array(
            'key'                 => 'field_pip_custom_style',
            'label'               => '',
            'name'                => 'pip_custom_style',
            'type'                => 'group',
            'instructions'        => __( 'Custom style', 'pilopress' ),
            'required'            => 0,
            'conditional_logic'   => 0,
            'wrapper'             => array(
                'width' => '',
                'class' => '',
                'id'    => '',
            ),
            'acfe_permissions'    => '',
            'layout'              => 'row',
            'acfe_seamless_style' => 0,
            'acfe_group_modal'    => 0,
            'sub_fields'          => array(
                array(
                    'key'               => 'field_custom_style',
                    'label'             => '',
                    'name'              => 'custom_style',
                    'type'              => 'acfe_code_editor',
                    'instructions'      => __( 'You can enter CSS or SCSS code.', 'pilopress' ),
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => array(
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ),
                    'acfe_permissions'  => '',
                    'default_value'     => '',
                    'placeholder'       => '',
                    'mode'              => 'css',
                    'lines'             => 1,
                    'indent_unit'       => 4,
                    'maxlength'         => '',
                    'rows'              => '',
                ),
            ),
        ),
    ),
    'location'              => array(
        array(
            array(
                'param'    => 'options_page',
                'operator' => '==',
                'value'    => 'styles-css',
            ),
        ),
    ),
    'menu_order'            => 0,
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
    'acfe_categories'       => array(
        'options' => 'Options',
    ),
) );