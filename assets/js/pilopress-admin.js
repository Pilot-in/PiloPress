(function ($) {

  $(document).ready(function () {

    new acf;

    var $title = $('#title');
    var $prepend = $('.acf-input-prepend span');
    var $layoutSlug = $('#acf_field_group-_pip_layout_slug');
    var $layoutTemplate = $('#acf_field_group-_pip_render_layout');
    var $renderCSS = $('#acf_field_group-_pip_render_style');
    var $renderSCSS = $('#acf_field_group-_pip_render_style_scss');
    var $renderScript = $('#acf_field_group-_pip_render_script');
    var layoutSwitch = false;

    // When something is typed in "title" field
    $title.on('input', function () {
      // Get slug
      var $this = $(this);

      // If new layout
      if ($('#auto_draft').val() === '1' && !layoutSwitch) {
        // Change values with sanitized slug
        change_values($this, true);
      }
    });

    // When something is typed in "layout slug" field
    $layoutSlug.on('input', function () {
      // Get slug
      var $this = $(this);

      layoutSwitch = true;

      // Change values with sanitized slug
      change_values($this);

      if (!$this.val()) {
        $prepend.html('layout');
        $this.focus();
      }
    });

    /**
     * Change input & span values
     * @param $this
     * @param draft
     */
    function change_values ($this, draft = false) {
      $layoutSlug.val(sanitized_title($this.val()));
      $prepend.html(sanitized_title($this.val()));

      if (draft) {
        updateRenderSettings($this.val());
      }
    }

    /**
     * Change render settings values
     * @param val
     */
    function updateRenderSettings (val) {
      $layoutTemplate.val(sanitized_title(val) + '.php');
      $renderCSS.val(sanitized_title(val) + '.css');
      $renderSCSS.val(sanitized_title(val) + '.scss');
      $renderScript.val(sanitized_title(val) + '.js');
    }

    /**
     * Sanitized value like wp function "sanitized_title"
     * @param $val
     * @returns {string}
     */
    function sanitized_title ($val) {
      return $val.toLowerCase()
        .replace(/\s+/g, '-')       // Replace spaces with -
        .replace(/[^\w\-]+/g, '')   // Remove all non-word chars
        .replace(/\-\-+/g, '-')     // Replace multiple - with single -
        .replace(/\_\_+/g, '_')     // Replace multiple _ with single _
        .replace(/^-+/, '');         // Trim - from start of text
    }
  });

})(jQuery);
