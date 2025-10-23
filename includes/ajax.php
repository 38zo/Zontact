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
		$message = wp_kses_post( wp_unslash( $_POST['message'] ?? '' ) );
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
	 * Store message in database if enabled.
	 *
	 * @param string $name    Sender name.
	 * @param string $email   Sender email.
	 * @param string $message Message body.
	 * @param bool   $consent Consent given.
	 * @param array  $options Plugin options.
	 * @return void
	 */
	private function store_message( string $name, string $email, string $message, bool $consent, array $options ): void {
		if ( empty( $options['save_messages'] ) ) {
			return;
		}

		$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
		$ua = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) );

		$meta = [
			'consent' => $consent ? 'yes' : 'no',
		];

		// Allow extensions to augment stored meta before insert.
		$meta = (array) apply_filters( 'zontact_store_meta', $meta, compact( 'name', 'email', 'message', 'consent', 'options' ) );

		// Ensure table exists before attempting insert (self-healing in case activation didn't run).
		Database::maybe_install();

		Database::insert_message(
			[
				'form_key'   => 'default',
				'name'       => $name,
				'email'      => $email,
				'phone'      => null,
				'subject'    => $options['subject'] ?? null,
				'message'    => $message,
				'meta'       => wp_json_encode( $meta ),
				'ip_address' => $ip,
				'user_agent' => $ua,
			]
		);
	}
}
