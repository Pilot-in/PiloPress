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
        var $renderSCSS     = $('#acf_field_group-_pip_render_style_scss');
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
            $renderSCSS.val((pip.sanitize_title(val) ? pip.sanitize_title(val) : 'style') + '.scss');
            $renderScript.val((pip.sanitize_title(val) ? pip.sanitize_title(val) : 'scrip') + '.js');
        }

        /**
         * Remove search for layouts admin page
         */
        var searchParams = new URLSearchParams(window.location.search);
        if ($('body').hasClass('wp-admin', 'post-type-acf-field-group') && searchParams.get('layouts') == 1) {
            $('.subsubsub li:last-child:not([class])').remove();
        }

        // Compile styles button
        $('#wp-admin-bar-compile_scss').on('click', function (e) {
            e.preventDefault();

            // Variables
            let confirmed              = '';
            let page_title             = '.wrap h1';
            let body                   = $('body');
            let compiling_message      = '<div class="notice notice-info is-dismissible compiling-notice"><p>Compiling...</p></div>';
            let compiled_message       = '<div class="notice notice-success is-dismissible compiling-notice"><p>Styles compiled successfully!</p></div>';
            let compiled_error_message = '<div class="notice notice-error is-dismissible compiling-notice"><p>An error occurred while compiling.</p></div>';
            let options_page           = (body.hasClass('admin_page_pip-styles-demo')
                                          || body.hasClass('admin_page_pip-styles-css')
                                          || body.hasClass('admin_page_pip-styles-fonts')
                                          || body.hasClass('admin_page_pip-styles-colors')
                                          || body.hasClass('admin_page_pip-styles-bt-options')
                                          || body.hasClass('admin_page_pip-styles-typography')
                                          || body.hasClass('admin_page_pip-styles-btn-form')
                                          || body.hasClass('admin_page_pip-styles-image-sizes'));

            // On styles option page, check if modified options
            if (options_page && $('#_acf_changed').val() == 1) {
                confirmed = confirm('Are you sure? You will lose all your changes. Click on "Update" button to save it.');
            }

            // Cancel action
            if (!confirmed && options_page) {
                return;
            }

            // Show loading message
            $(compiling_message).insertAfter(page_title);

            // AJAX action
            $.post(
                ajaxurl,
                {
                    'action': 'compile_styles',
                },
                function (response) {
                    if (response === '1') {
                        $('.compiling-notice').remove();
                        $(compiled_message).insertAfter(page_title);
                    } else {
                        $('.compiling-notice').remove();
                        $(compiled_error_message).insertAfter(page_title);
                    }
                }
            );
        });
    });

    /**
     * Sanitize value like wp function "sanitize_title"
     * @param $val
     * @returns {string}
     */
    pip.sanitize_title = function ($val) {
        return $val.toLowerCase()
            .replace(/\s+/g, '-')       // Replace spaces with -
            .replace(/[^\w\-]+/g, '')   // Remove all non-word chars
            .replace(/\-\-+/g, '-')     // Replace multiple - with single -
            .replace(/\_\_+/g, '_')     // Replace multiple _ with single _
            .replace(/^-+/, '');        // Trim - from start of text
    };

})(jQuery);
