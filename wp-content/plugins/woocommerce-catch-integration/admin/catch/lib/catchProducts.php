<?php

class Class_Ced_Catch_Products {

	public static $_instance;


		/**
		 * Ced_Catch_Config Instance.
		 *
		 * Ensures only one instance of Ced_Catch_Config is loaded or can be loaded.
		 *
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @since 1.0.0
		 * @static
		 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		$this->loadDependency();
	}

	public function loadDependency() {
		$file = CED_CATCH_DIRPATH . 'admin/catch/lib/catchSendHttpRequest.php';
		if ( file_exists( $file ) ) {
			require_once $file;
		}

		$this->catchSendHttpRequestInstance = new Class_Ced_Catch_Send_Http_Request();
	}

	public function ced_catch_prepareDataForUploading( $proIDs = array(), $shopId, $Offset = 'False' ) {

		foreach ( $proIDs as $key => $value ) {
			$prod_data = wc_get_product( $value );
			if ( ! is_object( $prod_data ) ) {
				continue;
			}

			$type = $prod_data->get_type();

			if ( $type == 'variable' ) {
				$prod_data  = wc_get_product( $value );
				$variations = $prod_data->get_available_variations();
				foreach ( $variations as $variation ) {
					$attributes                    = $variation['attributes'];
					$variation_id                  = $variation['variation_id'];
					$preparedData[ $variation_id ] = $this->getFormattedData( $variation_id, $shopId, $attributes );
					if ( ! is_array( $preparedData[ $variation_id ] ) ) {
						unset( $preparedData[ $variation_id ] );
					}
				}
			} else {
				$preparedData[ $value ] = $this->getFormattedData( $value, $shopId );
				if ( ! is_array( $preparedData[ $value ] ) ) {
					unset( $preparedData[ $value ] );
				}
			}
		}
		if ( isset( $preparedData ) && is_array( $preparedData ) && ! empty( $preparedData ) ) {
			$merchantFile = $this->create_csv( $preparedData, $Offset );
			return $merchantFile;
		}

	}


	public function ced_catch_prepareDataForOffers( $proIDs = array(), $shopId, $UpdateOrDelete = '', $isCron = false, $Offset = 'False' ) {

		foreach ( $proIDs as $key => $value ) {
			$on_catch = get_post_meta( $value, 'ced_catch_product_on_catch_' . $shopId, true );
			// if(empty($on_catch)) {
			// continue;
			// }
			$prod_data = wc_get_product( $value );
			if ( ! is_object( $prod_data ) ) {
				continue;
			}
			$type = $prod_data->get_type();

			if ( $type == 'variable' ) {
				$prod_data  = wc_get_product( $value );
				$variations = $prod_data->get_available_variations();
				foreach ( $variations as $variation ) {
					$attributes                    = $variation['attributes'];
					$variation_id                  = $variation['variation_id'];
					$preparedData[ $variation_id ] = $this->getFormattedDataforOffer( $variation_id, $shopId, $UpdateOrDelete, $attributes );
					if ( ! is_array( $preparedData[ $variation_id ] ) ) {
						unset( $preparedData[ $variation_id ] );
					}
				}
			} else {
				$preparedData[ $value ] = $this->getFormattedDataforOffer( $value, $shopId, $UpdateOrDelete );
				if ( ! is_array( $preparedData[ $value ] ) ) {
					unset( $preparedData[ $value ] );
				}
			}
		}
		if ( isset( $preparedData ) && is_array( $preparedData ) && ! empty( $preparedData ) ) {
			$merchantFile = $this->create_csv( $preparedData, $Offset, $isCron );
			return $merchantFile;
		}

	}


	public function create_csv( $preparedData, $Offset = 'False', $isCron = false ) {
		$wpuploadDir = wp_upload_dir();
		$baseDir     = $wpuploadDir['basedir'];
		$uploadDir   = $baseDir . '/cedcommerce_catchuploads';
		$nameTime    = time();
		if ( ! is_dir( $uploadDir ) ) {
			mkdir( $uploadDir, 0777, true );
		}

		if ( $isCron ) {
			$file     = fopen( $uploadDir . '/CronMerchant.csv', 'w' );
			$location = wp_upload_dir()['basedir'] . '/cedcommerce_catchuploads/CronMerchant.csv';
		} else {
			$file     = fopen( $uploadDir . '/Merchant.csv', 'w' );
			$location = wp_upload_dir()['basedir'] . '/cedcommerce_catchuploads/Merchant.csv';
		}
		if ( isset( $preparedData ) && is_array( $preparedData ) && ! empty( $preparedData ) ) {
			$count = 0;
			foreach ( $preparedData as $key_preparedData => $value_preparedData ) {
				foreach ( $value_preparedData as $key_header => $value_header ) {
					$key_prodata[] = $key_header;
				}
				$count++;
				$value_preparedDatas[] = $value_preparedData;
			}
			$key_prodata = array_unique( $key_prodata );
			fputcsv( $file, $key_prodata );
			foreach ( $value_preparedDatas as $key => $value ) {
				if ( is_array( $value ) ) {
					fputcsv( $file, $value );
				}
			}
		}
		return $location;
	}

	public function getSplitVariations( $proId = '', $shopId = '' ) {

		$prod_data  = wc_get_product( $proId );
		$variations = $prod_data->get_available_variations();
		foreach ( $variations as $variation ) {
			$attributes                    = $variation['attributes'];
			$variation_id                  = $variation['variation_id'];
			$preparedData[ $variation_id ] = $this->getFormattedData( $variation_id, $shopId, $attributes );
		}
		return $preparedData;
	}
	public function getFormattedData( $proIds = array(), $shopId = '', $attributesforVariation = '' ) {
		
		$profileData = $this->ced_catch_getProfileAssignedData( $proIds, $shopId );
		if ( ! $this->isProfileAssignedToProduct ) {
			return;
		}
		$product = wc_get_product( $proIds );
		if ( WC()->version > '3.0.0' ) {
			$product_data = $product->get_data();
			$productType  = $product->get_type();
			$description  = $product_data['description'] . ' ' . $product_data['short_description'];

			$custom_description = get_post_meta( $proIds, '_ced_catch_custom_description', true );
			if ( ! empty( $custom_description ) ) {
				$description = $custom_description;
			}

			if ( $product->get_type() == 'variation' ) {
				$parentId          = $product->get_parent_id();
				$parentProduct     = wc_get_product( $parentId );
				$parentProductData = $parentProduct->get_data();
				$description       = $parentProductData['description'] . '</br>' . $parentProductData['short_description'];

				$custom_description = get_post_meta( $parentId, '_ced_catch_custom_description', true );
				if ( ! empty( $custom_description ) ) {
					$description = $custom_description;
				}
			}
			$title = $product_data['name'];
			$price = (float) $product_data['price'];
			if ( $productType == 'variation' ) {
				$parent_id      = $product->get_parent_id();
				$parent_product = wc_get_product( $parent_id );
				$parent_product = $parent_product->get_data();

			}
		}
		$weight         = get_post_meta( $proIds, '_weight', true );
		$package_length = get_post_meta( $proIds, '_length', true );
		$package_width  = get_post_meta( $proIds, '_width', true );
		$package_height = get_post_meta( $proIds, '_height', true );

		if ( empty( $weight ) ) {
			$weight = $this->fetchMetaValueOfProduct( $proIds, '_ced_catch_weight' );
		}
		if ( empty( $package_length ) ) {
			$package_length = $this->fetchMetaValueOfProduct( $proIds, '_ced_catch_package_length' );
		}
		if ( empty( $package_width ) ) {
			$package_width = $this->fetchMetaValueOfProduct( $proIds, '_ced_catch_package_width' );
		}
		if ( empty( $package_height ) ) {
			$package_height = $this->fetchMetaValueOfProduct( $proIds, '_ced_catch_package_height' );
		}

		$condition = $this->fetchMetaValueOfProduct( $proIds, '_ced_catch_condition' );
		if ( $condition == '11' ) {
			$condition = 11;
		} elseif ( $condition == '10' ) {
			$condition = 10;
		}
		$referencetype    = get_post_meta( $proIds, 'ced_catch_custom_identifier_type' , true );
		if(empty($referencetype)) {
			$referencetype  = $this->fetchMetaValueOfProduct( $proIds, '_ced_catch_product_reference_type' );
		}
		$referencevalue = get_post_meta( $proIds, 'ced_catch_custom_identifier_value' , true );
		if(empty($referencevalue)) {
			$referencevalue = $this->fetchMetaValueOfProduct( $proIds, '_ced_catch_product_reference_value' );
		}

		$clubcatch = $this->fetchMetaValueOfProduct( $proIds, '_ced_catch_club_eligible' );
		if ( $clubcatch == 'false' ) {
			$clubcatch = false;
		} elseif ( $clubcatch == 'true' ) {
			$clubcatch = true;
		}
		$tax = $this->fetchMetaValueOfProduct( $proIds, '_ced_catch_tax' );

		$brand = $this->fetchMetaValueOfProduct( $proIds, '_ced_catch_brand' );
		if ( empty( $brand ) || empty( $referencevalue ) || empty( $referencetype ) ) {
			$referencetype  = 'mpn';
			$referencevalue = (string) $proIds;
			$brand          = 'unbranded';
		}
		$category_id = $this->fetchMetaValueOfProduct( $proIds, '_umb_catch_category' );
		$keywords    = get_post_meta( $proIds, 'ced_catch_product_keywords' , true );
		if(empty($keywords)) {
			$keywords    = $this->fetchMetaValueOfProduct( $proIds, '_ced_catch_keywords' );
		}
		if ( ! empty( $keywords ) ) {
			$keywords = str_replace( ' ', '|', $keywords );
			$keywords = str_replace( ',', '|', $keywords );
		}
		$attributes     = get_option( 'ced_catch_category_attributes_' . $category_id, true );
		$pro_attributes = array();
		if ( isset( $attributes ) && is_array( $attributes ) && ! empty( $attributes ) ) {
			foreach ( $attributes as $attribute_key => $attribute_value ) {
				$categoryId = str_replace( ' ', '_', $category_id );
				if ( $this->fetchMetaValueOfProduct( $proIds, $categoryId . '_' . $attribute_value['code'] ) != '' ) {
					$pro_attributes[ $attribute_key ]['code']  = $attribute_value['code'];
					$pro_attributes[ $attribute_key ]['value'] = $this->fetchMetaValueOfProduct( $proIds, $categoryId . '_' . $attribute_value['code'] );
				}
			}
		}
		$pro_attributes = array_values( $pro_attributes );
		$product        = wc_get_product( $proIds );
		if ( $product->get_type() == 'variable' ) {
			$variations = $product->get_available_variations();
			if ( $variations ) {
				if ( $weight == 0 || $weight == '' ) {
					$weight = $variations[0]['weight'];
				}

				if ( $package_width == 0 || $package_width == '' ) {
					$package_width = $variations[0]['dimensions']['width'];

				}
				if ( $package_height == 0 || $package_height == '' ) {
					$package_height = $variations[0]['dimensions']['height'];

				}
				if ( $package_length == 0 || $package_length == '' ) {
					$package_length = $variations[0]['dimensions']['length'];
				}
			}
		}
		if ( ! empty( $pro_attributes ) ) {
			foreach ( $pro_attributes as $key12 => $value12 ) {
				if ( isset( $value12 ) && ! empty( $value12 ) ) {
					$attr[ $value12['code'] ] = $value12['value'];
				}
			}
		}

		$dimension_unit = get_option( 'woocommerce_dimension_unit', true );
		$weight_unit    = get_option( 'woocommerce_weight_unit', true );

		$description = preg_replace("/<img[^>]+\>/i", "", $description); 
		$description = preg_replace("/<\/?a[^>]*>/", "", $description); 

		$category_id = str_replace( '~', "'", $category_id );
		$args        = array(

			'category'                => $category_id,
			'title'                   => $title,
			'product-reference-type'  => $referencetype,
			'product-reference-value' => $referencevalue,
			'brand'                   => ! empty( $brand ) ? $brand : 'unbranded',
			'product-description'     => $description,
			'adult'                   => isset( $adult ) ? $adult : '',
			'keywords'                => isset( $keywords ) ? $keywords : '',
			'weight'                  => (int) $weight,
			'weight-unit'             => isset( $weight_unit ) ? $weight_unit : '',
			'width'                   => (int) $package_width,
			'width-unit'              => isset( $dimension_unit ) ? $dimension_unit : '',
			'length'                  => (int) $package_length,
			'length-unit'             => isset( $dimension_unit ) ? $dimension_unit : '',
			'height'                  => (int) $package_height,
			'height-unit'             => isset( $dimension_unit ) ? $dimension_unit : '',
		);

		$args['variant-id']           = '';
		$args['variant-size-value']   = '';
		$args['variant-colour-value'] = '';

		$item_sku             = get_post_meta( $proIds, '_sku', true );
		$args['internal-sku'] = $item_sku;

		for ( $i = 1; $i <= 10; $i++ ) {
			$args[ 'image-' . $i ] = '';
		}

		if ( $product->get_type() == 'variation' ) {
			$variant_parent_id = $product->get_parent_id();
			$parent_sku        = get_post_meta( $variant_parent_id, '_sku', true );
			if ( empty( $parent_sku ) ) {
				$parent_sku = (string) $variant_parent_id;
			}
			$variation_attributes = $product->get_variation_attributes();
			if ( is_array( $variation_attributes ) ) {
				$variant_size_value = implode( '-', $variation_attributes );
			}
			$renderDataOnGlobalSettingsVariation = get_option( 'ced_catch_global_settings', array() );
			
			if($renderDataOnGlobalSettingsVariation[ $shopId ]['ced_catch_upload_pro_as_a_simple']!='on'){
				
				$args['variant-id']           = $parent_sku;
				$args['variant-size-value']   = $variant_size_value;
				$args['variant-colour-value'] = '';
		     }
		     
			$parentId                     = $variant_parent_id;

			$pictureUrl = wp_get_attachment_image_url( get_post_meta( $proIds, '_thumbnail_id', true ), 'full' ) ? wp_get_attachment_image_url( get_post_meta( $proIds, '_thumbnail_id', true ), 'full' ) : '';
			if ( isset( $pictureUrl ) && ! empty( $pictureUrl ) ) {
				$args['image-1'] = $pictureUrl;
			} else {
				$pictureUrl      = wp_get_attachment_image_url( get_post_meta( $parentId, '_thumbnail_id', true ), 'full' ) ? wp_get_attachment_image_url( get_post_meta( $parentId, '_thumbnail_id', true ), 'full' ) : '';
				$args['image-1'] = $pictureUrl;
			}

			$attachment_ids = $product->get_gallery_image_ids();
			if ( empty( $attachment_ids ) ) {
				$variant_parent_id = $product->get_parent_id();
				$parent_product    = wc_get_product( $variant_parent_id );
				$attachment_ids    = $parent_product->get_gallery_image_ids();
			}
			if ( ! empty( $attachment_ids ) && $renderDataOnGlobalSettingsVariation[ $shopId ]['ced_catch_upload_pro_as_a_simple']!='on') {
				$count = 2;
				foreach ( $attachment_ids as $attachment_id ) {

					if ( $count > 8 ) {
						continue;
					}

					$args[ 'image-' . $count ] = wp_get_attachment_url( $attachment_id );
					$count                     = $count + 1;
				}
			}
		} else {
			$pictureUrl = wp_get_attachment_image_url( get_post_meta( $proIds, '_thumbnail_id', true ), 'full' ) ? wp_get_attachment_image_url( get_post_meta( $proIds, '_thumbnail_id', true ), 'full' ) : '';

			$args['image-1'] = $pictureUrl;
			$attachment_ids  = $product->get_gallery_image_ids();
			if ( ! empty( $attachment_ids ) ) {
				$count = 2;
				foreach ( $attachment_ids as $attachment_id ) {

					if ( $count > 8 ) {
						continue;
					}

					$args[ 'image-' . $count ] = wp_get_attachment_url( $attachment_id );
					$count                     = $count + 1;
				}
			}
		}
		$image_size_chart = get_post_meta( $proIds, 'ced_catch_custom_size_chart' , true );
		if(empty($image_size_chart)) {
			$image_size_chart = $this->fetchMetaValueOfProduct( $proIds, '_ced_image_size_chart' );
		}
		$title_prefix = $this->fetchMetaValueOfProduct( $proIds, '_ced_title_prefix' );
		if(!empty($title_prefix) && 'unbranded' != $title_prefix) {
			$args['title'] = $title_prefix .' - ' . $args['title'];
		}
		$args['image-size-chart'] = $image_size_chart;
		if ( ! empty( $attr ) ) {
			$args = array_merge( $args, $attr );
		}
		return $args;
	}


	public function getFormattedDataforOffer( $proIds = array(), $shopId = '', $UpdateOrDelete = '', $attributesforVariation = '' ) {
		$profileData = $this->ced_catch_getProfileAssignedData( $proIds, $shopId );
		if ( ! $this->isProfileAssignedToProduct ) {
			return;
		}
		$product = wc_get_product( $proIds );
		if ( WC()->version > '3.0.0' ) {
			$product_data = $product->get_data();
			$productType  = $product->get_type();
			$quantity     = (int) get_post_meta( $proIds, '_stock', true );
			$description  = $product_data['description'] . ' ' . $product_data['short_description'];
			if ( $product->get_type() == 'variation' ) {
				$parentId          = $product->get_parent_id();
				$parentProduct     = wc_get_product( $parentId );
				$parentProductData = $parentProduct->get_data();
				$description       = $parentProductData['description'] . '' . $parentProductData['short_description'];
			}
			$title = $product_data['name'];
			$price = (float) $product_data['price'];
			if ( $productType == 'variable' ) {

				$variations = $product->get_available_variations();
				if ( isset( $variations['0']['display_regular_price'] ) ) {
					$price = $variations['0']['display_regular_price'];
				}
			}
		}
		$category_id         = $this->fetchMetaValueOfProduct( $proIds, '_umb_catch_category' );
		$product             = wc_get_product( $proIds );
		$Offerattributes     = get_option( 'ced_catch_category_offerattributes_' . $category_id, true );
		$pro_offerattributes = array();
		if ( isset( $Offerattributes ) && is_array( $Offerattributes ) && ! empty( $Offerattributes ) ) {
			foreach ( $Offerattributes as $attribute_key => $attribute_value ) {
				$categoryId = str_replace( ' ', '_', $category_id );
				if ( $attribute_value['code'] == 'price' ) {
					continue;
				}
				$pro_offerattributes[ $attribute_key ]['code']  = $attribute_value['code'];
				$pro_offerattributes[ $attribute_key ]['value'] = $this->fetchMetaValueOfProduct( $proIds, $categoryId . '_' . $attribute_value['code'] );
			}
		}
		$pro_offerattributes = array_values( $pro_offerattributes );

		$catch_custom_logistic_class = get_post_meta( $proIds, 'ced_catch_custom_logistic_class', true );

		if ( ! empty( $pro_offerattributes ) ) {
			foreach ( $pro_offerattributes as $key12 => $value12 ) {
				if ( isset( $value12 ) && ! empty( $value12 ) ) {
					if ( ! empty( $value12['value'] ) && ( $value12['code'] == 'discount-start-date' || $value12['code'] == 'discount-end-date' || $value12['code'] == 'best-before-date' || $value12['code'] == 'expiry-date' || $value12['code'] == 'available-start-date' || $value12['code'] == 'available-end-date' ) ) {
						$date = new DateTime( $value12['value'] );
						$data = $date->format( DATE_ISO8601 );
					} else {
						$data = $value12['value'];
					}
				}
				if ( $value['code'] == 'tax-au' && empty( $value12 ) ) {
					$offerattributes['tax-au'] = 0;
				}
				$offerattributes[ $value12['code'] ] = $data;

			}

			if ( ! empty( $catch_custom_logistic_class ) ) {
				$offerattributes['logistic-class'] = $catch_custom_logistic_class;
			}
		}

		if ( empty( $offerattributes['tax-au'] ) ) {
			$offerattributes['tax-au'] = 0;
		}
		if ( empty( $offerattributes['state'] ) ) {
			$offerattributes['state'] = '11';
		}
		if ( empty( $offerattributes['club-catch-eligible'] ) ) {
			$offerattributes['club-catch-eligible'] = 'false';
		}

		$markup_type = $this->fetchMetaValueOfProduct( $proIds, '_ced_catch_markup_type' );
		if ( ! empty( $markup_type ) ) {
			$markup_value = (int) $this->fetchMetaValueOfProduct( $proIds, '_ced_catch_markup_price' );
			if ( ! empty( $markup_value ) ) {
				if ( $markup_type == 'Fixed_Increased' ) {
					$price = $price + $markup_value;
				} elseif ( $markup_type == 'Fixed_Decreased' ) {
					$price = $price - $markup_value;
				} elseif ( $markup_type == 'Percentage_Increased' ) {
					$price = ( $price + ( ( $markup_value / 100 ) * $price ) );
				} elseif ( $markup_type == 'Percentage_Decreased' ) {
					$price = ( $price - ( ( $markup_value / 100 ) * $price ) );
				}
			}
		}

		$custom_price = get_post_meta( $proIds, 'ced_catch_custom_price', true );
		$manage_stock = get_post_meta( $proIds, '_manage_stock', true );
		$stock_status = get_post_meta( $proIds, '_stock_status', true );

		if ( ! empty( $custom_price ) ) {
			$price = $custom_price;
		}

		if ( trim( $stock_status ) == 'outofstock' ) {
			$quantity = 0;
		} elseif ( trim( $stock_status ) == 'instock' && trim( $manage_stock ) == 'no' ) {
			$quantity = 1;
		}

		if ( $quantity <= 0 ) {
			$quantity = 0;
		}

		$item_sku = get_post_meta( $proIds, '_sku', true );

		$product_catch_sku = get_post_meta( $proIds, 'ced_catch_product_sku', true );
		if ( ! empty( $product_catch_sku ) ) {
			$product_id_type = 'SKU';
			$product_id      = $product_catch_sku;
		} else {
			$product_id_type = 'SHOP_SKU';
			$product_id      = $item_sku;
		}
		$args               = array(
			'price'           => (float) $price,
			'quantity'        => (int) $quantity,
			'product-id-type' => $product_id_type,
			'update-delete'   => isset( $UpdateOrDelete ) ? $UpdateOrDelete : '',
		);
		$args['sku']        = $item_sku;
		$args['product-id'] = $product_id;

		if ( ! empty( $offerattributes ) ) {
			$args = array_merge( $args, $offerattributes );
		}

		return $args;
	}


	/*
	*
	*function for getting profile data of the product
	*
	*
	*/
	public function ced_catch_getProfileAssignedData( $proIds, $shopId ) {
		$data = wc_get_product( $proIds );
		$type = $data->get_type();
		if ( $type == 'variation' ) {
			$proIds = $data->get_parent_id();
		}
		global $wpdb;
		$table_name  = $wpdb->prefix . 'ced_catch_profiles';
		$productData = wc_get_product( $proIds );

		$product     = $productData->get_data();
		$category_id = isset( $product['category_ids'] ) ? $product['category_ids'] : array();
		foreach ( $category_id as $key => $value ) {
			$profile_id = get_term_meta( $value, 'ced_catch_profile_id_' . $shopId, true );
			if ( ! empty( $profile_id ) ) {
				break;
			}
		}
		if ( isset( $profile_id ) && ! empty( $profile_id ) && $profile_id != '' ) {
			$this->isProfileAssignedToProduct = true;
			$query                            = "SELECT * FROM `$table_name` WHERE `id`=$profile_id";
			$profile_data                     = $wpdb->get_results( $query, 'ARRAY_A' );
			if ( is_array( $profile_data ) ) {
				$profile_data = isset( $profile_data[0] ) ? $profile_data[0] : $profile_data;
				$profile_data = isset( $profile_data['profile_data'] ) ? json_decode( $profile_data['profile_data'], true ) : array();

			}
		} else {
			$this->isProfileAssignedToProduct = false;
		}
		$this->profile_data = isset( $profile_data ) ? $profile_data : '';
		return $this->profile_data;
	}

