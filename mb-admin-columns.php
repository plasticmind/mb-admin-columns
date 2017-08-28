<?php
/**
 * Plugin Name: MB Admin Columns
 * Plugin URI: https://metabox.io/plugins/mb-admin-columns/
 * Description: Show custom fields in the post list table.
 * Version: 1.2
 * Author: MetaBox.io
 * Author URI: https://metabox.io
 * License: GPL2+
 * Text Domain: mb-admin-columns
 * Domain Path: /languages/
 *
 * @package    Meta Box
 * @subpackage MB Admin Columns
 */

// Prevent loading this file directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MB_Admin_Columns' ) ) {
	/**
	 * Plugin main class.
	 */
	class MB_Admin_Columns {
		/**
		 * Constructor.
		 * Add hooks.
		 */
		public function __construct() {
			add_action( 'admin_init', array( $this, 'init' ) );
		}

		/**
		 * Initialization.
		 * Load plugin files and bootstrap for posts and taxonomies.
		 */
		public function init() {
			if ( ! defined( 'RWMB_VER' ) || class_exists( 'MB_Admin_Columns_Post' ) ) {
				return;
			}

			require_once dirname( __FILE__ ) . '/inc/class-mb-admin-columns-base.php';
			require_once dirname( __FILE__ ) . '/inc/class-mb-admin-columns-post.php';
			require_once dirname( __FILE__ ) . '/inc/class-mb-admin-columns-taxonomy.php';
			require_once dirname( __FILE__ ) . '/inc/class-mb-admin-columns-user.php';

			if ( function_exists( 'rwmb_get_registry' ) ) {
				$this->posts();
			} else {
				$this->posts_legacy();
			}

			$this->taxonomies();

			$this->users();
		}

		/**
		 * Add admin columns for posts.
		 */
		protected function posts() {
			$meta_boxes = rwmb_get_registry( 'meta_box' )->all();

			foreach ( $meta_boxes as $meta_box ) {
				$fields = array_filter( $meta_box->fields, array( $this, 'has_admin_columns' ) );
				if ( empty( $fields ) ) {
					continue;
				}

				foreach ( $meta_box->post_types as $post_type ) {
					new MB_Admin_Columns_Post( $post_type, $fields );
				}
			}
		}

		/**
		 * Add admin columns for posts. For Meta Box < 4.11.
		 *
		 * @deprecated Use $this->posts() function instead.
		 */
		protected function posts_legacy() {
			$meta_boxes = RWMB_Core::get_meta_boxes();
			$meta_boxes = array_map( array( 'RW_Meta_Box', 'normalize' ), $meta_boxes );

			foreach ( $meta_boxes as $meta_box ) {
				$fields = array_filter( $meta_box['fields'], array( $this, 'has_admin_columns' ) );
				if ( empty( $fields ) ) {
					continue;
				}

				foreach ( $meta_box['post_types'] as $post_type ) {
					new MB_Admin_Columns_Post( $post_type, $fields );
				}
			}
		}

		/**
		 * Add admin columns for terms.
		 */
		protected function taxonomies() {
			if ( ! class_exists( 'MB_Term_Meta_Loader' ) ) {
				return;
			}

			foreach ( MB_Term_Meta_Loader::$meta_boxes as $meta_box ) {
				$fields = array_filter( $meta_box['fields'], array( $this, 'has_admin_columns' ) );
				if ( empty( $fields ) ) {
					continue;
				}

				$taxonomies = (array) $meta_box['taxonomies'];
				foreach ( $taxonomies as $taxonomy ) {
					new MB_Admin_Columns_Taxonomy( $taxonomy, $fields );
				}
			}
		}

		/**
		 * Add admin columns for users.
		 */
		protected function users() {
			if ( ! class_exists( 'MB_User_Meta_Loader' ) ) {
				return;
			}

			foreach ( MB_User_Meta_Loader::$meta_boxes as $meta_box ) {
				$fields = array_filter( $meta_box['fields'], array( $this, 'has_admin_columns' ) );
				if ( empty( $fields ) ) {
					continue;
				}

				new MB_Admin_Columns_User( 'user', $fields );
			}
		}

		/**
		 * Check if field has admin columns.
		 *
		 * @param array $field Field configuration.
		 *
		 * @return bool
		 */
		protected function has_admin_columns( $field ) {
			return isset( $field['admin_columns'] );
		}
	}

	new MB_Admin_Columns;
} // End if().
