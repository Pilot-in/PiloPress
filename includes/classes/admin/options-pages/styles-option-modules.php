<?php

// Register "Module" field group
acf_add_local_field_group(
    array(
        'key'                   => 'group_styles_modules',
        'title'                 => __( 'Modules', 'pilopress' ),
        'fields'                => array(

            // Modules
            array(
                'key'                 => 'field_pip_modules',
                'label'               => '',
                'name'                => 'pip_modules',
                'type'                => 'group',
                'instructions'        => '',
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
                        'key'               => 'field_module_tailwind',
                        'label'             => __( 'TailwindCSS', 'pilopress' ),
                        'name'              => 'tailwind',
                        'type'              => 'true_false',
                        'instructions'      => __( 'Activate TailwindCSS module', 'pilopress' ),
                        'required'          => 0,
                        'conditional_logic' => 0,
                        'wrapper'           => array(
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ),
                        'acfe_permissions'  => '',
                        'message'           => '',
                        'default_value'     => 1,
                        'ui'                => 1,
                        'ui_on_text'        => '',
                        'ui_off_text'       => '',
                    ),
                    array(
                        'key'               => 'field_module_tinymce',
                        'label'             => __( 'TinyMCE', 'pilopress' ),
                        'name'              => 'tinymce',
                        'type'              => 'true_false',
                        'instructions'      => __( 'Activate TinyMCE module', 'pilopress' ),
                        'required'          => 0,
                        'conditional_logic' => 0,
                        'wrapper'           => array(
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ),
                        'acfe_permissions'  => '',
                        'message'           => '',
                        'default_value'     => 1,
                        'ui'                => 1,
                        'ui_on_text'        => '',
                        'ui_off_text'       => '',
                    ),
                ),
            ),

        ),
        'location'              => array(
            array(
                array(
                    'param'    => 'options_page',
                    'operator' => '==',
                    'value'    => 'pip-styles-modules',
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
