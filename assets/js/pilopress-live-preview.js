jQuery( document ).ready(
    function ( $ ) {

        var $typo_classes = $( '.acf-field-typography-classes' );

        // Load styles on page load
        $typo_classes.each(
            function () {
                apply_styles_to_preview( $( this ).find( 'textarea' ) );
            },
        );

        // Change classes on typing
        $typo_classes.find( 'textarea' ).keyup(
            function () {
                apply_styles_to_preview( $( this ) );
            },
        );

        /**
         * Change classes for live preview
         *
         * @param $textarea
         */
        function apply_styles_to_preview( $textarea ) {
            var $live_preview = $textarea.parents( '.acf-row' ).find( '.acf-field-typography-preview' );
            $live_preview.find( '.pip-live-preview span' ).removeClass();
            $live_preview.find( '.pip-live-preview span' ).addClass( $textarea.val() );
        }
    },
);
