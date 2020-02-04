<?php

if ( ! class_exists( 'PIP_Field_Groups' ) ) {
	class PIP_Field_Groups {

		public function __construct() {

			// WP hooks
			add_action( 'current_screen', array( $this, 'current_screen' ) );
			add_filter( 'pre_delete_post', array( $this, 'delete_post' ), 10, 2 );
			add_filter( 'pre_trash_post', array( $this, 'delete_post' ), 10, 2 );

			// ACF hooks
			add_action( 'acf/field_group/admin_head', array( $this, 'layout_meta_boxes' ) );
		}

		/**
		 * Pilo'Press meta boxes
		 */
		public function layout_meta_boxes() {
			// Get current field group
			global $field_group;

			// Is a layout field group ?
			$is_layout = acf_maybe_get( $field_group, '_pip_is_layout' );

			// If mirror flexible page, don't register meta boxes
			if ( $field_group['key'] === PIP_Flexible_Content::get_flexible_mirror_group_key() ) {
				return;
			}

			// Meta box: Is layout ?
			add_meta_box( 'pip_is_layout', __( "Pilo'Press: Layout", 'pilopress' ), array(
				$this,
				'render_meta_box_side',
			), 'acf-field-group', 'side', 'core', array( 'field_group' => $field_group ) );

			// Meta box: Layout settings
			if ( $is_layout ) {
				add_meta_box( 'pip_layout_settings', __( "Pilo'Press: Flexible Layout settings", 'pilopress' ), array(
					$this,
					'render_meta_box_main',
				), 'acf-field-group', 'normal', 'high', array( 'field_group' => $field_group ) );
			}
		}

		/**
		 * Meta box: side
		 *
		 * @param $post
		 * @param $meta_box
		 */
		public function render_meta_box_side( $post, $meta_box ) {
			$field_group = $meta_box['args']['field_group'];

			$field_group['_pip_is_layout'] = isset( $field_group['_pip_is_layout'] ) ? $field_group['_pip_is_layout'] : 0;

			if ( acf_maybe_get_GET( 'layout' ) ) {
				$field_group['_pip_is_layout'] = 1;
			}

			// Layout settings
			acf_render_field_wrap( array(
				'label'        => __( 'Field group as layout', 'pilopress' ),
				'name'         => '_pip_is_layout',
				'prefix'       => 'acf_field_group',
				'type'         => 'true_false',
				'ui'           => 1,
				'instructions' => '',
				'value'        => $field_group['_pip_is_layout'],
				'required'     => false,
			) );
		}

		/**
		 *  Meta box: Main
		 *
		 * @param $post
		 * @param $meta_box
		 */
		public function render_meta_box_main( $post, $meta_box ) {
			$field_group = $meta_box['args']['field_group'];

			// Layout
			$layout_name        = sanitize_title( str_replace( 'Layout: ', '', $field_group['title'] ) );
			$layout_path_prefix = str_replace( home_url() . '/wp-content/themes/', '', _PIP_THEME_STYLE_URL ) . '/layouts/' . $layout_name . '/';

			// Category
			acf_render_field_wrap( array(
				'label'         => __( 'Catégorie', 'pilopress' ),
				'instructions'  => __( 'Nom de catégorie du layout', 'pilopress' ),
				'type'          => 'text',
				'name'          => '_pip_category',
				'prefix'        => 'acf_field_group',
				'default_value' => 'classic',
				'value'         => isset( $field_group['_pip_category'] ) ? $field_group['_pip_category'] : 'Classic',
			) );

			// Layout
			acf_render_field_wrap( array(
				'label'         => __( 'Layout', 'pilopress' ),
				'instructions'  => __( 'Nom du fichier de layout', 'pilopress' ),
				'type'          => 'text',
				'name'          => '_pip_render_layout',
				'prefix'        => 'acf_field_group',
				'placeholder'   => $layout_name . '.php',
				'default_value' => $layout_name . '.php',
				'prepend'       => $layout_path_prefix,
				'value'         => isset( $field_group['_pip_render_layout'] ) ? $field_group['_pip_render_layout'] : '',
			) );

			// Style
			acf_render_field_wrap( array(
				'label'         => __( 'Style', 'pilopress' ),
				'instructions'  => __( 'Nom du fichier de style', 'pilopress' ),
				'type'          => 'text',
				'name'          => '_pip_render_style',
				'prefix'        => 'acf_field_group',
				'placeholder'   => $layout_name . '.css',
				'default_value' => $layout_name . '.css',
				'prepend'       => $layout_path_prefix,
				'value'         => isset( $field_group['_pip_render_style'] ) ? $field_group['_pip_render_style'] : '',
			) );

			// Script
			acf_render_field_wrap( array(
				'label'         => __( 'Script', 'pilopress' ),
				'instructions'  => __( 'Nom du fichier de script', 'pilopress' ),
				'type'          => 'text',
				'name'          => '_pip_render_script',
				'prefix'        => 'acf_field_group',
				'placeholder'   => $layout_name . '.js',
				'default_value' => $layout_name . '.js',
				'prepend'       => $layout_path_prefix,
				'value'         => isset( $field_group['_pip_render_script'] ) ? $field_group['_pip_render_script'] : '',
			) );

			// Get layouts for configuration field
			$choices = array();
			foreach ( PIP_Flexible_Content::get_layout_group_keys() as $layout_group_key ) {
				// Get current field group
				$group = acf_get_field_group( $layout_group_key );

				// Save title
				$choices[ $group['key'] ] = $group['title'];
			}

			// Configuration
			acf_render_field_wrap( array(
				'label'         => __( 'Configuration', 'pilopress' ),
				'instructions'  => __( 'Clones de configuration', 'pilopress' ),
				'type'          => 'select',
				'name'          => '_pip_configuration',
				'prefix'        => 'acf_field_group',
				'value'         => ( isset( $field_group['_pip_configuration'] ) ? $field_group['_pip_configuration'] : '' ),
				'choices'       => $choices,
				'allow_null'    => 1,
				'multiple'      => 1,
				'ui'            => 1,
				'ajax'          => 0,
				'return_format' => 0,
			) );

			// Miniature
			acf_render_field_wrap( array(
				'label'         => __( 'Thumbnail', 'pilopress' ),
				'instructions'  => __( 'Aperçu du layout', 'pilopress' ),
				'name'          => '_pip_thumbnail',
				'type'          => 'image',
				'class'         => '',
				'prefix'        => 'acf_field_group',
				'value'         => ( isset( $field_group['_pip_thumbnail'] ) ? $field_group['_pip_thumbnail'] : '' ),
				'return_format' => 'array',
				'preview_size'  => 'thumbnail',
				'library'       => 'all',
			) );

			// Script for admin style
			?>
            <script type="text/javascript">
              if (typeof acf !== 'undefined') {
                acf.postbox.render({
                  'id': 'pip_layout_settings',
                  'label': 'left'
                })
              }
            </script>
			<?php
		}

		/**
		 * Fire actions on acf field groups page
		 */
		public function current_screen() {
			// ACF field groups archive
			if ( acf_is_screen( 'edit-acf-field-group' ) ) {
				add_action( 'load-edit.php', array( $this, 'load_edit' ) );
				add_filter( 'page_row_actions', array( $this, 'row_actions' ), 10, 2 );
			}

			// ACF field group single
			if ( acf_is_screen( 'acf-field-group' ) ) {
				add_action( 'acf/input/admin_head', array( $this, 'meta_boxes' ) );
			}
		}

		/**
		 * Generate flexible mirror
		 */
		public function load_edit() {
			// If mirror flexible already exists, return
			if ( acf_get_field_group( PIP_Flexible_Content::get_flexible_mirror_group_key() ) ) {
				return;
			}

			// Mirror flexible field group
			$flexible_mirror = array(
				'key'                   => PIP_Flexible_Content::get_flexible_mirror_group_key(),
				'title'                 => 'Flexible Content',
				'fields'                => array(),
				'location'              => array(
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'all',
						),
					),
					array(
						array(
							'param'    => 'taxonomy',
							'operator' => '==',
							'value'    => 'all',
						),
					),
				),
				'menu_order'            => 0,
				'position'              => 'normal',
				'style'                 => 'seamless',
				'label_placement'       => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen'        => '',
				'active'                => true,
				'description'           => '',
				'acfe_display_title'    => '',
				'acfe_autosync'         => '',
				'acfe_permissions'      => '',
				'acfe_form'             => 0,
				'acfe_meta'             => '',
				'acfe_note'             => '',
			);

			// Import flexible in local
			acf_import_field_group( $flexible_mirror );
		}

		/**
		 * Remove trash action for mirror flexible field group
		 *
		 * @param $actions
		 * @param $post
		 *
		 * @return mixed
		 */
		public function row_actions( $actions, $post ) {
			// If not mirror flexible, return
			if ( $post->post_name !== PIP_Flexible_Content::get_flexible_mirror_group_key() ) {
				return $actions;
			}

			// Remove trash action
			unset( $actions['trash'] );

			return $actions;
		}

		/**
		 * Customize meta boxes on mirror flexible content page
		 */
		public function meta_boxes() {
			global $field_group;

			// If not mirror flexible group field, return
			if ( $field_group['key'] !== PIP_Flexible_Content::get_flexible_mirror_group_key() ) {
				return;
			}

			// Remove meta boxes
			remove_meta_box( 'acf-field-group-options', 'acf-field-group', 'normal' );
			remove_meta_box( 'acf-field-group-fields', 'acf-field-group', 'normal' );
			remove_meta_box( 'slugdiv', 'acf-field-group', 'normal' );
			remove_meta_box( 'acf-field-group-acfe-side', 'acf-field-group', 'side' );
			remove_meta_box( 'acf-field-group-acfe', 'acf-field-group', 'normal' );
			remove_meta_box( 'acfe-wp-custom-fields', 'acf-field-group', 'normal' );

			// Add meta box
			add_meta_box( 'pip-flexible-layouts', 'Layouts disponibles', array( $this, 'layouts_meta_box' ), 'acf-field-group', 'normal', 'high' );
		}

		/**
		 * Add custom meta box for mirror flexible
		 */
		public function layouts_meta_box() {
			foreach ( PIP_Flexible_Content::get_layout_group_keys() as $layout_group_key ) {
				// Get current field group
				$layout_field_group = acf_get_field_group( $layout_group_key );

				// Get locations html
				$locations = ''; // PILO_TODO: get ACFE helper (next version)

				// Structured array for template file
				$layouts[] = array(
					'title'     => $layout_field_group['title'],
					'locations' => $locations,
					'edit_link' => get_edit_post_link( $layout_field_group['ID'] ),
				);
			}

			// New field group link
			$add_new_link = add_query_arg(
				array(
					'post_type' => 'acf-field-group',
					'layout'    => 1,
				),
				admin_url( 'post-new.php' )
			);

			// Template file
			include_once( _PIP_PATH . 'includes/views/flexible-layouts-meta-box.php' );
		}

		/**
		 * Prevent removal of mirror flexible field group
		 *
		 * @param $trash
		 * @param $post
		 *
		 * @return bool
		 */
		private function delete_post( $trash, $post ) {
			// If not mirror flexible group field, return
			$flexible_mirror_group_key = PIP_Flexible_Content::get_flexible_mirror_group_key();
			if ( $post->post_name !== $flexible_mirror_group_key && $post->post_name !== $flexible_mirror_group_key . '__trashed' ) {
				return $trash;
			}

			// Prevent delete/trash field group
			return false;
		}
	}

	// Instantiate class
	new PIP_Field_Groups();
}