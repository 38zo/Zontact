<?php
/**
 * AJAX handler for Zontact.
 *
 * @package ThirtyEightZo\Zontact
 */

namespace ThirtyEightZo\Zontact;

defined( 'ABSPATH' ) || exit;

/**
 * Handles form submission via AJAX.
 */
final class Ajax {

	/**
	 * Register AJAX hooks.
	 *
	 * @return void
	 */
	public static function register(): void {
		$instance = new self();

		add_action( 'wp_ajax_zontact_submit', [ $instance, 'handle' ] );
		add_action( 'wp_ajax_nopriv_zontact_submit', [ $instance, 'handle' ] );
	}

	/**
	 * Handle AJAX submission.
	 *
	 * @return void
	 */
	public function handle(): void {
		check_ajax_referer( 'zontact_submit', 'nonce' );

		$name    = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
		$email   = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$message = zontact_sanitize_html( wp_unslash( $_POST['message'] ?? '' ) );
		$website = sanitize_text_field( wp_unslash( $_POST['website'] ?? '' ) );
		$consent = ! empty( $_POST['consent'] );

		$errors = [];

		if ( empty( $name ) ) {
			$errors['name'] = __( 'Name is required.', 'zontact' );
		}
		if ( empty( $email ) || ! is_email( $email ) ) {
			$errors['email'] = __( 'A valid email address is required.', 'zontact' );
		}
		if ( empty( $message ) ) {
			$errors['message'] = __( 'Message is required.', 'zontact' );
		}
		if ( ! empty( $website ) ) {
			wp_send_json_error(
				[ 'message' => __( 'Spam detected.', 'zontact' ) ],
				400
			);
		}

		$options = Options::get();

		if ( ! empty( $options['consent_text'] ) && ! $consent ) {
			$errors['consent'] = __( 'Consent is required.', 'zontact' );
		}

		if ( ! empty( $errors ) ) {
			wp_send_json_error( [ 'errors' => $errors ], 422 );
		}

		$this->send_email( $name, $email, $message, $options );
		$this->store_message( $name, $email, $message, $consent, $options );

		wp_send_json_success(
			[ 'message' => $options['success_message'] ?? __( 'Message sent successfully.', 'zontact' ) ]
		);
	}

	/**
	 * Send contact email.
	 *
	 * @param string $name    Sender name.
	 * @param string $email   Sender email.
	 * @param string $message Message body.
	 * @param array  $options Plugin options.
	 * @return void
	 */
	private function send_email( string $name, string $email, string $message, array $options ): void {
		$to       = $options['recipient_email'] ?? get_option( 'admin_email' );
		$subject  = $options['subject'] ?? __( 'New Zontact message', 'zontact' );
		$body     = sprintf(
			"Name: %s\nEmail: %s\n\nMessage:\n%s",
			$name,
			$email,
			wp_strip_all_tags( $message )
		);

		$site_domain = wp_parse_url( home_url(), PHP_URL_HOST );
		$from_email  = apply_filters( 'zontact_from_email', 'no-reply@' . $site_domain );
		$from_name   = apply_filters( 'zontact_from_name', wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ) );

		$headers = [
			"Reply-To: {$name} <{$email}>",
			"From: {$from_name} <{$from_email}>",
		];

		add_filter( 'wp_mail_content_type', fn() => 'text/plain; charset=UTF-8' );
		$sent = wp_mail( $to, $subject, $body, $headers );
		remove_all_filters( 'wp_mail_content_type' );

		if ( ! $sent ) {
			error_log( 'Zontact: wp_mail failed sending to ' . $to ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
			wp_send_json_error(
				[ 'message' => __( 'Unable to send email. Please try again later.', 'zontact' ) ],
				500
			);
		}
	}

	/**
	 * Optionally store message as CPT entry.
	 *
	 * @param string $name    Sender name.
	 * @param string $email   Sender email.
	 * @param string $message Message body.
	 * @param bool   $consent Consent given.
	 * @param array  $options Plugin options.
	 * @return void
	 */
	private function store_message( string $name, string $email, string $message, bool $consent, array $options ): void {
		if ( empty( $options['save_messages'] ) || ! post_type_exists( 'zontact_message' ) ) {
			return;
		}

		wp_insert_post(
			[
				'post_type'    => 'zontact_message',
				'post_status'  => 'private',
				'post_title'   => wp_trim_words( "{$name} – " . wp_strip_all_tags( $message ), 8, '…' ),
				'post_content' => $message,
				'meta_input'   => [
					'zontact_email'   => $email,
					'zontact_consent' => $consent ? 'yes' : 'no',
					'zontact_ip'      => sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' ),
					'zontact_ua'      => sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ?? '' ),
				],
			],
			true
		);
	}
}
