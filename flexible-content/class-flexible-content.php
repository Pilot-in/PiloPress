<?php

defined( 'ABSPATH' ) || exit;


if ( ! class_exists( 'PIP_Flexible_Content' ) ) {
	class PIP_Flexible_Content {

		private   $flexible_mirror_field_name = '_pip_flexible_mirror';
		private   $flexible_mirror_group_key  = 'group_pip_flexible_mirror';
		private   $flexible_field_name        = '_pip_flexible';
		private   $flexible_group_key         = 'group_pip_flexible_main';
		private   $user_view                  = 'edit';
		private   $icons                      = array(
			'edit'                  => array(
				'post_type',
				'post_template',
				'post_status',
				'post_format',
				'post',
			),
			'media-default'         => array(
				'page_template',
				'page_type',
				'page_parent',
				'page',
			),
			'admin-users'           => array(
				'current_user',
				'user_form',
			),
			'welcome-widgets-menus' => array(
				'widget',
				'nav_menu',
				'nav_menu_item',
			),
			'category'              => array(
				'taxonomy',
				'post_category',
				'post_taxonomy',
			),
			'admin-comments'        => array(
				'comment',
			),
			'paperclip'             => array(
				'attachment',
			),
			'admin-settings'        => array(
				'options_page',
			),
			'businessman'           => array(
				'current_user_role',
				'user_role',
			),
		);
		protected $layout_group_keys;

		public function __construct() {
			// WP hooks
			add_action( 'init', array( $this, '_pip_init' ) );
			add_action( 'current_screen', array( $this, '_pip_current_screen' ) );
			add_filter( 'pre_delete_post', array( $this, '_pip_delete_post' ), 10, 2 );
			add_filter( 'pre_trash_post', array( $this, '_pip_delete_post' ), 10, 2 );

			// ACF hooks
			add_action( "acf/prepare_field/name={$this->flexible_field_name}", array( $this, '_pip_prepare_field' ), 20 );
			add_action( "acf/prepare_field/name={$this->flexible_mirror_field_name}", array( $this, '_pip_prepare_field_mirror' ), 20 );
			add_action( 'acf/validate_field/type=flexible_content', array( $this, '_pip_validate_field' ), 20 );

			// Pilo'Press hooks
			add_filter( 'pip/flexible/locations', array( $this, '_pip_flexible_locations' ) );
		}

		/**
		 * Register main flexible field group
		 * Add layouts to main flexible
		 */
		public function _pip_init() {
			$layouts      = array();
			$group_keys   = array();
			$field_groups = acf_get_field_groups();

			// Get layouts
			if ( $field_groups ) {
				foreach ( $field_groups as $field_group ) {
					if ( strpos( $field_group['title'], 'Layout:' ) === 0 ) {

						$title          = str_replace( 'Layout: ', '', $field_group['title'] );
						$name           = sanitize_title( $title );
						$layout_uniq_id = 'layout_' . $name;

						$layouts[ $layout_uniq_id ] = [
							'key'        => $layout_uniq_id,
							'name'       => $name,
							'label'      => $title,
							'display'    => 'row',
							'sub_fields' => [
								[
									'key'               => 'field_clone_' . $name,
									'label'             => $title,
									'name'              => $name,
									'type'              => 'clone',
									'instructions'      => '',
									'required'          => 0,
									'conditional_logic' => 0,
									'wrapper'           => [
										'width' => '',
										'class' => '',
										'id'    => '',
									],
									'acfe_permissions'  => '',
									'clone'             => [
										$field_group['key'],
									],
									'display'           => 'seamless',
									'layout'            => 'block',
									'prefix_label'      => 0,
									'prefix_name'       => 1,
									'acfe_clone_modal'  => 0,
								],
							],
							'min'        => '',
							'max'        => '',
						];

						$group_keys[] = $field_group['key'];
					}
				}
			}
			$this->layout_group_keys = $group_keys;

			$locations = apply_filters( 'pip/flexible/locations', array() );

			// Main flexible content field group
			$args = array(
				'key'                   => $this->flexible_group_key,
				'title'                 => 'Flexible Content',
				'fields'                => array(
					array(
						'key'                               => 'field_pip_' . $this->flexible_field_name,
						'label'                             => 'Flexible Content',
						'name'                              => $this->flexible_field_name,
						'type'                              => 'PIP_Flexible_Content',
						'instructions'                      => '',
						'required'                          => 0,
						'conditional_logic'                 => 0,
						'wrapper'                           => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'acfe_permissions'                  => '',
						'acfe_flexible_stylised_button'     => 1,
						'acfe_flexible_layouts_thumbnails'  => 0,
						'acfe_flexible_layouts_settings'    => 0,
						'acfe_flexible_layouts_ajax'        => 0,
						'acfe_flexible_layouts_templates'   => 0,
						'acfe_flexible_layouts_placeholder' => 0,
						'acfe_flexible_disable_ajax_title'  => 0,
						'acfe_flexible_close_button'        => 0,
						'acfe_flexible_title_edition'       => 0,
						'acfe_flexible_copy_paste'          => 0,
						'acfe_flexible_modal_edition'       => 0,
						'acfe_flexible_modal'               => array(
							'acfe_flexible_modal_enabled' => '0', // PILO_TODO: Switch to 1
						),
						'acfe_flexible_layouts_state'       => '',
						'layouts'                           => $layouts,
						'button_label'                      => 'Ajouter une ligne',
						'min'                               => '',
						'max'                               => '',
					),
				),
				'location'              => $locations,
				'menu_order'            => 0,
				'position'              => 'normal',
				'style'                 => 'seamless',
				'label_placement'       => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen'        => array(
					'the_content',
				),
				'active'                => true,
				'description'           => '',
				'acfe_display_title'    => '',
				'acfe_autosync'         => '',
				'acfe_permissions'      => '',
				'acfe_form'             => 0,
				'acfe_meta'             => '',
				'acfe_note'             => '',
			);

			acf_add_local_field_group( $args );
		}

		/**
		 * Fire actions on acf field groups page
		 *
		 * @param $screen
		 */
		public function _pip_current_screen( $screen ) {
			// Acf field groups archive
			if ( acf_is_screen( 'edit-acf-field-group' ) ) {
				add_action( 'load-edit.php', array( $this, '_pip_load_edit' ) );
				add_filter( 'page_row_actions', array( $this, '_pip_row_actions' ), 10, 2 );
			}

			// Acf field group single
			if ( acf_is_screen( 'acf-field-group' ) ) {
				add_action( 'acf/input/admin_head', array( $this, '_pip_meta_boxes' ) );
			}
		}

		/**
		 * Customize meta boxes on mirror flexible content page
		 */
		public function _pip_meta_boxes() {
			global $field_group;

			// If not mirror flexible group field, return
			if ( $field_group['key'] !== $this->flexible_mirror_group_key ) {
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
			add_meta_box( 'pip-flexible-layouts', 'Layouts disponibles', array( $this, '_pip_layouts_meta_box' ), 'acf-field-group', 'normal', 'high' );
		}

		/**
		 * Add custom meta box for mirror flexible
		 */
		public function _pip_layouts_meta_box() {
			$layout_group_keys = $this->layout_group_keys['args'][0];
			foreach ( $layout_group_keys as $layout_group_key ) {
				// Get current field group
				$layout_field_group = acf_get_field_group( $layout_group_key );

				// Get locations html
				$locations = $this->_pip_get_locations_icons( $layout_field_group );

				// Get status html
				$status = '';
				if ( ! $layout_field_group['active'] ) {
					$status = '<i class="acf-icon -minus yellow small acf-js-tooltip" title="' . esc_attr__( 'Inactive', 'acf' ) . '"></i> ';
				}

				// Structured array for template file
				$layouts[] = array(
					'title'     => $layout_field_group['title'],
					'locations' => $locations,
					'edit_link' => get_edit_post_link( $layout_field_group['ID'] ),
					'status'    => $status,
				);
			}

			// New field group link
			$add_new_link = add_query_arg( array( 'post_type' => 'acf-field-group' ), admin_url( 'post-new.php' ) );;

			include_once( _PIP_PATH . 'flexible-content/views/flexible-layouts-meta-box.php' );
		}

		/**
		 * Get html with icons for field group locations
		 *
		 * @param $field_group
		 *
		 * @return string|null
		 */
		public function _pip_get_locations_icons( $field_group ) {
			$choices = acf_get_location_rule_types();

			if ( ! isset( $field_group['location'] ) || empty( $field_group['location'] ) || empty( $choices ) ) {
				return null;
			}

			$final = array();

			$icon_default = 'admin-generic';

			foreach ( $choices as $key => $sub_choices ) {
				foreach ( $sub_choices as $choice_slug => $choice_name ) {
					$final_icon = $icon_default;
					foreach ( $this->icons as $icon => $icon_slugs ) {
						foreach ( $icon_slugs as $icon_slug ) {
							if ( $choice_slug != $icon_slug ) {
								continue;
							}

							$final_icon = $icon;
							break( 2 );
						}
					}

					$final[ $choice_slug ] = array(
						'name' => $choice_name,
						'icon' => $final_icon,
					);
				}
			}


			$html = array();
			foreach ( $field_group['location'] as $or ) {
				foreach ( $or as $and ) {
					if ( ! isset( $final[ $and['param'] ] ) || ! isset( $and['value'] ) ) {
						continue;
					}

					$final_name = $and['value'];
					$values     = acf_get_location_rule_values( $and );

					if ( ! empty( $values ) && is_array( $values ) ) {
						foreach ( $values as $value_slug => $value_name ) {
							if ( $and['value'] != $value_slug ) {
								continue;
							}

							if ( is_array( $value_name ) && isset( $value_name[ $and['value'] ] ) ) {
								$final_name = $value_name[ $and['value'] ];
							} else {
								$final_name = $value_name;
							}

							break;
						}
					}

					$name = '<span class="acf-js-tooltip dashicons dashicons-' . $final[ $and['param'] ]['icon'] . '" title="' . $final[ $and['param'] ]['name'] . ' = ' . $final_name . '"></span>';
					if ( $and['operator'] === '!=' ) {
						$name = '<span class="acf-js-tooltip dashicons dashicons-' . $final[ $and['param'] ]['icon'] . '" title="' . $final[ $and['param'] ]['name'] . ' != ' . $final_name . '" style="color:#ccc;"></span>';
					}

					$html[] = $name;
				}
			}

			return implode( ' ', $html );
		}

		/**
		 * Generate flexible mirror
		 */
		public function _pip_load_edit() {
			if ( acf_get_field_group( $this->flexible_mirror_group_key ) ) {
				return;
			}

			$flexible_mirror = array(
				'key'                   => $this->flexible_mirror_group_key,
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
		public function _pip_row_actions( $actions, $post ) {
			if ( $post->post_name !== $this->flexible_mirror_group_key ) {
				return $actions;
			}

			unset( $actions['trash'] );

			return $actions;
		}

		/**
		 * Parse all field groups and show only those for current screen
		 *
		 * @param $field
		 *
		 * @return mixed
		 */
		public function _pip_validate_field( $field ) {
			// If not main flexible, return
			if ( $field['name'] !== $this->flexible_field_name ) {
				return $field;
			}

			// If no layouts, return
			if ( empty( $field['layouts'] ) ) {
				return $field;
			}

			// Initiate layouts to empty array for returns
			$layouts          = $field['layouts'];
			$field['layouts'] = array();

			// Get post_id and screen
			$screen  = acf_get_form_data( 'screen' );
			$post_id = acf_get_form_data( 'post_id' );

			/**
			 * Extract ACF id from URL id
			 * @var $id
			 */
			extract( acf_get_post_id_info( $post_id ) );

			// Get args depending on screen
			switch ( $screen ) {
				case 'user':
					$args = array(
						'user_id'   => $id,
						'user_form' => $this->user_view,
					);
					break;
				case 'attachment':
					$args = array(
						'attachment_id' => $id,
						'attachment'    => $id,
					);
					break;
				case 'taxonomy':
					if ( ! empty( $id ) ) {
						$term     = get_term( $id );
						$taxonomy = $term->taxonomy;
					} else {
						$taxonomy = acf_maybe_get_GET( 'taxonomy' );
					}

					$args = array(
						'taxonomy' => $taxonomy,
					);
					break;
				case 'post':
					$post_type = get_post_type( $post_id );

					// If Dynamic Template: Stop! // PILO_TODO: uncomment
//				if ( $post_type === 'acfe-template' ) {
//					return $field;
//				}

					$args = array(
						'post_id'   => $post_id,
						'post_type' => $post_type,
					);
					break;
			}

			// If no args, return
			if ( empty( $args ) ) {
				return $field;
			}

			// Get all fields groups (hidden included)
			$field_groups = acf_get_field_groups();

			// If no field groups, return
			if ( empty( $field_groups ) ) {
				return $field;
			}

			// Array for valid layouts
			$keep = array();

			foreach ( $field_groups as $field_group ) {

				// If current screen not included in field group location, skip
				if ( ! $this->_pip_get_field_group_visibility( $field_group, $args ) ) {
					continue;
				}

				// Sanitize label and name
				$field_group_label = str_ireplace( 'Layout: ', '', $field_group['title'] );
				$field_group_name  = sanitize_title( $field_group_label );

				// Browse all layouts
				foreach ( $layouts as $key => $layout ) {

					// If field group not in layouts, skip
					if ( $layout['name'] !== $field_group_name ) {
						continue;
					}

					// If field group in layouts, keep it
					$keep[ $key ] = $layout;
					break;
				}

			}

			// If no layouts, return
			if ( empty( $keep ) ) {
				return $field;
			}

			// Replace layouts
			$field['layouts'] = $keep;

			// Return field with layouts for current screen
			return $field;
		}

		/**
		 * Returns true if the given field group's location rules match the given $args
		 *
		 * @see ACF's acf_get_field_group_visibility()
		 *
		 * @param $field_group
		 * @param array $args
		 *
		 * @return bool
		 */
		public function _pip_get_field_group_visibility( $field_group, $args = array() ) {
			// Check if location rules exist
			if ( $field_group['location'] ) {

				// Get the current screen.
				$screen = acf_get_location_screen( $args );

				// Loop through location groups.
				foreach ( $field_group['location'] as $group ) {

					// ignore group if no rules.
					if ( empty( $group ) ) {
						continue;
					}

					// Loop over rules and determine if all rules match.
					$match_group = true;
					foreach ( $group as $rule ) {
						if ( ! acf_match_location_rule( $rule, $screen, $field_group ) ) {
							$match_group = false;
							break;
						}
					}

					// If this group matches, show the field group.
					if ( $match_group ) {
						return true;
					}
				}
			}

			return false;
		}

		/**
		 * Hide flexible if no layouts
		 *
		 * @param $field
		 *
		 * @return bool
		 */
		public function _pip_prepare_field( $field ) {
			if ( empty( $field['layouts'] ) ) {
				return false;
			}

			return $field;
		}

		/**
		 * Hide mirror flexible
		 *
		 * @return bool
		 */
		public function _pip_prepare_field_mirror() {
			return false;
		}

		/**
		 * Prevent removal of mirror flexible field group
		 *
		 * @param $trash
		 * @param $post
		 *
		 * @return bool
		 */
		public function _pip_delete_post( $trash, $post ) {
			// If not mirror flexible group field, return
			if ( $post->post_name !== $this->flexible_mirror_group_key && $post->post_name !== $this->flexible_mirror_group_key . '__trashed' ) {
				return $trash;
			}

			// Prevent delete/trash field group
			return false;
		}

		/**
		 * Get locations of mirror flexible
		 *
		 * @param $locations
		 *
		 * @return mixed
		 */
		public function _pip_flexible_locations( $locations ) {
			$mirror = acf_get_field_group( $this->flexible_mirror_group_key );
			if ( ! $mirror ) {
				return $locations;
			}

			$locations = $mirror['location'];

			return $locations;
		}

	}

	// Instantiate class
	new PIP_Flexible_Content();
}
