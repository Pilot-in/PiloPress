<?php

if ( !class_exists( 'PIP_Pattern' ) ) {

    /**
     * Class PIP_Pattern
     */
    class PIP_Pattern {

        /**
         * Option page
         *
         * @var array
         */
        public $pattern_option_page;

        /**
         * Menu slug
         *
         * @var string
         */
        public $menu_slug = 'pip-pattern';

        /**
         * Post ID
         *
         * @var string
         */
        public $pattern_post_id = 'pip_pattern';

        public function __construct() {

            // Fix post id when using multilingual plugin
            $this->pattern_post_id = acf_get_valid_post_id( $this->pattern_post_id );

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

            // Add option page
            $option_page = acf_add_options_page(
                array(
                    'page_title'  => __( 'Site Template', 'pilopress' ),
                    'menu_title'  => __( 'Site Template', 'pilopress' ),
                    'menu_slug'   => $this->menu_slug,
                    'capability'  => $capability,
                    'parent_slug' => 'pilopress',
                    'post_id'     => $this->pattern_post_id,
                    'autoload'    => true,
                )
            );

            // Set pattern option page
            $this->set_pattern_option_page( $option_page );
        }

        /**
         * Remove pattern from post types
         *
         * @param $choices
         *
         * @return mixed
         */
        public function remove_pattern_from_post_types( $choices ) {

            // Remove Pattern
            unset( $choices[ $this->menu_slug ] );

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
            $choices["Pilo'Press"][ $this->menu_slug ] = __( 'Site Template', 'pilopress' );

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

            $pip_flexible_header = acf_get_instance( 'PIP_Flexible_Header' );
            $pip_flexible_footer = acf_get_instance( 'PIP_Flexible_Footer' );

            // Add options
            $choices = array(
                'all' => __( 'All', 'acf' ),

                $pip_flexible_header->get_flexible_header_field_name() => __( 'Header', 'pilopress' ),
                $pip_flexible_footer->get_flexible_footer_field_name() => __( 'Footer', 'pilopress' ),
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
            if ( !acf_maybe_get( $screen, $this->menu_slug ) ) {
                return $match;
            }

            if ( $rule['value'] === 'all' ) {

                // Allow "all" to match any value.
                $match = true;

            } elseif ( $rule['value'] === $screen[ $this->menu_slug ] ) {
                $match = true;
            }

            // Allow for "!=" operator.
            if ( $rule['operator'] === '!=' ) {
                $match = !$match;
            }

            return $match;
        }

        /**
         * Getter: $pattern_option_page
         *
         * @return mixed
         */
        public function get_pattern_option_page() {

            return $this->pattern_option_page;
        }

        /**
         * Setter: $pattern_option_page
         *
         * @param mixed $pattern_option_page
         */
        public function set_pattern_option_page( $pattern_option_page ) {

            $this->pattern_option_page = $pattern_option_page;
        }

    }

    acf_new_instance( 'PIP_Pattern' );

}
