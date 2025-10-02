<?php
/**
 * Plugin Name: Zontact – Minimal Contact Button & Modal Form
 * Description: One button, one form, zero hassle. Floating contact button opens an accessible modal with a 3‑field form. Submits by email with optional DB save. No tracking.
 * Version: 0.1.0
 * Author: Zontact
 * Text Domain: zontact
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * License: GPLv2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ZONTACT_VERSION', '0.1.0' );
define( 'ZONTACT_SLUG', 'zontact' );
define( 'ZONTACT_PATH', plugin_dir_path( __FILE__ ) );
define( 'ZONTACT_URL', plugin_dir_url( __FILE__ ) );

// Default options.
function zontact_default_options() {
	return array(
		'recipient_email' => get_option( 'admin_email' ),
		'subject' => sprintf( /* translators: %s = site name */ __( 'New message from %s', 'zontact' ), wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ) ),
		'save_messages' => false,
		'button_position' => 'right', // left|right
		'accent_color' => '#2563eb', // Tailwind blue-600
		'consent_text' => __( 'I consent to having this website store my submitted information so they can respond to my inquiry.', 'zontact' ),
		'success_message' => __( 'Thanks! Your message has been sent.', 'zontact' ),
	);
}

function zontact_get_options() {
	$defaults = zontact_default_options();
	$opts = get_option( 'zontact_options', array() );
	if ( ! is_array( $opts ) ) {
		$opts = array();
	}
	return array_merge( $defaults, $opts );
}

// Activation: seed options.
function zontact_activate() {
	if ( ! get_option( 'zontact_options' ) ) {
		update_option( 'zontact_options', zontact_default_options() );
	}
	// Ensure CPT is registered on activation if saving is enabled later.
	zontact_register_cpt();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'zontact_activate' );

// Optional CPT for saving messages.
function zontact_register_cpt() {
	$labels = array(
		'name' => __( 'Zontact Messages', 'zontact' ),
		'singular_name' => __( 'Zontact Message', 'zontact' ),
	);
	register_post_type( 'zontact_message', array(
		'labels' => $labels,
		'public' => false,
		'show_ui' => current_user_can( 'manage_options' ),
		'show_in_menu' => 'options-general.php',
		'supports' => array( 'title', 'editor', 'custom-fields' ),
		'capability_type' => 'post',
		'map_meta_cap' => true,
	) );
}
add_action( 'init', 'zontact_register_cpt' );

// Settings page
function zontact_register_settings() {
	register_setting( 'zontact', 'zontact_options', array(
		'type' => 'array',
		'sanitize_callback' => 'zontact_sanitize_options',
		'default' => zontact_default_options(),
	) );

	add_settings_section( 'zontact_main', __( 'General', 'zontact' ), '__return_null', 'zontact' );

	$fields = array(
		'recipient_email' => __( 'Recipient email', 'zontact' ),
		'subject' => __( 'Email subject', 'zontact' ),
		'save_messages' => __( 'Save messages to DB', 'zontact' ),
		'button_position' => __( 'Button position', 'zontact' ),
		'accent_color' => __( 'Accent color', 'zontact' ),
		'consent_text' => __( 'Consent text', 'zontact' ),
		'success_message' => __( 'Success message', 'zontact' ),
	);

	foreach ( $fields as $key => $label ) {
		add_settings_field( $key, $label, 'zontact_render_field', 'zontact', 'zontact_main', array( 'key' => $key ) );
	}
}
add_action( 'admin_init', 'zontact_register_settings' );

function zontact_sanitize_options( $input ) {
	$defaults = zontact_default_options();
	$output = array();
	$output['recipient_email'] = isset( $input['recipient_email'] ) ? sanitize_email( $input['recipient_email'] ) : $defaults['recipient_email'];
	$output['subject'] = isset( $input['subject'] ) ? wp_kses_post( $input['subject'] ) : $defaults['subject'];
	$output['save_messages'] = ! empty( $input['save_messages'] );
	$output['button_position'] = in_array( $input['button_position'] ?? '', array( 'left', 'right' ), true ) ? $input['button_position'] : $defaults['button_position'];
	$output['accent_color'] = isset( $input['accent_color'] ) ? preg_replace( '/[^#a-fA-F0-9]/', '', (string) $input['accent_color'] ) : $defaults['accent_color'];
	$output['consent_text'] = isset( $input['consent_text'] ) ? wp_kses_post( $input['consent_text'] ) : $defaults['consent_text'];
	$output['success_message'] = isset( $input['success_message'] ) ? wp_kses_post( $input['success_message'] ) : $defaults['success_message'];

	// Ensure CPT exists if saving is enabled.
	if ( $output['save_messages'] && ! post_type_exists( 'zontact_message' ) ) {
		zontact_register_cpt();
	}

	return $output;
}

