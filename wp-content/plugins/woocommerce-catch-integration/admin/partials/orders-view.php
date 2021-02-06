<?php
/**
 * Display list of orders
 *
 * @package  Woocommerce_Catch_Integration
 * @version  1.0.0
 * @link     https://cedcommerce.com
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$file = CED_CATCH_DIRPATH . 'admin/partials/header.php';

if ( file_exists( $file ) ) {
	include_once $file;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Ced_Catch_List_Orders
 *
 * @since 1.0.0
 */
class Ced_Catch_List_Orders extends WP_List_Table {

	/**
	 * Ced_Catch_List_Orders construct
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'Catch Order', 'woocommerce-catch-integration' ),
				'plural'   => __( 'Catch Orders', 'woocommerce-catch-integration' ),
				'ajax'     => true,
			)
		);
	}

	/**
	 * Function for preparing data to be displayed
	 *
	 * @since 1.0.0
	 */
	public function prepareItems() {
		$per_page = apply_filters( 'ced_catch_orders_list_per_page', 10 );
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->getSortableColumns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$current_page = $this->get_pagenum();
		if ( 1 < $current_page ) {
			$offset = $per_page * ( $current_page - 1 );
		} else {
			$offset = 0;
		}

		$this->items = self::cedCatchOrders( $per_page, $current_page );
		$count       = self::getCount();

		$this->set_pagination_args(
			array(
				'total_items' => $count,
				'per_page'    => $per_page,
				'total_pages' => ceil( $count / $per_page ),
			)
		);

		if ( ! $this->current_action() ) {
			$this->items = self::cedCatchOrders( $per_page, $current_page );
			$this->renderHTML();
		}
	}

