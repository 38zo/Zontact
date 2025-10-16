<?php
/**
 * Core plugin class for Zontact.
 *
 * @package ThirtyEightZo\Zontact
 */

namespace ThirtyEightZo\Zontact;

defined( 'ABSPATH' ) || exit;

/**
 * Main Plugin class.
 */
final class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static ?Plugin $instance = null;

	/**
	 * List of core module classes.
	 *
	 * @var array
	 */
	private const MODULES = [
		Cpt::class,
		Settings::class,
		Assets::class,
		Frontend::class,
		Ajax::class,
	];

	/**
	 * Get instance.
	 *
	 * @return Plugin
	 */
	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Plugin constructor.
	 */
	private function __construct() {
		$this->define_constants();

		// Initialize the plugin.
		add_action( 'init', [ $this, 'init' ] );

		// Register activation hook.
		register_activation_hook( ZONTACT_FILE, [ __CLASS__, 'activate' ] );
	}

	/**
	 * Define plugin constants.
	 *
	 * @return void
	 */
	private function define_constants(): void {
		$this->define( 'ZONTACT_VERSION', '0.1.0' );
		$this->define( 'ZONTACT_SLUG', 'zontact' );
		$this->define( 'ZONTACT_PATH', plugin_dir_path( ZONTACT_FILE ) );
		$this->define( 'ZONTACT_URL', plugin_dir_url( ZONTACT_FILE ) );
	}

	/**
	 * Helper to define constants if not already defined.
	 *
	 * @param string $name  Constant name.
	 * @param mixed  $value Constant value.
	 * @return void
	 */
	private function define( string $name, $value ): void {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * On plugin activation.
	 *
	 * @return void
	 */
	public static function activate(): void {
		// Initialize default options.
		if ( ! get_option( 'zontact_options' ) && class_exists( Options::class ) ) {
			update_option( 'zontact_options', Options::defaults() );
		}

		// Register CPTs before flushing.
		if ( class_exists( Cpt::class ) ) {
			Cpt::register();
		}

		flush_rewrite_rules();
	}

	/**
	 * Initialize all plugin modules.
	 *
	 * @return void
	 */
	public function init(): void {
		foreach ( self::MODULES as $module ) {
			if ( class_exists( $module ) ) {
				$instance = new $module();

				if ( method_exists( $instance, 'register' ) ) {
					$instance->register();
				}
			}
		}
	}
}