function zontact_render_field( $args ) {
	$key = $args['key'];
	$opts = zontact_get_options();
	$value = isset( $opts[ $key ] ) ? $opts[ $key ] : '';
	$name = 'zontact_options[' . esc_attr( $key ) . ']';
	if ( 'save_messages' === $key ) {
		echo '<label><input type="checkbox" name="' . esc_attr( $name ) . '" value="1"' . checked( $value, true, false ) . '> ' . esc_html__( 'Store form submissions as private posts (no tracking otherwise).', 'zontact' ) . '</label>';
		return;
	}
	if ( 'button_position' === $key ) {
		echo '<select name="' . esc_attr( $name ) . '">';
		echo '<option value="right"' . selected( $value, 'right', false ) . '>' . esc_html__( 'Right', 'zontact' ) . '</option>';
		echo '<option value="left"' . selected( $value, 'left', false ) . '>' . esc_html__( 'Left', 'zontact' ) . '</option>';
		echo '</select>';
		return;
	}
	$input_type = ( 'accent_color' === $key ) ? 'color' : 'text';
	echo '<input type="' . esc_attr( $input_type ) . '" class="regular-text" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '">';
}

function zontact_add_settings_page() {
	add_options_page(
		'Zontact',
		'Zontact',
		'manage_options',
		'zontact',
		'zontact_render_settings_page'
	);
}
add_action( 'admin_menu', 'zontact_add_settings_page' );

function zontact_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1>Zontact</h1>
		<p><em><?php echo esc_html__( 'One button, one form, zero hassle.', 'zontact' ); ?></em></p>
		<form action="options.php" method="post">
			<?php settings_fields( 'zontact' ); ?>
			<?php do_settings_sections( 'zontact' ); ?>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

// Frontend assets and render.
function zontact_enqueue_assets() {
	$opts = zontact_get_options();
	wp_enqueue_style( 'zontact', ZONTACT_URL . 'assets/css/zontact.css', array(), ZONTACT_VERSION );
	wp_enqueue_script( 'zontact', ZONTACT_URL . 'assets/js/zontact.js', array(), ZONTACT_VERSION, true );
	$localized = array(
		'ajax_url' => admin_url( 'admin-ajax.php' ),
		'nonce' => wp_create_nonce( 'zontact_submit' ),
		'strings' => array(
			'sending' => __( 'Sending…', 'zontact' ),
			'error' => __( 'Please fix the errors and try again.', 'zontact' ),
			'success' => $opts['success_message'],
		),
	);
	wp_localize_script( 'zontact', 'Zontact', $localized );
}
add_action( 'wp_enqueue_scripts', 'zontact_enqueue_assets' );

