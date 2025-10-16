<?php
/**
 * Enqueue frontend assets for Zontact.
 *
 * @package ThirtyEightZo\Zontact
 */

namespace ThirtyEightZo\Zontact;

defined( 'ABSPATH' ) || exit;

/**
 * Handles scripts, styles, and localized data.
 */
final class Assets {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public static function register(): void {
		$instance = new self();

		add_action( 'wp_enqueue_scripts', [ $instance, 'enqueue' ] );
		add_action( 'wp_enqueue_scripts', [ $instance, 'add_inline_styles' ], 20 );
	}

	/**
	 * Enqueue plugin styles and scripts.
	 *
	 * @return void
	 */
	public function enqueue(): void {
		$options = Options::get();

		wp_enqueue_style(
			'zontact',
			ZONTACT_URL . 'assets/css/zontact.css',
			[],
			ZONTACT_VERSION
		);

		wp_enqueue_script(
			'zontact',
			ZONTACT_URL . 'assets/js/zontact.js',
			[],
			ZONTACT_VERSION,
			true
		);

		wp_localize_script(
			'zontact',
			'Zontact',
			[
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'zontact_submit' ),
				'strings'  => [
					'sending' => __( 'Sending…', 'zontact' ),
					'error'   => __( 'Please fix the errors and try again.', 'zontact' ),
					'success' => $options['success_message'] ?? __( 'Message sent successfully!', 'zontact' ),
				],
			]
		);
	}

	/**
	 * Add inline accent color styles.
	 *
	 * @return void
	 */
	public function add_inline_styles(): void {
		$options = Options::get();
		$accent  = ! empty( $options['accent_color'] ) ? esc_attr( $options['accent_color'] ) : '#0073aa';

		wp_add_inline_style(
			'zontact',
			":root { --zontact-accent: {$accent}; }"
		);
	}
}
