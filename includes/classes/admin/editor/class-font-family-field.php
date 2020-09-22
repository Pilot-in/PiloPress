<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( !class_exists( 'PIP_Font_Family_Field' ) ) {

    /**
     * Class PIP_Font_Family_Field
     */
    class PIP_Font_Family_Field extends acf_field {

        public function __construct() {

            $this->name     = 'pip_font_family';
            $this->label    = __( 'Font family', 'pilopress' );
            $this->category = __( "Pilo'Press", 'pilopress' );
            $this->defaults = array(
                'field_type'    => 'select',
                'choices'       => array(),
                'placeholder'   => '',
                'return_format' => 'value',
                'allow_null'    => true,
                'ajax'          => false,
            );

            parent::__construct();
        }

        /**
         * Get choices
         *
         * @return array
         */
        public function get_choices() {

            $pip_tinymce = acf_get_instance( 'PIP_TinyMCE' );

            $choices       = array();
            $custom_styles = $pip_tinymce->get_custom_fonts();
            if ( $custom_styles ) {
                foreach ( $custom_styles as $key => $custom_style ) {
                    $choices[ $key ] = $custom_style['name'];
                }
            }

            return $choices;
        }

        /**
         * Prepare field
         *
         * @param $field
         *
         * @return mixed
         */
        public function prepare_field( $field ) {

            $field['choices'] = $this->get_choices();
            $field['type']    = $field['field_type'];

            return $field;
        }

        /**
         * Render field
         *
         * @param $field
         */
        public function render_field( $field ) {

            $value   = acf_get_array( $field['value'] );
            $choices = acf_get_array( $field['choices'] );

            // Placeholder
            if ( empty( $field['placeholder'] ) ) {
                $field['placeholder'] = _x( 'Select', 'verb', 'acf' );
            }

            // Add empty value (allows '' to be selected)
            if ( empty( $value ) ) {
                $value = array( '' );
            }

            $select = array(
                'id'    => $field['id'],
                'class' => $field['class'],
                'name'  => $field['name'],
            );

            $select['value']   = $value;
            $select['choices'] = $choices;

            acf_select_input( $select );
        }

        /**
         * Render field settings
         *
         * @param $field
         */
        public function render_field_settings( $field ) {

            // Field type
            acf_render_field_setting( $field, array(
                'label'        => __( 'Appearance', 'acf' ),
                'instructions' => __( 'Select the appearance of this field', 'acf' ),
                'type'         => 'select',
                'name'         => 'field_type',
                'optgroup'     => true,
                'choices'      => array(
                    'checkbox' => __( 'Checkbox', 'acf' ),
                    'radio'    => __( 'Radio Buttons', 'acf' ),
                    'select'   => _x( 'Select', 'noun', 'acf' ),
                ),
            ) );

            // Placeholder
            acf_render_field_setting( $field, array(
                'label'             => __( 'Placeholder Text', 'acf' ),
                'instructions'      => __( 'Appears within the input', 'acf' ),
                'type'              => 'text',
                'name'              => 'placeholder',
                'placeholder'       => _x( 'Select', 'verb', 'acf' ),
                'conditional_logic' => array(
                    array(
                        array(
                            'field'    => 'field_type',
                            'operator' => '==',
                            'value'    => 'select',
                        ),
                        array(
                            'field'    => 'allow_null',
                            'operator' => '==',
                            'value'    => '1',
                        ),

                    ),
                    array(
                        array(
                            'field'    => 'field_type',
                            'operator' => '==',
                            'value'    => 'select',
                        ),
                        array(
                            'field'    => 'ui',
                            'operator' => '==',
                            'value'    => '1',
                        ),

                    ),
                ),
            ) );

            // Select: multiple
            acf_render_field_setting( $field, array(
                'label'        => __( 'Select multiple values?', 'acf' ),
                'instructions' => '',
                'name'         => 'multiple',
                'type'         => 'true_false',
                'ui'           => 1,
                'conditions'   => array(
                    array(
                        array(
                            'field'    => 'field_type',
                            'operator' => '==',
                            'value'    => 'select',
                        ),
                    ),
                ),
            ) );

            // Select: UI
            acf_render_field_setting( $field, array(
                'label'        => __( 'Stylised UI', 'acf' ),
                'instructions' => '',
                'name'         => 'ui',
                'type'         => 'true_false',
                'ui'           => 1,
                'conditions'   => array(
                    array(
                        array(
                            'field'    => 'field_type',
                            'operator' => '==',
                            'value'    => 'select',
                        ),
                    ),
                ),
            ) );

            // Checkbox: layout
            acf_render_field_setting( $field, array(
                'label'        => __( 'Layout', 'acf' ),
                'instructions' => '',
                'type'         => 'radio',
                'name'         => 'layout',
                'layout'       => 'horizontal',
                'choices'      => array(
                    'vertical'   => __( 'Vertical', 'acf' ),
                    'horizontal' => __( 'Horizontal', 'acf' ),
                ),
                'conditions'   => array(
                    array(
                        array(
                            'field'    => 'field_type',
                            'operator' => '==',
                            'value'    => 'checkbox',
                        ),
                    ),
                    array(
                        array(
                            'field'    => 'field_type',
                            'operator' => '==',
                            'value'    => 'radio',
                        ),
                    ),
                ),
            ) );

            // Checkbox: toggle
            acf_render_field_setting( $field, array(
                'label'        => __( 'Toggle', 'acf' ),
                'instructions' => __( 'Prepend an extra checkbox to toggle all choices', 'acf' ),
                'name'         => 'toggle',
                'type'         => 'true_false',
                'ui'           => 1,
                'conditions'   => array(
                    array(
                        array(
                            'field'    => 'field_type',
                            'operator' => '==',
                            'value'    => 'checkbox',
                        ),
                    ),
                ),
            ) );
        }

        /**
         * Format value
         *
         * @param $value
         * @param $post_id
         * @param $field
         *
         * @return mixed
         */
        public function format_value( $value, $post_id, $field ) {

            $pip_tinymce = acf_get_instance( 'PIP_TinyMCE' );

            // Get all font families
            $choices = $pip_tinymce->get_custom_fonts();

            $return = null;
            if ( is_array( $value ) ) {
                foreach ( $value as $item ) {
                    // Get selected option
                    $font_style      = acf_maybe_get( $choices, $item );
                    $return[ $item ] = $font_style ? 'font-' . $font_style['class_name'] : $item;
                }
            } else {
                // Get selected option
                $font_style = acf_maybe_get( $choices, $value );
                $return     = $font_style ? 'font-' . $font_style['class_name'] : $value;
            }

            return $return;
        }

    }

    acf_new_instance( 'PIP_Font_Family_Field' );

}
