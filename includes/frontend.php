<?php

namespace Zontact;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Frontend {
	public static function register() {
		add_action( 'wp_footer', [ __CLASS__, 'render' ] );
	}

	public static function render() {
		$opts = Options::get();
		$position_class = ( 'left' === $opts['button_position'] ) ? 'zontact-left' : 'zontact-right';
		$accent = esc_attr( $opts['accent_color'] );
		$consent_text = trim( wp_kses_post( $opts['consent_text'] ) );
		?>
		<div class="zontact-root <?php echo esc_attr( $position_class ); ?>" data-accent="<?php echo $accent; ?>">
			<button type="button" class="zontact-button" aria-haspopup="dialog" aria-controls="zontact-modal" aria-expanded="false">
				<span class="zontact-button-label"><?php echo esc_html__( 'Contact', 'Zontact' ); ?></span>
			</button>
			<div id="zontact-modal" class="zontact-modal" role="dialog" aria-modal="true" aria-labelledby="zontact-title" aria-hidden="true">
				<div class="zontact-modal__overlay" data-zontact-close></div>
				<div class="zontact-modal__dialog" role="document">
					<header class="zontact-modal__header">
						<h2 id="zontact-title"><?php echo esc_html__( 'Contact us', 'Zontact' ); ?></h2>
						<button type="button" class="zontact-close" aria-label="<?php echo esc_attr__( 'Close', 'Zontact' ); ?>" data-zontact-close>&times;</button>
					</header>
					<form class="zontact-form" novalidate>
						<div class="zontact-form__content">
							<div class="zontact-field">
								<label for="zontact-name"><?php echo esc_html__( 'Name', 'Zontact' ); ?></label>
								<input id="zontact-name" name="name" type="text" autocomplete="name" required>
							</div>
							<div class="zontact-field">
								<label for="zontact-email"><?php echo esc_html__( 'Email', 'Zontact' ); ?></label>
								<input id="zontact-email" name="email" type="email" autocomplete="email" required>
							</div>
							<div class="zontact-field">
								<label for="zontact-message"><?php echo esc_html__( 'Message', 'Zontact' ); ?></label>
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
						</div>
						<div class="zontact-actions">
							<button type="submit" class="zontact-submit"><?php echo esc_html__( 'Send', 'Zontact' ); ?></button>
							<div class="zontact-status" role="status" aria-live="polite"></div>
						</div>
					</form>
				</div>
			</div>
		</div>
		<?php
	}
}


