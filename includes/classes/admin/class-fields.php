<?php

if ( !class_exists( 'PIP_Fields' ) ) {
    class PIP_Fields {
        public function __construct() {
            // ACF hooks
            add_action( 'acf/input/admin_footer', array( $this, 'customize_color_picker' ) );
            add_action( 'acf/render_field_settings/type=color_picker', array( $this, 'add_setting_color_picker' ) );
            add_action( 'acf/render_field/type=color_picker', array( $this, 'bootstrap_colors_only' ), 15 );
        }

        /**
         * Customize color picker saved colors
         */
        public function customize_color_picker() {
            // Get colors
            $pip_colors = get_field( 'pip_colors', 'styles_colors' );
            if ( !$pip_colors ) {
                return;
            }

            // Separate color value and color name
            $color_range       = array();
            $color_range_names = array();
            foreach ( $pip_colors as $name => $color ) {
                $color_range[]       = '"' . $color . '"';
                $color_range_names[] = $name;
            }

            // Prepare variables for JS
            $color_range       = implode( ',', $color_range );
            $color_range_names = json_encode( $color_range_names ); ?>

            <script type="text/javascript">
              (function ($) {

                // Custom palette
                acf.addFilter('color_picker_args', function (args) {
                  args.palettes = [<?php echo $color_range ?>];
                  return args;
                });

                var fix_color_picker_height = function (field) {
                  var $iris_el = field.$el;
                  var $iris_palettes = $iris_el.find('.iris-palette');
                  var paletteCount = $iris_palettes.length;

                  // Dirty loop to wait till "wpColorPicker" is loaded (no event so we have no choice)
                  if (!paletteCount) { setTimeout(() => { fix_color_picker_height(field); }, 500); }

                  var paletteRowCount = Math.ceil(paletteCount / 10);
                  var palettes = <?php echo $color_range_names ?>;
                  var $iris_slider = $iris_el.find('.iris-slider');
                  var $iris_picker = $iris_el.find('.iris-picker');

                  // Fix height
                  $iris_slider.css('height', '180px');
                  $iris_picker.css({ height: 240 + (paletteRowCount * 10) + 'px', 'padding-bottom': '15px' });

                  // Add tooltip and fix style
                  $iris_palettes.each(function (index) {
                    var $iris_palette = $(this), paletteName = palettes[index];

                    $iris_palette.attr('title', paletteName).addClass('acf-js-tooltip');
                    $iris_palette.css({ height: '20px', width: '20px', 'margin-left': '', 'margin-right': '3px', 'margin-top': '3px' });
                  });
                };
                acf.addAction('new_field/type=color_picker', fix_color_picker_height, 20);

              })(jQuery);
            </script>

            <?php
        }

        /**
         * Add field to show/hide free color picker
         *
         * @param $field
         */
        public function add_setting_color_picker( $field ) {
            acf_render_field_setting( $field, array(
                'label'         => __( 'Show bootstrap colors only', 'pilopress' ),
                'instructions'  => __( '' ),
                'name'          => 'bootstrap_colors_only',
                'type'          => 'true_false',
                'ui'            => '1',
                'default_value' => '0',
            ) );
        }

        /**
         * Hide free color picker on ACF field
         *
         * @param $field
         */
        public function bootstrap_colors_only( $field ) {
            if ( !isset( $field['bootstrap_colors_only'] ) ) {
                return;
            }

            $field_key = $field['key'];
            if ( !$field_key ) {
                return;
            }

            $bootstrap_colors_only = $field['bootstrap_colors_only'] ? $field['bootstrap_colors_only'] : false;
            if ( $bootstrap_colors_only !== 1 ) {
                return;
            }

            ?>
            <script type="text/javascript">
              (function () {

                var hide_color_picker = function (field) {
                  var $iris_el = field.$el;
                  var $iris_palette_container = $iris_el.find('.iris-palette-container');
                  var $iris_picker = $iris_el.find('.iris-picker');
                  var $iris_picker_inner = $iris_el.find('.iris-picker-inner');

                  // Dirty loop to wait till "wpColorPicker" is loaded (no event so we have no choice)
                  if (!$iris_picker_inner.length) { setTimeout(() => { hide_color_picker(field); }, 500); }

                  // Hide color picker and fix style
                  $iris_picker_inner.hide();
                  $iris_picker.css({ width: '255px', height: 'auto', position: 'relative', border: '0px', padding: '0px' });
                  $iris_palette_container.css({ position: 'relative', top: 'auto', left: 'auto', right: 'auto', bottom: 'auto' });
                };

                acf.addAction('new_field/key=<?php echo $field_key; ?>', hide_color_picker, 20);

              })();
            </script>
            <?php
        }
    }

    // Instantiate class
    new PIP_Fields();
}