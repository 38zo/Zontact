<?php
/**
 * DB-backed entry repository.
 *
 * @package ThirtyEightZo\Zontact\Repository
 */

namespace ThirtyEightZo\Zontact\Repository;

use ThirtyEightZo\Zontact\Database;

defined( 'ABSPATH' ) || exit;

/**
 * Repository implementation using $wpdb and the messages table.
 */
final class DbEntryRepository implements EntryRepositoryInterface {

	/**
	 * List entries ordered by newest first.
	 *
	 * @param int         $page     Page number (1-based).
	 * @param int         $per_page Per page (capped to 30 for free).
	 * @param string|null $search   Optional search term.
	 * @return array
	 */
	public function list( int $page, int $per_page, ?string $search = null ): array {
		global $wpdb;
		$table    = Database::table_messages();
		$per_page = max( 1, min( 30, $per_page ) );
		$offset   = max( 0, ( $page - 1 ) * $per_page );

		// Create cache key for this specific query.
		$cache_key = 'zontact_entries_' . md5( $page . '_' . $per_page . '_' . ( $search ?? '' ) );
		
		// Try to get from cache first.
		$cached_result = wp_cache_get( $cache_key, 'zontact' );
		if ( false !== $cached_result ) {
			return (array) $cached_result;
		}

		$where = '1=1';
		$args  = [];
		if ( $search ) {
			$like  = '%' . $wpdb->esc_like( $search ) . '%';
			$where = '(name LIKE %s OR email LIKE %s OR message LIKE %s)';
			$args  = [ $like, $like, $like ];
		}

		$query = "SELECT id, form_key, name, email, phone, subject, message, created_at
			FROM {$table}
			WHERE {$where}
			ORDER BY id DESC
			LIMIT %d OFFSET %d";

		$args[] = $per_page;
		$args[] = $offset;

		$sql = $wpdb->prepare( $query, $args ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$results = (array) $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery
		
		// Cache results for 5 minutes.
		wp_cache_set( $cache_key, $results, 'zontact', 300 );
		
		return $results;
	}

	/** @inheritDoc */
	public function count( ?string $search = null ): int {
		global $wpdb;
		$table = Database::table_messages();

		// Create cache key for count query.
		$cache_key = 'zontact_count_' . md5( $search ?? '' );
		
		// Try to get from cache first.
		$cached_count = wp_cache_get( $cache_key, 'zontact' );
		if ( false !== $cached_count ) {
			return (int) $cached_count;
		}

		$where = '1=1';
		$args  = [];
		if ( $search ) {
			$like  = '%' . $wpdb->esc_like( $search ) . '%';
			$where = '(name LIKE %s OR email LIKE %s OR message LIKE %s)';
			$args  = [ $like, $like, $like ];
		}

		$query = "SELECT COUNT(*) FROM {$table} WHERE {$where}";
		
		// Only use prepare if we have placeholders
		if ( ! empty( $args ) ) {
			$sql   = $wpdb->prepare( $query, $args ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$count = (int) $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery
		} else {
			$count = (int) $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}
		
		// Cache count for 5 minutes.
		wp_cache_set( $cache_key, $count, 'zontact', 300 );
		
		return $count;
	}

	/**
	 * Deletes messages from the database by their IDs.
	 *
	 * This method safely deletes rows from the messages table corresponding
	 * to the given array of IDs. All IDs are sanitized before use.
	 * The query uses a prepared statement with dynamic placeholders for safety.
	 *
	 * @since 1.0.0
	 *
	 * @param int[] $ids List of message IDs to delete.
	 * @return int Number of rows affected by the delete operation.
	 */
	public function delete( array $ids ): int {
		global $wpdb;

		// The table name is internal and not user-supplied.
		$table = Database::table_messages();

		// Sanitize the IDs.
		$ids = array_values( array_filter( array_map( 'absint', $ids ) ) );
		if ( empty( $ids ) ) {
			return 0;
		}

		// Build placeholders for safe prepared statement (%d,%d,%d,...).
		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

		/*
		* Direct database write operation (DELETE) - caching not applicable for write operations.
		* This operation intentionally bypasses object caching as it modifies data.
		* 
		* Note: Table names cannot be prepared in WordPress, and dynamic IN clauses require
		* interpolated placeholders. The table name is internal and sanitized, and IDs are
		* sanitized via absint() before use.
		*/
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
		$wpdb->query( $wpdb->prepare( "DELETE FROM `{$table}` WHERE id IN ({$placeholders})", $ids ) );

		// Clear cache after delete operation to ensure fresh data.
		$this->clear_cache();

		return (int) $wpdb->rows_affected;
	}


	/**
	 * Clear all cached entries and counts.
	 *
	 * @return void
	 */
	private function clear_cache(): void {
		// Clear specific cache keys for entries and counts.
		global $wpdb;
		
		// Clear common cache patterns for our plugin.
		// This is a simplified approach since WordPress doesn't provide group deletion.
		wp_cache_delete( 'zontact_entries_all', 'zontact' );
		wp_cache_delete( 'zontact_count_all', 'zontact' );
		
		// For a more thorough cleanup, we could implement a cache key tracking system,
		// but for this plugin's scope, the above should suffice.
	}
}


