<?php

// Register "Module" field group
acf_add_local_field_group(
    array(
        'key'                   => 'group_styles_modules',
        'title'                 => __( 'Modules', 'pilopress' ),
        'fields'                => array(

            // Modules message
            array(
                'key'                        => 'field_modules_message',
                'label'                      => '',
                'name'                       => '',
                'type'                       => 'message',
                'instructions'               => '',
                'required'                   => 0,
                'conditional_logic'          => 0,
                'wrapper'                    => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'acfe_save_meta'             => 0,
                'message'                    => __( 'You can enabled or disabled available modules in this tab.', 'pilopress' ),
                'new_lines'                  => 'wpautop',
                'esc_html'                   => 0,
                'acfe_field_group_condition' => 0,
            ),

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

                    // TailwindCSS
                    array(
                        'key'               => 'field_module_tailwind',
                        'label'             => __( 'TailwindCSS', 'pilopress' ),
                        'name'              => 'tailwind',
                        'type'              => 'true_false',
                        'instructions'      => __( "Activate TailwindCSS module.<br>You will be able to compile styles through Pilo'Press API or enable local compilation.", 'pilopress' ),
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

                    // TinyMCE
                    array(
                        'key'               => 'field_module_tinymce',
                        'label'             => __( 'TinyMCE', 'pilopress' ),
                        'name'              => 'tinymce',
                        'type'              => 'true_false',
                        'instructions'      => __( 'Activate TinyMCE module.<br>Your styles configuration will be available through dropdowns in TinyMCE editors. Compiled styles will be enqueued in editor.', 'pilopress' ),
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

                    // AlpineJS
                    array(
                        'key'               => 'field_module_alpinejs',
                        'label'             => __( 'AlpineJS', 'pilopress' ),
                        'name'              => 'alpinejs',
                        'type'              => 'true_false',
                        'instructions'      => __( 'Activate AlpineJS module.<br>It will enqueue AlpineJS and you will be able to use it in your layouts.', 'pilopress' ),
                        'required'          => 0,
                        'conditional_logic' => 0,
                        'wrapper'           => array(
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ),
                        'acfe_permissions'  => '',
                        'message'           => '',
                        'default_value'     => 0,
                        'ui'                => 1,
                        'ui_on_text'        => '',
                        'ui_off_text'       => '',
                    ),
                    array(
                        'key'                        => 'field_alpinejs_version',
                        'label'                      => 'Version',
                        'name'                       => 'alpinejs_version',
                        'type'                       => 'text',
                        'instructions'               => 'See <a href="https://unpkg.com/browse/alpinejs/" target="_blank">unpkg.com</a> for available versions.',
                        'required'                   => 0,
                        'conditional_logic'          => array(
                            array(
                                array(
                                    'field'    => 'field_module_alpinejs',
                                    'operator' => '==',
                                    'value'    => '1',
                                ),
                            ),
                        ),
                        'wrapper'                    => array(
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ),
                        'acfe_save_meta'             => 0,
                        'default_value'              => '3.8.0',
                        'placeholder'                => '',
                        'prepend'                    => '',
                        'append'                     => '',
                        'maxlength'                  => '',
                        'acfe_field_group_condition' => 0,
                    ),

                ),
            ),

        ),
        'location'              => array(
            array(
                array(
                    'param'    => 'options_page',
                    'operator' => '==',
                    'value'    => 'pip_styles_modules',
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
            'options' => __( 'Options', 'pilopress' ),
        ),
    )
);
