<?php
/**
 * Admin Menu handler for Zontact.
 *
 * @package ThirtyEightZo\Zontact\Admin
 */

namespace ThirtyEightZo\Zontact\Admin;

defined( 'ABSPATH' ) || exit;

final class Menu {

	/**
	 * Registers admin menus and submenus.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_action( 'admin_menu', [ __CLASS__, 'add_menus' ] );
	}

	/**
	 * Add top-level and default submenus.
	 *
	 * @return void
	 */
	public static function add_menus(): void {
		$menu_title = zontact_plugin_name();

		add_menu_page(
			$menu_title,               // Page title.
			$menu_title,               // Menu title.
			'manage_options',          // Capability.
			'zontact',                 // Menu slug.
			[ __CLASS__, 'render_welcome_page' ], // Callback.
			'dashicons-email-alt2',    // Icon.
			58                         // Position.
		);

		// Default submenus.
		self::add_submenu(
			'zontact',
			__( 'Settings', 'zontact' ),
			__( 'Settings', 'zontact' ),
			'manage_options',
			'zontact-settings',
			[ Settings::class, 'render_settings_page' ]
		);

		/**
		 * Allow other modules to add submenus.
		 */
		do_action( 'zontact_admin_submenus' );
	}

	/**
	 * Helper to add submenu pages dynamically.
	 *
	 * @param string   $parent_slug Parent menu slug.
	 * @param string   $page_title  Page title.
	 * @param string   $menu_title  Menu title.
	 * @param string   $capability  Capability.
	 * @param string   $menu_slug   Slug.
	 * @param callable $callback    Callback.
	 * @return void
	 */
	public static function add_submenu(
		string $parent_slug,
		string $page_title,
		string $menu_title,
		string $capability,
		string $menu_slug,
		callable $callback
	): void {
		add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $callback );
	}

	/**
	 * Render the default "Welcome" page.
	 *
	 * @return void
	 */
	public static function render_welcome_page(): void {
		?>
		<div class="wrap zontact-welcome">
            <h1><?php echo esc_html( zontact_plugin_name() ); ?></h1>
            <p>
                <?php
                /* translators: %s: plugin name */
                printf(
                    esc_html__( 'Welcome to %s! One button. One form. Zero hassle.', 'zontact' ),
                    esc_html( zontact_plugin_name() )
                );
                ?>
            </p>
			<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=zontact-settings' ) ); ?>" class="button button-primary">
				<?php esc_html_e( 'Go to Settings', 'zontact' ); ?>
			</a></p>
		</div>
		<?php
	}
}