	/*
	*
	*function for getting meta value of the product
	*
	*
	*/
	public function fetchMetaValueOfProduct( $proIds, $metaKey  , $is_sync = false, $sync_data = array()) {

		if ( $is_sync ) {
			$this->isProfileAssignedToProduct = true;
			$this->profile_data                   = $sync_data;
		}

		if ( isset( $this->isProfileAssignedToProduct ) && $this->isProfileAssignedToProduct ) {
			$_product = wc_get_product( $proIds );
			if ( $_product->get_type() == 'variation' ) {
				$parentId = $_product->get_parent_id();
			} else {
				$parentId = '0';
			}

			if ( ! empty( $this->profile_data ) && isset( $this->profile_data[ $metaKey ] ) ) {
				$tempProfileData = $profileData = $this->profile_data[ $metaKey ];
				if ( isset( $tempProfileData['default'] ) && ! empty( $tempProfileData['default'] ) && $tempProfileData['default'] != '' && ! is_null( $tempProfileData['default'] ) ) {
					$value = $tempProfileData['default'];
				} elseif ( isset( $tempProfileData['metakey'] ) && ! empty( $tempProfileData['metakey'] ) && $tempProfileData['metakey'] != 'null' ) {

					if ( strpos( $tempProfileData['metakey'], 'umb_pattr_' ) !== false ) {

						$wooAttribute = explode( 'umb_pattr_', $tempProfileData['metakey'] );
						$wooAttribute = end( $wooAttribute );

						if ( $_product->get_type() == 'variation' ) {
							$var_product = wc_get_product( $parentId );
							$attributes  = $var_product->get_variation_attributes();
							if ( isset( $attributes[ 'attribute_pa_' . $wooAttribute ] ) && ! empty( $attributes[ 'attribute_pa_' . $wooAttribute ] ) ) {
								$wooAttributeValue = $attributes[ 'attribute_pa_' . $wooAttribute ];
								if ( $parentId != '0' ) {
									$product_terms = get_the_terms( $parentId, 'pa_' . $wooAttribute );
								} else {
									$product_terms = get_the_terms( $proIds, 'pa_' . $wooAttribute );
								}
							} else {
								$wooAttributeValue = $var_product->get_attribute( 'pa_' . $wooAttribute );
								$wooAttributeValue = explode( ',', $wooAttributeValue );
								$wooAttributeValue = $wooAttributeValue[0];

								if ( $parentId != '0' ) {
									$product_terms = get_the_terms( $parentId, 'pa_' . $wooAttribute );
								} else {
									$product_terms = get_the_terms( $proIds, 'pa_' . $wooAttribute );
								}
							}
							if ( is_array( $product_terms ) && ! empty( $product_terms ) ) {
								foreach ( $product_terms as $tempkey => $tempvalue ) {
									if ( $tempvalue->slug == $wooAttributeValue ) {
										$wooAttributeValue = $tempvalue->name;
										break;
									}
								}
								if ( isset( $wooAttributeValue ) && ! empty( $wooAttributeValue ) ) {
									$value = $wooAttributeValue;
								} else {
									$value = get_post_meta( $proIds, $metaKey, true );
								}
							} else {
								$value = get_post_meta( $proIds, $metaKey, true );
							}
						} else {
							$wooAttributeValue = $_product->get_attribute( 'pa_' . $wooAttribute );
							$product_terms     = get_the_terms( $proIds, 'pa_' . $wooAttribute );
							if ( is_array( $product_terms ) && ! empty( $product_terms ) ) {
								foreach ( $product_terms as $tempkey => $tempvalue ) {
									if ( $tempvalue->slug == $wooAttributeValue ) {
										$wooAttributeValue = $tempvalue->name;
										break;
									}
								}
								if ( isset( $wooAttributeValue ) && ! empty( $wooAttributeValue ) ) {
									$value = $wooAttributeValue;
								} else {
									$value = get_post_meta( $proIds, $metaKey, true );
								}
							} elseif ( ! empty( $wooAttributeValue ) ) {
								$value = $wooAttributeValue;
							} else {
								$value = get_post_meta( $proIds, $metaKey, true );
							}
						}
					} else {

						$value = get_post_meta( $proIds, $tempProfileData['metakey'], true );
						if ( $tempProfileData['metakey'] == '_thumbnail_id' ) {
							$value = wp_get_attachment_image_url( get_post_meta( $proIds, '_thumbnail_id', true ), 'thumbnail' ) ? wp_get_attachment_image_url( get_post_meta( $proIds, '_thumbnail_id', true ), 'thumbnail' ) : '';
						}
						if ( ! isset( $value ) || empty( $value ) || $value == '' || is_null( $value ) || $value == '0' || $value == 'null' ) {
							if ( $parentId != '0' ) {

								$value = get_post_meta( $parentId, $tempProfileData['metakey'], true );
								if ( $tempProfileData['metakey'] == '_thumbnail_id' ) {
									$value = wp_get_attachment_image_url( get_post_meta( $parentId, '_thumbnail_id', true ), 'thumbnail' ) ? wp_get_attachment_image_url( get_post_meta( $parentId, '_thumbnail_id', true ), 'thumbnail' ) : '';
								}

								if ( ! isset( $value ) || empty( $value ) || $value == '' || is_null( $value ) ) {
									$value = get_post_meta( $proIds, $metaKey, true );

								}
							} else {
								$value = get_post_meta( $proIds, $metaKey, true );
							}
						}
					}
				} else {
					$value = get_post_meta( $proIds, $metaKey, true );
				}
			} else {
				$value = get_post_meta( $proIds, $metaKey, true );
			}
			return $value;
		}

	}
	public function doupload( $file = '', $shopId, $uploadType = '', $isCron = false ) {

		$response = $this->uploadToCatch( $file, $shopId, $uploadType, $isCron );
		return $response;

	}
	public function uploadToCatch( $parameters, $shopId, $uploadType = '', $isCron ) {
		require_once CED_CATCH_DIRPATH . 'admin/catch/lib/catchSendHttpRequest.php';
		if ( $uploadType == 'Product' ) {
			$action = 'products/imports';
		} else {
			$action = 'offers/imports';
		}
		$parameters     = $parameters;
		$sendRequestObj = new Class_Ced_Catch_Send_Http_Request();

		$response = $sendRequestObj->sendHttpRequest( $action, $parameters = array( 'file' => $parameters ), $shopId, $uploadType );
		$response = json_decode( $response, true );
		if ( isset( $response['import_id'] ) ) {
			$importIds   = get_option( 'ced_catch_import_ids_' . $shopId, array() );
			$import_id   = $response['import_id'];
			$importIds[] = $import_id;
			if ( ! $isCron ) {
				update_option( 'ced_catch_import_ids_' . $shopId, $importIds );
				update_option( 'ced_catch_import_type_' . $import_id, $uploadType );
			}
		}
		return $response;
	}
}
