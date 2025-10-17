<?php
/**
 * Zontact global functions
 *
 * @package   Zontact
 * @author    Lewis ushindi <frenziecodes@gmail.com>
 * @license   GPL-3.0+
 * @link      https://github.com/38zo/zontact
 * @copyright 2025 Zontact LLC
 */

 /**
 * Returns the name of the plugin. (Allows the name to be overridden.)
 * @return string
 */
function zontact_plugin_name() {
	return apply_filters( 'zontact_plugin_name', 'Zontact' );
}

/**
 * Sanitize HTML to make it safe to output. Used to sanitize potentially harmful HTML.
 *
 * @since 1.0
 *
 * @param string $text
 * @return string
 */
function zontact_sanitize_html( $text ) {
	$safe_text = wp_kses_post( $text );
	return $safe_text;
}

/**
 * Filter out JavaScript-related keywords and inline scripts from an input string
 *
 * @param string $input
 * @return string
 */
function zontact_sanitize_javascript( $input ) {
    // list of JavaScript-related attributes to filter out
    $javascript_attributes = array(
        'innerHTML',
        'document\.write',
        'eval',
        'Function\(',
        'setTimeout',
        'setInterval',
        'new Function\(',
        'onmouseover',
        'onmouseout',
        'onpointerenter',
        'onclick',
        'onload',
        'onchange',
        'onerror',
        '<script>',
        '<\/script>',
        'encodeURIComponent',
        'decodeURIComponent',
        'JSON\.parse',
        'outerHTML',
        'innerHTML',
        'XMLHttpRequest',
        'createElement',
        'appendChild',
        'RegExp',
        'String\.fromCharCode',
        'encodeURI',
        'decodeURI',
        'javascript:'
    );

    $pattern = '/' . implode( '|', $javascript_attributes ) . '/i';

    // Use regex to replace potentially dangerous strings with an empty string
    $input = preg_replace( $pattern, '', $input );

    return $input;
}

/**
 * Do full sanitization of a string
 *
 * @param string $text
 *
 * @return string
 */
function zontact_sanitize_full( $text ) {
	return zontact_sanitize_html( zontact_sanitize_javascript( $text ) );
}

/**
 * Returns the role and all roles with higher privileges.
 *
 * @param $role
 * @return array|string[]
 */
function zontact_get_roles_and_higher( $role ) {
    // Define roles in hierarchical order
    $roles_hierarchy = array(
        'subscriber',
        'contributor',
        'author',
        'editor',
        'administrator',
        'super_admin' // Note: 'super_admin' is used in Multisite networks only
    );

    // Find the index of the input role
    $role_index = array_search( $role, $roles_hierarchy );

    // If the input role is not found, return the input role.
    if ( $role_index === false) {
        // Return the input role, and also admin, as we always want admins to be able to create Forms, when custom roles are set.
        return array( $role, 'administrator', 'super_admin' );
    }

    // Get the roles with the same or higher privileges
    return array_slice( $roles_hierarchy, $role_index );
}

/**
 * Get a Zontact setting by key.
 *
 * @param string $key     Option key.
 * @param mixed  $default Default value.
 * @return mixed
 */
function zontact_get_option( string $key, $default = '' ) {
	$options = get_option( 'zontact_options', [] );
	return $options[ $key ] ?? $default;
}

/**
 * Update a Zontact setting by key.
 *
 * @param string $key   Option key.
 * @param mixed  $value New value.
 * @return bool
 */
function zontact_update_option( string $key, $value ): bool {
	$options         = get_option( 'zontact_options', [] );
	$options[ $key ] = $value;
	return update_option( 'zontact_options', $options );
}
