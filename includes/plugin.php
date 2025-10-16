<?php

namespace ThirtyEightZo\Zontact;

defined( 'ABSPATH' ) || exit;

final class Plugin {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', [ $this, 'init' ] );
		register_activation_hook( ZONTACT_PATH . 'zontact.php', [ __CLASS__, 'activate' ] );
	}

	public static function activate() {
		if ( ! get_option( 'zontact_options' ) ) {
			update_option( 'zontact_options', Options::defaults() );
		}
		Cpt::register();
		flush_rewrite_rules();
	}

	public function init() {
		Cpt::register();
		Settings::register();
		Assets::register();
		Frontend::register();
		Ajax::register();
	}
}
