<?php
/**
 * Admin entries page.
 *
 * @package ThirtyEightZo\Zontact\Admin
 */

namespace ThirtyEightZo\Zontact\Admin;

use ThirtyEightZo\Zontact\Repository\DbEntryRepository;
use ThirtyEightZo\Zontact\Repository\EntryRepositoryInterface;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and renders the entries list screen.
 */
final class EntriesPage {

	/**
	 * Repository used to fetch data.
	 *
	 * @var EntryRepositoryInterface
	 */
	private EntryRepositoryInterface $repository;

	/**
	 * Constructor.
	 *
	 * @param EntryRepositoryInterface|null $repository Optional repository; defaults to DB repository.
	 */
	public function __construct( EntryRepositoryInterface $repository = null ) {
		$this->repository = $repository ?: new DbEntryRepository();
	}

	/**
	 * Register submenu.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_menu', [ $this, 'add_menu' ] );
	}

	/**
	 * Add submenu page under Zontact.
	 *
	 * @return void
	 */
	public function add_menu(): void {
		$capability = 'manage_options';
		$hook = add_submenu_page(
			'Zontact',
			__( 'Entries', 'zontact' ),
			__( 'Entries', 'zontact' ),
			$capability,
			'zontact-entries',
			[ $this, 'render' ]
		);

		add_action( "load-{$hook}", [ $this, 'load_screen' ] );
	}

	/**
	 * Screen load handler: set screen options if needed.
	 *
	 * @return void
	 */
	public function load_screen(): void {
		// Placeholder for future screen options (per-page, etc.).
	}

	/**
	 * Render the page output.
	 *
	 * @return void
	 */
	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'zontact' ) );
		}

		$list_table = new EntriesListTable( $this->repository );
		$list_table->prepare_items();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$search_value = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php echo esc_html__( 'Zontact Entries', 'zontact' ); ?></h1>
			<hr class="wp-header-end" />

			<form method="post">
				<?php wp_nonce_field( 'bulk-' . $list_table->_args['plural'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
				<?php $list_table->search_box( __( 'Search entries', 'zontact' ), 'zontact-entry' ); ?>
				<?php $list_table->display(); ?>
			</form>
		</div>
		<?php
	}
}


