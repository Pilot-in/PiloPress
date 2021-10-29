(
    function ( $ ) {

        if ( typeof acf === 'undefined' ) {
            return;
        }

        $( document ).ready(
            function () {
                // Pilo'Press field types
                const field_types = [
                    'pip_font_color',
                    'pip_font_family',
                    'pip_font_style',
                    'pip_button_styles',
                ];

                // Register condition for field types
                $.each(
                    field_types,
                    function ( key, field_type ) {
                        if ( field_type && $.isFunction( acf.registerConditionForFieldType ) ) {

                            acf.registerConditionForFieldType( 'hasValue', field_type );
                            acf.registerConditionForFieldType( 'hasNoValue', field_type );
                            acf.registerConditionForFieldType( 'Contains', field_type );
                            acf.registerConditionForFieldType( 'SelectEqualTo', field_type );
                            acf.registerConditionForFieldType( 'SelectNotEqualTo', field_type );
                            acf.registerConditionForFieldType( 'SelectionGreaterThan', field_type );
                            acf.registerConditionForFieldType( 'SelectionLessThan', field_type );

                        }
                    },
                );

            },
        );
    }
)( jQuery );
