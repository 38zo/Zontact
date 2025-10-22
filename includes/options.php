<?php
/**
 * Handles Zontact plugin options.
 *
 * @package ThirtyEightZo\Zontact
 */

namespace ThirtyEightZo\Zontact;

defined( 'ABSPATH' ) || exit;

/**
 * Class Options
 *
 * Provides default values, sanitization, and retrieval of Zontact settings.
 */
class Options {

	/**
	 * Get default plugin options.
	 *
	 * @return array Default option values.
	 */
	public static function defaults(): array {
		$defaults = array(
			'recipient_email'   => get_option( 'admin_email' ),
			'subject'           => sprintf(
				/* translators: %s: site name */
				__( 'New message from %s', 'Zontact' ),
				wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES )
			),
			'save_messages'     => false,
			'data_retention_days' => 30,
			'button_position'   => 'right',
			'accent_color'      => '#2563eb',
			'consent_text'      => __(
				'I agree to the processing of my personal data (name, email, message) for the purpose of responding to my inquiry. This data will be stored securely and not shared with third parties.',
				'Zontact'
			),
			'success_message'   => __( 'Thanks! Your message has been sent.', 'Zontact' ),
		);

		/**
		 * Filters the default Zontact options.
		 *
		 * @param array $defaults Default option values.
		 */
		return apply_filters( 'zontact_default_options', $defaults );
	}

	/**
	 * Get merged user and default options.
	 *
	 * @return array Plugin options.
	 */
	public static function get(): array {
		$defaults = self::defaults();
		$opts     = get_option( 'zontact_options', array() );

		if ( ! is_array( $opts ) ) {
			$opts = array();
		}

		return array_merge( $defaults, $opts );
	}

	/**
	 * Sanitize plugin options.
	 *
	 * @param array $input Raw input values.
	 * @return array Sanitized options.
	 */
	public static function sanitize( array $input ): array {
		$defaults = self::defaults();
		$output   = array();

		$output['recipient_email']   = isset( $input['recipient_email'] )
			? sanitize_email( $input['recipient_email'] )
			: $defaults['recipient_email'];

		$output['subject']           = isset( $input['subject'] )
			? zontact_sanitize_html( $input['subject'] )
			: $defaults['subject'];

		$output['save_messages']     = ! empty( $input['save_messages'] );

		$output['data_retention_days'] = isset( $input['data_retention_days'] )
			? max( 1, (int) $input['data_retention_days'] )
			: $defaults['data_retention_days'];

		$output['button_position']   = in_array( $input['button_position'] ?? '', array( 'left', 'right' ), true )
			? $input['button_position']
			: $defaults['button_position'];

		$output['accent_color']      = isset( $input['accent_color'] )
			? preg_replace( '/[^#a-fA-F0-9]/', '', (string) $input['accent_color'] )
			: $defaults['accent_color'];

		$output['consent_text']      = isset( $input['consent_text'] )
			? zontact_sanitize_html( $input['consent_text'] )
			: $defaults['consent_text'];

		$output['success_message']   = isset( $input['success_message'] )
			? zontact_sanitize_html( $input['success_message'] )
			: $defaults['success_message'];

		return $output;
	}
}
