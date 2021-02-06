<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
/**
 * FilterClass.
 *
 * @since 1.0.0
 */
class FilterClass {

	/**
	 * Function- filter_by_category.
	 * Used to Apply Filter on Product Page
	 *
	 * @since 1.0.0
	 */
	public function ced_catch_filters_on_products( $_products ) {

		if ( ( $_POST['status_sorting'] != '' && isset( $_POST['status_sorting'] ) ) || ( $_POST['pro_cat_sorting'] != '' && isset( $_POST['pro_cat_sorting'] ) ) || ( $_POST['pro_type_sorting'] != '' && isset( $_POST['pro_type_sorting'] ) ) ) {

				$status_sorting   = isset( $_POST['status_sorting'] ) ? $_POST['status_sorting'] : '';
				$pro_cat_sorting  = isset( $_POST['pro_cat_sorting'] ) ? $_POST['pro_cat_sorting'] : '';
				$pro_type_sorting = isset( $_POST['pro_type_sorting'] ) ? $_POST['pro_type_sorting'] : '';
				$current_url      = $_SERVER['REQUEST_URI'];
				wp_redirect( $current_url . '&status_sorting=' . $status_sorting . '&pro_cat_sorting=' . $pro_cat_sorting . '&pro_type_sorting=' . $pro_type_sorting );
		} else {
			$url = admin_url( 'admin.php?page=ced_catch&section=products-view&shop_id=' . $_GET['shop_id'] );
			wp_redirect( $url );
		}

	}//end ced_catch_filters_on_products()


	public function productSearch_box( $_products, $valueTobeSearched ) {

		if ( isset( $_POST['s'] ) && ! empty( $_POST['s'] ) ) {
			$current_url = $_SERVER['REQUEST_URI'];
			$searchdata  = isset( $_POST['s'] ) ? $_POST['s'] : '';
			$searchdata  = str_replace( ' ', ',', $searchdata );
			wp_redirect( $current_url . '&searchBy=' . $searchdata . '&shop_id=' . $_GET['shop_id'] );
		} else {
			$url = admin_url( 'admin.php?page=ced_catch&section=products-view&shop_id=' . $_GET['shop_id'] );
			wp_redirect( $url );
		}

	}
}//end class



