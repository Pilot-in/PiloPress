<?php

// Register "Tailwind Config" field group
acf_add_local_field_group( array(
    'key'                   => 'group_styles_tailwind_config',
    'title'                 => 'Tailwind Configuration',
    'fields'                => array(

        // Custom CSS
        array(
            'key'                 => 'field_pip_tailwind_config',
            'label'               => '',
            'name'                => 'pip_tailwind_config',
            'type'                => 'group',
            'instructions'        => __( 'Tailwind Configuration', 'pilopress' ),
            'required'            => 0,
            'conditional_logic'   => 0,
            'wrapper'             => array(
                'width' => '',
                'class' => '',
                'id'    => '',
            ),
            'acfe_permissions'    => '',
            'layout'              => 'block',
            'acfe_seamless_style' => 0,
            'acfe_group_modal'    => 0,
            'sub_fields'          => array(
                array(
                    'key'               => 'field_tailwind_config',
                    'label'             => '',
                    'name'              => 'tailwind_config',
                    'type'              => 'acfe_code_editor',
                    'instructions'      => '',
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => array(
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ),
                    'acfe_permissions'  => '',
                    'default_value'     => "module.exports = {
				theme: {

				},
				variants: {

				},
				plugins: [

				],
};",
                    'placeholder'       => '',
                    'mode'              => 'javascript',
                    'lines'             => 8,
                    'indent_unit'       => 4,
                    'maxlength'         => '',
                    'rows'              => 8,
                ),
            ),
        ),

    ),
    'location'              => array(
        array(
            array(
                'param'    => 'options_page',
                'operator' => '==',
                'value'    => 'pip-styles-tailwind-config',
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
