<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       awshweta@gmail.com
 * @since      1.0.0
 *
 * @package    Product_Importer_By_Cedcommerce
 * @subpackage Product_Importer_By_Cedcommerce/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Product_Importer_By_Cedcommerce
 * @subpackage Product_Importer_By_Cedcommerce/admin
 * author     Shweta Awasthi <shwetaawasthi@cedcoss.com>
 */
class Product_Importer_By_Cedcommerce_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		global $hook_suffix;
		// ini_set('display_errors', 1);
		// ini_set('display_startup_errors', 1);
		// error_reporting(E_ALL);
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Product_Importer_By_Cedcommerce_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Product_Importer_By_Cedcommerce_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/product-importer-by-cedcommerce-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Product_Importer_By_Cedcommerce_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Product_Importer_By_Cedcommerce_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/product-importer-by-cedcommerce-admin.js', array( 'jquery' ), $this->version, false );
		wp_localize_script($this->plugin_name, 'ajax_object',
			array( 
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			)
		);
		wp_localize_script(
			$this->plugin_name, 'myAjaxObject',
			array( 
				'nonce_verifify' => wp_create_nonce('nonce_verifify')
			) 
		);
	}
	
	/**
	 * This function is used for upload only json file
	 * ced_import_product
	 *
	 * @return void
	 */
	public function ced_import_product() {
		$message = '';
		$nonce_verification_upload_file = isset( $_POST['nonce_verification'] ) ? sanitize_text_field($_POST['nonce_verification']) : '';
		if ( wp_verify_nonce( $nonce_verification_upload_file, 'nonce_verification' )) {
			if (isset($_POST['save_upload_file'])) {
				$filename   = isset($_FILES['file']['name']) ? basename(sanitize_text_field($_FILES['file']['name'])) : '';
				$filetype   = isset($_FILES['file']['type']) ? sanitize_text_field($_FILES['file']['type']) : '';
				$ext        = explode('.', $filename);
				$checkext   = $ext[1];
				$upload     = wp_upload_dir();
				$upload_dir = $upload['basedir'];
				$check_file = false;
				$upload_dir = $upload_dir . '/upload_jsonFile/' . $filename;
				if ('json' == $checkext) {
					$ufiles = get_option('uploaded_files');
					if ( empty( $ufiles ) ) {
						$ufiles  = array($filename);
						$message = 'file added successfully';
						update_option('uploaded_files', $ufiles);
					} else {
						if (is_array($ufiles)) {
							foreach ($ufiles as $key=>$value) {
								if ($value == $filename) {
									$message    = 'file already exist';
									$check_file = true;
									break;
								}
							}
						}
						if (false == $check_file) {
							$ufiles[] = $filename;
							$message  = 'file added successfully';
							update_option('uploaded_files', $ufiles);
						}
					}
					$temp_name = isset($_FILES['file']['tmp_name']) ? sanitize_text_field($_FILES['file']['tmp_name']) : '';
					move_uploaded_file($temp_name, $upload_dir);
				} else {
					$message = 'please select only json file';
				}
			}
		}

		if ( wp_verify_nonce( $nonce_verification_upload_file, 'nonce_verification' )) {
			if (isset($_POST['save_order_file'])) {
				$file_order_name   = isset($_FILES['file_order']['name']) ? basename(sanitize_text_field($_FILES['file_order']['name'])) : '';
				$file_order_type   = isset($_FILES['file_order']['type']) ? sanitize_text_field($_FILES['file_order']['type']) : '';
				$ext_order       = explode('.', $file_order_name);
				$check_order_ext   = $ext_order[1];
				$upload_order_file     = wp_upload_dir();
				$upload_order_dir = $upload_order_file['basedir'];
				$check_file = false;
				$upload_order_dir = $upload_order_dir . '/upload_order_jsonFile/' . $file_order_name;
				if ('json' == $check_order_ext) {
					$order_files = get_option('uploaded_order_files');
					if ( empty( $order_files ) ) {
						$order_files  = array($file_order_name);
						$message = 'file added successfully';
						update_option('uploaded_order_files', $order_files);
					} else {
						if (is_array($order_files)) {
							foreach ($order_files as $key=>$value) {
								if ($value == $file_order_name) {
									$message    = 'file already exist';
									$check_file = true;
									break;
								}
							}
						}
						if (false == $check_file) {
							$order_files[] = $file_order_name;
							$message  = 'file added successfully';
							update_option('uploaded_order_files', $order_files);
						}
					}
					$tempname = isset($_FILES['file_order']['tmp_name']) ? sanitize_text_field($_FILES['file_order']['tmp_name']) : '';
					move_uploaded_file($tempname, $upload_order_dir);
				} else {
					$message = 'please select only json file';
				}
			}
		}

		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<div id="admin_notice"></div>
			<div id="loader">
				<img src="https://upload.wikimedia.org/wikipedia/commons/b/b1/Loading_icon.gif" alt="image">
			</div>
			<form class="wc-progress-form-content woocommerce-importer" enctype="multipart/form-data" method="post">
				<?php if ('' != $message) { ?>
					<div class="notice notice-success is-dismissible"><p><?php esc_attr_e($message); ?></p></div>
				<?php } ?>
				<div>
					<input type="hidden" id="nonce_verification" name="nonce_verification" value="<?php echo esc_attr(wp_create_nonce('nonce_verification')); ?>"/>	
					<input type="file" id="upload" name="file"/>
					<button type="submit" class="button button-primary button-next" value="<?php esc_attr_e( 'Upload file' ); ?>" name="save_upload_file"><?php esc_html_e( 'Upload file'); ?></button>
				</div>
				<h1>Select File</h1>
				<select id="selected_file">
					<?php 
						$uploaded_files = get_option('uploaded_files');
					if (is_array($uploaded_files)) {
						echo '<option value="" selected>--select--</option>';
						foreach ($uploaded_files as $key=>$value) {
							echo '<option value=' . esc_attr($value) . '>' . esc_attr($value) . '</option>';
						}
					}
					?>
				</select>
				<h1>Import Order<h1>
				<div>
					<input type="file" id="upload_order" name="file_order"/>
					<button type="submit" class="button button-primary button-next" value="<?php esc_attr_e( 'Upload Order file' ); ?>" name="save_order_file"><?php esc_html_e( 'Upload Order file'); ?></button>
				</div>
				<h1>Select order File</h1>
				<select id="selected_order_file">
					<?php 
						$uploaded_order_files = get_option('uploaded_order_files');
					if (is_array($uploaded_order_files)) {
						echo '<option value="" selected>--select--</option>';
						foreach ($uploaded_order_files as $key=>$value) {
							echo '<option value=' . esc_attr($value) . '>' . esc_attr($value) . '</option>';
						}
					}
					?>
				</select>
			</form>
			<div id="display_json_file_content"></div>
		</div>
	<?php
	}
	
	/**
	 * This function is used to create admin menu page
	 * ced_import_product_menu_page
	 *
	 * @return void
	 */
	public function ced_import_product_menu_page() {
		add_menu_page(
			'Import Product', //menu title
			'Import Product', //menu name
			'manage_options', // capabality
			'import_product', //slug
			array( $this, 'ced_import_product' ), //function
			0, 
			5 //position
		);
	}
	
	/**
	 * This function is for display json file data using wp_list_table
	 * ced_display_json_file_data
	 *
	 * @return void
	 */
	public function ced_display_json_file_data() {
		$nonce_verify = isset( $_POST['verify_nonce_for_file'] ) ? sanitize_text_field($_POST['verify_nonce_for_file']) : '';
		if ( wp_verify_nonce( $nonce_verify, 'nonce_verifify' )) {
			$filename = isset($_POST['file']) ? sanitize_text_field($_POST['file']) : '';
			require 'partials/Display_file_data.php';
			$upload                   = wp_upload_dir();
			$upload_dir               = $upload['basedir'];
			$upload_dir               = $upload_dir . '/upload_jsonFile/' . $filename;
			$file_data                = file_get_contents($upload_dir); 
			$fileData                 = json_decode($file_data, true);
			$Display_File_Data        = new Display_File_Data();
			$Display_File_Data->items = $fileData;
			$Display_File_Data->prepare_items();
			print_r($Display_File_Data->display());
			wp_die();
		}
	}
	
	/**
	 * This function is used to import product
	 * ced_import_json_file_data
	 *
	 * @return void
	 */
	public function ced_import_json_file_data() {
		$verify_nonce_for_import_single_product = isset( $_POST['verify_nonce_for_import_single_product'] ) ? sanitize_text_field($_POST['verify_nonce_for_import_single_product']) : '';
		if ( wp_verify_nonce( $verify_nonce_for_import_single_product, 'nonce_verifify' )) {
			$id       = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : '';
			$filename = isset($_POST['file']) ? sanitize_text_field($_POST['file']) : '';
			require 'partials/Display_file_data.php';
			$upload                   = wp_upload_dir();
			$upload_dir               = $upload['basedir'];
			$upload_dir               = $upload_dir . '/upload_jsonFile/' . $filename;
			$file_data                = file_get_contents($upload_dir); 
			$fileData                 = json_decode($file_data, true);
			$Display_File_Data        = new Display_File_Data();
			$Display_File_Data->items = $fileData;
			$Display_File_Data->import_product($id , $fileData);
			wp_die();
		}
	}
	
	/**
	 * This function is used to import bulk product
	 * ced_bulk_import_product
	 *
	 * @return void
	 */
	public function ced_bulk_import_product() {
		
		$verify_nonce_for_import_bulk = isset( $_POST['verify_nonce_for_import_bulk'] ) ? sanitize_text_field($_POST['verify_nonce_for_import_bulk']) : '';
		if ( wp_verify_nonce( $verify_nonce_for_import_bulk, 'nonce_verifify' )) {
			if ( isset($_POST['selected_id']) ) {
				$ids = array_map( 'sanitize_text_field', $_POST['selected_id'] );
			}
			$filename = isset($_POST['file']) ? sanitize_text_field($_POST['file']) : '';
			require 'partials/Display_file_data.php';
			$upload                   = wp_upload_dir();
			$upload_dir               = $upload['basedir'];
			$upload_dir               = $upload_dir . '/upload_jsonFile/' . $filename;
			$file_data                = file_get_contents($upload_dir); 
			$fileData                 = json_decode($file_data, true);
			$Display_File_Data        = new Display_File_Data();
			$Display_File_Data->items = $fileData;
			//echo $ids;
			if ( is_array( $ids ) ) {
				foreach ($ids as $key=>$id) {
					$Display_File_Data->import_product(sanitize_text_field($id), $fileData);
				}
			}
			wp_die();
		}
	}

	public function ced_add_address($allData) {
		$address = array(
			'first_name' => $allData['ShippingAddress']['Name'],
			'last_name'  => '',
			'phone'      => $allData['ShippingAddress']['Phone'],
			'address_1'  => '',
			'address_2'  => '', 
			'city'       => $allData['ShippingAddress']['CityName'],
			'state'      => $allData['ShippingAddress']['StateOrProvince'],
			'postcode'   => $allData['ShippingAddress']['PostalCode'],
			'country'    => $allData['ShippingAddress']['Country']
		);
		return $address;
	}

	public function ced_add_shipping_cost($shipping_option, $shippingServiceDetail, $international_option) {
		foreach($shipping_option as $shipping_key=>$shipping_details) {
			if($shippingServiceDetail['ShippingService'] == $shipping_details['ShippingService']) {
				return $shippingServiceDetail['ShippingServiceCost']['value'];
			}
		}
		foreach($international_option as $international_key=>$international_option_details) {
			if($shippingServiceDetail['ShippingService'] == $international_option_details['ShippingService']) {
				return $shippingServiceDetail['ShippingServiceCost']['value'];
			}
		}
	}

	
	public function ced_import_order_json_file_data() {
		//$nonce_order_verify = isset( $_POST['verify_nonce_for_order_file'] ) ? sanitize_text_field($_POST['verify_nonce_for_order_file']) : '';
		//if ( wp_verify_nonce( $nonce_order_verify, 'nonce_verifify' )) {
			$file_name = isset($_POST['file']) ? sanitize_text_field($_POST['file']) : '';
			$upload_order                   = wp_upload_dir();
			$upload_order_dir               = $upload_order['basedir'];
			$upload_order_dir               = $upload_order_dir . '/upload_order_jsonFile/' . $file_name;
			$order_file_data                = file_get_contents($upload_order_dir); 
			$order_fileData                 = json_decode($order_file_data, true);

			$check = false;
			//echo '<pre>';
			global $woocommerce;

			$loop = new WP_Query(array('post_type' => array('product')));
			if (is_array($loop->posts)) {
				foreach ( $loop->posts as $post_key=>$post_value) {
					foreach($order_fileData as $key=>$order_data) {
						//print_r($order_data);
						foreach($order_data as $order_key=>$orderDetail) {
							foreach($orderDetail as $all_key=>$allData) {
								foreach($allData['TransactionArray']['Transaction'] as $transaction_key=>$transaction_value) {
									if($transaction_value['Item']['SKU'] == get_post_meta($post_value->ID, '_sku', 1)) {
										$get_order_id = get_post_meta($post_value->ID, 'order_id',1);
										
										if($get_order_id == "") {
											update_post_meta( $post_value->ID, 'order_id',$allData['OrderID']);
										}
										else {
											if($get_order_id == $allData['OrderID']) {
												$check = true;
												?>
													<div class="notice notice-success is-dismissible">
														<p><?php esc_attr_e( 'order already imported!' ); ?></p>
													</div>
												<?php
												break;
											}
										}
										if($check == false) {
											$args = array(
												'status'        => $allData['OrderStatus'],
												'customer_id'   => get_current_user_id(),
												'customer_note' => null,
												'parent'        => null
											);
							
											$order = wc_create_order($args);
											
											update_post_meta($post_value->ID, '_price', $allData['Subtotal']['value']);
											$order->add_product( get_product( $post_value->ID ), 1 );
											$address = $this->ced_add_address($allData);
											$order->set_address( $address, 'billing' );
											$order->set_address( $address, 'shipping' );
											$shipping_option = $allData['ShippingDetails']['ShippingServiceOptions'];
											$international_option = $allData['ShippingDetails']['InternationalShippingServiceOption'];
											$shippingServiceDetail = $allData['ShippingServiceSelected'];

											$ShippingCost = $this->ced_add_shipping_cost($shipping_option, $shippingServiceDetail , $international_option);
											/* ----- add shipping cost-------------*/
											$item_ship = new WC_Order_Item_Shipping();
											$item_ship->set_name($shippingServiceDetail['ShippingService']);
											$item_ship->set_total($ShippingCost);
											// Add Shipping item to the order
											$order->add_item( $item_ship );

											$tax = $transaction_value['Taxes']['TotalTaxAmount']['value'];

											/* ----- add tax--------------*/
											$item_fee = new WC_Order_Item_Fee();
											$item_fee->set_name( $transaction_value['Taxes']['TaxDetails']['TaxDescription'] ); // Generic fee name
											$item_fee->set_amount( $tax ); // Fee amount
											$item_fee->set_tax_class('' ); // default for ''
											$item_fee->set_tax_status( 'taxable' ); // or 'none'
											$item_fee->set_total($tax); // Fee amount
											$order->add_item( $item_fee );

											/* ------- add Total Amount ----------*/
											$total = $allData['Total']['value'];
											$order->set_total( $total );
											$order->set_created_via( sanitize_text_field( 'checkout' ) );
											$order->set_currency($transaction_value['TransactionPrice']['currencyID']);
											
											/*--------- add payment Method -----------*/
											foreach($allData['PaymentMethods'] as $pay=>$pay_method) {
												update_post_meta( $order->id, '_payment_method',$pay_method);
											}
											
											$order->set_customer_id(get_current_user_id());
											$order->calculate_totals();
											$order->save(); ?>
											<div class="notice notice-success is-dismissible">
												<p><?php esc_attr_e( 'order imported successfully!' ); ?></p>
											</div>
										<?php }	
									}
								}
							}
						}
					}
				}
			}
			wp_die();
		//}
	}

}
