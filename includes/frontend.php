<?php
/**
 * Handles frontend rendering for Zontact.
 *
 * @package ThirtyEightZo\Zontact
 */

namespace ThirtyEightZo\Zontact;

defined( 'ABSPATH' ) || exit;

/**
 * Manages frontend form output and structure.
 */
final class Frontend {

	/**
	 * Initialize frontend hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'wp_footer', [ $this, 'render_form' ] );
	}

	/**
	 * Render the contact form markup.
	 *
	 * @return void
	 */
	public function render_form(): void {
		$opts          = Options::get();
		$position_class = ( 'left' === $opts['button_position'] ) ? 'zontact-left' : 'zontact-right';
		$accent         = esc_attr( $opts['accent_color'] );
		$consent_text   = trim( wp_kses_post( $opts['consent_text'] ) );

		?>
		<div 
			class="zontact-root <?php echo esc_attr( $position_class ); ?>" 
			data-accent="<?php echo $accent; ?>"
		>
			<button 
				type="button" 
				class="zontact-button" 
				aria-haspopup="dialog" 
				aria-controls="zontact-modal" 
				aria-expanded="false"
			>
				<span class="zontact-button-label">
					<?php echo esc_html__( 'Contact', 'zontact' ); ?>
				</span>
			</button>

			<div 
				id="zontact-modal" 
				class="zontact-modal" 
				role="dialog" 
				aria-modal="true" 
				aria-labelledby="zontact-title" 
				aria-hidden="true"
			>
				<div class="zontact-modal__overlay" data-zontact-close></div>
				<div class="zontact-modal__dialog" role="document">

					<header class="zontact-modal__header">
						<h2 id="zontact-title"><?php esc_html_e( 'Contact us', 'zontact' ); ?></h2>
						<button 
							type="button" 
							class="zontact-close" 
							aria-label="<?php esc_attr_e( 'Close', 'zontact' ); ?>" 
							data-zontact-close
						>
							&times;
						</button>
					</header>

					<form class="zontact-form" novalidate>
						<div class="zontact-form__content">
							<?php $this->render_fields( $consent_text ); ?>
						</div>

						<div class="zontact-actions">
							<button type="submit" class="zontact-submit">
								<?php esc_html_e( 'Send', 'zontact' ); ?>
							</button>
							<div class="zontact-status" role="status" aria-live="polite"></div>
						</div>
					</form>

				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the input fields and consent checkbox.
	 *
	 * @param string $consent_text Optional consent text.
	 * @return void
	 */
	private function render_fields( string $consent_text ): void {
		?>
		<div class="zontact-field">
			<label for="zontact-name"><?php esc_html_e( 'Name', 'zontact' ); ?></label>
			<input id="zontact-name" name="name" type="text" autocomplete="name" required>
		</div>

		<div class="zontact-field">
			<label for="zontact-email"><?php esc_html_e( 'Email', 'zontact' ); ?></label>
			<input id="zontact-email" name="email" type="email" autocomplete="email" required>
		</div>

		<div class="zontact-field">
			<label for="zontact-message"><?php esc_html_e( 'Message', 'zontact' ); ?></label>
			<textarea id="zontact-message" name="message" rows="4" required></textarea>
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
		<?php
	}
}
