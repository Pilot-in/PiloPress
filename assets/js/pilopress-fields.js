(
    function ( $ ) {

        // Avoid admin error when acf is not present
        if ( typeof acf === 'undefined' ) {
            return;
        }

        // Pilo'Press field types
        var field_types = [
            'pip_font_color',
            'pip_font_family',
            'pip_font_style',
            'pip_button_styles',
        ]

        // Register condition for field types
        $.map(
            field_types,
            function ( field_type ) {
                acf.registerConditionForFieldType( 'hasValue', field_type )
                acf.registerConditionForFieldType( 'hasNoValue', field_type )
                acf.registerConditionForFieldType( 'Contains', field_type )
                acf.registerConditionForFieldType( 'SelectEqualTo', field_type )
                acf.registerConditionForFieldType( 'SelectNotEqualTo', field_type )
                acf.registerConditionForFieldType( 'SelectionGreaterThan', field_type )
                acf.registerConditionForFieldType( 'SelectionLessThan', field_type )
            }
        )

    }
)( jQuery )
