<?php

$fields = apply_filters(
    'pip/locked_content/fields',
    array(
        array(
            'key'                        => 'field_pip_target_content_message',
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
            'message'                    => __( 'Use this layout to display dynamically layouts of target content.', 'pilopress' ),
            'new_lines'                  => 'wpautop',
            'esc_html'                   => 0,
            'acfe_field_group_condition' => 0,
        ),
    )
);

acf_add_local_field_group(
    array(
        'key'                     => 'group_pip_target_content',
        'title'                   => __( 'Locked content: Target content', 'pilopress' ),
        'fields'                  => $fields,
        'location'                => array(
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => PIP_Patterns::get_locked_content_slug(),
                ),
            ),
        ),
        'menu_order'              => 0,
        'position'                => 'normal',
        'style'                   => 'default',
        'label_placement'         => 'top',
        'instruction_placement'   => 'above_field',
        'hide_on_screen'          => '',
        'active'                  => false,
        'description'             => '',
        'acfe_autosync'           => array(
            'json',
            'php',
        ),
        '_pip_thumbnail'          => '',
        '_pip_is_layout'          => 1,
        '_pip_layout_slug'        => 'template-target-content',
        '_pip_render_layout'      => 'template-target-content.php',
        '_pip_render_style'       => 'template-target-content.css',
        '_pip_render_script'      => 'template-target-content.js',
        'field_add_config_file'   => 0,
        'field_add_configuration' => 0,
        'pip_layout_var'          => '',
        'pip_layout_vars_lock'    => 0,
        'acfe_form'               => 0,
        'acfe_display_title'      => '',
        'acfe_meta'               => '',
        'acfe_note'               => '',
        'layout_categories'       => '',
        'layout_collections'      => '',
    )
);
