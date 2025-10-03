<?php

namespace Zontact;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Options {
	public static function defaults() {
		return array(
			'recipient_email' => get_option( 'admin_email' ),
			'subject' => sprintf( __( 'New message from %s', 'Zontact' ), wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ) ),
			'save_messages' => false,
			'button_position' => 'right',
			'accent_color' => '#2563eb',
			'consent_text' => __( 'I consent to having this website store my submitted information so they can respond to my inquiry.', 'Zontact' ),
			'success_message' => __( 'Thanks! Your message has been sent.', 'Zontact' ),
		);
	}

	public static function get() {
		$defaults = self::defaults();
		$opts = get_option( 'zontact_options', array() );
		if ( ! is_array( $opts ) ) { $opts = array(); }
		return array_merge( $defaults, $opts );
	}

	public static function sanitize( $input ) {
		$defaults = self::defaults();
		$output = array();
		$output['recipient_email'] = isset( $input['recipient_email'] ) ? sanitize_email( $input['recipient_email'] ) : $defaults['recipient_email'];
		$output['subject'] = isset( $input['subject'] ) ? wp_kses_post( $input['subject'] ) : $defaults['subject'];
		$output['save_messages'] = ! empty( $input['save_messages'] );
		$output['button_position'] = in_array( $input['button_position'] ?? '', array( 'left','right' ), true ) ? $input['button_position'] : $defaults['button_position'];
		$output['accent_color'] = isset( $input['accent_color'] ) ? preg_replace( '/[^#a-fA-F0-9]/', '', (string) $input['accent_color'] ) : $defaults['accent_color'];
		$output['consent_text'] = isset( $input['consent_text'] ) ? wp_kses_post( $input['consent_text'] ) : $defaults['consent_text'];
		$output['success_message'] = isset( $input['success_message'] ) ? wp_kses_post( $input['success_message'] ) : $defaults['success_message'];
		return $output;
	}
}


