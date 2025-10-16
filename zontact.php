<?php
/**
 * Plugin Name: Zontact
 * Description: One button, one form, zero hassle. Floating contact button opens an accessible modal with a contact form.
 * Version: 0.1.0
 * Author: 38zo
 * Text Domain: zontact
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * License: GPLv3 or later
 */

defined( 'ABSPATH' ) || exit;

// Plugin constants.
define( 'ZONTACT_VERSION', '0.1.0' );
define( 'ZONTACT_SLUG', 'zontact' );
define( 'ZONTACT_PATH', plugin_dir_path( __FILE__ ) );
define( 'ZONTACT_URL', plugin_dir_url( __FILE__ ) );

// Load Composer autoloader.
if ( file_exists( ZONTACT_PATH . 'vendor/autoload.php' ) ) {
	require_once ZONTACT_PATH . 'vendor/autoload.php';
} else {
	// Optional fallback notice for devs.
	add_action( 'admin_notices', function () {
		echo '<div class="notice notice-error"><p><strong>Zontact:</strong> Autoloader not found. Please run <code>composer install</code>.</p></div>';
	});
	return;
}

// Load translations.
add_action( 'plugins_loaded', function () {
	load_plugin_textdomain( 'zontact', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
});

// Bootstrap the plugin.
add_action( 'plugins_loaded', function () {
	if ( class_exists( \ThirtyEightZo\Zontact\Plugin::class ) ) {
		\ThirtyEightZo\Zontact\Plugin::instance();
	}
});