	/**
	 * Function to count number of responses in result
	 *
	 * @since 1.0.0
	 */
	public function getCount() {
		global $wpdb;
		$shop_id        = isset( $_GET['shop_id'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_id'] ) ) : '';
		$orders_post_id = $wpdb->get_results( $wpdb->prepare( "SELECT `post_id` FROM $wpdb->postmeta WHERE `meta_key`=%s AND `meta_value`=%d  group by `post_id` ", 'ced_catch_order_shop_id', $shop_id ), 'ARRAY_A' );
		return count( $orders_post_id );
	}

	/**
	 * Text displayed when no  data is available
	 *
	 * @since 1.0.0
	 */
	public function no_items() {
		esc_html_e( 'No Orders To Display.', 'woocommerce-catch-integration' );
	}

	/**
	 * Function for id column
	 *
	 * @since 1.0.0
	 * @param array $items Order Data.
	 */
	public function column_id( $items ) {
		foreach ( $items as $key => $value ) {
			$display_orders = $value->get_data();
			echo '<b>#' . esc_attr( $display_orders['order_id'] ) . '</b>';
			break;
		}
	}

	/**
	 * Function for name column
	 *
	 * @since 1.0.0
	 * @param array $items Order Data.
	 */
	public function column_name( $items ) {
		foreach ( $items as $key => $value ) {
			$display_orders = $value->get_data();
			$product_id     = $display_orders['product_id'];
			$url            = get_edit_post_link( $product_id, '' );
			echo '<b><a class="ced_catch_prod_name" href="' . esc_url( $url ) . '" target="#">' . esc_attr( $display_orders['name'] ) . '</a></b></br>';
		}
	}

	/**
	 * Function for order Id column
	 *
	 * @since 1.0.0
	 * @param array $items Order Data.
	 */
	public function column_catch_order_id( $items ) {
		foreach ( $items as $key => $value ) {
			$display_orders  = $value->get_data();
			$order_id        = $display_orders['order_id'];
			$details         = wc_get_order( $order_id );
			$details         = $details->get_data();
			$order_meta_data = $details['meta_data'];
			foreach ( $order_meta_data as $key1 => $value1 ) {
				$order_id = $value1->get_data();
				if ( 'merchant_order_id' == $order_id['key'] ) {
					echo '<b>' . esc_attr( $order_id['value'] ) . '</b>';
				}
			}
			break;
		}
	}

	// /**
	// * Function for order status column
	// *
	// * @since 1.0.0
	// * @param array $items Order Data.
	// */
	// public function column_order_status( $items ) {
	// foreach ( $items as $key => $value ) {
	// $display_orders  = $value->get_data();
	// $order_id        = $display_orders['order_id'];
	// $details         = wc_get_order( $order_id );
	// $details         = $details->get_data();
	// $order_meta_data = $details['meta_data'];
	// foreach ( $order_meta_data as $key1 => $value1 ) {
	// $order_status = $value1->get_data();
	// if ( '_catch_umb_order_status' == $order_status['key'] ) {
	// echo '<b>' . esc_attr( $order_status['value'] ) . '</b>';
	// }
	// }
	// break;
	// }
	// }

	/**
	 * Function for Edit order column
	 *
	 * @since 1.0.0
	 * @param array $items Order Data.
	 */
	public function column_status( $items ) {
		$shop_id = isset( $_GET['shop_id'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_id'] ) ) : '';
		foreach ( $items as $key => $value ) {
			$display_orders = $value->get_data();
			$woo_order_url  = get_edit_post_link( $display_orders['order_id'], '' );
			// echo '<a href="' . esc_url( $woo_order_url ) . '" target="#">' . esc_html( __( 'Edit', 'woocommerce-catch-integration' ) ) . '</a>';
			$order_id     = $display_orders['order_id'];
			$order_status = get_post_meta( $display_orders['order_id'], '_catch_umb_order_status', true );
			if ( $order_status == 'Fetched' ) {
				echo "<input type='button' class='ced_catch_process_order_button order_accept' data-OrderId=" . $order_id . ' data-id=' . $shop_id . "  value='Accept'>";
				echo "<input type='button' class='ced_catch_process_order_button order_reject' data-OrderId=" . $order_id . ' data-id=' . $shop_id . "  value='Reject'>";
			} else {
				echo '<b>' . $order_status . '</b>';
			}
			break;
		}
	}

	public function column_action( $items ) {
		foreach ( $items as $key => $value ) {
			$display_orders = $value->get_data();
			$woo_order_url  = get_edit_post_link( $display_orders['order_id'], '' );
			echo '<a href="' . esc_url( $woo_order_url ) . '" target="#">' . esc_html( __( 'Edit', 'woocommerce-catch-integration' ) ) . '</a>';
			// $catch_order_id = get_post_meta( $display_orders['order_id'], '_ced_catch_order_id', true );
			// $order_status   = get_post_meta( $display_orders['order_id'], '_catch_umb_order_status', true );
			// $order_status = 'Rejected';
			// if ( $order_status == 'Fetched' ) {
			// echo "<input type='button' class='ced_catch_order_accept_button' data-OrderId=" . $catch_order_id . " value='Accept'>";
			// echo "<input type='button' class='ced_catch_order_reject_button' data-OrderId=" . $catch_order_id . " value='Reject'>";
			// } elseif ( $order_status == 'Accepted' ) {
			// echo '<b><a>Accepted</a></b>';
			// } elseif ( $order_status == 'Rejected' ) {
			// echo '<b class="not_completed">Rejected</b>';
			// }

			// echo '<b><a>'.$order_status.'</a></b>';
			break;
		}
	}

	/**
	 * Function for customer name column
	 *
	 * @since 1.0.0
	 * @param array $items Order Data.
	 */
	public function column_customer_name( $items ) {
		foreach ( $items as $key => $value ) {
			$display_orders = $value->get_data();
			$order_id       = $display_orders['order_id'];
			$details        = wc_get_order( $order_id );
			$details        = $details->get_data();
			echo '<b>' . esc_attr( $details['billing']['first_name'] ) . '</b>';
			break;
		}
	}

	/**
	 * Associative array of columns
	 *
	 * @since 1.0.0
	 */
	public function get_columns() {
		$columns = array(
			'id'             => __( 'WooCommerce Order', 'woocommerce-catch-integration' ),
			'name'           => __( 'Product Name', 'woocommerce-catch-integration' ),
			'catch_order_id' => __( 'Catch Order ID', 'woocommerce-catch-integration' ),
			'customer_name'  => __( 'Customer Name', 'woocommerce-catch-integration' ),
			// 'order_status'    => __( 'Order Status', 'woocommerce-catch-integration' ),
			'status'         => __( 'Status', 'woocommerce-catch-integration' ),
			'action'         => __( 'Action', 'woocommerce-catch-integration' ),
		);
		$columns = apply_filters( 'ced_catch_orders_columns', $columns );
		return $columns;
	}

	/**
	 * Columns to make sortable.
	 *
	 * @since 1.0.0
	 */
	public function getSortableColumns() {
		$sortable_columns = array();
		return $sortable_columns;
	}

	/**
	 * Render html content
	 *
	 * @since 1.0.0
	 */
	public function renderHTML() {
		?>
		<div class="ced_catch_wrap ced_catch_wrap_extn">
			<div class="ced_catch_setting_header ">
				<label class="manage_labels"><b><?php esc_html_e( 'CATCH ORDERS', 'woocommerce-catch-integration' ); ?></b></label>
				<?php
				$shop_id = isset( $_GET['shop_id'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_id'] ) ) : '';
				 echo '<button  class="ced_catch_custom_button" id="ced_catch_fetch_orders" data-id="' . esc_attr( $shop_id ) . '" >' . esc_html( __( 'Fetch Orders', 'woocommerce-catch-integration' ) ) . '</button>';
				?>
			</div>
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
		</div>
		<?php
	}

	/**
	 * Function to get all the orders
	 *
	 * @since 1.0.0
	 * @param      int $per_page    Results per page.
	 * @param      int $page_number   Page number.
	 */
	public function cedCatchOrders( $per_page, $page_number = 1 ) {
		global $wpdb;
		$shop_id = isset( $_GET['shop_id'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_id'] ) ) : '';
		$offset  = ( $page_number - 1 ) * $per_page;

		$orders_post_id = $wpdb->get_results( $wpdb->prepare( "SELECT `post_id` FROM $wpdb->postmeta WHERE `meta_key`=%s AND `meta_value`=%d  group by `post_id` DESC LIMIT %d OFFSET %d", 'ced_catch_order_shop_id', $shop_id, $per_page, $offset ), 'ARRAY_A' );
		foreach ( $orders_post_id as $key => $value ) {
			$post_id        = isset( $value['post_id'] ) ? $value['post_id'] : '';
			$post_details   = wc_get_order( $post_id );
			$order_detail[] = $post_details->get_items();
		}
		$order_detail = isset( $order_detail ) ? $order_detail : '';
		return( $order_detail );
	}
}

$ced_catch_orders_obj = new Ced_Catch_List_Orders();
$ced_catch_orders_obj->prepareItems();
