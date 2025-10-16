<?php
/**
 * Registers custom post types for Zontact.
 *
 * @package ThirtyEightZo\Zontact
 */

namespace ThirtyEightZo\Zontact;

use WP_Post_Type;

defined( 'ABSPATH' ) || exit;

/**
 * Handles Zontact custom post type registration.
 */
final class Cpt {

	/**
	 * Initialize the CPT registration.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'init', [ $this, 'register_post_types' ] );
	}

	/**
	 * Registers the custom post type for contact messages.
	 *
	 * @return void
	 */
	public function register_post_types(): void {
		$labels = [
			'name'               => __( 'Zontact Messages', 'zontact' ),
			'singular_name'      => __( 'Zontact Message', 'zontact' ),
			'menu_name'          => __( 'Zontact Messages', 'zontact' ),
			'name_admin_bar'     => __( 'Zontact Message', 'zontact' ),
			'add_new'            => __( 'Add New', 'zontact' ),
			'add_new_item'       => __( 'Add New Message', 'zontact' ),
			'edit_item'          => __( 'Edit Message', 'zontact' ),
			'view_item'          => __( 'View Message', 'zontact' ),
			'search_items'       => __( 'Search Messages', 'zontact' ),
			'not_found'          => __( 'No messages found.', 'zontact' ),
			'not_found_in_trash' => __( 'No messages found in Trash.', 'zontact' ),
		];

		$args = [
			'labels'             => $labels,
			'public'             => false,
			'show_ui'            => current_user_can( 'manage_options' ),
			'show_in_menu'       => 'options-general.php',
			'supports'           => [ 'title', 'editor', 'custom-fields' ],
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
			'rewrite'            => false,
			'query_var'          => false,
			'can_export'         => false,
		];

		register_post_type( 'zontact_message', $args );
	}
}
