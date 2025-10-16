<?php

namespace ThirtyEightZo\Zontact;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Cpt {
	public static function register() {
		$labels = array(
			'name' => __( 'Zontact Messages', 'Zontact' ),
			'singular_name' => __( 'Zontact Message', 'Zontact' ),
		);
		register_post_type( 'zontact_message', array(
			'labels' => $labels,
			'public' => false,
			'show_ui' => current_user_can( 'manage_options' ),
			'show_in_menu' => 'options-general.php',
			'supports' => array( 'title','editor','custom-fields' ),
			'capability_type' => 'post',
			'map_meta_cap' => true,
		) );
	}
}


