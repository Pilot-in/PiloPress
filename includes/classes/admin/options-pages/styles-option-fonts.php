<?php

// Register "Fonts" field group
acf_add_local_field_group(
    array(
        'key'                   => 'group_styles_fonts',
        'title'                 => __( 'Fonts', 'pilopress' ),
        'fields'                => array(

            // Fonts message
            array(
                'key'                        => 'field_fonts_message',
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
                'message'                    => __( 'You can add external and/or local fonts in this tab.', 'pilopress' ),
                'new_lines'                  => 'wpautop',
                'esc_html'                   => 0,
                'acfe_field_group_condition' => 0,
            ),

            // Fonts
            array(
                'acfe_flexible_advanced'                => 1,
                'acfe_flexible_stylised_button'         => 1,
                'acfe_flexible_hide_empty_message'      => 0,
                'acfe_flexible_empty_message'           => '',
                'acfe_flexible_disable_ajax_title'      => 0,
                'acfe_flexible_layouts_thumbnails'      => 0,
                'acfe_flexible_layouts_settings'        => 0,
                'acfe_flexible_layouts_ajax'            => 0,
                'acfe_flexible_layouts_templates'       => 0,
                'acfe_flexible_layouts_previews'        => 0,
                'acfe_flexible_layouts_placeholder'     => 0,
                'acfe_flexible_title_edition'           => 1,
                'acfe_flexible_clone'                   => 0,
                'acfe_flexible_copy_paste'              => 0,
                'acfe_flexible_close_button'            => 0,
                'acfe_flexible_remove_add_button'       => 0,
                'acfe_flexible_remove_delete_button'    => 0,
                'acfe_flexible_lock'                    => 0,
                'acfe_flexible_modal_edition'           => 0,
                'acfe_flexible_modal'                   => array(
                    'acfe_flexible_modal_enabled' => '0',
                ),
                'acfe_flexible_layouts_state'           => '',
                'acfe_flexible_layouts_remove_collapse' => 0,
                'key'                                   => 'field_pip_fonts',
                'label'                                 => '',
                'name'                                  => 'pip_fonts',
                'type'                                  => 'flexible_content',
                'instructions'                          => __( 'Fonts', 'pilopress' ),
                'required'                              => 0,
                'conditional_logic'                     => 0,
                'wrapper'                               => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'acfe_permissions'                      => '',
                'layouts'                               => array(

                    // External font
                    'layout_google_font' => array(
                        'key'                           => 'layout_google_font',
                        'name'                          => 'google_font',
                        'label'                         => __( 'External Font', 'pilopress' ),
                        'display'                       => 'row',
                        'sub_fields'                    => array(

                            // Preview
                            array(
                                'key'                        => 'field_google_font_preview',
                                'label'                      => __( 'Preview', 'pilopress' ),
                                'name'                       => '',
                                'type'                       => 'message',
                                'required'                   => 0,
                                'conditional_logic'          => 0,
                                'wrapper'                    => array(
                                    'width' => '10',
                                    'class' => '',
                                    'id'    => '',
                                ),
                                'acfe_save_meta'             => 0,
                                'message'                    => '<div class="-preview pip-live-preview"><div>The quick brown fox jumps over the lazy dog.</div></div>',
                                'new_lines'                  => 'wpautop',
                                'esc_html'                   => 0,
                                'acfe_field_group_condition' => 0,
                            ),

                            // Name
                            array(
                                'key'               => 'field_google_font_name',
                                'label'             => __( 'Name', 'pilopress' ),
                                'name'              => 'name',
                                'type'              => 'text',
                                'instructions'      => '',
                                'required'          => 1,
                                'conditional_logic' => 0,
                                'wrapper'           => array(
                                    'width' => '',
                                    'class' => '',
                                    'id'    => '',
                                ),
                                'acfe_permissions'  => '',
                                'default_value'     => '',
                                'placeholder'       => '',
                                'prepend'           => '',
                                'append'            => '',
                                'maxlength'         => '',
                            ),

                            // URL
                            array(
                                'key'               => 'field_google_font_url',
                                'label'             => __( 'URL', 'pilopress' ),
                                'name'              => 'url',
                                'type'              => 'url',
                                'instructions'      => '',
                                'required'          => 1,
                                'conditional_logic' => 0,
                                'wrapper'           => array(
                                    'width' => '',
                                    'class' => '',
                                    'id'    => '',
                                ),
                                'acfe_permissions'  => '',
                                'default_value'     => '',
                                'placeholder'       => '',
                            ),

                            // Auto-enqueue
                            array(
                                'key'               => 'field_google_font_enqueue',
                                'label'             => __( 'Auto-enqueue', 'pilopress' ),
                                'name'              => 'enqueue',
                                'type'              => 'true_false',
                                'instructions'      => '',
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

                            // Class name
                            array(
                                'key'               => 'field_google_font_class_name',
                                'label'             => __( 'Class name', 'pilopress' ),
                                'name'              => 'class_name',
                                'type'              => 'acfe_slug',
                                'instructions'      => __( 'By default, the field "name" will be use to generate the CSS class name.', 'pilopress' ),
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
                                'prepend'           => 'font-',
                                'append'            => '',
                                'maxlength'         => '',
                            ),

                            // Fallback fonts
                            array(
                                'key'               => 'field_google_font_fallback',
                                'label'             => __( 'Fallback fonts', 'pilopress' ),
                                'name'              => 'fallback',
                                'type'              => 'text',
                                'instructions'      => __( 'Fonts to use if the main font is unavailable.', 'pilopress' ),
                                'required'          => 0,
                                'conditional_logic' => 0,
                                'wrapper'           => array(
                                    'width' => '',
                                    'class' => '',
                                    'id'    => '',
                                ),
                                'acfe_permissions'  => '',
                                'default_value'     => 'system-ui,-apple-system,BlinkMacSystemFont,Segoe UI,sans-serif',
                                'placeholder'       => '',
                                'prepend'           => '',
                                'append'            => '',
                                'maxlength'         => '',
                            ),

                            // Add to editor
                            array(
                                'key'               => 'field_google_font_add_to_editor',
                                'label'             => __( 'Add to editor menu?', 'pilopress' ),
                                'name'              => 'add_to_editor',
                                'type'              => 'true_false',
                                'instructions'      => __( 'Needs TinyMCE Module', 'pilopress' ),
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
                        'min'                           => '',
                        'max'                           => '',
                        'acfe_flexible_thumbnail'       => false,
                        'acfe_flexible_category'        => false,
                        'acfe_flexible_render_template' => false,
                        'acfe_flexible_render_style'    => false,
                        'acfe_flexible_render_script'   => false,
                        'acfe_flexible_settings'        => false,
                        'acfe_flexible_settings_size'   => 'medium',
                    ),

                    // Custom font
                    'layout_custom_font' => array(
                        'key'                           => 'layout_custom_font',
                        'name'                          => 'custom_font',
                        'label'                         => __( 'Custom font', 'pilopress' ),
                        'display'                       => 'row',
                        'sub_fields'                    => array(

                            // Preview
                            array(
                                'key'                        => 'field_custom_font_preview',
                                'label'                      => __( 'Preview', 'pilopress' ),
                                'name'                       => '',
                                'type'                       => 'message',
                                'required'                   => 0,
                                'conditional_logic'          => 0,
                                'wrapper'                    => array(
                                    'width' => '10',
                                    'class' => '',
                                    'id'    => '',
                                ),
                                'acfe_save_meta'             => 0,
                                'message'                    => '<div class="-preview pip-live-preview"><div>The quick brown fox jumps over the lazy dog.</div></div>',
                                'new_lines'                  => 'wpautop',
                                'esc_html'                   => 0,
                                'acfe_field_group_condition' => 0,
                            ),

                            // Name
                            array(
                                'key'               => 'field_custom_font_name',
                                'label'             => __( 'Name', 'pilopress' ),
                                'name'              => 'name',
                                'type'              => 'text',
                                'instructions'      => '',
                                'required'          => 1,
                                'conditional_logic' => 0,
                                'wrapper'           => array(
                                    'width' => '',
                                    'class' => '',
                                    'id'    => '',
                                ),
                                'acfe_permissions'  => '',
                                'default_value'     => '',
                                'placeholder'       => '',
                                'prepend'           => '',
                                'append'            => '',
                                'maxlength'         => '',
                            ),

                            // Class name
                            array(
                                'key'               => 'field_custom_font_class_name',
                                'label'             => __( 'Class name', 'pilopress' ),
                                'name'              => 'class_name',
                                'type'              => 'acfe_slug',
                                'instructions'      => __( 'By default, the field "name" will be use to generate the CSS class name.', 'pilopress' ),
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
                                'prepend'           => 'font-',
                                'append'            => '',
                                'maxlength'         => '',
                            ),

                            // Fallback fonts
                            array(
                                'key'               => 'field_custom_font_fallback',
                                'label'             => __( 'Fallback fonts', 'pilopress' ),
                                'name'              => 'fallback',
                                'type'              => 'text',
                                'instructions'      => __( 'Fonts to use if the main font is unavailable.', 'pilopress' ),
                                'required'          => 0,
                                'conditional_logic' => 0,
                                'wrapper'           => array(
                                    'width' => '',
                                    'class' => '',
                                    'id'    => '',
                                ),
                                'acfe_permissions'  => '',
                                'default_value'     => 'system-ui,-apple-system,BlinkMacSystemFont,Segoe UI,sans-serif',
                                'placeholder'       => '',
                                'prepend'           => '',
                                'append'            => '',
                                'maxlength'         => '',
                            ),

                            // Add to editor
                            array(
                                'key'               => 'field_custom_font_add_to_editor',
                                'label'             => __( 'Add to editor menu?', 'pilopress' ),
                                'name'              => 'add_to_editor',
                                'type'              => 'true_false',
                                'instructions'      => __( 'Needs TinyMCE Module', 'pilopress' ),
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

                            // Multiple weight and style
                            array(
                                'key'               => 'field_multiple_weight_and_style',
                                'label'             => __( 'Weight and style', 'pilopress' ),
                                'name'              => 'multiple_weight_and_style',
                                'type'              => 'true_false',
                                'instructions'      => '',
                                'required'          => 0,
                                'conditional_logic' => 0,
                                'wrapper'           => array(
                                    'width' => '',
                                    'class' => '',
                                    'id'    => '',
                                ),
                                'acfe_save_meta'    => 0,
                                'message'           => '',
                                'default_value'     => 0,
                                'ui'                => 1,
                                'ui_on_text'        => __( 'Multiple', 'pilopress' ),
                                'ui_off_text'       => __( 'Simple', 'pilopress' ),
                            ),

                            // Weight
                            array(
                                'key'               => 'field_custom_font_weight',
                                'label'             => __( 'Weight', 'pilopress' ),
                                'name'              => 'weight',
                                'type'              => 'text',
                                'instructions'      => '',
                                'required'          => 0,
                                'conditional_logic' => array(
                                    array(
                                        array(
                                            'field'    => 'field_multiple_weight_and_style',
                                            'operator' => '!=',
                                            'value'    => '1',
                                        ),
                                    ),
                                ),
                                'wrapper'           => array(
                                    'width' => '',
                                    'class' => '',
                                    'id'    => '',
                                ),
                                'acfe_permissions'  => '',
                                'default_value'     => 'normal',
                                'placeholder'       => '',
                                'prepend'           => '',
                                'append'            => '',
                                'maxlength'         => '',
                            ),

                            // Style
                            array(
                                'key'               => 'field_custom_font_style',
                                'label'             => __( 'Style', 'pilopress' ),
                                'name'              => 'style',
                                'type'              => 'text',
                                'instructions'      => '',
                                'required'          => 0,
                                'conditional_logic' => array(
                                    array(
                                        array(
                                            'field'    => 'field_multiple_weight_and_style',
                                            'operator' => '!=',
                                            'value'    => '1',
                                        ),
                                    ),
                                ),
                                'wrapper'           => array(
                                    'width' => '',
                                    'class' => '',
                                    'id'    => '',
                                ),
                                'acfe_permissions'  => '',
                                'default_value'     => 'normal',
                                'placeholder'       => '',
                                'prepend'           => '',
                                'append'            => '',
                                'maxlength'         => '',
                            ),

                            // Display
                            array(
                                'key'               => 'field_custom_font_display',
                                'label'             => __( 'Display', 'pilopress' ),
                                'name'              => 'display',
                                'type'              => 'text',
                                'instructions'      => '',
                                'required'          => 0,
                                'conditional_logic' => array(
                                    array(
                                        array(
                                            'field'    => 'field_multiple_weight_and_style',
                                            'operator' => '!=',
                                            'value'    => '1',
                                        ),
                                    ),
                                ),
                                'wrapper'           => array(
                                    'width' => '',
                                    'class' => '',
                                    'id'    => '',
                                ),
                                'acfe_permissions'  => '',
                                'default_value'     => 'swap',
                                'placeholder'       => '',
                                'prepend'           => '',
                                'append'            => '',
                                'maxlength'         => '',
                            ),

                            // Variable font
                            array(
                                'key'               => 'field_variable_font',
                                'label'             => __( 'Variable font', 'pilopress' ),
                                'name'              => 'variable_font',
                                'type'              => 'true_false',
                                'instructions'      => '',
                                'required'          => 0,
                                'conditional_logic' => array(
                                    array(
                                        array(
                                            'field'    => 'field_multiple_weight_and_style',
                                            'operator' => '!=',
                                            'value'    => '1',
                                        ),
                                    ),
                                ),
                                'wrapper'           => array(
                                    'width' => '',
                                    'class' => '',
                                    'id'    => '',
                                ),
                                'acfe_save_meta'    => 0,
                                'message'           => '',
                                'default_value'     => 0,
                                'ui'                => 1,
                                'ui_on_text'        => __( 'Yes', 'pilopress' ),
                                'ui_off_text'       => __( 'No', 'pilopress' ),
                            ),

                            // Files
                            array(
                                'key'                           => 'field_custom_font_files',
                                'label'                         => __( 'Files', 'pilopress' ),
                                'name'                          => 'files',
                                'type'                          => 'repeater',
                                'instructions'                  => '',
                                'required'                      => 1,
                                'conditional_logic'             => array(
                                    array(
                                        array(
                                            'field'    => 'field_multiple_weight_and_style',
                                            'operator' => '!=',
                                            'value'    => '1',
                                        ),
                                    ),
                                ),
                                'wrapper'                       => array(
                                    'width' => '',
                                    'class' => '',
                                    'id'    => '',
                                ),
                                'acfe_repeater_stylised_button' => 1,
                                'acfe_permissions'              => '',
                                'collapsed'                     => '',
                                'min'                           => 0,
                                'max'                           => 0,
                                'layout'                        => 'block',
                                'button_label'                  => __( 'Add file', 'pilopress' ),
                                'sub_fields'                    => array(
                                    array(
                                        'key'               => 'field_custom_font_file',
                                        'label'             => __( 'File', 'pilopress' ),
                                        'name'              => 'file',
                                        'type'              => 'file',
                                        'instructions'      => '',
                                        'required'          => 1,
                                        'conditional_logic' => 0,
                                        'wrapper'           => array(
                                            'width' => '',
                                            'class' => '',
                                            'id'    => '',
                                        ),
                                        'acfe_permissions'  => '',
                                        'acfe_uploader'     => 'wp',
                                        'return_format'     => 'array',
                                        'library'           => 'all',
                                        'min_size'          => '',
                                        'max_size'          => '',
                                        'mime_types'        => '',
                                    ),
                                ),
                            ),

                            // Variations
                            array(
                                'key'                               => 'field_variations',
                                'label'                             => __( 'Variations', 'pilopress' ),
                                'name'                              => 'variations',
                                'type'                              => 'flexible_content',
                                'instructions'                      => '',
                                'required'                          => 0,
                                'conditional_logic'                 => array(
                                    array(
                                        array(
                                            'field'    => 'field_multiple_weight_and_style',
                                            'operator' => '==',
                                            'value'    => '1',
                                        ),
                                    ),
                                ),
                                'wrapper'                           => array(
                                    'width' => '',
                                    'class' => '',
                                    'id'    => '',
                                ),
                                'acfe_save_meta'                    => 0,
                                'acfe_flexible_advanced'            => 1,
                                'acfe_flexible_stylised_button'     => 1,
                                'acfe_flexible_hide_empty_message'  => 0,
                                'acfe_flexible_empty_message'       => '',
                                'acfe_flexible_layouts_templates'   => 0,
                                'acfe_flexible_layouts_placeholder' => 0,
                                'acfe_flexible_layouts_thumbnails'  => 0,
                                'acfe_flexible_layouts_settings'    => 0,
                                'acfe_flexible_disable_ajax_title'  => 0,
                                'acfe_flexible_layouts_ajax'        => 0,
                                'acfe_flexible_add_actions'         => array(
                                    0 => 'title',
                                ),
                                'acfe_flexible_remove_button'       => array(),
                                'acfe_flexible_layouts_state'       => 'user',
                                'acfe_flexible_modal_edit'          => array(
                                    'acfe_flexible_modal_edit_enabled' => '0',
                                    'acfe_flexible_modal_edit_size'    => 'large',
                                ),
                                'acfe_flexible_modal'               => array(
                                    'acfe_flexible_modal_enabled'    => '0',
                                    'acfe_flexible_modal_title'      => false,
                                    'acfe_flexible_modal_size'       => 'full',
                                    'acfe_flexible_modal_col'        => '4',
                                    'acfe_flexible_modal_categories' => false,
                                ),
                                'layouts'                           => array(
                                    'layout_variation' => array(
                                        'key'                           => 'layout_variation',
                                        'name'                          => 'variation',
                                        'label'                         => __( 'Variation', 'pilopress' ),
                                        'display'                       => 'block',
                                        'sub_fields'                    => array(

                                            // Weight
                                            array(
                                                'key'               => 'field_variation_weight',
                                                'label'             => __( 'Weight', 'pilopress' ),
                                                'name'              => 'weight',
                                                'type'              => 'text',
                                                'instructions'      => '',
                                                'required'          => 0,
                                                'conditional_logic' => 0,
                                                'wrapper'           => array(
                                                    'width' => '28',
                                                    'class' => '',
                                                    'id'    => '',
                                                ),
                                                'acfe_save_meta'    => 0,
                                                'default_value'     => 'normal',
                                                'placeholder'       => '',
                                                'prepend'           => '',
                                                'append'            => '',
                                                'maxlength'         => '',
                                            ),

                                            // Style
                                            array(
                                                'key'               => 'field_variation_style',
                                                'label'             => __( 'Style', 'pilopress' ),
                                                'name'              => 'style',
                                                'type'              => 'text',
                                                'instructions'      => '',
                                                'required'          => 0,
                                                'conditional_logic' => 0,
                                                'wrapper'           => array(
                                                    'width' => '28',
                                                    'class' => '',
                                                    'id'    => '',
                                                ),
                                                'acfe_save_meta'    => 0,
                                                'default_value'     => 'normal',
                                                'placeholder'       => '',
                                                'prepend'           => '',
                                                'append'            => '',
                                                'maxlength'         => '',
                                            ),

                                            // Display
                                            array(
                                                'key'               => 'field_variation_display',
                                                'label'             => __( 'Display', 'pilopress' ),
                                                'name'              => 'display',
                                                'type'              => 'text',
                                                'instructions'      => '',
                                                'required'          => 0,
                                                'conditional_logic' => 0,
                                                'wrapper'           => array(
                                                    'width' => '28',
                                                    'class' => '',
                                                    'id'    => '',
                                                ),
                                                'acfe_save_meta'    => 0,
                                                'default_value'     => 'swap',
                                                'placeholder'       => '',
                                                'prepend'           => '',
                                                'append'            => '',
                                                'maxlength'         => '',
                                            ),

                                            // Variable font
                                            array(
                                                'key'               => 'field_variation_variable_font',
                                                'label'             => __( 'Variable font', 'pilopress' ),
                                                'name'              => 'variable_font',
                                                'type'              => 'true_false',
                                                'instructions'      => '',
                                                'required'          => 0,
                                                'conditional_logic' => 0,
                                                'wrapper'           => array(
                                                    'width' => '16',
                                                    'class' => '',
                                                    'id'    => '',
                                                ),
                                                'acfe_save_meta'    => 0,
                                                'message'           => '',
                                                'default_value'     => 0,
                                                'ui'                => 1,
                                                'ui_on_text'        => __( 'Yes', 'pilopress' ),
                                                'ui_off_text'       => __( 'No', 'pilopress' ),
                                            ),

                                            // Files
                                            array(
                                                'key'                           => 'field_variation_files',
                                                'label'                         => __( 'Files', 'pilopress' ),
                                                'name'                          => 'files',
                                                'type'                          => 'repeater',
                                                'instructions'                  => '',
                                                'required'                      => 1,
                                                'conditional_logic'             => 0,
                                                'wrapper'                       => array(
                                                    'width' => '',
                                                    'class' => '',
                                                    'id'    => '',
                                                ),
                                                'acfe_save_meta'                => 0,
                                                'acfe_repeater_stylised_button' => 1,
                                                'collapsed'                     => '',
                                                'min'                           => 0,
                                                'max'                           => 0,
                                                'layout'                        => 'table',
                                                'button_label'                  => __( 'Add File', 'pilopress' ),
                                                'sub_fields'                    => array(
                                                    array(
                                                        'key'               => 'field_variation_file',
                                                        'label'             => __( 'File', 'pilopress' ),
                                                        'name'              => 'file',
                                                        'type'              => 'file',
                                                        'instructions'      => '',
                                                        'required'          => 1,
                                                        'conditional_logic' => 0,
                                                        'wrapper'           => array(
                                                            'width' => '',
                                                            'class' => '',
                                                            'id'    => '',
                                                        ),
                                                        'acfe_save_meta'    => 0,
                                                        'uploader'          => '',
                                                        'return_format'     => 'array',
                                                        'min_size'          => '',
                                                        'max_size'          => '',
                                                        'mime_types'        => '',
                                                        'library'           => 'all',
                                                    ),
                                                ),
                                            ),

                                        ),
                                        'min'                           => 1,
                                        'max'                           => '',
                                        'acfe_flexible_render_template' => false,
                                        'acfe_flexible_render_style'    => false,
                                        'acfe_flexible_render_script'   => false,
                                        'acfe_flexible_thumbnail'       => false,
                                        'acfe_flexible_settings'        => false,
                                        'acfe_flexible_settings_size'   => 'medium',
                                        'acfe_flexible_modal_edit_size' => false,
                                        'acfe_flexible_category'        => false,
                                    ),
                                ),
                                'button_label'                      => __( 'Add Variation', 'pilopress' ),
                                'min'                               => '',
                                'max'                               => '',
                                'acfe_flexible_layouts_previews'    => false,
                            ),

                        ),
                        'min'                           => '',
                        'max'                           => '',
                        'acfe_flexible_thumbnail'       => false,
                        'acfe_flexible_category'        => false,
                        'acfe_flexible_render_template' => false,
                        'acfe_flexible_render_style'    => false,
                        'acfe_flexible_render_script'   => false,
                        'acfe_flexible_settings'        => false,
                        'acfe_flexible_settings_size'   => 'medium',
                    ),

                ),
                'button_label'                          => __( 'Add font', 'pilopress' ),
                'min'                                   => '',
                'max'                                   => '',
            ),

        ),
        'location'              => array(
            array(
                array(
                    'param'    => 'options_page',
                    'operator' => '==',
                    'value'    => 'pip_styles_fonts',
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
