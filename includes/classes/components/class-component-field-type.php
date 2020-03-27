<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( !class_exists( 'PIP_Component_Field_Type' ) ) {
    class PIP_Component_Field_Type extends acf_field {

        private $initial_value;

        public function __construct() {
            $this->name     = 'pip_component';
            $this->label    = __( 'Component', 'pilopress' );
            $this->category = 'relational';
            $this->defaults = array(
                'field_type'    => 'radio',
                'multiple'      => 0,
                'allow_null'    => 0,
                'choices'       => array(),
                'default_value' => '',
                'ui'            => 0,
                'ajax'          => 0,
                'placeholder'   => '',
                'layout'        => '',
                'toggle'        => 0,
                'allow_custom'  => 0,
                'return_format' => 'name',
            );

            parent::__construct();
        }

        /**
         * Get components posts for choices
         *
         * @param bool $field
         *
         * @return array
         */
        public static function get_choices( $field = false ) {
            $choices = array();

            // If field, get allowed options
            $post_in = null;
            if ( $field ) {
                $post_in = $field['pip_components'];
            }

            // Get all components
            $args  = array(
                'post_type'      => PIP_Components::$post_type,
                'posts_per_page' => - 1,
                'post__in'       => $post_in,
            );
            $posts = get_posts( $args );
            if ( empty( $posts ) ) {
                return $choices;
            }

            // Get only titles
            foreach ( $posts as $post ) {
                $choices[ $post->ID ] = $post->post_title;
            }

            return $choices;
        }

        /**
         * Load value
         *
         * @param $value
         * @param $post_id
         * @param $field
         *
         * @return array|int|string
         */
        public function load_value( $value, $post_id, $field ) {
            // Store value for format value
            if ( is_numeric( $value ) ) {
                $this->initial_value = $value;
            }

            // Get component sub fields
            $sub_fields = get_field_objects( $this->initial_value, false );
            if ( !$sub_fields ) {
                return $value;
            }

            // Format values
            $values = array();
            foreach ( $sub_fields as $sub_field ) {
                $values[ $sub_field['key'] ] = $sub_field['value'];
            }

            return $values;
        }

        /**
         * Prepare values
         *
         * @param $field
         *
         * @return mixed
         */
        public function prepare_field( $field ) {
            $field['choices'] = self::get_choices( $field );
            $field['type']    = $field['field_type'];
            $field['value']   = $this->initial_value ? $this->initial_value : '';

            return $field;
        }

        /**
         * Format value
         *
         * @param $value
         * @param $post_id
         * @param $field
         *
         * @return array|bool
         */
        public function format_value( $value, $post_id, $field ) {
            return get_fields( $this->initial_value, true );
        }

        /**
         * Render settings
         *
         * @param $field
         */
        public function render_field_settings( $field ) {
            if ( isset( $field['default_value'] ) ) {
                $field['default_value'] = acf_encode_choices( $field['default_value'], false );
            }

            // Allow components
            acf_render_field_setting( $field, array(
                'label'        => __( 'Allow components', 'pilopress' ),
                'instructions' => '',
                'type'         => 'select',
                'name'         => 'pip_components',
                'choices'      => self::get_choices(),
                'multiple'     => 1,
                'ui'           => 1,
                'allow_null'   => 1,
                'placeholder'  => __( 'All components', 'pilopress' ),
            ) );

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

            // Default value
            acf_render_field_setting( $field, array(
                'label'        => __( 'Default Value', 'acf' ),
                'instructions' => __( 'Enter each default value on a new line', 'acf' ),
                'name'         => 'default_value',
                'type'         => 'textarea',
            ) );

            // Select + Radio: allow null
            acf_render_field_setting( $field, array(
                'label'        => __( 'Allow Null?', 'acf' ),
                'instructions' => '',
                'name'         => 'allow_null',
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
                    array(
                        array(
                            'field'    => 'field_type',
                            'operator' => '==',
                            'value'    => 'radio',
                        ),
                    ),
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

            // Select: AJAX
            acf_render_field_setting( $field, array(
                'label'        => __( 'Use AJAX to lazy load choices?', 'acf' ),
                'instructions' => '',
                'name'         => 'ajax',
                'type'         => 'true_false',
                'ui'           => 1,
                'conditions'   => array(
                    array(
                        array(
                            'field'    => 'field_type',
                            'operator' => '==',
                            'value'    => 'select',
                        ),
                        array(
                            'field'    => 'ui',
                            'operator' => '==',
                            'value'    => 1,
                        ),
                    ),
                ),
            ) );

            // Radio: other choice
            acf_render_field_setting( $field, array(
                'label'        => __( 'Other', 'acf' ),
                'instructions' => '',
                'name'         => 'other_choice',
                'type'         => 'true_false',
                'ui'           => 1,
                'message'      => __( "Add \"other\" choice to allow for custom values", 'acf' ),
                'conditions'   => array(
                    array(
                        array(
                            'field'    => 'field_type',
                            'operator' => '==',
                            'value'    => 'radio',
                        ),
                    ),
                ),
            ) );

            // Radio: save other choice
            acf_render_field_setting( $field, array(
                'label'        => __( 'Save Other', 'acf' ),
                'instructions' => '',
                'name'         => 'save_other_choice',
                'type'         => 'true_false',
                'ui'           => 1,
                'message'      => __( "Save \"other\" values to the field's choices", 'acf' ),
                'conditions'   => array(
                    array(
                        array(
                            'field'    => 'field_type',
                            'operator' => '==',
                            'value'    => 'radio',
                        ),
                        array(
                            'field'    => 'other_choice',
                            'operator' => '==',
                            'value'    => 1,
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
                    'vertical'   => __( "Vertical", 'acf' ),
                    'horizontal' => __( "Horizontal", 'acf' ),
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

            // Checkbox: other choice
            acf_render_field_setting( $field, array(
                'label'        => __( 'Allow Custom', 'acf' ),
                'instructions' => '',
                'name'         => 'allow_custom',
                'type'         => 'true_false',
                'ui'           => 1,
                'message'      => __( "Allow \"custom\" values to be added", 'acf' ),
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

            // Checkbox: save other choice
            acf_render_field_setting( $field, array(
                'label'        => __( 'Save Custom', 'acf' ),
                'instructions' => '',
                'name'         => 'save_custom',
                'type'         => 'true_false',
                'ui'           => 1,
                'message'      => __( "Save \"custom\" values to the field's choices", 'acf' ),
                'conditions'   => array(
                    array(
                        array(
                            'field'    => 'field_type',
                            'operator' => '==',
                            'value'    => 'checkbox',
                        ),
                        array(
                            'field'    => 'allow_custom',
                            'operator' => '==',
                            'value'    => 1,
                        ),
                    ),
                ),
            ) );
        }

    }

    // Initialize
    acf_register_field_type( 'PIP_Component_Field_Type' );
}
