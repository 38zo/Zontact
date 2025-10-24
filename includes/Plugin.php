<?php
/**
 * Core plugin class for Zontact.
 *
 * @package ThirtyEightZo\Zontact
 */

namespace ThirtyEightZo\Zontact;

use ThirtyEightZo\Zontact\Database;
use ThirtyEightZo\Zontact\Options;
use ThirtyEightZo\Zontact\Admin\Settings;
use ThirtyEightZo\Zontact\Admin\Menu;
use ThirtyEightZo\Zontact\Admin\EntriesPage;

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

		add_action( 'init', [ $this, 'init' ] );
		add_action( 'init', [ $this, 'maybe_install_db' ], 5 );

		if ( is_admin() ) {
			add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );
			add_action( 'admin_init', [ $this, 'register_admin_settings' ] );
		}

		register_activation_hook( ZONTACT_FILE, [ __CLASS__, 'activate' ] );
	}

	/**
	 * Define plugin constants.
	 *
	 * @return void
	 */
	private function define_constants(): void {
		$this->define( 'ZONTACT_VERSION', '1.0.0' );
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
		if ( class_exists( Database::class ) ) {
			Database::create_tables();
		}

		if ( ! get_option( 'zontact_options' ) && class_exists( Options::class ) ) {
			update_option( 'zontact_options', Options::defaults() );
		}
	}

	/**
	 * Ensure DB is installed when needed.
	 *
	 * @return void
	 */
	public function maybe_install_db(): void {
		if ( class_exists( Database::class ) ) {
			Database::maybe_install();
		}
	}

	/**
	 * Register admin menu.
	 *
	 * @return void
	 */
	public function register_admin_menu(): void {
		Menu::register();
	}

	/**
	 * Register admin settings safely (runs only when WordPress settings API is loaded).
	 *
	 * @return void
	 */
	public function register_admin_settings(): void {
		Settings::register();
		( new EntriesPage() )->register();
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
