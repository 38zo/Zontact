<?php
/**
 * WP_List_Table implementation for Zontact entries.
 *
 * @package ThirtyEightZo\Zontact\Admin
 */

namespace ThirtyEightZo\Zontact\Admin;

use ThirtyEightZo\Zontact\Repository\EntryRepositoryInterface;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( '\WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Entries table view.
 */
final class EntriesListTable extends \WP_List_Table {

	/**
	 * Repository for data access.
	 *
	 * @var EntryRepositoryInterface
	 */
	private EntryRepositoryInterface $repository;

	/**
	 * Constructor.
	 *
	 * @param EntryRepositoryInterface $repository Repository instance.
	 */
	public function __construct( EntryRepositoryInterface $repository ) {
		parent::__construct( [
			'plural'   => 'zontact_entries',
			'singular' => 'zontact_entry',
			'ajax'     => false,
		] );
		$this->repository = $repository;
	}

	/** @inheritDoc */
	public function get_columns(): array {
		return [
			'cb'        => '<input type="checkbox" />',
			'id'        => __( 'ID', 'zontact' ),
			'name'      => __( 'Name', 'zontact' ),
			'email'     => __( 'Email', 'zontact' ),
			'subject'   => __( 'Subject', 'zontact' ),
			'message'   => __( 'Message', 'zontact' ),
			'created_at'=> __( 'Date', 'zontact' ),
		];
	}

	/** @inheritDoc */
	protected function get_sortable_columns(): array {
		return [
			'id'         => [ 'id', true ],
			'created_at' => [ 'created_at', true ],
		];
	}

	/** @inheritDoc */
	protected function column_cb( $item ): string {
		return sprintf( '<input type="checkbox" name="ids[]" value="%d" />', (int) $item['id'] );
	}

	/** @inheritDoc */
	protected function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'id':
				return (int) $item['id'];
			case 'name':
				return esc_html( (string) $item['name'] );
			case 'email':
				return esc_html( (string) $item['email'] );
			case 'subject':
				return esc_html( (string) ( $item['subject'] ?? '' ) );
			case 'message':
				return esc_html( wp_trim_words( wp_strip_all_tags( (string) $item['message'] ), 20, '…' ) );
			case 'created_at':
				$time = strtotime( (string) $item['created_at'] );
				return esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $time ) );
			default:
				return '';
		}
	}

	/** @inheritDoc */
	protected function get_bulk_actions(): array {
		return [
			'delete' => __( 'Delete', 'zontact' ),
		];
	}

	/**
	 * Process bulk actions.
	 *
	 * @return void
	 */
	public function process_bulk_action(): void {
		if ( 'delete' === $this->current_action() && ! empty( $_POST['ids'] ) && is_array( $_POST['ids'] ) ) {
			check_admin_referer( 'bulk-' . $this->_args['plural'] );
			$ids = array_map( 'intval', wp_unslash( $_POST['ids'] ) );
			$this->repository->delete( $ids );
		}
	}

	/** @inheritDoc */
	public function prepare_items() {
		$per_page = 30;
		$page     = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
		$search   = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : null;

		$this->_column_headers = [ $this->get_columns(), [], $this->get_sortable_columns() ];

		$this->process_bulk_action();

		$total_items = $this->repository->count( $search );
		$this->items = $this->repository->list( $page, $per_page, $search );

		$this->set_pagination_args( [
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => (int) ceil( $total_items / $per_page ),
		] );
	}
}


