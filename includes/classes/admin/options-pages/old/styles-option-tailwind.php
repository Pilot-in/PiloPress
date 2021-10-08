<?php

// Register "Tailwind CSS" field group
acf_add_local_field_group(
    array(
        'key'                   => 'group_styles_tailwind',
        'title'                 => 'Tailwind',
        'fields'                => array(

            // CSS
            array(
                'key'                 => 'field_pip_tailwind_style',
                'label'               => '',
                'name'                => 'pip_tailwind_style',
                'type'                => 'group',
                'instructions'        => __( 'CSS', 'pilopress' ),
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
                        'key'               => 'field_tailwind_style',
                        'label'             => '',
                        'name'              => 'tailwind_style',
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
                        'default_value'     => "@tailwind base;\n\n@tailwind components;\n\n@tailwind utilities;",
                        'placeholder'       => '',
                        'mode'              => 'css',
                        'lines'             => 8,
                        'indent_unit'       => 4,
                        'maxlength'         => '',
                        'rows'              => 8,
                    ),
                ),
            ),

            // JS Config
            array(
                'key'                 => 'field_pip_tailwind_config',
                'label'               => '',
                'name'                => 'pip_tailwind_config',
                'type'                => 'group',
                'instructions'        => __( 'Configuration', 'pilopress' ),
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
                        'default_value'     => "module.exports = {\n    theme: {\n\n    },\n    variants: {\n\n    },\n    plugins: [\n\n    ],\n};",
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
                    'value'    => 'pip_styles_tailwind',
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
    )
);
