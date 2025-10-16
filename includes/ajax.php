<?php

namespace ThirtyEightZo\Zontact;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Ajax {
	public static function register() {
		add_action( 'wp_ajax_zontact_submit', [ __CLASS__, 'handle' ] );
		add_action( 'wp_ajax_nopriv_zontact_submit', [ __CLASS__, 'handle' ] );
	}

	public static function handle() {
		check_ajax_referer( 'zontact_submit', 'nonce' );

		$name = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
		$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$message = wp_kses_post( wp_unslash( $_POST['message'] ?? '' ) );
		$website = sanitize_text_field( wp_unslash( $_POST['website'] ?? '' ) );
		$consent = ! empty( $_POST['consent'] );

		$errors = array();
		if ( empty( $name ) ) { $errors['name'] = __( 'Name is required.', 'Zontact' ); }
		if ( empty( $email ) || ! is_email( $email ) ) { $errors['email'] = __( 'A valid email is required.', 'Zontact' ); }
		if ( empty( $message ) ) { $errors['message'] = __( 'Message is required.', 'Zontact' ); }
		if ( ! empty( $website ) ) { wp_send_json_error( array( 'message' => __( 'Spam detected.', 'Zontact' ) ), 400 ); }

		$opts = Options::get();
		if ( $opts['consent_text'] && ! $consent ) { $errors['consent'] = __( 'Consent is required.', 'Zontact' ); }
		if ( $errors ) { wp_send_json_error( array( 'errors' => $errors ), 422 ); }

		$to = $opts['recipient_email'];
		$subject = $opts['subject'];
		$body = sprintf("Name: %s\nEmail: %s\n\nMessage:\n%s", $name, $email, wp_strip_all_tags( $message ) );

		$headers = array();
		$site_domain = wp_parse_url( home_url(), PHP_URL_HOST );
		$from_email = apply_filters( 'zontact_from_email', 'no-reply@' . $site_domain );
		$from_name  = apply_filters( 'zontact_from_name', wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ) );
		$headers[] = 'Reply-To: ' . $name . ' <' . $email . '>';
		$headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';

		add_filter( 'wp_mail_content_type', function(){ return 'text/plain; charset=UTF-8'; } );
		$sent = wp_mail( $to, $subject, $body, $headers );
		remove_all_filters( 'wp_mail_content_type' );

		if ( ! $sent ) {
			error_log( 'Zontact: wp_mail failed sending to ' . $to );
			wp_send_json_error( array( 'message' => __( 'Unable to send email. Please try later.', 'Zontact' ) ), 500 );
		}

		if ( ! empty( $opts['save_messages'] ) && post_type_exists( 'zontact_message' ) ) {
			wp_insert_post( array(
				'post_type' => 'zontact_message',
				'post_status' => 'private',
				'post_title' => wp_trim_words( $name . ' – ' . wp_strip_all_tags( $message ), 8, '…' ),
				'post_content' => $message,
				'meta_input' => array(
					'zontact_email' => $email,
					'zontact_consent' => $consent ? 'yes' : 'no',
					'zontact_ip' => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
					'zontact_ua' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
				),
			), true );
		}

		wp_send_json_success( array( 'message' => $opts['success_message'] ) );
	}
}


