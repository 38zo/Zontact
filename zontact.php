<?php
/**
 * Plugin Name: Zontact
 * Description: One button, one form, zero hassle. Floating contact button opens an accessible modal with a 3‑field form. Submits by email with optional DB save. No tracking.
 * Version: 0.1.0
 * Author: Zontact
 * Text Domain: Zontact
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

// Load i18n.
add_action( 'plugins_loaded', function () {
	load_plugin_textdomain( 'Zontact', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
} );

// Lightweight autoloader.
spl_autoload_register( function ( $class ) {
	if ( 0 !== strpos( $class, 'Zontact\\' ) ) {
		return;
	}
	$path = ZONTACT_PATH . 'includes/' . strtolower( str_replace( array( 'Zontact\\', '_' ), array( '', '-' ), $class ) ) . '.php';
	if ( file_exists( $path ) ) {
		require_once $path;
	}
} );

// Fallback manual requires for simple environment.
$files = array(
	'includes/plugin.php',
	'includes/options.php',
	'includes/cpt.php',
	'includes/settings.php',
	'includes/assets.php',
	'includes/frontend.php',
	'includes/ajax.php',
);
foreach ( $files as $file ) {
	$path = ZONTACT_PATH . $file;
	if ( file_exists( $path ) ) {
		require_once $path;
	}
}

// Bootstrap plugin.
if ( class_exists( 'Zontact\\Plugin' ) ) {
	Zontact\Plugin::instance();
}

