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

class CatchListProducts extends WP_List_Table {


	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'Product', 'woocommerce-catch-integration' ), // singular name of the listed records
				'plural'   => __( 'Products', 'woocommerce-catch-integration' ), // plural name of the listed records
				'ajax'     => true, // does this table support ajax?
			)
		);
	}

	/**
	 *
	 * Function for preparing data to be displayed
	 */

	public function prepare_items() {

		global $wpdb;

		$per_page  = apply_filters( 'ced_catch_products_per_page', 50 );
		$post_type = 'product';
		$columns   = $this->get_columns();
		$hidden    = array();
		$sortable  = $this->get_sortable_columns();

			// Column headers
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$current_page = $this->get_pagenum();

		if ( 1 < $current_page ) {
			$offset = $per_page * ( $current_page - 1 );
		} else {
			$offset = 0;
		}
		$this->items = self::get_product_details( $per_page, $current_page, $post_type );
		$count       = self::get_count( $per_page, $current_page );

		// Set the pagination
		$this->set_pagination_args(
			array(
				'total_items' => $count,
				'per_page'    => $per_page,
				'total_pages' => ceil( $count / $per_page ),
			)
		);

		if ( ! $this->current_action() ) {
			// $this->items = self::get_product_details( $per_page, $current_page ,$post_type  );
			$this->renderHTML();
		} else {
			$this->process_bulk_action();
		}
	}

	/**
	 *
	 * Function for get product data
	 */
	public function get_product_details( $per_page = '', $page_number, $post_type ) {
		$filterFile = CED_CATCH_DIRPATH . 'admin/partials/products-filters.php';
		if ( file_exists( $filterFile ) ) {
			require_once $filterFile;
		}

		$getImportIds = get_option( 'ced_catch_import_ids_' . $_GET['shop_id'], array() );
		// if(!empty($getImportIds))
		// {
		// foreach ($getImportIds as $key1 => $value1) {
		// $pathOfIntegrationFile = wp_upload_dir()['basedir'].'/cedcommerce_catchFeedReports/Integration_Feed'.$value1.'.csv';
		// if(file_exists($pathOfIntegrationFile))
		// {
		// $IntegrationFileContents = fopen($pathOfIntegrationFile,"r");
		// while(($data = fgetcsv($IntegrationFileContents,0,';'))!== FALSE)
		// $feedData[] = $data ;
		// }
		// }
		// foreach ($feedData as $key2 => $value2) {
		// if($key2!=0){
		// $productStatus['sku']= $value2[1];
		// $productStatus['status'] = $value2[3];
		// $productstatusbySku[] = $productStatus;
		// }
		// }
		// }
		$instanceOf_FilterClass = new FilterClass();
		$args                   = $this->GetFilteredData( $per_page, $page_number );
		if ( ! empty( $args ) && isset( $args['tax_query'] ) || isset( $args['meta_query'] ) ) {
			$args = $args;
		} else {
			$args = array(
				'post_type'      => $post_type,
				'posts_per_page' => $per_page,
				'paged'          => $page_number,
			);
		}
		$loop           = new WP_Query( $args );
		$product_data   = $loop->posts;
		$woo_categories = get_terms( 'product_cat', array( 'hide_empty' => false ) );
		$woo_products   = array();
		foreach ( $product_data as $key => $value ) {
			$get_product_data                     = wc_get_product( $value->ID );
			$get_product_data                     = $get_product_data->get_data();
			$woo_products[ $key ]['category_ids'] = isset( $get_product_data['category_ids'] ) ? $get_product_data['category_ids'] : array();
			$woo_products[ $key ]['id']           = $value->ID;
			$woo_products[ $key ]['name']         = $get_product_data['name'];
			$woo_products[ $key ]['stock']        = $get_product_data['stock_quantity'];
			if ( ! empty( $productstatusbySku ) ) {
				foreach ( $productstatusbySku as $key3 => $value3 ) {
					if ( $value3['sku'] == $get_product_data['sku'] ) {
						$woo_products[ $key ]['status'] = $value3['status'];
					}
				}
			}
			$woo_products[ $key ]['sku']   = $get_product_data['sku'];
			$woo_products[ $key ]['price'] = $get_product_data['price'];
			$Image_url_id                  = $get_product_data['image_id'];
			$woo_products[ $key ]['image'] = wp_get_attachment_url( $Image_url_id );
			foreach ( $woo_categories as $key1 => $value1 ) {
				if ( isset( $get_product_data['category_ids'][0] ) ) {
					if ( $value1->term_id == $get_product_data['category_ids'][0] ) {
						$woo_products[ $key ]['category'] = $value1->name;
					}
				}
			}
		}

		if ( isset( $_POST['filter_button'] ) ) {
			$woo_products = $instanceOf_FilterClass->ced_catch_filters_on_products( $woo_products );
		} elseif ( isset( $_POST['s'] ) && $_POST['s'] != '' ) {
			// $filteredProducts = $this->ced_catchGetAllposts();
			// $substring = stripcslashes(strtolower($_POST['s']));
			$woo_products = $instanceOf_FilterClass->productSearch_box( $filteredProducts, $substring );
		}
		return $woo_products;

	}

	public function ced_catchGetAllposts() {
		$args           = array(
			'post_type'      => 'product',
			'posts_per_page' => -1,
		);
		$loop           = new WP_Query( $args );
		$product_data   = $loop->posts;
		$woo_categories = get_terms( 'product_cat', array( 'hide_empty' => false ) );
		$woo_products   = array();
		foreach ( $product_data as $key => $value ) {
			$get_product_data                    = wc_get_product( $value->ID );
			$get_product_data                    = $get_product_data->get_data();
			$woo_products[ $key ]['category_id'] = isset( $get_product_data['category_ids'][0] ) ? $get_product_data['category_ids'][0] : '';
			$woo_products[ $key ]['id']          = $value->ID;
			$woo_products[ $key ]['name']        = $get_product_data['name'];
			$woo_products[ $key ]['stock']       = $get_product_data['stock_quantity'];
			$woo_products[ $key ]['sku']         = $get_product_data['sku'];
			$woo_products[ $key ]['price']       = $get_product_data['price'];
			$Image_url_id                        = $get_product_data['image_id'];
			$woo_products[ $key ]['image']       = wp_get_attachment_url( $Image_url_id );
			foreach ( $woo_categories as $key1 => $value1 ) {
				if ( isset( $get_product_data['category_ids'][0] ) ) {
					if ( $value1->term_id == $get_product_data['category_ids'][0] ) {
						$woo_products[ $key ]['category'] = $value1->name;
					}
				}
			}
		}
		return $woo_products;
	}

	/**
	 *
	 * Text displayed when no data is available
	 */
	public function no_items() {
		_e( 'No Products To Show.', 'woocommerce-catch-integration' );
	}

	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */

	public function get_sortable_columns() {
		return $sortable_columns = array();
	}

	/*
	 * Render the bulk edit checkbox
	 *
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="catch_product_ids[]" class="catch_products_id" value="%s" />',
			$item['id']
		);
	}

	/**
	 *
	 * function for name column
	 */
	public function column_name( $item ) {

		$url           = get_edit_post_link( $item['id'], '' );
		$actions['id'] = 'ID:' . $item['id'];

		echo '<b><a class="ced_catch_prod_name" href="' . $url . '">' . $item['name'] . '</a></b><br>';
		return $this->row_actions( $actions );

	}


	/**
	 *
	 * function for profile column
	 */
	public function column_profile( $item ) {
		$shop_id                      = $_GET['shop_id'];
		$get_profile_id_of_prod_level = get_post_meta( $item['id'], 'ced_catch_profile_assigned' . $_GET['shop_id'], true );
		if ( ! empty( $get_profile_id_of_prod_level ) ) {
			global $wpdb;
			$tableName    = $wpdb->prefix . 'ced_catch_profiles';
			$sql          = "SELECT `profile_name` FROM `$tableName` WHERE `id` = '$get_profile_id_of_prod_level' ";
			$profile_name = $wpdb->get_results( $sql, 'ARRAY_A' );
			echo '<b>' . $profile_name[0]['profile_name'] . '</b>';
			$profile_id = $get_profile_id_of_prod_level;
		} else {
			$get_catch_category_id = '';
			$category_ids          = isset( $item['category_ids'] ) ? $item['category_ids'] : array();
			foreach ( $category_ids as $index => $data ) {
				$get_catch_category_id_data = get_term_meta( $data );
				$get_catch_category_id      = isset( $get_catch_category_id_data[ 'ced_catch_mapped_category_' . $shop_id ] ) ? $get_catch_category_id_data[ 'ced_catch_mapped_category_' . $shop_id ] : '';
				if ( ! empty( $get_catch_category_id ) ) {
					break;
				}
			}

			if ( ! empty( $get_catch_category_id ) ) {
				foreach ( $get_catch_category_id as $key => $catch_id ) {
					$get_catch_profile_assigned = get_option( 'ced_woo_catch_mapped_categories_name' );
					$get_catch_profile_assigned = isset( $get_catch_profile_assigned[ $_GET['shop_id'] ][ $catch_id ] ) ? $get_catch_profile_assigned[ $_GET['shop_id'] ][ $catch_id ] : '';
				}

				if ( isset( $get_catch_profile_assigned ) && ! empty( $get_catch_profile_assigned ) ) {
					echo '<b>' . $get_catch_profile_assigned . '</b>';
				}
			} else {
				echo '<b class="not_completed">' . __( 'Profile Not Assigned', 'woocommerce-catch-integration' ) . '</b>';
				$actions['edit'] = '<a href="javascript:void(0)" class="ced_catch_profiles_on_pop_up" data-shopid="' . $_GET['shop_id'] . '" data-product_id="' . $item['id'] . '">' . __( 'Assign Profile', 'woocommerce-catch-integration' ) . '</a>';
				return $this->row_actions( $actions );
			}
			$profile_id = isset( $get_catch_category_id_data[ 'ced_catch_profile_id_' . $_GET['shop_id'] ] ) ? $get_catch_category_id_data[ 'ced_catch_profile_id_' . $_GET['shop_id'] ] : '';
			$profile_id = $profile_id[0];
		}

		if ( $profile_id ) {
			$edit_profile_url  = admin_url( 'admin.php?page=ced_catch&section=profiles-view&shop_id=' . $_GET['shop_id'] . '&profileID=' . $profile_id . '&panel=edit' );
			$actions['edit']   = '<a href="' . $edit_profile_url . '">' . __( 'Edit', 'woocommerce-catch-integration' ) . '</a>';
			$actions['change'] = '<a href="javascript:void(0)" class="ced_catch_profiles_on_pop_up" data-shopid="' . $_GET['shop_id'] . '" data-product_id="' . $item['id'] . '">' . __( 'Change', 'woocommerce-catch-integration' ) . '</a>';
			return $this->row_actions( $actions );
		}
	}
	/**
	 *
	 * function for stock column
	 */
	public function column_stock( $item ) {

		$catch_stock  = get_post_meta( $item['id'], 'ced_catch_custom_stock', true );
		$stock_status = get_post_meta( $item['id'], '_stock_status', true );
		if ( $stock_status == 'outofstock' ) {
			return '<b class="stock_alert" >' . __( 'Out Of Stock', 'woocommerce-catch-integration' ) . '</b>';
		} elseif ( $catch_stock ) {
			return '<b>' . $catch_stock . '</b>';
		} elseif ( $item['stock'] != '' ) {
			return '<b>' . $item['stock'] . '</b>';
		} else {
			return '<b>10</b>';
		}
	}
	/**
	 *
	 * function for category column
	 */
	public function column_category( $item ) {
		if ( isset( $item['category'] ) ) {
			return '<b>' . $item['category'] . '</b>';
		}

	}
	/**
	 *
	 * function for price column
	 */
	public function column_price( $item ) {
		$catch_price = get_post_meta( $item['id'], 'ced_catch_custom_price', true );
		if ( $catch_price ) {
			return get_woocommerce_currency_symbol() . ' <b class="success_upload_on_catch">' . $catch_price . '</b>';
		}
		return get_woocommerce_currency_symbol() . '&nbsp<b class="success_upload_on_catch">' . $item['price'] . '</b>';
	}
	/**
	 *
	 * function for product type column
	 */
	public function column_type( $item ) {
		$product      = wc_get_product( $item['id'] );
		$product_type = $product->get_type();
		return '<b>' . $product_type . '</b>';
	}
	/**
	 *
	 * function for sku column
	 */
	public function column_sku( $item ) {
		return '<b>' . $item['sku'] . '</b>';
	}
	/**
	 *
	 * function for image column
	 */
	public function column_image( $item ) {
		return '<img height="50" width="50" src="' . $item['image'] . '">';
	}

	/**
	 *
	 * function for reference number
	 */
	public function column_referenceNo( $item ) {
		$referenceNo = get_post_meta( $item['id'], '_wpm_gtin_code', true );
		if ( $referenceNo != '' ) {
			echo '<b>' . $referenceNo . '</b>';
		}
	}

	public function column_status( $item ) {
		$shop_id        = isset( $_GET['shop_id'] ) ? $_GET['shop_id'] : '';
		$product_status = get_post_meta( $item['id'], 'ced_catch_product_on_catch_' . $shop_id, true );
		if ( $product_status ) {
			echo '<b><a>Uploaded</a></b>';
		} else {
			echo '<b>Not Uploaded</b>';
		}
	}
	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */

	function get_columns() {
		$columns = array(
			'cb'       => '<input type="checkbox" />',
			'image'    => __( 'Product Image', 'woocommerce-catch-integration' ),
			'name'     => __( 'Product Name', 'woocommerce-catch-integration' ),
			'type'     => __( 'Product Type', 'woocommerce-catch-integration' ),
			'price'    => __( 'Product Price', 'woocommerce-catch-integration' ),
			'profile'  => __( 'Profile Assigned', 'woocommerce-catch-integration' ),
			'sku'      => __( 'Product Sku', 'woocommerce-catch-integration' ),
			'stock'    => __( 'Product Stock', 'woocommerce-catch-integration' ),
			'category' => __( 'Woo Category', 'woocommerce-catch-integration' ),
			'status'   => __( 'Product Status', 'woocommerce-catch-integration' ),
		);
		$columns = apply_filters( 'ced_catch_alter_product_table_columns', $columns );
		return $columns;
	}


	/**
	 * Function to return count of total products to make sortable.
	 *
	 * @return array
	 */

	public function get_count( $per_page, $page_number ) {
		$args = $this->GetFilteredData( $per_page, $page_number );
		if ( ! empty( $args ) && isset( $args['tax_query'] ) || isset( $args['meta_query'] ) ) {
			$args = $args;
		} else {
			$args = array( 'post_type' => 'product' );
		}
		$loop         = new WP_Query( $args );
		$product_data = $loop->posts;
		$product_data = $loop->found_posts;

		return $product_data;

	}

	public function GetFilteredData( $per_page, $page_number ) {
		$shop_id = isset( $_GET['shop_id'] ) ? $_GET['shop_id'] : '';
		if ( isset( $_GET['status_sorting'] ) || isset( $_GET['pro_cat_sorting'] ) || isset( $_GET['pro_type_sorting'] ) || isset( $_GET['searchBy'] ) ) {
			if ( ! empty( $_REQUEST['pro_cat_sorting'] ) ) {
				$pro_cat_sorting = isset( $_GET['pro_cat_sorting'] ) ? $_GET['pro_cat_sorting'] : '';
				if ( $pro_cat_sorting != '' ) {
					$selected_cat          = array( $pro_cat_sorting );
					$tax_query             = array();
					$tax_queries           = array();
					$tax_query['taxonomy'] = 'product_cat';
					$tax_query['field']    = 'id';
					$tax_query['terms']    = $selected_cat;
					$args['tax_query'][]   = $tax_query;
				}
			}

			if ( ! empty( $_REQUEST['pro_type_sorting'] ) ) {
				$pro_type_sorting = isset( $_GET['pro_type_sorting'] ) ? $_GET['pro_type_sorting'] : '';
				if ( $pro_type_sorting != '' ) {
					$selected_type         = array( $pro_type_sorting );
					$tax_query             = array();
					$tax_queries           = array();
					$tax_query['taxonomy'] = 'product_type';
					$tax_query['field']    = 'id';
					$tax_query['terms']    = $selected_type;
					$args['tax_query'][]   = $tax_query;
				}
			}

			if ( ! empty( $_REQUEST['status_sorting'] ) ) {
				$status_sorting = isset( $_GET['status_sorting'] ) ? $_GET['status_sorting'] : '';
				if ( $status_sorting != '' ) {
					$meta_query = array();
					if ( $status_sorting == 'Uploaded' ) {
						$args['orderby'] = 'meta_value_num';
						$args['order']   = 'ASC';

						$meta_query[] = array(
							'key'     => 'ced_catch_product_on_catch_' . $shop_id,
							'compare' => 'EXISTS',
						);
					} elseif ( $status_sorting == 'NotUploaded' ) {
						$meta_query[] = array(
							'key'     => 'ced_catch_product_on_catch_' . $shop_id,
							'compare' => 'NOT EXISTS',
						);
					}
					$args['meta_query'] = $meta_query;
				}
			}

			if ( ! empty( $_REQUEST['searchBy'] ) ) {
				$searchBy = isset( $_GET['searchBy'] ) ? $_GET['searchBy'] : '';
				if ( $searchBy != '' ) {
					$meta_query = array();

					// elseif ($status_sorting == 'NotUploaded') {
					$meta_query[] = array(
						'key'     => '_sku',
						'value'   => $searchBy,
						'compare' => 'LIKE',
					);
					// }
					$args['meta_query'] = $meta_query;
				}
			}

			$args['post_type']      = 'product';
			$args['posts_per_page'] = $per_page;
			$args['paged']          = $page_number;
			return $args;
		}

	}
	/**
	 *
	 * render bulk actions
	 */

	protected function bulk_actions( $which = '' ) {
		if ( $which == 'top' ) :
			if ( is_null( $this->_actions ) ) {
				$this->_actions = $this->get_bulk_actions();
				/**
				 * Filters the list table Bulk Actions drop-down.
				 *
				 * The dynamic portion of the hook name, `$this->screen->id`, refers
				 * to the ID of the current screen, usually a string.
				 *
				 * This filter can currently only be used to remove bulk actions.
				 *
				 * @since 3.5.0
				 *
				 * @param array $actions An array of the available bulk actions.
				 */
				$this->_actions = apply_filters( "bulk_actions-{$this->screen->id}", $this->_actions );
				$two            = '';
			} else {
				$two = '2';
			}

			if ( empty( $this->_actions ) ) {
				return;
			}

			echo '<label for="bulk-action-selector-' . esc_attr( $which ) . '" class="screen-reader-text">' . __( 'Select bulk action' ) . '</label>';
			echo '<select name="action' . $two . '" class="bulk-action-selector ">';
			echo '<option value="-1">' . __( 'Bulk Actions' ) . "</option>\n";

			foreach ( $this->_actions as $name => $title ) {
				$class = 'edit' === $name ? ' class="hide-if-no-js"' : '';

				echo "\t" . '<option value="' . $name . '"' . $class . '>' . $title . "</option>\n";
			}

			echo "</select>\n";

			submit_button( __( 'Apply' ), 'action', '', false, array( 'id' => 'ced_catch_bulk_operation' ) );
			echo "\n";
		endif;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'upload_product' => __( 'Upload/Update Products', 'woocommerce-catch-integration' ),
			'upload_offer'   => __( 'Upload Offers/Update Inventory', 'woocommerce-catch-integration' ),
			'remove_offer'   => __( 'Remove Offers', 'woocommerce-catch-integration' ),
		);
		return $actions;
	}
	/**
	 *
	 * function for rendering html
	 */
	public function renderHTML() {      ?>
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<div class="meta-box-sortables ui-sortable">
					<?php
					$status_actions = array(
						'Uploaded'    => __( 'Uploaded', 'woocommerce-catch-integration' ),
						'NotUploaded' => __( 'Not Uploaded', 'woocommerce-catch-integration' ),
					);

					$product_types = get_terms( 'product_type', array( 'hide_empty' => false ) );
					$temp_array    = array();
					foreach ( $product_types as $key => $value ) {
						if ( $value->name == 'simple' || $value->name == 'variable' ) {
							$temp_array_type[ $value->term_id ] = ucfirst( $value->name );
						}
					}
					$product_types      = $temp_array_type;
					$product_categories = get_terms( 'product_cat', array( 'hide_empty' => false ) );
					$temp_array         = array();
					foreach ( $product_categories as $key => $value ) {
						$temp_array[ $value->term_id ] = $value->name;
					}
					$product_categories = $temp_array;

					$previous_selected_status = isset( $_GET['status_sorting'] ) ? $_GET['status_sorting'] : '';
					$previous_selected_cat    = isset( $_GET['pro_cat_sorting'] ) ? $_GET['pro_cat_sorting'] : '';
					$previous_selected_type   = isset( $_GET['pro_type_sorting'] ) ? $_GET['pro_type_sorting'] : '';
					echo '<div class="ced_catch_wrap">';
					echo '<form method="post" action="">';
					echo '<div class="ced_catch_top_wrapper">';
					echo '<select name="status_sorting" class="select_boxes_product_page">';
					echo '<option value="">' . __( 'Product Status', 'woocommerce-catch-integration' ) . '</option>';
					foreach ( $status_actions as $name => $title ) {
						$selectedStatus = ( $previous_selected_status == $name ) ? 'selected="selected"' : '';
						$class          = 'edit' === $name ? ' class="hide-if-no-js"' : '';
						echo '<option ' . $selectedStatus . ' value="' . $name . '"' . $class . '>' . $title . '</option>';
					}
					echo '</select>';
					echo '<select name="pro_cat_sorting" class="select_boxes_product_page">';
					echo '<option value="">' . __( 'Product Category', 'woocommerce-catch-integration' ) . '</option>';
					foreach ( $product_categories as $name => $title ) {
						$selectedCat = ( $previous_selected_cat == $name ) ? 'selected="selected"' : '';
						$class       = 'edit' === $name ? ' class="hide-if-no-js"' : '';
						echo '<option ' . $selectedCat . ' value="' . $name . '"' . $class . '>' . $title . '</option>';
					}
					echo '</select>';
					echo '<select name="pro_type_sorting" class="select_boxes_product_page">';
					echo '<option value="">' . __( 'Product Type', 'woocommerce-catch-integration' ) . '</option>';
					foreach ( $product_types as $name => $title ) {
						$selectedType = ( $previous_selected_type == $name ) ? 'selected="selected"' : '';
						$class        = 'edit' === $name ? ' class="hide-if-no-js"' : '';
						echo '<option ' . $selectedType . ' value="' . $name . '"' . $class . '>' . $title . '</option>';
					}
					echo '</select>';
					$this->search_box( 'Search Products', 'search_id', 'search_product' );
					submit_button( __( 'Filter', 'ced-catch' ), 'action', 'filter_button', false, array() );
					echo '</div>';
					echo '</form>';
					echo '</div>';

					?>


					<form method="post">
						<?php
						$this->display();
						?>
					</form>

				</div>
			</div>
			<div class="clear"></div>
		</div>
		<div class="ced_catch_preview_product_popup_main_wrapper"></div>
		<?php
	}
}

$ced_catch_products_obj = new CatchListProducts();
$ced_catch_products_obj->prepare_items();
