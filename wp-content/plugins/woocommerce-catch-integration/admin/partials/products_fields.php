<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 *
 * @since      1.0.0
 *
 * @package    Woocommerce catch Integration
 * @subpackage Woocommerce catch Integration/admin/helper
 */

if ( ! class_exists( 'cedCatchProductsFields' ) ) {

	/**
	 * single product related functionality.
	 *
	 * Manage all single product related functionality required for listing product on marketplaces.
	 *
	 * @since      1.0.0
	 * @package    Woocommerce catch Integration
	 * @subpackage Woocommerce catch Integration/admin/helper
	 * @author     CedCommerce <cedcommerce.com>
	 */
	class cedCatchProductsFields {

		/**
		 * The Instace of CED_catch_product_fields.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      $_instance   The Instance of CED_catch_product_fields class.
		 */
		private static $_instance;

		/**
		 * CED_catch_product_fields Instance.
		 *
		 * Ensures only one instance of CED_catch_product_fields is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @return CED_catch_product_fields instance.
		 */
		public static function get_instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		public static function ced_catch_get_custom_products_fields() {
			$required_fields = array(
				array(
					'type'   => '_hidden',
					'id'     => '_umb_catch_category',
					'fields' => array(
						'id'          => '_umb_catch_category',
						'label'       => __( 'Category Name', 'woocommerce-catch-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Specify the category name.', 'woocommerce-catch-integration' ),
						'type'        => 'hidden',
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_select',
					'id'     => '_ced_catch_product_reference_type',
					'fields' => array(
						'id'          => '_ced_catch_product_reference_type',
						'label'       => __( 'Product Reference Type', 'woocommerce-catch-integration' ) . '<span class="ced_catch_wal_required"> [ ' . __( 'Required', 'woocommerce-catch-integration' ) . ' ]</span>',
						'desc_tip'    => true,
						'description' => __( 'Specify the Product Reference Type.', 'woocommerce-catch-integration' ),
						'type'        => 'select',
						'options'     => array(
							'ean' => __( 'EAN' ),
							'upc' => __( 'UPC' ),
							'mpn' => __( 'MPN' ),
						),
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => '_ced_catch_product_reference_value',
					'fields' => array(
						'id'          => '_ced_catch_product_reference_value',
						'label'       => __( 'Product Reference Value', 'woocommerce-catch-integration' ) . '<span class="ced_catch_wal_required"> [ ' . __( 'Required', 'woocommerce-catch-integration' ) . ' ]</span>',
						'desc_tip'    => true,
						'description' => __( 'Specify the catch reference value.', 'woocommerce-catch-integration' ),
						'type'        => 'text',
						'class'       => 'wc_input_price',
						'placeholder' => 'Please enter valid reference number',
					),
				),
				/*
				array(
					'type' => '_select',
					'id' => '_ced_catch_condition',
					'fields' => array(
						'id'                => '_ced_catch_condition',
						'label'             => __( ' Product Condition', 'woocommerce-catch-integration' ).'<span class="ced_catch_wal_required"> [ '.__( 'Required', 'woocommerce-catch-integration' ).' ]</span>',
						'desc_tip'          => true,
						'description'       => __( 'Specify the Condition.', 'woocommerce-catch-integration' ),
						'type'              => 'select',
						'options'           => array(
							'11'  => __('New', 'woocommerce-catch-integration'),
							'13' => __('Refurbished', 'woocommerce-catch-integration')
						),
						'class'             => 'wc_input_price'
					)
				),*/
				/*
				array(
					'type' => '_select',
					'id' => '_ced_catch_club_eligible',
					'fields' => array(
						'id'                => '_ced_catch_club_eligible',
						'label'             => __( 'Club Catch Eligible', 'woocommerce-catch-integration' ).'<span class="ced_catch_wal_required"> [ '.__( 'Required', 'woocommerce-catch-integration' ).' ]</span>',
						'desc_tip'          => true,
						'description'       => __( 'Specifies whether club catch eligible or not.', 'woocommerce-catch-integration' ),
						'type'              => 'select',
						'options'           => array(
							'true'     => __('Yes'),
							'false'      => __('No')
						),
						'class'             => 'wc_input_price'
					)
				),*/

				array(
					'type'   => '_text_input',
					'id'     => '_ced_catch_brand',
					'fields' => array(
						'id'          => '_ced_catch_brand',
						'label'       => __( 'Brand', 'woocommerce-catch-integration' ) . '<span class="ced_catch_wal_required"> [ ' . __( 'Required', 'woocommerce-catch-integration' ) . ' ]</span>',
						'desc_tip'    => true,
						'description' => __( 'Specifies the item brand.', 'woocommerce-catch-integration' ),
						'type'        => 'text',
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => '_ced_title_prefix',
					'fields' => array(
						'id'          => '_ced_title_prefix',
						'label'       => __( 'Title Prefix', 'woocommerce-catch-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Specifies the item brand.', 'woocommerce-catch-integration' ),
						'type'        => 'text',
						'class'       => 'wc_input_price',
					),
				),

				array(
					'type' => '_text_input',
					'id' => '_ced_image_size_chart',
					'fields' => array(
						'id'                => '_ced_image_size_chart',
						'label'             => __( 'Size chart url', 'woocommerce-catch-integration' ),
						'desc_tip'          => true,
						'description'       => __( 'Specifies whether the product contains sexual content.', 'woocommerce-catch-integration' ),
						'type'              => 'text',
						'class'             => 'wc_input_price'
					)
				),
				array(
					'type'   => '_text_input',
					'id'     => '_ced_catch_keywords',
					'fields' => array(
						'id'          => '_ced_catch_keywords',
						'label'       => __( 'Keywords', 'woocommerce-catch-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Specifies the keywords for a product.', 'woocommerce-catch-integration' ),
						'type'        => 'text',
						'class'       => 'wc_input_price',
					),
				),

				array(
					'type'   => '_text_input',
					'id'     => '_ced_catch_weight',
					'fields' => array(
						'id'          => '_ced_catch_weight',
						'label'       => __( 'Item Weight', 'woocommerce-catch-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Specifies the item weight.', 'woocommerce-catch-integration' ),
						'type'        => 'text',
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => '_ced_catch_package_length',
					'fields' => array(
						'id'          => '_ced_catch_package_length',
						'label'       => __( 'Package Length', 'woocommerce-catch-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Specifies the Package Length.', 'woocommerce-catch-integration' ),
						'type'        => 'text',
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => '_ced_catch_package_width',
					'fields' => array(
						'id'          => '_ced_catch_package_width',
						'label'       => __( 'Package Width', 'woocommerce-catch-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Specifies the Package Width.', 'woocommerce-catch-integration' ),
						'type'        => 'text',
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => '_ced_catch_package_height',
					'fields' => array(
						'id'          => '_ced_catch_package_height',
						'label'       => __( 'Package Height', 'woocommerce-catch-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Specifies the Package Height.', 'woocommerce-catch-integration' ),
						'type'        => 'text',
						'class'       => 'wc_input_price',
					),
				),

				/*
				array(
					'type' => '_text_input',
					'id' => '_ced_catch_tax',
					'fields' => array(
						'id'                => '_ced_catch_tax',
						'label'             => __( 'GST %', 'woocommerce-catch-integration' ).'<span class="ced_catch_wal_required"> [ '.__( 'Required', 'woocommerce-catch-integration' ).' ]</span>',
						'desc_tip'          => true,
						'description'       => __( 'Specifies the tax rate.', 'woocommerce-catch-integration' ),
						'type'              => 'text',
						'class'             => 'wc_input_price'
					)
				),*/
				array(
					'type'   => '_select',
					'id'     => '_ced_catch_markup_type',
					'fields' => array(
						'id'          => '_ced_catch_markup_type',
						'label'       => __( 'Markup Type', 'woocommerce-catch-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Specify the Markup Price.', 'woocommerce-catch-integration' ),
						'type'        => 'select',
						'options'     => array(
							'Fixed_Increased'      => __( 'Fixed_Increased' ),
							'Fixed_Decreased'      => __( 'Fixed_Decreased' ),
							'Percentage_Increased' => __( 'Percentage_Increased' ),
							'Percentage_Decreased' => __( 'Percentage_Decreased' ),
						),
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => '_ced_catch_markup_price',
					'fields' => array(
						'id'          => '_ced_catch_markup_price',
						'label'       => __( 'Markup Price', 'woocommerce-catch-integration' ),
						'desc_tip'    => true,
						'description' => __( 'Specifies the Markup Price.', 'woocommerce-catch-integration' ),
						'type'        => 'text',
						'class'       => 'wc_input_price',
					),
				),
			);

return $required_fields;
}


		/**
		 *
		 * function for render dropdown html
		 */
		function renderDropdownHTML( $attribute_id, $attribute_name, $values, $categoryID, $productID, $marketPlace, $attribute_description = null, $indexToUse, $additionalInfo = array( 'case' => 'product' ), $is_required = '' ) {
			$fieldName = $categoryID . '_' . $attribute_id;

			if ( $additionalInfo['case'] == 'product' ) {
				$previousValue = get_post_meta( $productID, $fieldName, true );
			} else {
				$previousValue = $additionalInfo['value'];
			}

			?><input type="hidden" name="<?php echo $marketPlace . '[]'; ?>" value="<?php echo $fieldName; ?>" />

			<td>
				<label for=""><?php echo $attribute_name; ?>
				<?php
				if ( $is_required == 'required' ) {
					?>
					<span class="ced_catch_wal_required">
						<?php
						_e( '[Required]' )
						?>
					</span>
					<?php
				}
				?>
			</label>
		</td>
		<td>
			<select id="" name="<?php echo $fieldName . '[' . $indexToUse . ']'; ?>" class="select short" style="">
				<?php
				echo '<option value="">' . __( '-- Select --' ) . '</option>';
				foreach ( $values as $key => $value ) {
					if ( $previousValue == $key ) {
						echo '<option value="' . $key . '" selected>' . $value . '</option>';
					} else {
						echo '<option value="' . $key . '">' . $value . '</option>';
					}
				}
				?>
			</select>
		</td>
		
		<?php
	}
	function renderDropdownHTMLForCategorySpecifics( $attribute_id, $attribute_name, $values, $categoryID, $productID, $marketPlace, $attribute_description = null, $indexToUse, $additionalInfo = array( 'case' => 'product' ), $is_required = '' ) {
		$fieldName = $categoryID . '_' . $attribute_id;

		if ( $additionalInfo['case'] == 'product' ) {
			$previousValue = get_post_meta( $productID, $fieldName, true );
		} else {
			$previousValue = $additionalInfo['value'];
		}

		?>
		<input type="hidden" name="<?php echo $marketPlace . '[]'; ?>" value="<?php echo $fieldName; ?>" />
		
		<td>
			<label for=""><?php echo $attribute_name; ?>
			<?php
			if ( $is_required == 'required' ) {
				?>
				<span class="ced_catch_wal_required">
					<?php
					_e( '[Required]' )
					?>
				</span>
				<?php
			}
			?>
		</label>
	</td>
	<td>
		<select id="" name="<?php echo $fieldName . '[' . $indexToUse . ']'; ?>" class="select short" style="">
			<?php
			echo '<option value="">' . __( '-- Select --' ) . '</option>';
			foreach ( $values as $key => $value ) {
				if ( $previousValue == $value['code'] ) {
					echo '<option value="' . $value['code'] . '" selected>' . $value['label'] . '</option>';
				} else {
					echo '<option value="' . $value['code'] . '">' . $value['label'] . '</option>';
				}
			}
			?>
		</select>
	</td>

	<?php
}

		/**
		 *
		 * function to render input fields
		 */
		function renderInputTextHTML( $attribute_id, $attribute_name, $categoryID, $productID, $marketPlace, $attribute_description = null, $indexToUse, $additionalInfo = array( 'case' => 'product' ), $conditionally_required = false, $conditionally_required_text = '', $valueType = '', $input_type = '' ) {
			if ( $attribute_name == 'Price' ) {
				return;
			}
			if ( $attribute_name == 'Discount Start Date' || $attribute_name == 'Discount End Date' || $attribute_name == 'Best Before Date' || $attribute_name == 'Expiry Date' || $attribute_name == 'Available Start Date' || $attribute_name == 'Available End Date' ) {
				$input_type = 'date';
			} else {
				$input_type = 'text';
			}

			global $post,$product,$loop;
			$fieldName = $categoryID . '_' . $attribute_id;
			if ( $additionalInfo['case'] == 'product' ) {
				$previousValue = get_post_meta( $productID, $fieldName, true );
			} else {
				$previousValue = $additionalInfo['value'];
			}

			?>

			<input type="hidden" name="<?php echo $marketPlace . '[]'; ?>" value="<?php echo $fieldName; ?>" />
			<td>
				<label for=""><?php echo $attribute_name; ?>
				<?php
				if ( $conditionally_required == 'required' ) {
					?>
					<span class="ced_catch_wal_required"><?php _e( '[Required]' ); ?></span>
					<?php
				}
				?>
			</label>
		</td>
		<td>
			<?php
			if ( $valueType == 'Date' ) {
				$placeholder = $valueType;
			} else {
				$placeholder = '';
			}

			?>
			
			<input class="short" style="" name="<?php echo $fieldName . '[' . $indexToUse . ']'; ?>" id="" value="<?php echo $previousValue; ?>" placeholder="<?php echo $placeholder; ?>" type="<?php echo $input_type; ?>" /> 
		</td>
		
		<?php
	}
		/**
		 *
		 * function to render hidden input fields
		 */
		function renderInputTextHTMLhidden( $attribute_id, $attribute_name, $categoryID, $productID, $marketPlace, $attribute_description = null, $indexToUse, $additionalInfo = array( 'case' => 'product' ), $conditionally_required = false, $conditionally_required_text = '' ) {
			global $post,$product,$loop;
			$fieldName = $categoryID . '_' . $attribute_id;
			if ( $additionalInfo['case'] == 'product' ) {
				$previousValue = get_post_meta( $productID, $fieldName, true );
			} else {
				$previousValue = $additionalInfo['value'];
			}

			?>

			<input type="hidden" name="<?php echo $marketPlace . '[]'; ?>" value="<?php echo $fieldName; ?>" />
			<td>
				<!--<label for=""><?php echo $attribute_name; ?>-->
				</label>
			</td>
			<td>
				<label></label>
				<input class="short" style="" name="<?php echo $fieldName . '[' . $indexToUse . ']'; ?>" id="" value="<?php echo $previousValue; ?>" placeholder="" type="hidden" /> 
			</td>
			

			<?php
		}

		 /**
		  *
		  * function to fetch category specific attributes
		  */
		 public function ced_catch_getCategorySpecificAtrributes( $attributes = '', $profile_category_id = '' ) {
		 	$attributes = json_decode( $attributes, true );
		 	if ( ! empty( $attributes ) ) {
		 		$categorySpecific = array();
		 		foreach ( $attributes['attributes'] as $key => $value ) {
		 			if ( $value['hierarchy_code'] != '' ) {
		 				if ( $value['hierarchy_code'] == $profile_category_id ) {
		 					$categorySpecific[] = $value;
		 				}
		 			}
		 		}
		 	}
		 	return $categorySpecific;
		 }

		}
	}
