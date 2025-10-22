<?php
/**
 * Plugin Name:       Zontact
 * Description:       One button, one form, zero hassle. Floating contact button opens an accessible modal with a contact form.
 * Version:           1.0.0
 * Author:            38zo
 * Author URI:        https://38zo.com
 * Text Domain:       zontact
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * License:           GPLv3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 */

defined( 'ABSPATH' ) || exit;

define( 'ZONTACT_FILE', __FILE__ );
define( 'ZONTACT_PATH', plugin_dir_path( ZONTACT_FILE ) );
define( 'ZONTACT_URL', plugin_dir_url( ZONTACT_FILE ) );

/**
 * Autoload dependencies.
 */
$autoload = ZONTACT_PATH . '/vendor/autoload.php';
if ( file_exists( $autoload ) ) {
    require_once $autoload;
}

/**
 * Include necessary files.
 */
$functions = ZONTACT_PATH . '/includes/functions.php';
if ( file_exists( $functions ) ) {
	require_once $functions;
}

/**
 * Load plugin text domain.
 */
add_action( 'plugins_loaded', function () {
	load_plugin_textdomain( 'Zontact', false, dirname( plugin_basename( ZONTACT_FILE ) ) . '/languages/' );
});

/**
 * Bootstrap the plugin.
 */
add_action( 'plugins_loaded', function () {
	if ( class_exists( \ThirtyEightZo\Zontact\Plugin::class ) ) {
		\ThirtyEightZo\Zontact\Plugin::instance();
		/**
		 * Fires after Zontact has been bootstrapped.
		 *
		 * Allows third-parties to hook once the plugin is initialized.
		 *
		 * @since 1.0.0
		 */
		do_action( 'Zontact_bootstrapped' );
	}
});
