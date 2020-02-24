(function ($) {

  // Check if "acf" is available
  if (typeof acf === 'undefined') {
    return;
  }

  /**
   * Define variables
   */
  var fonts = acf.get('custom_fonts');
  var colors = {
    primary: 'Primary',
    secondary: 'Secondary',
    success: 'Success',
    info: 'Info',
    warning: 'Warning',
    danger: 'Danger',
    light: 'Light',
    dark: 'Dark',
    body: 'Body',
    muted: 'Muted',
    white: 'White',
    'white-50': 'White 50%',
    'black-50': 'Black 50%',
  };
  var styles = {
    h1: 'H1 style',
    h2: 'H2 style',
    h3: 'H3 style',
    h4: 'H4 style',
    h5: 'H5 style',
    h6: 'H6 style',
  };

  /**
   * Colors
   * @returns {*}
   */
  var get_custom_colors = function () {
    return $.map(colors, function (color, key) {
      return {
        name: 'pip-text-' + key,
        value: 'pip-text-' + key,
        text: color,
        textStyle: 'text-' + key,
        format: {
          inline: 'span',
          classes: 'text-' + key,
          wrapper: true,
          deep: true,
          split: true,
        }
      };
    });
  };

  /**
   * Fonts
   * @returns {*}
   */
  var get_custom_fonts = function () {
    return $.map(fonts, function (font, key) {
      return {
        name: 'pip-font-' + key,
        value: 'pip-font-' + key,
        text: font.name,
        textStyle: 'font-family:' + font.font,
        format: {
          inline: 'span',
          classes: 'font-' + key,
          wrapper: true,
          deep: true,
          split: true,
        }
      };
    });
  };

  /**
   * Styles
   * @returns {*}
   */
  var get_custom_styles = function () {
    return $.map(styles, function (style, key) {
      return {
        name: 'pip-style-' + key,
        value: 'pip-style-' + key,
        text: style,
        textStyle: key,
        format: {
          block: 'span',
          classes: key,
          wrapper: true,
          deep: true,
          split: true,
        }
      };
    });
  };

  /**
   * Customize TinyMCE Editor
   */
  acf.addFilter('wysiwyg_tinymce_settings', function (init) {

    init.toolbar1 = 'formatselect pip_styles pip_fonts pip_colors _pip_shortcodes_button bold italic underline strikethrough bullist numlist alignleft aligncenter alignright alignjustify link wp_add_media wp_adv';
    init.toolbar2 = 'blockquote hr forecolor backcolor pastetext removeformat charmap outdent indent subscript superscript fullscreen wp_help';

    init.menubar = true;
    init.elementpath = false;
    init.block_formats = '<p>=p;<h1>=h1;<h2>=h2;<h3>=h3;<h4>=h4;<h5>=h5;<h6>=h6;<address>=address;<pre>=pre';
    init.valid_elements = '*[*]';
    init.extended_valid_elements = '*[*]';

    return init;
  });

  // Wait for TinyMCE to be ready
  $(document).on('tinymce-editor-setup', function (event, editor) {

    // Register custom commands
    Commands.register(editor);

    /**
     * Add colors menu button
     */
    editor.addButton('pip_colors', function () {
      return {
        type: 'listbox',
        text: 'Colors',
        tooltip: 'Colors',
        values: get_custom_colors(),
        fixedWidth: true,
        onPostRender: custom_list_box_change_handler(editor, get_custom_colors()),
        onselect: function (event) {
          if (event.control.settings.value) {
            editor.execCommand('add_custom_style', false, event.control.settings);
          }
        },
      };
    });

    /**
     * Add fonts menu button
     */
    editor.addButton('pip_fonts', function () {
      return {
        type: 'listbox',
        text: 'Fonts',
        tooltip: 'Fonts',
        values: get_custom_fonts(),
        fixedWidth: true,
        onPostRender: custom_list_box_change_handler(editor, get_custom_fonts()),
        onselect: function (event) {
          if (event.control.settings.value) {
            editor.execCommand('add_custom_style', false, event.control.settings);
          }
        },
      };
    });

    /**
     * Add styles menu button
     */
    editor.addButton('pip_styles', function () {
      return {
        type: 'listbox',
        text: 'Styles',
        tooltip: 'Styles',
        values: get_custom_styles(),
        fixedWidth: true,
        onPostRender: custom_list_box_change_handler(editor, get_custom_styles()),
        onselect: function (event) {
          if (event.control.settings.value) {
            editor.execCommand('add_custom_style', false, event.control.settings);
          }
        },
      };
    });

    /**
     * Register custom formats
     */
    editor.on('init', function () {
      get_custom_colors().map(function (item) {
        editor.formatter.register(item.name, item.format);
      });

      get_custom_fonts().map(function (item) {
        editor.formatter.register(item.name, item.format);
      });

      get_custom_styles().map(function (item) {
        editor.formatter.register(item.name, item.format);
      });
    });

  });

  /**
   * Register custom commands
   * @param editor
   */
  var register = function (editor) {
    editor.addCommand('add_custom_style', function (command, item) {
      editor.formatter.toggle(item.name);
      editor.nodeChanged();
    });
  };
  var Commands = { register: register };

  /**
   * Set active on menu item
   * @param editor
   * @param items
   * @returns {function(...[*]=)}
   */
  var custom_list_box_change_handler = function (editor, items) {
    return function () {
      var self = this;
      self.value(null);

      editor.on('nodeChange', function (e) {

        // Get value
        var current_value = null;
        $.map(items, function (item) {
          if (editor.formatter.match(item.name)) {
            current_value = item.value;
          }
        });

        // Update value
        self.value(current_value);

      });
    };
  };

})(jQuery);