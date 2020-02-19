(function ($) {
  'use strict';

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
  var fonts = {
    primary: 'Roboto',
    secondary: 'Dancing Script',
    tertiary: 'Freshman',
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
        inline: 'span',
        classes: 'text-' + key,
        textStyle: 'text-' + key,
        wrapper: true,
        deep: true,
        split: true,
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
        text: font,
        block: 'span',
        classes: 'font-' + key,
        textStyle: 'font-family:' + font,
        wrapper: true,
        deep: true,
        split: true,
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
        block: 'span',
        classes: key,
        textStyle: key,
        wrapper: true,
        deep: true,
        split: true,
      };
    });
  };

  $(document).on('tinymce-editor-setup', function (event, editor) {
    // Register custom commands
    Commands.register(editor);

    /**
     * Add colors menu button
     */
    editor.addButton('pip_colors', function () {

      var clean_items = Array();

      // Clone original items array
      $.extend(true, clean_items, get_custom_colors());

      // Remove classes for menu items
      $.each(clean_items, function (key, item) {
        delete item.classes;
      });

      return {
        type: 'listbox',
        text: 'Colors',
        tooltip: 'Colors',
        values: clean_items,
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

      var clean_items = Array();

      // Clone original items array
      $.extend(true, clean_items, get_custom_fonts());

      // Remove classes for menu items
      $.each(clean_items, function (key, item) {
        delete item.classes;
      });

      return {
        type: 'listbox',
        text: 'Fonts',
        tooltip: 'Fonts',
        values: clean_items,
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

      var clean_items = Array();

      // Clone original items array
      $.extend(true, clean_items, get_custom_styles());

      // Remove classes for menu items
      $.each(clean_items, function (key, item) {
        delete item.classes;
      });

      return {
        type: 'listbox',
        text: 'Styles',
        tooltip: 'Styles',
        values: clean_items,
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
        editor.formatter.register(item.name, item);
      });
      get_custom_fonts().map(function (item) {
        editor.formatter.register(item.name, item);
      });
      get_custom_styles().map(function (item) {
        editor.formatter.register(item.name, item);
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