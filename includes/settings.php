<?php
/**
 * Admin settings for Zontact plugin.
 *
 * @package ThirtyEightZo\Zontact
 */

namespace ThirtyEightZo\Zontact;

defined( 'ABSPATH' ) || exit;

/**
 * Class Settings
 *
 * Registers and renders the Zontact settings page.
 */
class Settings {

	/**
	 * Register settings and admin menu.
	 *
	 * @return void
	 */
	public static function register(): void {
		register_setting(
			'zontact',
			'zontact_options',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => Options::defaults(),
			)
		);

		add_settings_section(
			'zontact_main',
			__( 'General', 'zontact' ),
			'__return_null',
			'zontact'
		);

		$fields = array(
			'recipient_email'    => __( 'Recipient email', 'zontact' ),
			'subject'            => __( 'Email subject', 'zontact' ),
			'save_messages'      => __( 'Save messages to DB', 'zontact' ),
			'data_retention_days' => __( 'Data retention (days)', 'zontact' ),
			'button_position'    => __( 'Button position', 'zontact' ),
			'accent_color'       => __( 'Accent color', 'zontact' ),
			'consent_text'       => __( 'Consent text', 'zontact' ),
			'success_message'    => __( 'Success message', 'zontact' ),
		);

		foreach ( $fields as $key => $label ) {
			add_settings_field(
				$key,
				$label,
				array( __CLASS__, 'render_field' ),
				'zontact',
				'zontact_main',
				array( 'key' => $key )
			);
		}

		add_action(
			'admin_menu',
			function (): void {
				add_options_page(
					'Zontact',
					'Zontact',
					'manage_options',
					'zontact',
					array( __CLASS__, 'render_settings_page' )
				);
			}
		);

		/**
		 * Fires after all Zontact settings are registered.
		 */
		do_action( 'zontact_register_settings' );
	}

	/**
	 * Sanitize settings input.
	 *
	 * @param array $input Raw input data.
	 * @return array Sanitized settings.
	 */
	public static function sanitize( array $input ): array {
		return Options::sanitize( $input );
	}

	/**
	 * Render a field in the settings page.
	 *
	 * @param array $args Field arguments.
	 * @return void
	 */
	public static function render_field( array $args ): void {
		$key   = $args['key'];
		$opts  = Options::get();
		$value = isset( $opts[ $key ] ) ? $opts[ $key ] : '';
		$name  = 'zontact_options[' . esc_attr( $key ) . ']';

		switch ( $key ) {
			case 'save_messages':
				printf(
					'<label><input type="checkbox" name="%1$s" value="1" %2$s> %3$s</label>',
					esc_attr( $name ),
					checked( $value, true, false ),
					esc_html__( 'Store form submissions as private posts (no tracking otherwise).', 'zontact' )
				);
				return;

			case 'data_retention_days':
				printf(
					'<input type="number" class="small-text" name="%1$s" value="%2$s" min="1" max="365">',
					esc_attr( $name ),
					esc_attr( $value )
				);
				echo '<p class="description">' . esc_html__( 'How many days to keep saved messages (GDPR compliance).', 'zontact' ) . '</p>';
				return;

			case 'button_position':
				?>
				<select name="<?php echo esc_attr( $name ); ?>">
					<option value="right" <?php selected( $value, 'right' ); ?>><?php esc_html_e( 'Right', 'zontact' ); ?></option>
					<option value="left" <?php selected( $value, 'left' ); ?>><?php esc_html_e( 'Left', 'zontact' ); ?></option>
				</select>
				<?php
				return;

			default:
				$input_type = ( 'accent_color' === $key ) ? 'color' : 'text';
				printf(
					'<input type="%1$s" class="regular-text" name="%2$s" value="%3$s">',
					esc_attr( $input_type ),
					esc_attr( $name ),
					esc_attr( $value )
				);
		}
	}

	/**
	 * Render the plugin settings page.
	 *
	 * @return void
	 */
	public static function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap zontact-settings">
			<h1><?php esc_html_e( 'Zontact', 'zontact' ); ?></h1>
			<p><em><?php esc_html_e( 'One button, one form, zero hassle.', 'zontact' ); ?></em></p>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'zontact' );
				do_settings_sections( 'zontact' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}
