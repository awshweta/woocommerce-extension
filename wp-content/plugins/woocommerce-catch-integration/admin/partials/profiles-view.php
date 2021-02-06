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
class Ced_Catch_Profile_Table extends WP_List_Table {

	/** Class constructor */
	public function __construct() {

		parent::__construct(
			array(
				'singular' => __( 'Catch Profile', 'woocommerce-catch-integration' ), // singular name of the listed records
				'plural'   => __( 'Catch Profiles', 'woocommerce-catch-integration' ), // plural name of the listed records
				'ajax'     => false, // does this table support ajax?
			)
		);
	}
	/**
	 *
	 * function for preparing profile data to be displayed column
	 */
	public function prepare_items() {

		global $wpdb;

		$per_page = apply_filters( 'ced_catch_profile_list_per_page', 10 );
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

		$this->items = self::get_profiles( $per_page, $current_page );

		$count = self::get_count();

		// Set the pagination
		$this->set_pagination_args(
			array(
				'total_items' => $count,
				'per_page'    => $per_page,
				'total_pages' => ceil( $count / $per_page ),
			)
		);

		if ( ! $this->current_action() ) {
			$this->items = self::get_profiles( $per_page, $current_page );
			$this->renderHTML();
		} else {
			$this->process_bulk_action();
		}
	}
	/**
	 *
	 * function for status column
	 */
	public function get_profiles( $per_page = 10, $page_number = 1 ) {

		global $wpdb;
		$tableName = $wpdb->prefix . 'ced_catch_profiles';
		$shop_id   = $_GET['shop_id'];
		$sql       = "SELECT * FROM `$tableName` WHERE `shop_id`=$shop_id ORDER BY `id` DESC";
		$sql      .= " LIMIT $per_page";
		$sql      .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

		$result = $wpdb->get_results( $sql, 'ARRAY_A' );
		return $result;
	}

	/*
	*
	* function to count number of responses in result
	*
	*/
	public function get_count() {
		global $wpdb;
		$shop_id   = $_GET['shop_id'];
		$tableName = $wpdb->prefix . 'ced_catch_profiles';
		$sql       = "SELECT * FROM `$tableName` WHERE `shop_id`=$shop_id";
		$result    = $wpdb->get_results( $sql, 'ARRAY_A' );
		return count( $result );
	}

