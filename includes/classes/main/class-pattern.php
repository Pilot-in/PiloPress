<?php

if ( !class_exists( 'PIP_Pattern' ) ) {
    class PIP_Pattern {

        public static $pattern_option_page;

        public function __construct() {
            add_action( 'init', array( $this, 'register_option_page' ) );
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

            // Get parent slug
            $flexible_mirror = PIP_Flexible_Mirror::get_flexible_mirror_group();
            $parent_slug     = 'post.php?post=' . $flexible_mirror['ID'] . '&action=edit';

            // Add option page
            $option_page = acf_add_options_page(
                array(
                    'page_title'  => __( 'Pattern', 'pilopress' ),
                    'menu_title'  => __( 'Pattern', 'pilopress' ),
                    'menu_slug'   => 'pip-pattern',
                    'capability'  => $capability,
                    'parent_slug' => $parent_slug,
                    'post_id'     => 'pip_pattern',
                    'autoload'    => true,
                )
            );

            // Set pattern option page
            self::set_pattern_option_page( $option_page );
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
