<?php

namespace ThirtyEightZo\Zontact;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Assets {
	public static function register() {
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue' ] );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'inline_styles' ], 20 );
	}

	public static function enqueue() {
		$opts = Options::get();
		wp_enqueue_style( 'zontact', ZONTACT_URL . 'assets/css/zontact.css', array(), ZONTACT_VERSION );
		wp_enqueue_script( 'zontact', ZONTACT_URL . 'assets/js/zontact.js', array(), ZONTACT_VERSION, true );
		wp_localize_script( 'zontact', 'Zontact', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'zontact_submit' ),
			'strings' => array(
				'sending' => __( 'Sending…', 'Zontact' ),
				'error' => __( 'Please fix the errors and try again.', 'Zontact' ),
				'success' => $opts['success_message'],
			),
		) );
	}

	public static function inline_styles() {
		$accent = Options::get()['accent_color'];
		wp_add_inline_style( 'zontact', ':root{--zontact-accent:' . esc_attr( $accent ) . ';}' );
	}
}


