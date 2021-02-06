<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$file = CED_CATCH_DIRPATH . 'admin/partials/header.php';
if ( file_exists( $file ) ) {
	require_once $file;
}
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Ced_Catch_Import_Status_Table extends WP_List_Table {

	/** Class constructor */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'Catch Account', 'woocommerce-catch-integration' ), // singular name of the listed records
				'plural'   => __( 'Catch Accounts', 'woocommerce-catch-integration' ), // plural name of the listed records
				'ajax'     => false, // does this table support ajax?
			)
		);
	}

	public function prepare_items() {

		global $wpdb;

		$per_page = apply_filters( 'ced_catch_import_status_per_page', 10 );
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		// Column headers
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$current_page = $this->get_pagenum();
		if ( 1 < $current_page ) {
			$offset = $per_page * ( $current_page - 1 );
		} else {
			$offset = 0;
		}

		$this->items = self::get_import_Ids( $per_page );

		$count = self::get_count();

		// Set the pagination

		if ( ! $this->current_action() ) {

			$this->set_pagination_args(
				array(
					'total_items' => $count,
					'per_page'    => $per_page,
					'total_pages' => ceil( $count / $per_page ),
				)
			);
			$this->renderHTML();
		} else {
			$this->process_bulk_action();
		}

	}

	/*
	*
	* Function to get all the accounts
	*
	*/


	public function get_import_Ids( $per_page = 10 ) {

		$ImportIds              = get_option( 'ced_catch_import_ids_' . $_GET['shop_id'], array() );
		$ImportIds              = array_reverse( $ImportIds );
		$current_page           = $this->get_pagenum();
		$count                  = 0;
		$totalCount             = ( $current_page - 1 ) * $per_page;
		$ImportIdsToBeDisplayed = array();
		foreach ( $ImportIds as $key => $value ) {

			if ( $current_page == 1 && $count < $per_page ) {
				$count++;
				$ImportIdsToBeDisplayed[] = $value;
			} elseif ( $current_page > 1 ) {
				if ( $key < $totalCount ) {
					continue;
				} elseif ( $count < $per_page ) {
					$count++;
					$ImportIdsToBeDisplayed[] = $value;
				}
			}
		}
		return $ImportIdsToBeDisplayed;
	}

	/**
	 *
	 * Function to count number of responses in result
	 */
	public function get_count() {

		$ImportIds = get_option( 'ced_catch_import_ids_' . $_GET['shop_id'], array() );
		return count( $ImportIds );
	}

	/*
	*
	*Text displayed when no customer data is available
	*
	*/
	public function no_items() {
		_e( 'No Imports Yet.', 'woocommerce-catch-integration' );
	}

	function column_cb( $item ) {
		echo "<input type='checkbox' value=" . $item . " name='import_ids[]'>";
	}
	function column_import_id( $item ) {
		echo '<b><a>' . $item . '</a></b>';
	}

	function column_product_offer( $item ) {
		$uploadType = get_option( 'ced_catch_import_type_' . $item, '' );
		echo '<b><a>' . $uploadType . '</a></b>';
	}


	function column_offerStatus( $item ) {

		$report = get_option( 'ced_catch_import_status_' . $item, false );
		if ( isset( $report['import_status'] ) ) {
			echo '<b><a>' . $report['import_status'] . '</a></b>';
		} elseif ( isset( $report['status'] ) ) {
			echo '<b><a>' . $report['status'] . '</a></b>';
		}
	}


	function column_getIntegrationReport( $item ) {
		$pathOfIntegrationFile = wp_upload_dir()['basedir'] . '/cedcommerce_catchFeedReports/Integration_Feed' . $item . '.csv';

			$uploadType = get_option( 'ced_catch_import_type_' . $item, '' );
		if ( $uploadType == 'Product' ) {
			$buttonHtml = "<a class='button ced_catch_get_integration_report' data-uploadType = " . $uploadType . ' data-shopId=' . $_GET['shop_id'] . ' data-ImportId=' . $item . " href='javascript:void(0)'>" . __( 'Get Integration Report', 'woocommerce-catch-integration' ) . '</a>';
			echo $buttonHtml;
			$pathOfIntegrationFile = wp_upload_dir()['basedir'] . '/cedcommerce_catchFeedReports/Integration_Feed' . $item . '.csv';
			if ( file_exists( $pathOfIntegrationFile ) ) {
				$path           = wp_upload_dir()['baseurl'] . '/cedcommerce_catchFeedReports/Integration_Feed' . $item . '.csv';
				$action['view'] = '<a href=' . $path . ' target="#">' . __( 'View Integartion Report' ) . '</a>';
				return $this->row_actions( $action );
			}
		}
	}

	function column_getImportProgress( $item ) {
		$report     = get_option( 'ced_catch_import_status_' . $item, false );
		$itemId     = $item;
		$uploadType = get_option( 'ced_catch_import_type_' . $itemId, '' );
		if ( $uploadType == 'Offer' ) {
			if ( $report['offer_deleted'] || $report['offer_inserted'] || $report['offer_updated'] ) {
				if ( $report['offer_inserted'] ) {
					$html = '<b><a>Offer Inserted : ' . $report['offer_inserted'] . '</a></b>';
					return $html;
				} elseif ( $report['offer_deleted'] ) {
					$html = '<b><a>Offer Deleted : ' . $report['offer_deleted'] . '</a></b>';
					return $html;
				} elseif ( $report['offer_updated'] ) {
					$html = '<b><a>Offer Updated : ' . $report['offer_updated'] . '</a></b>';
					return $html;
				}
			}
		}
		$report_status = isset( $report['status'] ) ? $report['status'] : '';
		if ( isset( $report ) && ! empty( $report ) && isset( $report['import_status'] ) && $report['import_status'] == 'COMPLETE' || $report_status == 'COMPLETE' ) {
			$pathoferrorfile = wp_upload_dir()['basedir'] . '/cedcommerce_catchFeedReports/Feed' . $itemId . '.csv';
			if ( file_exists( $pathoferrorfile ) ) {
				$pathoferrorfile = wp_upload_dir()['baseurl'] . '/cedcommerce_catchFeedReports/Feed' . $itemId . '.csv';
				$ErrorReport     = file_get_contents( $pathoferrorfile );}

				$buttonHtml = "<a class='button ced_catch_get_import_report' data-uploadType = " . $uploadType . ' data-shopId=' . $_GET['shop_id'] . ' data-ImportId=' . $itemId . " href='javascript:void(0)'>" . __( 'Get Report', 'woocommerce-catch-integration' ) . '</a>';
				echo $buttonHtml;

				$pathoferrorfile = wp_upload_dir()['basedir'] . '/cedcommerce_catchFeedReports/Feed' . $itemId . '.csv';
			if ( file_exists( $pathoferrorfile ) ) {
				$path           = wp_upload_dir()['baseurl'] . '/cedcommerce_catchFeedReports/Feed' . $itemId . '.csv';
				$action['view'] = '<a href=' . $path . ' target="#">' . __( 'View Error Report' ) . '</a>';
				return $this->row_actions( $action );
			}
		} else {
			$buttonHtml = "<a class='button ced_catch_get_import_status' data-uploadType = " . $uploadType . ' data-shopId=' . $_GET['shop_id'] . ' data-ImportId=' . $itemId . " href='javascript:void(0)'>" . __( 'Get Import Progress', 'woocommerce-catch-integration' ) . '</a>';

			return $buttonHtml;}
	}

	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = array(
			'cb'                   => '<input type="checkbox">',
			'import_id'            => __( 'Import ID', 'woocommerce-catch-integration' ),
			'product_offer'        => __( 'Product/Offer', 'woocommerce-catch-integration' ),
			'offerStatus'          => __( 'Status', 'woocommerce-catch-integration' ),
			'getImportProgress'    => __( 'Import Progress', 'woocommerce-catch-integration' ),
			'getIntegrationReport' => __( 'Integration Report', 'woocommerce-catch-integration' ),
		);
		$columns = apply_filters( 'ced_catch_alter_import_status_table_columns', $columns );
		return $columns;
	}


	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return $sortable_columns = array();
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete', 'woocommerce-catch-integration' ),
		);
		return $actions;
	}

	/**
	 * Function to get changes in html
	 */
	public function renderHTML() {

		?>
		<div class="ced_catch_wrap ced_catch_wrap_extn">

			<div>
				<?php
				global $cedsearshelper;
				if ( ! session_id() ) {
					session_start();
				}
				?>
				<div id="post-body" class="metabox-holder columns-2">
					<div id="">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								$this->display();
								?>
							</form>
						</div>
					</div>
					<div class="clear"></div>
				</div>
				<br class="clear">
			</div>
		</div>


		<?php
	}
	public function process_bulk_action() {
		if ( $_POST['action'] == 'delete' || $_POST['action2'] == 'delete' ) {
			$ImportIdstoBeDeleted = isset( $_POST['import_ids'] ) ? $_POST['import_ids'] : array();
			if ( ! empty( $ImportIdstoBeDeleted ) && is_array( $ImportIdstoBeDeleted ) ) {
				$ImportIds = get_option( 'ced_catch_import_ids_' . $_GET['shop_id'], array() );
				foreach ( $ImportIdstoBeDeleted as $key => $value ) {
					foreach ( $ImportIds as $k => $v ) {
						if ( $v == $value ) {
							unset( $ImportIds[ $k ] );
							update_option( 'ced_catch_import_ids_' . $_GET['shop_id'], $ImportIds );
						}
					}
				}
				$redirectURL = get_admin_url() . 'admin.php?page=ced_catch&section=import-status-view&shop_id=' . $_GET['shop_id'];
				wp_redirect( $redirectURL );
			}
		} else {

		}
	}


}

$ced_catch_account_obj = new Ced_Catch_Import_Status_Table();
$ced_catch_account_obj->prepare_items();
