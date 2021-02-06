<?php
/**
 * main class for handling reqests.
 *
 * @since      1.0.0
 *
 * @package    Woocommerce catch Integration
 * @subpackage Woocommerce catch Integration/admin/catch
 */

if ( ! class_exists( 'Class_Ced_Catch_Manager' ) ) {

	/**
	 * single product related functionality.
	 *
	 * Manage all single product related functionality required for listing product on admin.
	 *
	 * @since      1.0.0
	 * @package    Woocommerce catch Integration
	 * @subpackage Woocommerce catch Integration/admin/catch
	 * @author     CedCommerce <cedcommerce.com>
	 */
	class Class_Ced_Catch_Manager {

		/**
		 * The Instace of CED_catch_catch_Manager.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      $_instance   The Instance of CED_catch_catch_Manager class.
		 */
		private static $_instance;
		private static $authorization_obj;
		private static $client_obj;
		/**
		 * CED_catch_catch_Manager Instance.
		 *
		 * Ensures only one instance of CED_catch_catch_Manager is loaded or can be loaded.
		 *
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @since 1.0.0
		 * @static
		 * @return CED_catch_catch_Manager instance.
		 */
		public static function get_instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		public $marketplaceID   = 'catch';
		public $marketplaceName = 'catch';


		public function __construct() {
			add_action( 'admin_init', array( $this, 'ced_catch_fetch_orders_schedule' ) );
			$this->loadDependency();
			add_action( 'woocommerce_order_status_completed', array( $this, 'ced_catch_order_submit_tracking' ) );
			add_action( 'woocommerce_thankyou', array( $this, 'ced_catch_update_inventory_on_order_creation' ), 10, 1 );

		}

		public function ced_catch_update_inventory_on_save_post( $post_id ) {
			if ( empty( $post_id ) ) {
				return;
			}
			$response = $this->prepareProductHtmlForOffer( array( $post_id ), '', 'UPDATE', true );
		}

		public function ced_catch_update_inventory_on_order_creation( $order_id ) {
			if ( empty( $order_id ) ) {
				return;
			}
			$product_ids   = array();
			$inventory_log = array();
			$order_obj     = wc_get_order( $order_id );
			$order_items   = $order_obj->get_items();
			if ( is_array( $order_items ) && ! empty( $order_items ) ) {
				foreach ( $order_items as $key => $value ) {
					$product_id    = $value->get_data()['product_id'];
					$product_ids[] = $product_id;
				}
			}
			if ( is_array( $product_ids ) && ! empty( $product_ids ) ) {
				$response        = $this->prepareProductHtmlForOffer( $product_ids, '', 'UPDATE', true );
				$inventory_log[] = $response;
			}
			update_option( 'update_inventory_on_order_creation', $inventory_log );
		}



		public function ced_catch_order_submit_tracking( $order_id = '' ) {
			if ( empty( $order_id ) ) {
				return;
			}

			$shop_id        = get_post_meta( $order_id, 'ced_catch_order_shop_id', true );
			$catch_order_id = get_post_meta( $order_id, '_ced_catch_order_id', true );
			$carrier_code   = get_post_meta( $order_id, 'ced_catch_carrier_code', true );
			$carrier_name   = get_post_meta( $order_id, 'ced_catch_carrier_name', true );
			$carrier_url    = get_post_meta( $order_id, 'ced_catch_carrier_url', true );
			$tracking_no    = get_post_meta( $order_id, 'ced_catch_tracking_number', true );

			$carrier_code = ! empty( $carrier_code ) ? $carrier_code : '';
			$carrier_name = ! empty( $carrier_name ) ? $carrier_name : '';
			$carrier_url  = ! empty( $carrier_url ) ? $carrier_url : '';
			$tracking_no  = ! empty( $tracking_no ) ? $tracking_no : '';

			$action     = 'orders/' . $catch_order_id . '/tracking';
			$parameters = array(
				'carrier_code'    => $carrier_code,
				'carrier_name'    => $carrier_name,
				'carrier_url'     => $carrier_url,
				'tracking_number' => $tracking_no,
			);

			// print_r($parameters);die();
			$actionShip = 'orders/' . $catch_order_id . '/ship';
			$this->sendRequestObj->sendHttpRequestPut( $action, $parameters, $shop_id );
			$this->sendRequestObj->sendHttpRequestPut( $actionShip, array(), $shop_id );
			update_post_meta( $order_id, '_catch_umb_order_status', 'Shipped' );

		}
		public function ced_catch_fetch_orders_schedule() {

			$shop_id = isset( $_GET['shop_id'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_id'] ) ) : '';
			if ( $shop_id ) {
				$is_scheduled = wp_get_schedule( 'ced_catch_Fetch_orders_' . $shop_id );
				if ( ! $is_scheduled ) {
					wp_schedule_event( time(), 'ced_catch_6min', 'ced_catch_Fetch_orders_' . $shop_id );
				}
				$is_another_scheduled = wp_get_schedule( 'ced_catch_Fetch_orders_by_id_' . $shop_id );
				if ( ! $is_another_scheduled ) {
					wp_schedule_event( time(), 'ced_catch_6min', 'ced_catch_Fetch_orders_by_id_' . $shop_id );
				}
				$is_sync_scheduled = wp_get_schedule( 'ced_catch_sync_products_' . $shop_id );
				if ( ! $is_sync_scheduled ) {
					wp_schedule_event( time(), 'ced_catch_10min', 'ced_catch_sync_products_' . $shop_id );
				}


				$is_sync_existing_products_scheduled = get_option( 'ced_catch_sync_existing_products_' . $shop_id, '' );
				if ( $is_sync_existing_products_scheduled == 'on' ) {
					$is_sync_existing_products = wp_get_schedule( 'ced_catch_sync_existing_products_' . $shop_id );
					if ( ! $is_sync_existing_products ) {
						wp_schedule_event( time(), 'ced_catch_2_min', 'ced_catch_sync_existing_products_' . $shop_id );
					}
				} else {
					wp_clear_scheduled_hook( 'ced_catch_sync_existing_products_' . $shop_id );
				}

			}
		}


		public function ced_catch_update_integration_status() {
			if ( isset( $_GET['shop_id'] ) && ! empty( $_GET['shop_id'] ) ) {
				if ( ! wp_get_schedule( 'ced_catch_integration_report_' . $_GET['shop_id'] ) ) {
					wp_schedule_event( time(), 'ced_catch_6min', 'ced_catch_integration_report_' . $_GET['shop_id'] );
				}
			}
		}

		public function loadDependency() {

			$fileConfig = CED_CATCH_DIRPATH . 'admin/catch/lib/catchConfig.php';
			if ( file_exists( $fileConfig ) ) {
				require_once $fileConfig;
			}
			$fileProducts = CED_CATCH_DIRPATH . 'admin/catch/lib/catchProducts.php';
			if ( file_exists( $fileProducts ) ) {
				require_once $fileProducts;
			}

			require_once CED_CATCH_DIRPATH . 'admin/catch/lib/catchSendHttpRequest.php';
			$this->sendRequestObj = new Class_Ced_Catch_Send_Http_Request();

			$this->ced_catch_configInstance = new Ced_Catch_Config();
			$this->catchProductsInstance    = Class_Ced_Catch_Products::get_instance();
		}


		/*
		*
		*Creating Auto Profiles
		*
		*
		*/

		public function ced_catch_createAutoProfiles( $catchMappedCategories = array(), $catchMappedCategoriesName = array(), $catchStoreId = '' ) {

			global $wpdb;

			$wooStoreCategories          = get_terms( 'product_cat' );
			$alreadyMappedCategories     = get_option( 'ced_woo_catch_mapped_categories', array() );
			$alreadyMappedCategoriesName = get_option( 'ced_woo_catch_mapped_categories_name', array() );

			if ( ! empty( $catchMappedCategories ) ) {
				foreach ( $catchMappedCategories as $key => $value ) {
					$profileAlreadyCreated = get_term_meta( $key, 'ced_catch_profile_created_' . $catchStoreId, true );
					$createdProfileId      = get_term_meta( $key, 'ced_catch_profile_id_' . $catchStoreId, true );
					if ( $profileAlreadyCreated == 'yes' && $createdProfileId != '' ) {

						$newProfileNeedToBeCreated = $this->checkIfNewProfileNeedToBeCreated( $key, $value, $catchStoreId );

						if ( ! $newProfileNeedToBeCreated ) {
							continue;
						} else {
							$this->resetMappedCategoryData( $key, $value, $catchStoreId );
						}
					}

					$wooCategories      = array();
					$categoryAttributes = array();

					$profileName     = isset( $catchMappedCategoriesName[ $value ] ) ? $catchMappedCategoriesName[ $value ] : 'Profile for Catch - Category Id : ' . $value;
					$is_active       = 1;
					$marketplaceName = 'Catch';

					foreach ( $catchMappedCategories as $key1 => $value1 ) {
						if ( $value1 == $value ) {
							$wooCategories[] = $key1;
						}
					}

					$profileData = array();
					$profileData = $this->prepareProfileData( $catchStoreId, $value, $wooCategories );

					$profileDetails = array(
						'profile_name'   => $profileName,
						'profile_status' => 'active',
						'profile_data'   => json_encode( $profileData ),
						'shop_id'        => $catchStoreId,
						'woo_categories' => json_encode( $wooCategories ),
					);
					$profileId      = $this->insertCatchProfile( $profileDetails );
					foreach ( $wooCategories as $key12 => $value12 ) {
						update_term_meta( $value12, 'ced_catch_profile_created_' . $catchStoreId, 'yes' );
						update_term_meta( $value12, 'ced_catch_profile_id_' . $catchStoreId, $profileId );
						update_term_meta( $value12, 'ced_catch_mapped_category_' . $catchStoreId, $value );
					}
				}
			}
		}

		/*
		*
		*Updating profile for a woo category if mapped again
		*   *
		*
		*/

		public function resetMappedCategoryData( $wooCategoryId = '', $CatchCategoryId = '', $catchStoreId = '' ) {

			update_term_meta( $wooCategoryId, 'ced_catch_mapped_category_' . $catchStoreId, $CatchCategoryId );

			delete_term_meta( $wooCategoryId, 'ced_catch_profile_created_' . $catchStoreId );

			$createdProfileId = get_term_meta( $wooCategoryId, 'ced_catch_profile_id_' . $catchStoreId, true );

			delete_term_meta( $wooCategoryId, 'ced_catch_profile_id_' . $catchStoreId );

			$this->removeCategoryMappingFromProfile( $createdProfileId, $wooCategoryId );
		}

		/*
		*
		*removing previous mapped profile to a woo category
		*
		*
		*/


		public function removeCategoryMappingFromProfile( $createdProfileId = '', $wooCategoryId = '' ) {

			global $wpdb;
			$profileTableName = $wpdb->prefix . 'ced_catch_profiles';

			$query        = "SELECT `woo_categories` FROM `$profileTableName` WHERE `id`=$createdProfileId";
			$profile_data = $wpdb->get_results( $query, 'ARRAY_A' );

			if ( is_array( $profile_data ) ) {

				$profile_data  = isset( $profile_data[0] ) ? $profile_data[0] : $profile_data;
				$wooCategories = isset( $profile_data['woo_categories'] ) ? json_decode( $profile_data['woo_categories'], true ) : array();
				if ( is_array( $wooCategories ) && ! empty( $wooCategories ) ) {
					$categories = array();
					foreach ( $wooCategories as $key => $value ) {
						if ( $value != $wooCategoryId ) {
							$categories[] = $value;
						}
					}
					$categories = json_encode( $categories );
					$wpdb->update( $profileTableName, array( 'woo_categories' => $categories ), array( 'id' => $createdProfileId ) );
				}
			}
		}
		/*
		*
		*Checking if new profile to be created for woo category
		*
		*
		*/

		public function checkIfNewProfileNeedToBeCreated( $wooCategoryId = '', $CatchCategoryId = '', $catchStoreId = '' ) {

			$oldCatchCategoryMapped = get_term_meta( $wooCategoryId, 'ced_catch_mapped_category_' . $catchStoreId, true );
			if ( $oldCatchCategoryMapped == $CatchCategoryId ) {
				return false;
			} else {
				return true;
			}
		}

		/*
		*
		*Preparing profile data for saving
		*
		*
		*/

		public function prepareProfileData( $catchStoreId, $catchCategoryId, $wooCategories = '' ) {
			$profileData                = array();
			$shop_id                    = $catchStoreId;
			$renderDataOnGlobalSettings = get_option( 'ced_catch_global_settings', false );

			$profileData['_umb_catch_category']['default'] = $catchCategoryId;
			$profileData['_umb_catch_category']['metakey'] = null;

			$profileData['_ced_catch_weight']['default'] = isset( $renderDataOnGlobalSettings[ $shop_id ]['ced_catch_package_weight'] ) ? $renderDataOnGlobalSettings[ $shop_id ]['ced_catch_package_weight'] : '';
			$profileData['_ced_catch_weight']['metakey'] = null;

			$profileData['_ced_catch_package_length']['default'] = isset( $renderDataOnGlobalSettings[ $shop_id ]['ced_catch_package_length'] ) ? $renderDataOnGlobalSettings[ $shop_id ]['ced_catch_package_length'] : '';
			$profileData['_ced_catch_package_length']['metakey'] = null;

			$profileData['_ced_catch_package_height']['default'] = isset( $renderDataOnGlobalSettings[ $shop_id ]['ced_catch_package_height'] ) ? $renderDataOnGlobalSettings[ $shop_id ]['ced_catch_package_height'] : '';
			$profileData['_ced_catch_package_height']['metakey'] = null;

			$profileData['_ced_catch_package_width']['default'] = isset( $renderDataOnGlobalSettings[ $shop_id ]['ced_catch_package_width'] ) ? $renderDataOnGlobalSettings[ $shop_id ]['ced_catch_package_width'] : '';
			$profileData['_ced_catch_package_width']['metakey'] = null;

			$profileData['_ced_catch_markup_type']['default'] = isset( $renderDataOnGlobalSettings[ $shop_id ]['ced_catch_product_markup_type'] ) ? $renderDataOnGlobalSettings[ $shop_id ]['ced_catch_product_markup_type'] : '';
			$profileData['_ced_catch_markup_type']['metakey'] = null;

			$profileData['_ced_catch_markup_price']['default'] = isset( $renderDataOnGlobalSettings[ $shop_id ]['ced_catch_product_markup'] ) ? $renderDataOnGlobalSettings[ $shop_id ]['ced_catch_product_markup'] : '';
			$profileData['_ced_catch_markup_price']['metakey'] = null;

			return $profileData;
		}


		/*
		*
		*Inserting and Saving Profiles
		*
		*
		*/

		public function insertCatchProfile( $profileDetails ) {

			global $wpdb;
			$profileTableName = $wpdb->prefix . 'ced_catch_profiles';

			$wpdb->insert( $profileTableName, $profileDetails );

			$profileId = $wpdb->insert_id;
			return $profileId;
		}


		public function prepareProductHtmlForUpload( $proIDs = array(), $shopID ) {
			if ( ! is_array( $proIDs ) ) {
				$proIDs = array( $proIDs );
			}
			$response = $this->catchProductsInstance->ced_catch_prepareDataForUploading( $proIDs, $shopID );
			$response = $this->catchProductsInstance->doupload( $response, $shopID, 'Product' );
			return $response;
		}

		public function prepareProductHtmlForOffer( $proIDs = array(), $shopID = '', $UpdateOrDelete = '', $isCron = false ) {
			if ( ! is_array( $proIDs ) ) {
				$proIDs = array( $proIDs );
			}
			if ( empty( $shopID ) ) {
				$shopID = get_option( 'ced_catch_active_shop', '' );
			}
			$response = $this->catchProductsInstance->ced_catch_prepareDataForOffers( $proIDs, $shopID, $UpdateOrDelete, $isCron );
			$response = $this->catchProductsInstance->doupload( $response, $shopID, 'Offer', $isCron );
			return $response;
		}
		/*
			  public function prepareProductHtmlForOfferWithProduct( $proIDs = array(), $shopID, $UpdateOrDelete = '', $isCron = false ) {
			if ( ! is_array( $proIDs ) ) {
				$proIDs = array( $proIDs );
			}
			$response = $this->catchProductsInstance->ced_catch_prepareDataForOffersWithProducts( $proIDs, $shopID, $UpdateOrDelete, $isCron );
			 $response = $this->catchProductsInstance->doupload( $response, $shopID, 'Offer', $isCron );
			return $response;
		}*/

	}
}
