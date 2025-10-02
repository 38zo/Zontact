<?php
// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete options.
delete_option( 'zontact_options' );

// Optionally delete CPT data (only if exists); conservative approach: remove only our CPT posts.
$posts = get_posts( array(
	'post_type' => 'zontact_message',
	'post_status' => 'any',
	'numberposts' => -1,
) );
if ( $posts ) {
	foreach ( $posts as $post ) {
		wp_delete_post( $post->ID, true );
	}
}


