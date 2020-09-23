<?php

if ( !class_exists( 'PIP_Field_Groups' ) ) {

    /**
     * Class PIP_Field_Groups
     */
    class PIP_Field_Groups {

        public function __construct() {

            add_action( 'current_screen', array( $this, 'current_screen' ) );

        }

        function current_screen(){

            if(!acf_is_screen('acf-field-group') || pip_is_layout_screen())
                return;

            add_action( 'acf/field_group/admin_head', array( $this, 'metaboxes' ) );

        }

        function metaboxes(){

            // Remove Pilo'Press Layotus Categories / Collections Metaboxes
            remove_meta_box( 'acf-layouts-categorydiv', 'acf-field-group', 'side' );
            remove_meta_box( 'acf-layouts-collectiondiv', 'acf-field-group', 'side' );

        }

    }

    acf_new_instance( 'PIP_Field_Groups' );

}