	/*
	*
	* Text displayed when no customer data is available
	*
	*/
	public function no_items() {
		_e( 'No Profiles Created.', 'woocommerce-catch-integration' );
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="catch_profile_ids[]" value="%s" />',
			$item['id']
		);
	}


	/**
	 * function for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_profile_name( $item ) {

		$title = '<strong>' . $item['profile_name'] . '</strong>';

		$actions = array(
			'edit' => sprintf( '<a href="?page=%s&section=%s&shop_id=%s&profileID=%s&panel=edit">Edit</a>', esc_attr( $_REQUEST['page'] ), 'profiles-view', $_GET['shop_id'], $item['id'] ),
		);
		return $title . $this->row_actions( $actions );
		return $title;
	}

	/**
	 *
	 * function for profile status column
	 */
	function column_profile_status( $item ) {

		if ( $item['profile_status'] == 'inactive' ) {
			return 'InActive';
		} else {
			return 'Active';
		}
	}
	/**
	 *
	 * function for category column
	 */
	function column_woo_categories( $item ) {

		$woo_categories = json_decode( $item['woo_categories'], true );

		if ( ! empty( $woo_categories ) ) {
			foreach ( $woo_categories as $key => $value ) {
				if ( $term = get_term_by( 'id', $value, 'product_cat' ) ) {
					echo '<p>' . $term->name . '</p>';
				}
			}
		}
	}

	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = array(
			'cb'             => '<input type="checkbox" />',
			'profile_name'   => __( 'Profile Name', 'woocommerce-catch-integration' ),
			'profile_status' => __( 'Profile Status', 'woocommerce-catch-integration' ),
			'woo_categories' => __( 'Mapped WooCommerce Categories', 'woocommerce-catch-integration' ),
		);
		$columns = apply_filters( 'ced_catch_alter_profiles_table_columns', $columns );
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
			'bulk-delete' => __( 'Delete', 'woocommerce-catch-integration' ),
		);
		return $actions;
	}


	/**
	 * Function to get changes in html
	 */
	public function renderHTML() {
		?>
		<div class="ced_catch_wrap ced_catch_wrap_extn">
				<div class="ced_catch_setting_header ">
					<b class="manage_labels"><?php _e( 'CATCH PROFILES', 'woocommerce-catch-integration' ); ?></b>
					<!-- <a href="<?php echo admin_url( 'admin.php?page=ced_catch&section=profiles-view&shop_id=' . $_GET['shop_id'] . '&panel=edit' ); ?>"  class="ced_catch_custom_button add_profile_button"><?php _e( 'Add Custom Profile', 'woocommerce-catch-integration' ); ?></a> -->
				</div>			
			<div>
				<?php
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
	/**
	 *
	 * function for getting current status
	 */
	public function current_action() {

		if ( isset( $_GET['panel'] ) ) {
			$action = isset( $_GET['panel'] ) ? $_GET['panel'] : '';
			return $action;
		} elseif ( isset( $_POST['action'] ) ) {
			$action = isset( $_POST['action'] ) ? $_POST['action'] : '';
			return $action;
		}
	}

	/**
	 *
	 * function for processing bulk actions
	 */
	public function process_bulk_action() {

		if ( ! session_id() ) {
			session_start();
		}

		if ( 'bulk-delete' === $this->current_action() || ( isset( $_GET['action'] ) && 'bulk-delete' === $_GET['action'] ) ) {

			$profileIds = isset( $_POST['catch_profile_ids'] ) ? $_POST['catch_profile_ids'] : array();
			if ( is_array( $profileIds ) && ! empty( $profileIds ) ) {

				global $wpdb;

				$tableName = $wpdb->prefix . 'ced_catch_profiles';

				$shop_id = isset( $_GET['shop_id'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_id'] ) ) : '';

				foreach ( $profileIds as $index => $pid ) {

					$product_ids_assigned = get_option( 'ced_catch_product_ids_in_profile_' . $pid, array() );
					foreach ( $product_ids_assigned as $index => $ppid ) {
						delete_post_meta( $ppid, 'ced_catch_profile_assigned' . $shop_id );
					}

					$term_id = $wpdb->get_results( $wpdb->prepare( ' SELECT `woo_categories` FROM ' . $tableName . ' WHERE `id` = %d ', $pid ), 'ARRAY_A' );
					$term_id = json_decode( $term_id[0]['woo_categories'], true );
					foreach ( $term_id as $key => $value ) {
						delete_term_meta( $value, 'ced_catch_profile_created_' . $shop_id );
						delete_term_meta( $value, 'ced_catch_profile_id_' . $shop_id );
						delete_term_meta( $value, 'ced_catch_mapped_category_' . $shop_id );
					}
				}

				$sql = 'DELETE FROM `' . $tableName . '` WHERE `id` IN (';
				foreach ( $profileIds as $id ) {
					$sql .= $id . ',';
				}
				$sql          = rtrim( $sql, ',' );
				$sql         .= ')';
				$deleteStatus = $wpdb->query( $sql );

				$redirectURL = get_admin_url() . 'admin.php?page=ced_catch&section=profiles-view&shop_id=' . $_GET['shop_id'];
				wp_redirect( $redirectURL );
			}
		} elseif ( 'bulk-activate' === $this->current_action() || ( isset( $_POST['action'] ) && 'bulk-activate' === $_POST['action'] ) ) {

			$profileIds = isset( $_POST['catch_profile_ids'] ) ? $_POST['catch_profile_ids'] : array();
			if ( is_array( $profileIds ) && ! empty( $profileIds ) ) {

				global $wpdb;
				$tableName = $wpdb->prefix . 'ced_catch_profiles';
				foreach ( $profileIds as $key => $value ) {
					$wpdb->update( $tableName, array( 'profile_status' => 'active' ), array( 'id' => $value ) );
				}
			}
			$redirectURL = get_admin_url() . 'admin.php?page=ced_catch&section=profiles-view&shop_id=' . $_GET['shop_id'];
			wp_redirect( $redirectURL );
		} elseif ( 'bulk-deactivate' === $this->current_action() || ( isset( $_POST['action'] ) && 'bulk-deactivate' === $_POST['action'] ) ) {

			$profileIds = isset( $_POST['catch_profile_ids'] ) ? $_POST['catch_profile_ids'] : array();
			if ( is_array( $profileIds ) && ! empty( $profileIds ) ) {

				global $wpdb;
				$tableName = $wpdb->prefix . 'ced_catch_profiles';
				foreach ( $profileIds as $key => $value ) {
					$wpdb->update( $tableName, array( 'profile_status' => 'inactive' ), array( 'id' => $value ) );
				}
			}
			$redirectURL = get_admin_url() . 'admin.php?page=ced_catch&section=profiles-view&shop_id=' . $_GET['shop_id'];
			wp_redirect( $redirectURL );
		} elseif ( isset( $_GET['panel'] ) && $_GET['panel'] == 'edit' ) {

			$file = CED_CATCH_DIRPATH . 'admin/partials/profile-edit-view.php';
			if ( file_exists( $file ) ) {
				require_once $file;
			}
		}
	}
}

$ced_catch_profile_obj = new Ced_Catch_Profile_Table();
$ced_catch_profile_obj->prepare_items();
