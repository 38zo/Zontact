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
		return (array) $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery
	}

	/** @inheritDoc */
	public function count( ?string $search = null ): int {
		global $wpdb;
		$table = Database::table_messages();

		$where = '1=1';
		$args  = [];
		if ( $search ) {
			$like  = '%' . $wpdb->esc_like( $search ) . '%';
			$where = '(name LIKE %s OR email LIKE %s OR message LIKE %s)';
			$args  = [ $like, $like, $like ];
		}

		$query = "SELECT COUNT(*) FROM {$table} WHERE {$where}";
		$sql   = $wpdb->prepare( $query, $args ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return (int) $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery
	}

	/** @inheritDoc */
	public function delete( array $ids ): int {
		global $wpdb;
		$table = Database::table_messages();
		$ids   = array_values( array_filter( array_map( 'intval', $ids ) ) );
		if ( empty( $ids ) ) {
			return 0;
		}

		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$sql          = $wpdb->prepare( "DELETE FROM {$table} WHERE id IN ({$placeholders})", $ids ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery
		return (int) $wpdb->rows_affected;
	}
}


