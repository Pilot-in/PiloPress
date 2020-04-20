<?php

if ( !class_exists( 'PIP_Pattern' ) ) {
    class PIP_Pattern {

        public static $pattern_option_page;

        public function __construct() {
            // WP hooks
            add_action( 'init', array( $this, 'register_option_page' ) );

            // ACF hooks - Pattern location rule
            add_filter( 'acf/location/rule_values/options_page', array( $this, 'remove_pattern_from_post_types' ) );
            add_filter( 'acf/location/rule_types', array( $this, 'location_types' ) );
            add_filter( 'acf/location/rule_values/pip-pattern', array( $this, 'location_values' ) );
            add_filter( 'acf/location/match_rule/type=pip-pattern', array( $this, 'location_match' ), 10, 3 );
        }

        /**
         * Add option page
         */
        public function register_option_page() {
            // Capability
            $capability = apply_filters( 'pip/options/capability', acf_get_setting( 'capability' ) );
            if ( !current_user_can( $capability ) ) {
                return;
            }

            // Add option page
            $option_page = acf_add_options_page(
                array(
                    'page_title'  => __( 'Pattern', 'pilopress' ),
                    'menu_title'  => __( 'Pattern', 'pilopress' ),
                    'menu_slug'   => 'pip-pattern',
                    'capability'  => $capability,
                    'parent_slug' => 'pilopress',
                    'post_id'     => 'pip_pattern',
                    'autoload'    => true,
                )
            );

            // Set pattern option page
            self::set_pattern_option_page( $option_page );
        }

        public function remove_pattern_from_post_types( $choices ) {
            // Remove Pattern
            unset( $choices['pip-pattern'] );

            return $choices;
        }

        /**
         * Add component rule
         *
         * @param $choices
         *
         * @return mixed
         */
        public function location_types( $choices ) {
            // Add component option
            $choices["Pilo'Press"]['pip-pattern'] = __( 'Pattern', 'pilopress' );

            return $choices;
        }

        /**
         * Component rule values
         *
         * @param $choices
         *
         * @return array
         */
        public function location_values( $choices ) {
            // Add options
            $choices = array(
                'all'                                                 => __( 'All', 'acf' ),
                PIP_Flexible_Header::get_flexible_header_field_name() => __( 'Header', 'pilopress' ),
                PIP_Flexible_Footer::get_flexible_footer_field_name() => __( 'Footer', 'pilopress' ),
            );

            return $choices;
        }

        /**
         * Component rule matches
         *
         * @param $result
         * @param $rule
         * @param $screen
         *
         * @return bool
         */
        public function location_match( $result, $rule, $screen ) {
            $match = false;

            // If not on Pattern page, return
            if ( !acf_maybe_get( $screen, 'pip-pattern' ) ) {
                return $match;
            }

            if ( $rule['value'] === 'all' ) {

                // Allow "all" to match any value.
                $match = true;

            } elseif ( $rule['value'] === $screen['pip-pattern'] ) {
                $match = true;
            }

            // Allow for "!=" operator.
            if ( $rule['operator'] == '!=' ) {
                $match = !$match;
            }

            return $match;
        }

        /**
         * Getter: $pattern_option_page
         * @return mixed
         */
        public static function get_pattern_option_page() {
            return self::$pattern_option_page;
        }

        /**
         * Setter: $pattern_option_page
         *
         * @param mixed $pattern_option_page
         */
        public static function set_pattern_option_page( $pattern_option_page ) {
            self::$pattern_option_page = $pattern_option_page;
        }
    }

    // Instantiate class
    new PIP_Pattern();
}
