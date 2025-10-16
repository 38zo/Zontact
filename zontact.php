<?php
/**
 * Plugin Name:       Zontact
 * Description:       One button, one form, zero hassle. Floating contact button opens an accessible modal with a contact form.
 * Version:           0.1.0
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
$autoload = __DIR__ . '/vendor/autoload.php';
if ( file_exists( $autoload ) ) {
	require_once $autoload;
} else {
	add_action( 'admin_notices', function () {
		echo '<div class="notice notice-error"><p><strong>Zontact:</strong> Autoloader not found. Please run <code>composer install</code>.</p></div>';
	});
	return;
}

/**
 * Bootstrap the plugin.
 */
add_action( 'plugins_loaded', function () {
	if ( class_exists( \ThirtyEightZo\Zontact\Plugin::class ) ) {
		\ThirtyEightZo\Zontact\Plugin::instance();
	}
});
