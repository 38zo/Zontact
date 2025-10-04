<?php

namespace Zontact;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Settings {
	public static function register() {
		register_setting( 'zontact', 'zontact_options', array(
			'type' => 'array',
			'sanitize_callback' => [ __CLASS__, 'sanitize' ],
			'default' => Options::defaults(),
		) );

		add_settings_section( 'zontact_main', __( 'General', 'Zontact' ), '__return_null', 'zontact' );

		$fields = array(
			'recipient_email' => __( 'Recipient email', 'Zontact' ),
			'subject' => __( 'Email subject', 'Zontact' ),
			'save_messages' => __( 'Save messages to DB', 'Zontact' ),
			'data_retention_days' => __( 'Data retention (days)', 'Zontact' ),
			'button_position' => __( 'Button position', 'Zontact' ),
			'accent_color' => __( 'Accent color', 'Zontact' ),
			'consent_text' => __( 'Consent text', 'Zontact' ),
			'success_message' => __( 'Success message', 'Zontact' ),
		);

		foreach ( $fields as $key => $label ) {
			add_settings_field( $key, $label, [ __CLASS__, 'render_field' ], 'zontact', 'zontact_main', array( 'key' => $key ) );
		}

		add_action( 'admin_menu', function(){
			add_options_page( 'Zontact', 'Zontact', 'manage_options', 'zontact', [ __CLASS__, 'render_settings_page' ] );
		} );
	}

	public static function sanitize( $input ) {
		return Options::sanitize( $input );
	}

	public static function render_field( $args ) {
		$key = $args['key'];
		$opts = Options::get();
		$value = isset( $opts[ $key ] ) ? $opts[ $key ] : '';
		$name = 'zontact_options[' . esc_attr( $key ) . ']';
		if ( 'save_messages' === $key ) {
			echo '<label><input type="checkbox" name="' . esc_attr( $name ) . '" value="1"' . checked( $value, true, false ) . '> ' . esc_html__( 'Store form submissions as private posts (no tracking otherwise).', 'Zontact' ) . '</label>';
			return;
		}
		if ( 'data_retention_days' === $key ) {
			echo '<input type="number" class="small-text" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" min="1" max="365">';
			echo '<p class="description">' . esc_html__( 'How many days to keep saved messages (GDPR compliance).', 'Zontact' ) . '</p>';
			return;
		}
		if ( 'button_position' === $key ) {
			echo '<select name="' . esc_attr( $name ) . '">';
			echo '<option value="right"' . selected( $value, 'right', false ) . '>' . esc_html__( 'Right', 'Zontact' ) . '</option>';
			echo '<option value="left"' . selected( $value, 'left', false ) . '>' . esc_html__( 'Left', 'Zontact' ) . '</option>';
			echo '</select>';
			return;
		}
		$input_type = ( 'accent_color' === $key ) ? 'color' : 'text';
		echo '<input type="' . esc_attr( $input_type ) . '" class="regular-text" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '">';
	}

	public static function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		?>
		<div class="wrap">
			<h1>Zontact</h1>
			<p><em><?php echo esc_html__( 'One button, one form, zero hassle.', 'Zontact' ); ?></em></p>
			<form action="options.php" method="post">
				<?php settings_fields( 'zontact' ); ?>
				<?php do_settings_sections( 'zontact' ); ?>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}


