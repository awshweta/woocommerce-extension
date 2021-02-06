<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://cedcommerce.com
 * @since      1.0.0
 *
 * @package    Woocommerce_Catch_Integration
 * @subpackage Woocommerce_Catch_Integration/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woocommerce_Catch_Integration
 * @subpackage Woocommerce_Catch_Integration/admin
 * @author     CedCommerce <plugins@cedcommerce.com>
 */
class Woocommerce_Catch_Integration_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		// error_reporting(~0);
		// ini_set('display_errors', 1);
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->loadDependency();
	}

	public function loadDependency() {
		require_once CED_CATCH_DIRPATH . 'admin/catch/class-catch.php';
		$this->ced_catch_manager = Class_Ced_Catch_Manager::get_instance();
		require_once CED_CATCH_DIRPATH . 'admin/catch/lib/catchSendHttpRequest.php';
		$this->sendRequestObj = new Class_Ced_Catch_Send_Http_Request();
		require_once CED_CATCH_DIRPATH . 'admin/catch/lib/catchOrders.php';
		$this->catchOrdersInstance = Class_Ced_Catch_Orders::get_instance();
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
		 * defined in Woocommerce_Catch_Integration_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woocommerce_Catch_Integration_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woocommerce-catch-integration-admin.css', array(), $this->version, 'all' );

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
		 * defined in Woocommerce_Catch_Integration_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woocommerce_Catch_Integration_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woocommerce-catch-integration-admin.js', array( 'jquery' ), $this->version, false );
		$ajax_nonce     = wp_create_nonce( 'ced-catch-ajax-seurity-string' );
		$localize_array = array(
			'ajax_url'   => admin_url( 'admin-ajax.php' ),
			'ajax_nonce' => $ajax_nonce,
			'shop_id'    => isset( $_GET['shop_id'] ) ? $_GET['shop_id'] : '',
		);
		wp_localize_script( $this->plugin_name, 'ced_catch_admin_obj', $localize_array );

	}

	public function ced_catch_add_menus() {
		global $submenu;
		if ( empty( $GLOBALS['admin_page_hooks']['cedcommerce-integrations'] ) ) {
			add_menu_page( __( 'CedCommerce', 'woocommerce-catch-integration' ), __( 'CedCommerce', 'woocommerce-catch-integration' ), 'manage_woocommerce', 'cedcommerce-integrations', array( $this, 'ced_marketplace_listing_page' ), plugins_url( 'woocommerce-catch-integration/admin/images/logo1.png' ), 12 );
			$menus = apply_filters( 'ced_add_marketplace_menus_array', array() );
			if ( is_array( $menus ) && ! empty( $menus ) ) {
				foreach ( $menus as $key => $value ) {
					add_submenu_page( 'cedcommerce-integrations', $value['name'], $value['name'], 'manage_woocommerce', $value['menu_link'], array( $value['instance'], $value['function'] ) );
				}
			}
			/*
			add_submenu_page( 'cedcommerce-integrations', "Additionals", "Additionals", 'manage_options', 'ced_additional', array( $this, 'ced_additional_page' ) );*/
		}
	}

	public function ced_catch_add_marketplace_menus_to_array( $menus = array() ) {
		$menus[] = array(
			'name'            => 'Catch',
			'slug'            => 'woocommerce-catch-integration',
			'menu_link'       => 'ced_catch',
			'instance'        => $this,
			'function'        => 'ced_catch_accounts_page',
			'card_image_link' => CED_CATCH_URL . 'admin/images/catch-card.png',
		);
		return $menus;
	}

	public function ced_catch_marketplace_to_be_logged( $marketplaces = array() ) {

		$marketplaces[] = array(
			'name'             => 'Catch',
			'marketplace_slug' => 'catch',
		);
		return $marketplaces;
	}

	public function ced_marketplace_listing_page() {
		$activeMarketplaces = apply_filters( 'ced_add_marketplace_menus_array', array() );
		if ( is_array( $activeMarketplaces ) && ! empty( $activeMarketplaces ) ) {
			require CED_CATCH_DIRPATH . 'admin/partials/marketplaces.php';
		}
	}

	public function my_catch_cron_schedules( $schedules ) {
		if ( ! isset( $schedules['ced_catch_2min'] ) ) {
			$schedules['ced_catch_2min'] = array(
				'interval' => 2 * 60,
				'display'  => __( 'Once every 2 minutes' ),
			);
		}

		if ( ! isset( $schedules['ced_catch_6min'] ) ) {
			$schedules['ced_catch_6min'] = array(
				'interval' => 6 * 60,
				'display'  => __( 'Once every 6 minutes' ),
			);
		}
		if ( ! isset( $schedules['ced_catch_2min'] ) ) {
			$schedules['ced_catch_2min'] = array(
				'interval' => 2 * 60,
				'display'  => __( 'Once every 2 minutes' ),
			);
		}
		if ( ! isset( $schedules['ced_catch_10min'] ) ) {
			$schedules['ced_catch_10min'] = array(
				'interval' => 10 * 60,
				'display'  => __( 'Once every 10 minutes' ),
			);
		}
		if ( ! isset( $schedules['ced_catch_15min'] ) ) {
			$schedules['ced_catch_15min'] = array(
				'interval' => 15 * 60,
				'display'  => __( 'Once every 15 minutes' ),
			);
		}
		if ( ! isset( $schedules['ced_catch_30min'] ) ) {
			$schedules['ced_catch_30min'] = array(
				'interval' => 30 * 60,
				'display'  => __( 'Once every 30 minutes' ),
			);
		}
		if ( ! isset( $schedules['ced_catch_60min'] ) ) {
			$schedules['ced_catch_60min'] = array(
				'interval' => 60 * 60,
				'display'  => __( 'Once every 60 minutes' ),
			);
		}
		return $schedules;
	}

	/*
	*
	*Function for displaying default page
	*
	*
	*/
	public function ced_catch_accounts_page() {

		$status = checkLicenseValidationForCatch();
		if ( $status ) {
			$fileAccounts = CED_CATCH_DIRPATH . 'admin/partials/ced-catch-accounts.php';
			if ( file_exists( $fileAccounts ) ) {
				require_once $fileAccounts;
			}
		} else {
			do_action( 'ced_catch_license_panel' );
		}
	}

	public function ced_catch_license_panel() {
		$fileLicense = CED_CATCH_DIRPATH . 'admin/partials/catchLicense.php';
		if ( file_exists( $fileLicense ) ) {
			include_once $fileLicense;
		}
	}

	public function ced_catch_validate_licensce_callback() {
		global $wp_version;

		$admin_name    = '';
		$admin_email   = get_option( 'admin_email', null );
		$admin_details = get_user_by( 'email', $admin_email );
	
		if ( isset( $admin_details->data ) ) {
			if ( isset( $admin_details->data->display_name ) ) {
				$admin_name = $admin_details->data->display_name;
			}
		}

		$return_response               = array();
		$license_arg                   = array();
		$license_arg['domain_name']    = $_SERVER['HTTP_HOST'];
		$license_arg['module_name']    = 'woocommerce-catch-integration';
		$license_arg['version']        = $wp_version;
		$license_arg['php_version']    = phpversion();
		$license_arg['framework']      = 'WordPress';
		$license_arg['admin_name']     = $admin_name;
		$license_arg['admin_email']    = $admin_email;
		$license_arg['module_license'] = $_POST['license_key'];
		$license_arg['edition']        = '';

			// $curl = curl_init();
			// curl_setopt_array($curl, array(
			// CURLOPT_RETURNTRANSFER => 1,
			// CURLOPT_URL => 'http://cedcommerce.com/licensing/validate',
			// CURLOPT_USERAGENT => 'Cedcommerce',
			// CURLOPT_POST => 1,
			// CURLOPT_POSTFIELDS => $license_arg
			// ));

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, 'https://cedcommerce.com/licensing/validate' );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_POST, 1 );
			// Edit: prior variable $postFields should be $postfields;
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $license_arg );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 ); // On dev server only!
			$res = curl_exec( $ch );

			curl_close( $ch );
			$response = json_decode( $res, true );

			// $res = curl_exec($curl);

			// $response = json_decode($res, true);
			$ced_hash = '';
			if ( isset( $response['hash'] ) && isset( $response['level'] ) ) {
				$ced_hash  = $response['hash'];
				$ced_level = $response['level'];
				{
					$i     = 1;
					for ( $i = 1;$i <= $ced_level;$i++ ) {
						$ced_hash = base64_decode( $ced_hash );
					}
				}
			}
			$ced_response = json_decode( $ced_hash, true );
			if ( $ced_response['domain'] == $_SERVER['HTTP_HOST'] && $ced_response['license'] == $license_arg['module_license'] && $ced_response['module_name'] == $license_arg['module_name'] ) {
				update_option( 'ced_catch_license', $res );
				update_option( 'ced_catch_license_key', $ced_response['license'] );
				update_option( 'ced_catch_license_module', $ced_response['module_name'] );
				$return_response['response'] = 'success';
			} else {
				$return_response['response'] = 'failure';
			}
			echo json_encode( $return_response );
			die;
		}

		public function ced_catch_license_check_function( $check ) {
			$catch_license        = get_option( 'ced_catch_license', false );
			$catch_license_key    = get_option( 'ced_catch_license_key', false );
			$catch_license_module = get_option( 'ced_catch_license_module', false );

			if ( ! empty( $catch_license ) ) {
				$response = json_decode( $catch_license, true );
				$ced_hash = '';

				if ( isset( $response['hash'] ) && isset( $response['level'] ) ) {
					$ced_hash  = $response['hash'];
					$ced_level = $response['level'];
					{
						$i     = 1;
						for ( $i = 1;$i <= $ced_level;$i++ ) {
							$ced_hash = base64_decode( $ced_hash );
						}
					}
				}

				$catch_license = json_decode( $ced_hash, true );

				if ( isset( $catch_license['license'] ) && isset( $catch_license['module_name'] ) ) {
					if ( $catch_license['license'] == $catch_license_key && $catch_license['module_name'] == $catch_license_module && $catch_license['domain'] == $_SERVER['HTTP_HOST'] ) {
						$check = true;
					}
				}
			}
			return $check;
		}

		public function ced_catch_save_operation_mode() {
			$operationMode = isset( $_POST['operationMode'] ) ? $_POST['operationMode'] : 'sandbox';
			update_option( 'ced_catch_operation_mode', $operationMode );
			die();
		}

		public function ced_catch_authorise_account() {
			$check_ajax = check_ajax_referer( 'ced-catch-ajax-seurity-string', 'ajax_nonce' );

			if ( $check_ajax ) {
				$apiKey = isset( $_POST['ApiKey'] ) ? $_POST['ApiKey'] : '';
				$apiKey = trim( $apiKey );

				$action      = 'account';
				$shopDetails = $this->sendRequestObj->sendHttpRequestGet( $action, '', '', $apiKey );

				$shopDetails = json_decode( $shopDetails, true );

				if ( isset( $shopDetails['status'] ) ) {
					echo json_encode(
						array(
							'status' => 400,
							'msg'    => $shopDetails['message'],
						)
					);
					wp_die();
				} elseif ( ! empty( $shopDetails ) ) {
					update_option( 'ced_catch_api_key_' . $shopDetails['shop_id'], $apiKey );
					global $wpdb;
					$tableName = $wpdb->prefix . 'ced_catch_accounts';
					$sql       = "SELECT * FROM `$tableName` WHERE `shop_id`=" . $shopDetails['shop_id'];
					$result    = $wpdb->get_results( $sql, 'ARRAY_A' );
					if ( empty( $result ) ) {
						$wpdb->insert(
							$tableName,
							array(
								'name'           => $shopDetails['shop_name'],
								'account_status' => 'active',
								'shop_id'        => $shopDetails['shop_id'],
								'location'       => $shopDetails['contact_informations']['city'] . ',' . $shopDetails['contact_informations']['country'],
								'shop_data'      => json_encode( $shopDetails ),
							)
						);
					}

					echo json_encode(
						array(
							'status' => 200,
							'msg'    => 'Authorized Successfully',
						)
					);
					wp_die();
				}
			}
		}

		public function ced_catch_get_import_status() {
			$check_ajax = check_ajax_referer( 'ced-catch-ajax-seurity-string', 'ajax_nonce' );
			if ( $check_ajax ) {
				$importId   = trim( isset( $_POST['ImportId'] ) ? $_POST['ImportId'] : '' );
				$shopId     = trim( isset( $_POST['ShopId'] ) ? $_POST['ShopId'] : '' );
				$uploadType = trim( isset( $_POST['uploadType'] ) ? $_POST['uploadType'] : '' );
				if ( $uploadType == 'Product' ) {
					$action = 'products/imports/' . $importId;
				} else {
					$action = 'offers/imports/' . $importId;
				}
				$response = $this->sendRequestObj->sendHttpRequestGet( $action, '', $shopId );
				$response = json_decode( $response, true );
				if ( isset( $response['has_error_report'] ) ) {
					update_option( 'ced_catch_import_status_' . $response['import_id'], $response );
					die;
				}
			}
		}
		public function ced_catch_get_import_report() {
			$check_ajax = check_ajax_referer( 'ced-catch-ajax-seurity-string', 'ajax_nonce' );
			if ( $check_ajax ) {
				$importId   = trim( isset( $_POST['ImportId'] ) ? $_POST['ImportId'] : '' );
				$shopId     = trim( isset( $_POST['ShopId'] ) ? $_POST['ShopId'] : '' );
				$uploadType = trim( isset( $_POST['uploadType'] ) ? $_POST['uploadType'] : '' );
				if ( $uploadType == 'Product' ) {
					$action = 'products/imports/' . $importId . '/error_report';
				} else {
					$action = 'offers/imports/' . $importId . '/error_report';
				}
				$response = $this->sendRequestObj->sendHttpRequestGet( $action, '', $shopId );
				if ( ( is_string( $response ) && ( is_object( json_decode( $response ) ) || is_array( json_decode( $response ) ) ) ) ) {
					$response = json_decode( $response, true );
				}
				if ( isset( $response['status'] ) ) {
					echo json_encode(
						array(
							'status'  => 201,
							'message' => $response['message'],
						)
					);
					die();
				} else {
					$wpuploadDir = wp_upload_dir();
					$baseDir     = $wpuploadDir['basedir'];
					$uploadDir   = $baseDir . '/cedcommerce_catchFeedReports';
					$nameTime    = time();
					if ( ! is_dir( $uploadDir ) ) {
						mkdir( $uploadDir, 0777, true );
					}
					$file = $uploadDir . '/Feed' . $importId . '.csv';
					file_put_contents( $file, $response );
					die;
				}
			}
		}
		public function ced_catch_get_integration_report() {
			$check_ajax = check_ajax_referer( 'ced-catch-ajax-seurity-string', 'ajax_nonce' );
			if ( $check_ajax ) {
				$importId   = trim( isset( $_POST['ImportId'] ) ? $_POST['ImportId'] : '' );
				$shopId     = trim( isset( $_POST['ShopId'] ) ? $_POST['ShopId'] : '' );
				$uploadType = trim( isset( $_POST['uploadType'] ) ? $_POST['uploadType'] : '' );
				if ( $uploadType == 'Product' ) {
					$action = 'products/imports/' . $importId . '/new_product_report';
				}
				$response = $this->sendRequestObj->sendHttpRequestGet( $action, '', $shopId );
				if ( ( is_string( $response ) && ( is_object( json_decode( $response ) ) || is_array( json_decode( $response ) ) ) ) ) {
					$response = json_decode( $response, true );
				}
				if ( isset( $response['status'] ) ) {
					echo json_encode(
						array(
							'status'  => 201,
							'message' => $response['message'],
						)
					);
					die();
				} else {
					$wpuploadDir = wp_upload_dir();
					$baseDir     = $wpuploadDir['basedir'];
					$uploadDir   = $baseDir . '/cedcommerce_catchFeedReports';
					$nameTime    = time();
					if ( ! is_dir( $uploadDir ) ) {
						mkdir( $uploadDir, 0777, true );
					}
					$file = $uploadDir . '/Integration_Feed' . $importId . '.csv';
					file_put_contents( $file, $response );
					echo json_encode(
						array(
							'status'  => 200,
							'message' => 'File Updated Successfully',
						)
					);
					die;
				}
			}
		}


		public function ced_catch_update_integration_report() {
			$current_action     = current_action();
			$shopId             = str_replace( 'ced_catch_integration_report_', '', $current_action );
			$ImportIds          = get_option( 'ced_catch_import_ids_' . $shopId, array() );
			$getProductIdsChunk = get_option( 'ced_catch_get_import_ids_chunk_' . $shopId, array() );
			if ( ! empty( $ImportIds ) ) {
				if ( empty( $getProductIdsChunk ) ) {
					$getProductIdsChunk = array_chunk( $ImportIds, 1 );
				}
				foreach ( $getProductIdsChunk[0] as $key => $value ) {
					$uploadType = get_option( 'ced_catch_import_type_' . $value, '' );
					if ( $uploadType == 'Product' ) {
						$action = 'products/imports/' . $value . '/new_product_report';
					}
					$response = $this->sendRequestObj->sendHttpRequestGet( $action, '', $shopId );
					if ( ( is_string( $response ) && ( is_object( json_decode( $response ) ) || is_array( json_decode( $response ) ) ) ) ) {
						$response = json_decode( $response, true );
					}
					if ( isset( $response['status'] ) ) {
						continue;
					} else {
						$wpuploadDir = wp_upload_dir();
						$baseDir     = $wpuploadDir['basedir'];
						$uploadDir   = $baseDir . '/cedcommerce_catchFeedReports';
						$nameTime    = time();
						if ( ! is_dir( $uploadDir ) ) {
							mkdir( $uploadDir, 0777, true );
						}
						$file = $uploadDir . '/Integration_Feed' . $value . '.csv';
						file_put_contents( $file, $response );
						die;
					}
				}
				unset( $getProductIdsChunk[0] );
				$getProductIdsChunk = array_values( $getProductIdsChunk );
				update_option( 'ced_catch_get_import_ids_chunk_' . $shopId, $getProductIdsChunk );
			}
		}

		public function ced_catch_category_refresh_button() {
			$check_ajax = check_ajax_referer( 'ced-catch-ajax-seurity-string', 'ajax_nonce' );
			if ( $check_ajax ) {
				$shopid         = isset( $_POST['store_id'] ) ? $_POST['store_id'] : '';
				$isShopInActive = ced_catch_inactive_shops( $shopid );
				if ( $isShopInActive ) {
					echo json_encode(
						array(
							'status'  => 400,
							'message' => __(
								'Shop is Not Active',
								'woocommerce-catch-integration'
							),
						)
					);
					die;
				}

				$fileCategory = CED_CATCH_DIRPATH . 'admin/catch/lib/catchCategory.php';
				if ( file_exists( $fileCategory ) ) {
					require_once $fileCategory;
				}

				$catchCategory = Class_Ced_Catch_Category::get_instance();
				$catchCategory = $catchCategory->getRefreshedCatchCategory( $shopid );
				$catchCategory = json_decode( $catchCategory, true );
				if ( isset( $catchCategory['hierarchies'] ) && ! empty( $catchCategory['hierarchies'] ) ) {
					$folderName          = CED_CATCH_DIRPATH . 'admin/catch/lib/json/';
					$completeCatListFile = $folderName . 'categoryList.json';
					file_put_contents( $completeCatListFile, json_encode( $catchCategory['hierarchies'] ) );
					echo json_encode(
						array(
							'status'  => 200,
							'message' => 'Categories Refreshed successfully.',
						)
					);
					die;
				} else {
					echo json_encode(
						array(
							'status'  => 400,
							'message' => 'Categories Not Refreshed.',
						)
					);
					die;
				}
			}

		}

		public function ced_catch_fetch_next_level_category() {
			$check_ajax = check_ajax_referer( 'ced-catch-ajax-seurity-string', 'ajax_nonce' );
			if ( $check_ajax ) {
				$store_category_id      = isset( $_POST['store_id'] ) ? $_POST['store_id'] : '';
				$catch_store_id         = isset( $_POST['catch_store_id'] ) ? $_POST['catch_store_id'] : '';
				$catch_category_name    = isset( $_POST['name'] ) ? $_POST['name'] : '';
				$catch_category_id      = isset( $_POST['id'] ) ? $_POST['id'] : '';
				$level                  = isset( $_POST['level'] ) ? $_POST['level'] : '';
				$next_level             = intval( $level ) + 1;
				$folderName             = CED_CATCH_DIRPATH . 'admin/catch/lib/json/';
				$categoryFirstLevelFile = $folderName . 'categoryList.json';
				$catchCategoryList      = file_get_contents( $categoryFirstLevelFile );
				$catchCategoryList      = json_decode( $catchCategoryList, true );
				$select_html            = '';
				$nextLevelCategoryArray = array();
			// print_r($catch_category_id);
				if ( ! empty( $catchCategoryList ) ) {
					foreach ( $catchCategoryList as $key => $value ) {
						if ( isset( $value['parent_code'] ) && str_replace( "'", '~', $value['parent_code'] ) == str_replace( "'", '~', $catch_category_id ) ) {
							$nextLevelCategoryArray[] = $value;
						}
					}
				}
				if ( is_array( $nextLevelCategoryArray ) && ! empty( $nextLevelCategoryArray ) ) {

					$select_html .= '<td data-catlevel="' . $next_level . '"><select class="ced_catch_level' . $next_level . '_category ced_catch_select_category  select_boxes_cat_map" name="ced_catch_level' . $next_level . '_category[]" data-level=' . $next_level . ' data-storeCategoryID="' . $store_category_id . '" data-catchStoreId="' . $catch_store_id . '">';
					$select_html .= '<option value=""> --' . __( 'Select', 'woocommerce-catch-integration' ) . '-- </option>';
					foreach ( $nextLevelCategoryArray as $key => $value ) {
						if ( $value['label'] != '' ) {
							$select_html .= '<option value="' . str_replace( "'", '~', $value['code'] ) . '">' . $value['label'] . '</option>';
						}
					}
					$select_html .= '</select></td>';
					echo $select_html;
					die;
				}
			}
		}

		public function ced_catch_fetch_next_level_category_add_profile() {
			$check_ajax = check_ajax_referer( 'ced-catch-ajax-seurity-string', 'ajax_nonce' );
			if ( $check_ajax ) {
				$store_category_id      = isset( $_POST['store_id'] ) ? $_POST['store_id'] : '';
				$catch_store_id         = isset( $_POST['catch_store_id'] ) ? $_POST['catch_store_id'] : '';
				$catch_category_name    = isset( $_POST['name'] ) ? $_POST['name'] : '';
				$catch_category_id      = isset( $_POST['id'] ) ? $_POST['id'] : '';
				$level                  = isset( $_POST['level'] ) ? $_POST['level'] : '';
				$next_level             = intval( $level ) + 1;
				$folderName             = CED_CATCH_DIRPATH . 'admin/catch/lib/json/';
				$categoryFirstLevelFile = $folderName . 'categoryList.json';
				$catchCategoryList      = file_get_contents( $categoryFirstLevelFile );
				$catchCategoryList      = json_decode( $catchCategoryList, true );
				$select_html            = '';
				$nextLevelCategoryArray = array();
				if ( ! empty( $catchCategoryList ) ) {
					foreach ( $catchCategoryList as $key => $value ) {
						if ( isset( $value['parent_code'] ) && str_replace( "'", '~', $value['parent_code'] ) == str_replace( "'", '~', $catch_category_id ) ) {
							$nextLevelCategoryArray[] = $value;
						}
					}
				}
				if ( is_array( $nextLevelCategoryArray ) && ! empty( $nextLevelCategoryArray ) ) {

					$select_html .= '<td data-catlevel="' . $next_level . '"><select class="ced_catch_level' . $next_level . '_category ced_catch_select_category_on_add_profile  select_boxes_cat_map" name="ced_catch_level' . $next_level . '_category[]" data-level=' . $next_level . ' data-storeCategoryID="' . $store_category_id . '" data-catchStoreId="' . $catch_store_id . '">';
					$select_html .= '<option value=""> --' . __( 'Select', 'woocommerce-catch-integration' ) . '-- </option>';
					foreach ( $nextLevelCategoryArray as $key => $value ) {
						if ( $value['label'] != '' ) {
							$select_html .= '<option value="' . str_replace( "'", '~', $value['code'] ) . '">' . $value['label'] . '</option>';
						}
					}
					$select_html .= '</select></td>';
					echo $select_html;
					die;
				}
			}
		}

	/*
	*
	*Function for listing all the available profiles in products section
	*
	*
	*/

	public function ced_catch_profiles_on_pop_up() {
		$check_ajax = check_ajax_referer( 'ced-catch-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$store_id = isset( $_POST['shopid'] ) ? $_POST['shopid'] : '';
			$prodId   = isset( $_POST['prodId'] ) ? $_POST['prodId'] : '';
			global $wpdb;
			$tableName = $wpdb->prefix . 'ced_catch_profiles';
			$sql       = "SELECT * FROM `$tableName` WHERE `shop_id` = '$store_id' ";
			$profiles  = $wpdb->get_results( $sql, 'ARRAY_A' );
			?><div class="ced_catch_profile_popup_content">
				<div id="profile_pop_up_head_main">
					<h2><?php _e( 'CHOOSE PROFILE FOR THIS PRODUCT', 'woocommerce-catch-integration' ); ?></h2>
					<div class="ced_catch_profile_popup_close">X</div>
				</div>
				<div id="profile_pop_up_head"><h3><?php _e( 'Available Profiles', 'woocommerce-catch-integration' ); ?></h3></div>
				<div class="ced_catch_profile_dropdown">
					<select name="ced_catch_profile_selected_on_popup" class="ced_catch_profile_selected_on_popup">
						<option class="profile_options" value=""><?php _e( '---Select Profile---', 'woocommerce-catch-integration' ); ?></option>
						<?php
						foreach ( $profiles as $key => $value ) {
							echo '<option  class="profile_options" value="' . $value['id'] . '">' . $value['profile_name'] . '</option>';
						}
						?>
					</select>
				</div>	
				<div id="ced_catch_save_profile_through_popup_container">
					<button data-prodId="<?php echo $prodId; ?>" class="ced_catch_custom_button" id="ced_catch_save_profile_through_popup"  data-shopid="<?php echo $store_id; ?>"><?php _e( 'Assign Profile', 'woocommerce-catch-integration' ); ?></button>
				</div>
			</div>


			<?php
			wp_die();
		}

	}

	/*
	*
	*Function for saving profile data assigned on product level
	*
	*
	*/

	public function ced_catch_save_profile_through_popup() {
		$check_ajax = check_ajax_referer( 'ced-catch-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$shopid     = isset( $_POST['shopid'] ) ? $_POST['shopid'] : '';
			$prodId     = isset( $_POST['prodId'] ) ? $_POST['prodId'] : '';
			$profile_id = isset( $_POST['profile_id'] ) ? $_POST['profile_id'] : '';
			if ( $profile_id == '' ) {
				echo 'null';
				wp_die();
			}

			update_post_meta( $prodId, 'ced_catch_profile_assigned' . $shopid, $profile_id );
		}
	}

	/*
	*
	*Function for Storing mapped categories
	*
	*
	*/

	public function ced_catch_map_categories_to_store() {
		$check_ajax = check_ajax_referer( 'ced-catch-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$catch_category_array = isset( $_POST['catch_category_array'] ) ? $_POST['catch_category_array'] : '';
			$store_category_array = isset( $_POST['store_category_array'] ) ? $_POST['store_category_array'] : '';
			$catch_category_name  = isset( $_POST['catch_category_name'] ) ? $_POST['catch_category_name'] : '';
			$catch_store_id       = isset( $_POST['catch_store_id'] ) ? $_POST['catch_store_id'] : '';

			$catch_saved_category        = get_option( 'ced_catch_saved_category', array() );
			$alreadyMappedCategories     = array();
			$alreadyMappedCategoriesName = array();
			$catchMappedCategories       = array_combine( $store_category_array, $catch_category_array );
			$catchMappedCategories       = array_filter( $catchMappedCategories );
			$alreadyMappedCategories     = get_option( 'ced_woo_catch_mapped_categories', array() );
			if ( is_array( $catchMappedCategories ) && ! empty( $catchMappedCategories ) ) {
				foreach ( $catchMappedCategories as $key => $value ) {
					$alreadyMappedCategories[ $catch_store_id ][ $key ] = $value;
				}
			}
			update_option( 'ced_woo_catch_mapped_categories', $alreadyMappedCategories );
			$catchMappedCategoriesName   = array_combine( $catch_category_array, $catch_category_name );
			$catchMappedCategoriesName   = array_filter( $catchMappedCategoriesName );
			$alreadyMappedCategoriesName = get_option( 'ced_woo_catch_mapped_categories_name', array() );
			if ( is_array( $catchMappedCategoriesName ) && ! empty( $catchMappedCategoriesName ) ) {
				foreach ( $catchMappedCategoriesName as $key => $value ) {
					$alreadyMappedCategoriesName[ $catch_store_id ][ $key ] = $value;
				}
			}
			update_option( 'ced_woo_catch_mapped_categories_name', $alreadyMappedCategoriesName );
			$this->ced_catch_manager->ced_catch_createAutoProfiles( $catchMappedCategories, $catchMappedCategoriesName, $catch_store_id );
			wp_die();
		}
	}


	/*
	*
	*Function for changing account status
	*
	*
	*/
	public function ced_catch_change_account_status() {
		$check_ajax = check_ajax_referer( 'ced-catch-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			global $wpdb;
			$tableName = $wpdb->prefix . 'ced_catch_accounts';
			$id        = isset( $_POST['id'] ) ? $_POST['id'] : '';
			$status    = isset( $_POST['status'] ) ? $_POST['status'] : 'active';
			$wpdb->update( $tableName, array( 'account_status' => $status ), array( 'id' => $id ) );
			echo json_encode( array( 'status' => '200' ) );
			die;
		}
	}

	public function ced_catch_Fetch_orders() {

		$current_action     = current_action();
		$shop_id            = str_replace( 'ced_catch_Fetch_orders_', '', $current_action );
		$shop_id            = trim( $shop_id );
		$last_created_order = get_option( 'ced_catch_last_order_created_time', '' );
		$action             = 'orders';
		$status             = 'WAITING_ACCEPTANCE';
		if ( $last_created_order != '' ) {
			$action = $action . '?order_state_codes=' . $status . '&start_date=' . $last_created_order;
		} else {
			$action = $action . '?order_state_codes=' . $status;
		}
		$orders = $this->sendRequestObj->sendHttpRequestGet( $action, '', $shop_id );
		$orders = json_decode( $orders, true );
		if ( is_array( $orders['orders'] ) && count( $orders['orders'] ) > 0 ) {
			$this->catchOrdersInstance->create_local_order( $orders['orders'], $shop_id );
		}
	}

	public function ced_catch_manual_fetch_orders() {

		$shop_id            = isset( $_POST['shop_id'] ) ? $_POST['shop_id'] : '';
		$shop_id            = trim( $shop_id );
		$last_created_order = get_option( 'ced_catch_last_order_created_time', '' );
		$action             = 'orders';
		$status             = 'WAITING_ACCEPTANCE';
		if ( $last_created_order != '' ) {
			$action = $action . '?order_state_codes=' . $status . '&start_date=' . $last_created_order;
		} else {
			$action = $action . '?order_state_codes=' . $status;
		}
		$orders = $this->sendRequestObj->sendHttpRequestGet( $action, '', $shop_id );
		$orders = json_decode( $orders, true );
		if ( is_array( $orders['orders'] ) && count( $orders['orders'] ) > 0 ) {
			$this->catchOrdersInstance->create_local_order( $orders['orders'], $shop_id );
		}
	}

	public function ced_catch_process_catch_orders() {
		$shop_id        = isset( $_POST['shop_id'] ) ? $_POST['shop_id'] : '';
		$shop_id        = trim( $shop_id );
		$order_id       = isset( $_POST['order_id'] ) ? trim( $_POST['order_id'] ) : '';
		$operation      = isset( $_POST['operation'] ) ? trim( $_POST['operation'] ) : '';
		$catch_order_id = get_post_meta( $order_id, '_ced_catch_order_id', true );
		$order          = wc_get_order( $order_id );
		$order_items    = get_post_meta( $order_id, 'order_items', true );
		if ( ! empty( $catch_order_id ) ) {
			$action = 'orders/' . $catch_order_id . '/accept';
			if ( $operation == 'Accept' ) {
				$accept = true;
			} elseif ( $operation == 'Reject' ) {
				$accept = false;
			}
			$order_lines = array();
			foreach ( $order_items as $index => $details ) {
				$orderAccept['accepted']      = $accept;
				$orderAccept['id']            = $details['order_line_id'];
				$order_lines['order_lines'][] = $orderAccept;
			}
			$parameters = $order_lines;
			$response   = $this->sendRequestObj->sendHttpRequestPut( $action, $parameters, $shop_id );
			if ( $operation == 'Accept' ) {
				update_post_meta( $order_id, '_catch_umb_order_status', 'Accepted' );
				$order->update_status( 'processing' );
			} elseif ( $operation == 'Reject' ) {
				update_post_meta( $order_id, '_catch_umb_order_status', 'Rejected' );
				$order->update_status( 'cancelled' );
			}
		}

	}

	public function ced_catch_Fetch_orders_by_id() {
		require_once CED_CATCH_DIRPATH . 'admin/catch/lib/catchOrders.php';
		$catchOrdersInstance             = Class_Ced_Catch_Orders::get_instance();
		$current_action                  = current_action();
		$shop_id                         = str_replace( 'ced_catch_Fetch_orders_by_id_', '', $current_action );
		$shop_id                         = trim( $shop_id );
		$action                          = 'orders';
		$order_ids_to_get_updated_status = get_option( 'ced_catch_get_order_ids_to_be_updated', array() );

		if ( empty( $order_ids_to_get_updated_status ) ) {
			$order_ids                       = $this->catchOrdersInstance->get_catch_order_ids( $shop_id );
			if(!empty($order_ids)) {
				$order_ids_to_get_updated_status = array_chunk( $order_ids, 10 );
			}
		}

		if ( isset( $order_ids_to_get_updated_status[0] ) && ! empty( $order_ids_to_get_updated_status[0] ) ) {
			$order_ids = implode( ',', $order_ids_to_get_updated_status[0] );
		}
		$action = $action . '?order_ids=' . $order_ids;
		$orders = $this->sendRequestObj->sendHttpRequestGet( $action, '', $shop_id );
		$orders = json_decode( $orders, true );
		if ( is_array( $orders['orders'] ) && count( $orders['orders'] ) > 0 ) {
			$orders_count = count( $orders['orders'] );
			update_option( 'ced_catch_orders_offset_' . $shop_id, $orders_count );
			$catchOrdersInstance->create_local_order( $orders['orders'], $shop_id, true );
			unset( $order_ids_to_get_updated_status[0] );
			$order_ids_to_get_updated_status = array_values( $order_ids_to_get_updated_status );
			update_option( 'ced_catch_get_order_ids_to_be_updated', $order_ids_to_get_updated_status );
		}
	}

	public function ced_catch_inventory_schedule_manager() {
		$current_action = current_action();
		$shop_id        = str_replace( 'ced_catch_inventory_scheduler_job_', '', $current_action );
		$shop_id        = trim( $shop_id );
		$offset         = get_option( 'ced_catch_offers_offset_' . $shop_id, false );
		if ( ! empty( $offset ) ) {
			$offset = $offset;
		} else {
			$offset = 0;
		}
		$max    = 100;
		$action = 'offers?max=' . $max . '&offset=' . $offset;

		$getOffers = $this->sendRequestObj->sendHttpRequestGet( $action, '', $shop_id );
		$getOffers = json_decode( $getOffers, true );

		if ( isset( $getOffers['offers'] ) && ! empty( $getOffers['offers'] ) ) {
			$totalOffersOnShop    = isset( $getOffers['total_count'] ) ? $getOffers['total_count'] : '';
			$totalOffersRetrieved = get_option( 'ced_catch_offers_retrieved_' . $shop_id, false );
			$totalOffersRetrieved = (int) $totalOffersRetrieved;
			$offersRetrieved      = count( $getOffers['offers'] ) + $totalOffersRetrieved;
			update_option( 'ced_catch_offers_retrieved_' . $shop_id, $offersRetrieved );
			$offset = $offset + count( $getOffers['offers'] );
			if ( $offset < $totalOffersOnShop ) {
				update_option( 'ced_catch_offers_offset_' . $shop_id, $offset );
			} else {
				update_option( 'ced_catch_offers_offset_' . $shop_id, '' );
			}
			foreach ( $getOffers['offers'] as $key => $value ) {
				$productSku = isset( $value['shop_sku'] ) ? $value['shop_sku'] : '';
				$catch_sku  = isset( $value['product_sku'] ) ? $value['product_sku'] : '';
				$offerIDs   = $this->ced_catch_if_product_exists_in_store( $productSku );
				if ( $offerIDs ) {
					update_post_meta( $offerIDs, 'ced_catch_product_sku', $catch_sku );
					update_post_meta( $offerIDs, 'ced_catch_product_on_catch_' . $shop_id, true );
					$offersToBeUpdated[] = $offerIDs;
				}
			}
			if ( ! empty( $offersToBeUpdated ) ) {
				$this->ced_catch_manager->prepareProductHtmlForOffer( $offersToBeUpdated, $shop_id, 'UPDATE', true );
			}
		}
	}


	public function ced_catch_sync_products() {

		$current_action = current_action();
		$shop_id        = str_replace( 'ced_catch_sync_products_', '', $current_action );
		$shop_id        = trim( $shop_id );
		$offset         = get_option( 'ced_catch_sync_products_offset_' . $shop_id, false );
		if ( ! empty( $offset ) ) {
			$offset = $offset;
		} else {
			$offset = 0;
		}
		$max    = 100;
		$action = 'offers?max=' . $max . '&offset=' . $offset;

		$getOffers = $this->sendRequestObj->sendHttpRequestGet( $action, '', $shop_id );

		$getOffers = json_decode( $getOffers, true );

		if ( isset( $getOffers['offers'] ) && ! empty( $getOffers['offers'] ) ) {
			$totalOffersOnShop    = isset( $getOffers['total_count'] ) ? $getOffers['total_count'] : '';
			$totalOffersRetrieved = get_option( 'ced_catch_synced_products_' . $shop_id, true );
			$totalOffersRetrieved = (int) $totalOffersRetrieved;
			$offersRetrieved      = count( $getOffers['offers'] ) + $totalOffersRetrieved;
			update_option( 'ced_catch_synced_products_' . $shop_id, $offersRetrieved );
			$offset = $offset + count( $getOffers['offers'] );
			if ( $offset < $totalOffersOnShop ) {
				update_option( 'ced_catch_sync_products_offset_' . $shop_id, $offset );
			} else {
				update_option( 'ced_catch_sync_products_offset_' . $shop_id, '' );
			}
			foreach ( $getOffers['offers'] as $key => $value ) {
				$productSku = isset( $value['shop_sku'] ) ? $value['shop_sku'] : '';
				$offerID    = $this->ced_catch_if_product_exists_in_store( $productSku );
				if ( $offerID ) {
					$catch_sku = $value['product_sku'];
					update_post_meta( $offerID, 'ced_catch_product_sku', $catch_sku );
					update_post_meta( $offerID, 'ced_catch_product_on_catch_' . $shop_id, true );
					$product = wc_get_product( $offerID );
					$type    = $product->get_type();
					if ( $type == 'variation' ) {
						$parent_id = $product->get_parent_id();
						update_post_meta( $parent_id, 'ced_catch_product_on_catch_' . $shop_id, true );
					}
				}
			}
		}
	}


	public function ced_catch_if_product_exists_in_store( $Sku = '' ) {

		$Id = get_posts(
			array(
				'numberposts' => -1,
				'post_type'   => array( 'product', 'product_variation' ),
				'meta_key'    => '_sku',
				'meta_value'  => $Sku,
				'compare'     => '=',
				'fields'      => 'ids',
			)
		);

		if ( $Id ) {
			return $Id[0];
		} else {
			return false;
		}
	}

	public function ced_catch_add_order_metabox() {
		global $post;
		$product = wc_get_product( $post->ID );
		if ( get_post_meta( $post->ID, '_is_ced_catch_order', true ) ) {
			add_meta_box(
				'ced_catch_manage_orders_metabox',
				__( 'Manage Catch Orders', 'woocommerce-catch-integration' ) . wc_help_tip( __( 'Please save tracking information of order.', 'woocommerce-catch-integration' ) ),
				array( $this, 'catch_render_orders_metabox' ),
				'shop_order',
				'advanced',
				'high'
			);
		}

		add_meta_box(
			'ced_catch_description_metabox',
			__( 'Catch Custom Description', 'catch-woocommerce-integration' ),
			array( $this, 'ced_catch_render_metabox' ),
			'product',
			'advanced',
			'high'
		);
	}

	public function ced_catch_render_metabox() {
		global $post;
		$product_id       = $post->ID;
		$long_description = get_post_meta( $product_id, '_ced_catch_custom_description', true );
		?>
		<table>
			<tbody>
				<tr>
					<td>
						<?php
						$content   = $long_description;
						$editor_id = '_ced_catch_custom_description';
						$settings  = array( 'textarea_rows' => 10 );
						wp_editor( $content, $editor_id, $settings );
						?>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
	}


	public function catch_render_orders_metabox() {
		global $post;
		$order_id = isset( $post->ID ) ? intval( $post->ID ) : '';

		$carrier_code = get_post_meta( $order_id, 'ced_catch_carrier_code', true );
		$carrier_name = get_post_meta( $order_id, 'ced_catch_carrier_name', true );
		$carrier_url  = get_post_meta( $order_id, 'ced_catch_carrier_url', true );
		$tracking_no  = get_post_meta( $order_id, 'ced_catch_tracking_number', true );

		$carrier_code = ! empty( $carrier_code ) ? $carrier_code : '';
		$carrier_name = ! empty( $carrier_name ) ? $carrier_name : '';
		$carrier_url  = ! empty( $carrier_url ) ? $carrier_url : '';
		$tracking_no  = ! empty( $tracking_no ) ? $tracking_no : '';

		$carrier_codes = get_option( 'ced_catch_carrier_codes_list', array() );
		if ( empty( $carrier_codes ) ) {
			$action   = 'shipping/carriers';
			$shopId   = get_option( 'ced_catch_shop_id', '' );
			$response = $this->sendRequestObj->sendHttpRequestGet( $action, '', $shopId );
			$response = json_decode( $response, true );
			if ( isset( $response['carriers'] ) ) {
				$carrier_codes = $response['carriers'];
				update_option( 'ced_catch_carrier_codes_list', $response['carriers'] );
			}
		}

		?>
		<table>
			<tr>
				<td>Shipping Carrier Code</td>
				<td><select name="ced_catch_carrier_code">
					<?php
					foreach ( $carrier_codes as $key => $value ) {
						$selected = '';
						if ( $carrier_code == $value['code'] ) {
							$selected = 'selected';
						}
						?>
						<option value="<?php echo $value['code']; ?>" <?php echo $selected; ?>><?php echo $value['label']; ?></option>
						<?php
					}
					?>
				</select></td>
			</tr>
			<tr>
				<td>Shipping Carrier Name</td>
				<td><input type='text' name='ced_catch_carrier_name' value='<?php echo $carrier_name; ?>'></td>
			</tr>
			<tr>
				<td>Shipping Carrier URL</td>
				<td><input type='text' name='ced_catch_carrier_url' value='<?php echo $carrier_url; ?>'></td>
			</tr>
			<tr>
				<td>Tracking Number</td>
				<td><input type='text' name='ced_catch_tracking_number' value='<?php echo $tracking_no; ?>'></td>
			</tr>
		</table>
		<?php
	}

	public function ced_catch_custom_product_tabs( $tab ) {
		$tab['custom_inventory'] = array(
			'label'  => __( 'Catch Data', 'woocommerce' ),
			'target' => 'inventory_options',
			'class'  => array( 'show_if_simple' ),
		);
		return $tab;
	}


	public function inventory_options_product_tab_content() {
		global $post;

		// Note the 'id' attribute needs to match the 'target' parameter set above
		?>
		<div id='inventory_options' class='panel woocommerce_options_panel'>
			<div class='options_group'>
				<?php
				$this->render_fields($post->ID);
				?>
			</div>		
		</div>
		<?php
	}

	public function render_fields( $post_id ) {

		$ced_catch_custom_identifier_type          = get_post_meta( $post_id, 'ced_catch_custom_identifier_type', true );
		$ced_catch_custom_identifier_value          = get_post_meta( $post_id, 'ced_catch_custom_identifier_value', true );
		$ced_catch_custom_size_chart          = get_post_meta( $post_id, 'ced_catch_custom_size_chart', true );
		$catch_custom_price          = get_post_meta( $post_id, 'ced_catch_custom_price', true );
		$catch_custom_stock          = get_post_meta( $post_id, 'ced_catch_custom_stock', true );
		$catch_custom_logistic_class = get_post_meta( $post_id, 'ced_catch_custom_logistic_class', true );
		$ced_catch_product_keywords  = get_post_meta( $post_id, 'ced_catch_product_keywords', true );
		woocommerce_wp_text_input(
			array(
				'id'                => 'ced_catch_data['.$post_id.'][ced_catch_custom_price]',
				'label'             => __( 'Price', 'woocommerce' ),
				'desc_tip'          => 'true',
				'description'       => __( 'Enter the price to be uploaded on Catch.', 'woocommerce' ),
				'type'              => 'text',
				'value'             => $catch_custom_price,
				'custom_attributes' => array(
					'min'  => '1',
					'step' => '1',
				),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'          => 'ced_catch_data['.$post_id.'][ced_catch_product_keywords]',
				'label'       => __( 'Keywords', 'woocommerce' ),
				'desc_tip'    => 'true',
				'description' => __( 'Enter the keywords for the product.Should be comma separated', 'woocommerce' ),
				'type'        => 'text',
				'value'       => $ced_catch_product_keywords,
			)
		);

		$offer_attribute_data = CED_CATCH_DIRPATH . 'admin/catch/lib/json/offer-attributes.json';
		$offer_attribute_data = file_get_contents( $offer_attribute_data );
		$offer_attribute_data = json_decode( $offer_attribute_data, true );

		foreach ( $offer_attribute_data as $key => $value ) {
			if ( $value['code'] == 'logistic-class' ) {
				$options[] = '--select--';
				foreach ( $value['values_list'] as $label => $label_data ) {
					$option_value = $label_data['code'];
					$option_label = $label_data['label'];
					$options[$option_value] = $option_label;
				}
				break;
			}
		}

		woocommerce_wp_select(
			array(
				'id'      => 'ced_catch_data['.$post_id.'][ced_catch_custom_logistic_class]',
				'label'   => 'Logistics Class',
				'options' => $options,
				'value'   => isset( $catch_custom_logistic_class ) ? $catch_custom_logistic_class : '',
				'desc_tip'    => 'true',
				'description' => __( 'Select the logistic-class', 'woocommerce' ),
			)
		);

		woocommerce_wp_select(
			array(
				'id'      => 'ced_catch_data['.$post_id.'][ced_catch_custom_identifier_type]',
				'label'   => 'Identifier type',
				'options' => array(
					'' => __( '--select--' ),
					'ean' => __( 'EAN' ),
					'upc' => __( 'UPC' ),
					'mpn' => __( 'MPN' ),
				),
				'value'   => isset( $ced_catch_custom_identifier_type ) ? $ced_catch_custom_identifier_type : '',
				'desc_tip'    => 'true',
				'description' => __( 'Select the identifier type', 'woocommerce' ),
			)
		);
		woocommerce_wp_text_input(
			array(
				'id'          => 'ced_catch_data['.$post_id.'][ced_catch_custom_identifier_value]',
				'label'       => __( 'Identifier Value', 'woocommerce' ),
				'desc_tip'    => 'true',
				'description' => __( 'Enter the identifier value', 'woocommerce' ),
				'type'        => 'text',
				'value'       => $ced_catch_custom_identifier_value,
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'          => 'ced_catch_data['.$post_id.'][ced_catch_custom_size_chart]',
				'label'       => __( 'Size Chart URL', 'woocommerce' ),
				'desc_tip'    => 'true',
				'description' => __( 'Enter the size chart url', 'woocommerce' ),
				'type'        => 'text',
				'value'       => $ced_catch_custom_size_chart,
			)
		);


	}

	public function ced_catch_render_product_fields( $loop, $variation_data, $variation ) {
		if ( ! empty( $variation_data ) ) {
			?><div id='catch_inventory_options_variable' class='panel woocommerce_options_panel'><div class='options_group'>
				<?php
				echo "<div class='ced_catch_variation_product_level_wrap'>";
				echo "<div class=''>";
				echo "<h2 class='catch-cool'>Catch Product Data";
				echo '</h2>';
				echo '</div>';
				echo "<div class='ced_catch_variation_product_content'>";
				$this->render_fields( $variation->ID );
				echo '</div>';
				echo '</div>';
				?>
			</div></div>
			<?php
		}
	}


	public function ced_catch_search_product_name() {

		$check_ajax = check_ajax_referer( 'ced-catch-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$keyword      = isset( $_POST['keyword'] ) ? sanitize_text_field( $_POST['keyword'] ) : '';
			$product_list = '';
			if ( ! empty( $keyword ) ) {
				$arguements = array(
					'numberposts' => -1,
					'post_type'   => array( 'product', 'product_variation' ),
					's'           => $keyword,
				);
				$post_data  = get_posts( $arguements );
				if ( ! empty( $post_data ) ) {
					foreach ( $post_data as $key => $data ) {
						$product_list .= '<li class="ced_catch_searched_product" data-post-id="' . esc_attr( $data->ID ) . '">' . esc_html( __( $data->post_title, 'catch-woocommerce-integration' ) ) . '</li>';
					}
				} else {
					$product_list .= '<li>No products found.</li>';
				}
			} else {
				$product_list .= '<li>No products found.</li>';
			}
			echo json_encode( array( 'html' => $product_list ) );
			wp_die();
		}
	}

	public function ced_catch_get_product_metakeys() {

		$check_ajax = check_ajax_referer( 'ced-catch-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$product_id = isset( $_POST['post_id'] ) ? sanitize_text_field( $_POST['post_id'] ) : '';
			include_once CED_CATCH_DIRPATH . 'admin/partials/ced-catch-metakeys-list.php';
		}
	}

	public function ced_catch_process_metakeys() {

		$check_ajax = check_ajax_referer( 'ced-catch-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$metakey   = isset( $_POST['metakey'] ) ? sanitize_text_field( wp_unslash( $_POST['metakey'] ) ) : '';
			$operation = isset( $_POST['operation'] ) ? sanitize_text_field( wp_unslash( $_POST['operation'] ) ) : '';
			if ( ! empty( $metakey ) ) {
				$added_meta_keys = get_option( 'ced_catch_selected_metakeys', array() );
				if ( 'store' == $operation ) {
					$added_meta_keys[ $metakey ] = $metakey;
				} elseif ( 'remove' == $operation ) {
					unset( $added_meta_keys[ $metakey ] );
				}
				update_option( 'ced_catch_selected_metakeys', $added_meta_keys );
				echo json_encode( array( 'status' => 200 ) );
				die();
			} else {
				echo json_encode( array( 'status' => 400 ) );
				die();
			}
		}
	}

	public function ced_catch_save_product_fields( $post_id = '', $i = '' ) {
		if ( empty( $post_id ) ) {
			return;
		}
		
		if ( isset( $_POST['ced_catch_data'] ) ) {
			$sanitized_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
			if ( ! empty( $sanitized_array ) ) {
				foreach ( $sanitized_array['ced_catch_data'] as $id => $value ) {
					foreach ( $value as $meta_key => $meta_val ) {
						update_post_meta( $id, $meta_key, $meta_val );
					}
				}
			}
		}
	}

	public function ced_catch_save_metadata( $post_id = '' ) {
		if ( ! $post_id ) {
			return;
		}

		if ( $post_id ) {

			if( isset($_POST['ced_catch_data']) ) {
				foreach ($_POST['ced_catch_data'] as $key => $value) {
					foreach ($value as $meta_key => $meta_val) {
						update_post_meta( $key , $meta_key , $meta_val);
					}
				}
			}	
			if ( isset( $_POST['ced_catch_carrier_code'] ) ) {
				update_post_meta( $post_id, 'ced_catch_carrier_code', $_POST['ced_catch_carrier_code'] );
			}

			if ( isset( $_POST['ced_catch_carrier_name'] ) ) {
				update_post_meta( $post_id, 'ced_catch_carrier_name', $_POST['ced_catch_carrier_name'] );
			}

			if ( isset( $_POST['ced_catch_carrier_url'] ) ) {
				update_post_meta( $post_id, 'ced_catch_carrier_url', $_POST['ced_catch_carrier_url'] );
			}

			if ( isset( $_POST['ced_catch_tracking_number'] ) ) {
				update_post_meta( $post_id, 'ced_catch_tracking_number', $_POST['ced_catch_tracking_number'] );
			}

			if ( isset( $_POST['_ced_catch_custom_description'] ) ) {
				update_post_meta( $post_id, '_ced_catch_custom_description', $_POST['_ced_catch_custom_description'] );
			}

			$shop_id     = get_option( 'ced_catch_shop_id', '' );
			$shop_id     = trim( $shop_id );
			$is_uploaded = get_post_meta( $post_id, 'ced_catch_product_on_catch_' . $shop_id, true );
			if ( $is_uploaded ) {
				$is_transient_set = get_transient( 'ced_update_offers' );
				if ( ! $is_transient_set ) {
					$this->ced_catch_manager->prepareProductHtmlForOffer( array( $post_id ), $shop_id, 'UPDATE', true );
					set_transient( 'ced_update_offers', true, 60 );
				}
			}
		}
	}

	public function ced_catch_sync_products_using_identifier() {

		require_once CED_CATCH_DIRPATH . 'admin/catch/lib/catchProducts.php';
		$ced_catch_products_instance = new Class_Ced_Catch_Products();
		$current_action       = current_action();
		$shop_id              = str_replace( 'ced_catch_sync_existing_products_', '', $current_action );
		$products_for_syncing = get_option( 'ced_catch_products_to_be_synced', array() );
		$product_to_sync      = isset( $products_for_syncing[0] ) ? $products_for_syncing[0] : '';
		if ( empty( $product_to_sync ) ) {
			$all_product_ids = get_posts(
				array(
					'numberposts' => -1,
					'post_type'   => array('product_variation','product'),
					'meta_query'  => array(
						array(
							'key'     => 'ced_catch_product_on_catch_' . $shop_id,
							'compare' => 'NOT EXISTS',
						),
					),
					'fields'      => 'ids',
				)
			);
			if ( ! empty( $all_product_ids ) ) {
				update_option( 'ced_catch_products_to_be_synced', $all_product_ids );
				$products_for_syncing = get_option( 'ced_catch_products_to_be_synced', array() );
				$product_to_sync      = isset( $products_for_syncing[0] ) ? $products_for_syncing[0] : '';
			}
		}
		if ( ! empty( $product_to_sync ) ) {
			$sync_data               = get_option( 'ced_catch_sync_existing_product_data_' . $shop_id, array() );
			$product_reference_value = $ced_catch_products_instance->fetchMetaValueOfProduct( $product_to_sync, 'sync_existing_product', true, $sync_data[$shop_id] );
			$product_reference_type = $ced_catch_products_instance->fetchMetaValueOfProduct( $product_to_sync, 'ced_catch_syncing_identifier_type', true, $sync_data[$shop_id] );
			if ( ! empty( $product_reference_value ) ) {
				$action   = 'products?product_references=' . strtoupper($product_reference_type) .'|' . $product_reference_value;
				$response = $this->sendRequestObj->sendHttpRequestGet( $action, array(), $shop_id );
				$response = json_decode( $response, true );
				if ( isset( $response['products'] ) && ! empty( $response['products'] ) ) {
					foreach ( $response['products'] as $index => $data ) {
						$catch_sku = $data['product_sku'];
						$product   = wc_get_product( $product_to_sync );
						$type      = '';
						if ( is_object( $product ) ) {
							$type = $product->get_type();
							if ( $type == 'variation' ) {
								$parent_id = $product->get_parent_id();
							}
						}
						update_post_meta( $product_to_sync, 'ced_catch_product_sku', $catch_sku );
						update_post_meta( $product_to_sync, 'ced_catch_product_on_catch_' . $shop_id, true );
						if ( ! empty( $parent_id ) ) {
							update_post_meta( $parent_id, 'ced_catch_product_on_catch_' . $shop_id, true );
						}
					}
					$this->ced_catch_manager->prepareProductHtmlForOffer( array( $product_to_sync ), $shop_id, 'UPDATE', true );
				}
			}
			unset( $products_for_syncing[0] );
			$products_for_syncing = array_values( $products_for_syncing );
			update_option( 'ced_catch_products_to_be_synced', $products_for_syncing );
				// print_r(count($products_for_syncing));die();
		}

	}

	/*
	*
	*Function for Processing different Bulk Actions
	*
	*
	*/

	public function ced_catch_process_bulk_action() {
		$check_ajax = check_ajax_referer( 'ced-catch-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$ced_catch_manager = $this->ced_catch_manager;
			$shop_id           = isset( $_POST['shopid'] ) ? $_POST['shopid'] : '';

			$isShopInActive = ced_catch_inactive_shops( $shop_id );
			if ( $isShopInActive ) {
				echo json_encode(
					array(
						'status'  => 400,
						'message' => __(
							'Shop is Not Active',
							'woocommerce-catch-integration'
						),
					)
				);
				die;
			}

			$operation   = isset( $_POST['operation_to_be_performed'] ) ? $_POST['operation_to_be_performed'] : '';
			$product_ids = isset( $_POST['id'] ) ? $_POST['id'] : '';
			if ( is_array( $product_ids ) ) {
				if ( $operation == 'upload_product' ) {
					$prodIDs = $product_ids;

					$get_product_detail = $ced_catch_manager->prepareProductHtmlForUpload( $prodIDs, $shop_id );

					if ( isset( $get_product_detail['import_id'] ) ) {
						echo json_encode(
							array(
								'status'  => 200,
								'message' => 'Product(s) File Uploaded Successfully .',
								'prodid'  => $prodIDs,
							)
						);
						die;
					} elseif ( isset( $get_product_detail['message'] ) ) {
						echo json_encode(
							array(
								'status'  => 400,
								'message' => $get_product_detail['message'],
							)
						);
						die;
					} else {
						echo json_encode(
							array(
								'status'  => 400,
								'message' => 'Product(s) File Not Uploaded',
								'prodid'  => $prodIDs,
							)
						);
						die;
					}
				} elseif ( $operation == 'upload_offer' ) {
					$prodIDs = $product_ids;

					$get_product_detail = $ced_catch_manager->prepareProductHtmlForOffer( $prodIDs, $shop_id, 'UPDATE' );
					if ( isset( $get_product_detail['import_id'] ) ) {
						echo json_encode(
							array(
								'status'  => 200,
								'message' => 'Offer(s) File Imported Successfully',
							)
						);
						die;
					} elseif ( isset( $get_product_detail['message'] ) ) {
						echo json_encode(
							array(
								'status'  => 400,
								'message' => $get_product_detail['message'],
							)
						);
						die;
					} else {
						echo json_encode(
							array(
								'status'  => 400,
								'message' => 'Offer(s) File Not Imported',
							)
						);
						die;
					}
				} elseif ( $operation == 'remove_offer' ) {
					$prodIDs = $product_ids;

					$get_product_detail = $ced_catch_manager->prepareProductHtmlForOffer( $prodIDs, $shop_id, 'DELETE' );
					if ( isset( $get_product_detail['import_id'] ) ) {
						echo json_encode(
							array(
								'status'  => 200,
								'message' => 'Offer(s) Removal File Added Succesfully',
								'prodid'  => $prodIDs,
							)
						);
						die;
					} elseif ( isset( $get_product_detail['message'] ) ) {
						echo json_encode(
							array(
								'status'  => 400,
								'message' => $get_product_detail['message'],
							)
						);
						die;
					} else {
						echo json_encode(
							array(
								'status'  => 400,
								'message' => 'Offer(s) Removal File Not Added',
							)
						);
						die;
					}
				} elseif ( $operation == 'upload_combine' ) {
					$prodIDs = $product_ids;

					$get_product_detail = $ced_catch_manager->prepareProductHtmlForOfferWithProduct( $prodIDs, $shop_id, 'UPDATE' );
					if ( isset( $get_product_detail['import_id'] ) ) {
						echo json_encode(
							array(
								'status'  => 200,
								'message' => 'Offer(s) File Imported Successfully',
							)
						);
						die;
					} elseif ( isset( $get_product_detail['message'] ) ) {
						echo json_encode(
							array(
								'status'  => 400,
								'message' => $get_product_detail['message'],
							)
						);
						die;
					} else {
						echo json_encode(
							array(
								'status'  => 400,
								'message' => 'Offer(s) File Not Imported',
							)
						);
						die;
					}
				}
			}
		}
	}

}