function zontact_footer_markup() {
	$opts = zontact_get_options();
	$position_class = ( 'left' === $opts['button_position'] ) ? 'zontact-left' : 'zontact-right';
	$accent = esc_attr( $opts['accent_color'] );
	$consent_text = trim( wp_kses_post( $opts['consent_text'] ) );
	?>
	<div class="zontact-root <?php echo esc_attr( $position_class ); ?>" data-accent="<?php echo $accent; ?>">
		<button type="button" class="zontact-button" aria-haspopup="dialog" aria-controls="zontact-modal" aria-expanded="false">
			<span class="zontact-button-label"><?php echo esc_html__( 'Contact', 'zontact' ); ?></span>
		</button>
		<div id="zontact-modal" class="zontact-modal" role="dialog" aria-modal="true" aria-labelledby="zontact-title" aria-hidden="true">
			<div class="zontact-modal__overlay" data-zontact-close></div>
			<div class="zontact-modal__dialog" role="document">
				<header class="zontact-modal__header">
					<h2 id="zontact-title"><?php echo esc_html__( 'Contact us', 'zontact' ); ?></h2>
					<button type="button" class="zontact-close" aria-label="<?php echo esc_attr__( 'Close', 'zontact' ); ?>" data-zontact-close>&times;</button>
				</header>
				<form class="zontact-form" novalidate>
					<div class="zontact-field">
						<label for="zontact-name"><?php echo esc_html__( 'Name', 'zontact' ); ?></label>
						<input id="zontact-name" name="name" type="text" autocomplete="name" required>
					</div>
					<div class="zontact-field">
						<label for="zontact-email"><?php echo esc_html__( 'Email', 'zontact' ); ?></label>
						<input id="zontact-email" name="email" type="email" autocomplete="email" required>
					</div>
					<div class="zontact-field">
						<label for="zontact-message"><?php echo esc_html__( 'Message', 'zontact' ); ?></label>
						<textarea id="zontact-message" name="message" rows="5" required></textarea>
					</div>
					<div class="zontact-field zontact--hp" aria-hidden="true" hidden>
						<label for="zontact-website">Website</label>
						<input id="zontact-website" name="website" type="text" tabindex="-1" autocomplete="off">
					</div>
					<?php if ( $consent_text ) : ?>
					<div class="zontact-field zontact-consent">
						<label>
							<input name="consent" type="checkbox" required>
							<span class="zontact-consent__text"><?php echo $consent_text; ?></span>
						</label>
					</div>
					<?php endif; ?>
					<div class="zontact-actions">
						<button type="submit" class="zontact-submit"><?php echo esc_html__( 'Send', 'zontact' ); ?></button>
						<div class="zontact-status" role="status" aria-live="polite"></div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'zontact_footer_markup' );

// AJAX handler
function zontact_handle_submit() {
	check_ajax_referer( 'zontact_submit', 'nonce' );

	$name = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$message = wp_kses_post( wp_unslash( $_POST['message'] ?? '' ) );
	$website = sanitize_text_field( wp_unslash( $_POST['website'] ?? '' ) ); // honeypot
	$consent = ! empty( $_POST['consent'] );

	$errors = array();
	if ( empty( $name ) ) {
		$errors['name'] = __( 'Name is required.', 'zontact' );
	}
	if ( empty( $email ) || ! is_email( $email ) ) {
		$errors['email'] = __( 'A valid email is required.', 'zontact' );
	}
	if ( empty( $message ) ) {
		$errors['message'] = __( 'Message is required.', 'zontact' );
	}
	if ( ! empty( $website ) ) {
		wp_send_json_error( array( 'message' => __( 'Spam detected.', 'zontact' ) ), 400 );
	}

	$opts = zontact_get_options();
	if ( $opts['consent_text'] && ! $consent ) {
		$errors['consent'] = __( 'Consent is required.', 'zontact' );
	}

	if ( $errors ) {
		wp_send_json_error( array( 'errors' => $errors ), 422 );
	}

	$to = $opts['recipient_email'];
	$subject = $opts['subject'];
	$body = sprintf(
		"Name: %s\nEmail: %s\n\nMessage:\n%s",
		$name,
		$email,
		wp_strip_all_tags( $message )
	);
	$headers = array();
	$from_email = apply_filters( 'zontact_from_email', get_bloginfo( 'admin_email' ) );
	$headers[] = 'Reply-To: ' . $name . ' <' . $email . '>';
	$headers[] = 'From: ' . wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ) . ' <' . $from_email . '>';

	$sent = wp_mail( $to, $subject, $body, $headers );
	if ( ! $sent ) {
		wp_send_json_error( array( 'message' => __( 'Unable to send email. Please try later.', 'zontact' ) ), 500 );
	}

	if ( ! empty( $opts['save_messages'] ) && post_type_exists( 'zontact_message' ) ) {
		$post_id = wp_insert_post( array(
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
		if ( is_wp_error( $post_id ) ) {
			// Non-fatal; still return success.
		}
	}

	wp_send_json_success( array( 'message' => $opts['success_message'] ) );
}
add_action( 'wp_ajax_zontact_submit', 'zontact_handle_submit' );
add_action( 'wp_ajax_nopriv_zontact_submit', 'zontact_handle_submit' );

// Basic inline style variable hook.
function zontact_inline_styles() {
	$opts = zontact_get_options();
	$accent = $opts['accent_color'];
	$css = ':root{--zontact-accent:' . esc_attr( $accent ) . ';}';
	wp_add_inline_style( 'zontact', $css );
}
add_action( 'wp_enqueue_scripts', 'zontact_inline_styles', 20 );


