(function ($) {
    'use strict';

    // The global acf object
    var pip = {};

    // Set as a browser global
    window.pip = pip;

    $(document).ready(function () {

        /**
         * Layout admin page
         */

        var $title          = $('#title');
        var $prepend        = $('.acf-input-prepend span');
        var $layoutSlug     = $('#acf_field_group-_pip_layout_slug');
        var $layoutTemplate = $('#acf_field_group-_pip_render_layout');
        var $renderCSS      = $('#acf_field_group-_pip_render_style');
        var $renderScript   = $('#acf_field_group-_pip_render_script');
        var layoutSwitch    = false;

        /**
         * When something is typed in "title" field
         */
        $title.on('input', function () {
            // Get title
            var $this = $(this);

            // If new layout
            if ($('#auto_draft').val() === '1' && !layoutSwitch) {
                // Change values with sanitized slug
                change_values($this, true);
            }
        });

        /**
         * When something is typed in "layout slug" field
         */
        $layoutSlug.on('input', function () {
            // Get layout slug
            var $this = $(this);

            layoutSwitch = true;

            // Change values with sanitized slug
            change_values($this);
        });

        /**
         * Change input & span values
         * @param $this
         * @param draft
         */
        function change_values ($this, draft = false) {
            $layoutSlug.val(pip.sanitize_title($this.val()));
            $prepend.html(pip.sanitize_title($this.val().replace(/-$/, '')));

            if (draft) {
                updateRenderSettings($this.val());
            }

            if (!$this.val()) {
                $prepend.html('layout');
            }
        }

        /**
         * Change render settings values
         * @param val
         */
        function updateRenderSettings (val) {
            $layoutTemplate.val((pip.sanitize_title(val) ? pip.sanitize_title(val) : 'template') + '.php');
            $renderCSS.val((pip.sanitize_title(val) ? pip.sanitize_title(val) : 'style') + '.css');
            $renderScript.val((pip.sanitize_title(val) ? pip.sanitize_title(val) : 'scrip') + '.js');
        }

        /**
         * Remove search for layouts admin page
         */
        var searchParams = new URLSearchParams(window.location.search);
        if ($('body').hasClass('wp-admin', 'post-type-acf-field-group') && searchParams.get('layouts') == 1) {
            $('.subsubsub li:last-child:not([class])').remove();
        }
    });

    /**
     * Sanitize value like WP function "sanitize_title"
     * @param $val
     * @returns {string}
     */
    pip.sanitize_title = function ($val) {
        return $val.toLowerCase()
            .replace(/\s+/g, '-')                // Replace spaces with -
            .replace(/\-\-+/g, '-')              // Replace multiple - with single -
            .replace(/\_\_+/g, '_')              // Replace multiple _ with single _
            .replace(/^-+/, '')                  // Trim - from start of text
            .normalize('NFD')                                   // Change accent to unicode value
            .replace(/[\u0300-\u036f]/g, '')     // From unicode value to letter
            .replace(/[^a-zA-Z0-9_\-\s]+/g, ''); // Remove all non-word chars
    };

})(jQuery);
