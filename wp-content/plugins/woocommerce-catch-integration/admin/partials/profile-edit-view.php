<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$file = CED_CATCH_DIRPATH . 'admin/partials/header.php';
require_once CED_CATCH_DIRPATH . 'admin/catch/lib/catchCategory.php';
require_once CED_CATCH_DIRPATH . 'admin/partials/products_fields.php';
if ( file_exists( $file ) ) {
	require_once $file;
}

$shop_id = isset( $_GET['shop_id'] ) ? $_GET['shop_id'] : '';

$profileID = isset( $_GET['profileID'] ) ? $_GET['profileID'] : '';

if ( isset( $_POST['add_meta_keys'] ) || isset( $_POST['ced_catch_profile_save_button'] ) ) {
	$is_active       = isset( $_POST['profile_status'] ) ? 'Active' : 'Inactive';
	$marketplaceName = isset( $_POST['marketplaceName'] ) ? $_POST['marketplaceName'] : 'all';

	$updateinfo = array();

	foreach ( $_POST['ced_catch_required_common'] as $key ) {
		$arrayToSave = array();
		isset( $_POST[ $key ][0] ) ? $arrayToSave['default'] = $_POST[ $key ][0] : $arrayToSave['default'] = '';
		if ( $key == '_umb_' . $marketplaceName . '_subcategory' ) {
			isset( $_POST[ $key ] ) ? $arrayToSave['default'] = $_POST[ $key ] : $arrayToSave['default'] = '';
		}
		if ( $key == '_umb_catch_category' && $profileID == '' ) {
			$profileCategoryNames = array();
			for ( $i = 1; $i < 8; $i++ ) {
				$profileCategoryNames[] = isset( $_POST[ 'ced_catch_level' . $i . '_category' ] ) ? $_POST[ 'ced_catch_level' . $i . '_category' ] : '';
			}
			$CategoryNames = array();
			foreach ( $profileCategoryNames as $key1 => $value1 ) {
				if ( isset( $value1[0] ) && ! empty( $value1[0] ) ) {
					$CategoryName = $value1[0];
				}
			}
			$category_id = $CategoryName;
			isset( $_POST[ $key ][0] ) ? $arrayToSave['default'] = $category_id : $arrayToSave['default'] = '';

		}
		isset( $_POST[ $key . '_attibuteMeta' ] ) ? $arrayToSave['metakey'] = $_POST[ $key . '_attibuteMeta' ] : $arrayToSave['metakey'] = 'null';
		$updateinfo[ $key ] = $arrayToSave;
	}

	$updateinfo['selected_product_id']   = isset( $_POST['selected_product_id'] ) ? $_POST['selected_product_id'] : '';
	$updateinfo['selected_product_name'] = isset( $_POST['ced_sears_pro_search_box'] ) ? $_POST['ced_sears_pro_search_box'] : '';

	$updateinfo = json_encode( $updateinfo );

	global $wpdb;
	$tableName = $wpdb->prefix . 'ced_catch_profiles';
	if ( $profileID == '' ) {
		$profileCategoryNames = array();
		for ( $i = 1; $i < 8; $i++ ) {
			$profileCategoryNames[] = isset( $_POST[ 'ced_catch_level' . $i . '_category' ] ) ? $_POST[ 'ced_catch_level' . $i . '_category' ] : '';
		}
		$CategoryNames = array();
		foreach ( $profileCategoryNames as $key => $value ) {

			if ( isset( $value[0] ) && ! empty( $value[0] ) ) {
				$CategoryName = $value[0];
			}
		}

		$profile_category_id = $CategoryName;
		$profileDetails      = array(
			'profile_name'   => $CategoryName,
			'profile_status' => 'active',
			'profile_data'   => $updateinfo,
			'shop_id'        => $_GET['shop_id'],
		);

		global $wpdb;
		$profileTableName = $wpdb->prefix . 'ced_catch_profiles';
		$wpdb->insert( $profileTableName, $profileDetails );
		$profileId = $wpdb->insert_id;

		$profile_edit_url = admin_url( 'admin.php?page=ced_catch&profileID=' . $profileId . '&section=profiles-view&panel=edit&shop_id=' . $_GET['shop_id'] );
		header( 'location:' . $profile_edit_url . '' );
	} elseif ( $profileID ) {
		$wpdb->update(
			$tableName,
			array(
				'profile_status' => $is_active,
				'profile_data'   => $updateinfo,
			),
			array( 'id' => $profileID )
		);
	}
}

global $wpdb;

$tableName = $wpdb->prefix . 'ced_catch_profiles';

$query        = "SELECT * FROM `$tableName` WHERE `id`='$profileID'";
$profile_data = $wpdb->get_results( $query, 'ARRAY_A' );
if ( ! empty( $profile_data ) ) {
	$profile_category_data = json_decode( $profile_data[0]['profile_data'], true );
}
$profile_category_data = isset( $profile_category_data ) ? $profile_category_data : '';
$profile_category_id   = isset( $profile_category_data['_umb_catch_category']['default'] ) ? $profile_category_data['_umb_catch_category']['default'] : '';
$profile_data          = isset( $profile_data[0] ) ? $profile_data[0] : $profile_data;
$attributes            = wc_get_attribute_taxonomies();
$attrOptions           = array();
$addedMetaKeys         = get_option( 'ced_catch_selected_metakeys', array() );
$selectDropdownHTML    = '';

if ( $addedMetaKeys && count( $addedMetaKeys ) > 0 ) {
	foreach ( $addedMetaKeys as $key => $metaKey ) {
		if ( is_array( $metaKey ) ) {
			continue;
		}
		$attrOptions[ $metaKey ] = $metaKey;
	}
}
if ( ! empty( $attributes ) ) {
	foreach ( $attributes as $attributesObject ) {
		$attrOptions[ 'umb_pattr_' . $attributesObject->attribute_name ] = $attributesObject->attribute_label;
	}
}
/* select dropdown setup */
ob_start();
$fieldID             = '{{*fieldID}}';
$selectId            = $fieldID . '_attibuteMeta';
$selectDropdownHTML .= '<select id="' . $selectId . '" name="' . $selectId . '">';
$selectDropdownHTML .= '<option value="null"> -- select -- </option>';
if ( is_array( $attrOptions ) ) {
	foreach ( $attrOptions as $attrKey => $attrName ) :
		$selectDropdownHTML .= '<option value="' . $attrKey . '">' . $attrName . '</option>';
	endforeach;
}
$selectDropdownHTML  .= '</select>';
$attributesFilePath   = CED_CATCH_DIRPATH . 'admin/catch/lib/json/';
$attributes           = file_get_contents( $attributesFilePath . 'catch-attributes.json' );
$OfferAttributes      = file_get_contents( $attributesFilePath . 'offer-attributes.json' );
$OfferAttributes      = json_decode( $OfferAttributes, true );
$productFieldInstance = cedCatchProductsFields::get_instance();
$categorySpecifics    = $productFieldInstance->ced_catch_getCategorySpecificAtrributes( $attributes, $profile_category_id );
if ( ! empty( $categorySpecifics ) ) {
	foreach ( $categorySpecifics as $key => $value ) {
		$attribute_data[] = array(
			'code'  => $value['code'],
			'label' => $value['label'],
		);
	}
	update_option( 'ced_catch_category_attributes_' . $profile_category_id, $attribute_data );
}
if ( ! empty( $OfferAttributes ) ) {
	foreach ( $OfferAttributes as $key => $value ) {
		$attribute_offerdata[] = array(
			'code'  => $value['code'],
			'label' => $value['label'],
		);
	}
	update_option( 'ced_catch_category_offerattributes_' . $profile_category_id, $attribute_offerdata );
}
$fields                = $productFieldInstance->ced_catch_get_custom_products_fields();
$catchCategorieslevel1 = file_get_contents( CED_CATCH_DIRPATH . 'admin/catch/lib/json/categoryLevel-1.json' );
$catchCategorieslevel1 = json_decode( $catchCategorieslevel1, true );
// print_r($catchCategorieslevel1);
?>
		<?php require_once CED_CATCH_DIRPATH . 'admin/partials/ced-catch-metakeys-template.php'; ?>
<form action="" method="post">
	<div class="ced_catch_profile_details_wrapper">
		<div class="ced_catch_profile_details_fields">
			<table>
				<thead>
					<tr>
						<th class="ced_catch_profile_heading ced_catch_settings_heading">
							<label class="basic_heading"><?php _e( 'BASIC DETAILS', 'woocommerce-catch-integration' ); ?></label>
						</th>
					</tr>
				</thead>
				<tbody>		
					<tr>
						<td>
							<label><?php _e( 'Profile Name', 'woocommerce-catch-integration' ); ?></label>
						</td>
						<?php

						if ( isset( $profile_data['profile_name'] ) ) {

							?>
							<td>
								<label><b><?php echo ucwords( $profile_data['profile_name'] ); ?></b></label>
							</td>
						</tr>
							<?php
						} else {
							?>

						<td data-catlevel="1" id="ced_catch_categories_in_profile">
							<select class="ced_catch_select_category_on_add_profile select2 ced_catch_select2 ced_catch_level1_category"  name="ced_catch_level1_category[]" data-level=1 data-catchStoreId="<?php echo $_GET['shop_id']; ?>">
								<option value=""><?php _e( '--Select--', 'woocommerce-catch-integration' ); ?></option>
								<?php
								foreach ( $catchCategorieslevel1 as $key1 => $value1 ) {
									if ( isset( $value1['label'] ) && $value1['label'] != '' ) {
										?>
										<option value="<?php echo $value1['code']; ?>"><?php echo $value1['label']; ?></option>	
										<?php
									}
								}
								?>
							</select>
						</td>
						<td><a><?php _e( '( Please Select Profile Name )', 'woocommerce-catch-integration' ); ?></a></td>
							<?php
						}

						?>
					<tr>

						<th  class="ced_catch_profile_heading ced_catch_settings_heading">
							<label class="basic_heading"><?php _e( 'PRODUCT SPECIFIC', 'woocommerce-catch-integration' ); ?></label>
						</th>

					</tr>
					<tr>
						<?php
						$requiredInAnyCase = array( '_umb_id_type', '_umb_id_val', '_umb_brand' );
						global $global_CED_catch_Render_Attributes;
						$marketPlace        = 'ced_catch_required_common';
						$productID          = 0;
						$categoryID         = '';
						$indexToUse         = 0;
						$selectDropdownHTML = $selectDropdownHTML;
						if ( ! empty( $profile_data ) ) {
							$data = json_decode( $profile_data['profile_data'], true );
						}
						foreach ( $fields as $value ) {
							$isText   = true;
							$field_id = trim( $value['fields']['id'], '_' );
							if ( in_array( $value['fields']['id'], $requiredInAnyCase ) ) {
								$attributeNameToRender  = ucfirst( $value['fields']['label'] );
								$attributeNameToRender .= '<span class="ced_catch_wal_required">' . __( '[ Required ]', 'woocommerce-catch-integration' ) . '</span>';
							} else {
								$attributeNameToRender = ucfirst( $value['fields']['label'] );
							}
							$default = isset( $data[ $value['fields']['id'] ]['default'] ) ? $data[ $value['fields']['id'] ]['default'] : '';
							echo '<tr class="form-field _umb_id_type_field ">';
							if ( $value['type'] == '_select' ) {
								$valueForDropdown = $value['fields']['options'];
								if ( $value['fields']['id'] == '_umb_id_type' ) {
									unset( $valueForDropdown['null'] );
								}
								$valueForDropdown = apply_filters( 'ced_catch_alter_data_to_render_on_profile', $valueForDropdown, $field_id );
								$productFieldInstance->renderDropdownHTML(
									$field_id,
									$attributeNameToRender,
									$valueForDropdown,
									$categoryID,
									$productID,
									$marketPlace,
									$value['fields']['description'],
									$indexToUse,
									array(
										'case'  => 'profile',
										'value' => $default,
									)
								);
								$isText = false;
							} elseif ( $value['type'] == '_text_input' ) {
								$productFieldInstance->renderInputTextHTML(
									$field_id,
									$attributeNameToRender,
									$categoryID,
									$productID,
									$marketPlace,
									$value['fields']['description'],
									$indexToUse,
									array(
										'case'  => 'profile',
										'value' => $default,
									)
								);
							} elseif ( $value['type'] == '_hidden' ) {
								$productFieldInstance->renderInputTextHTMLhidden(
									$field_id,
									$attributeNameToRender,
									$categoryID,
									$productID,
									$marketPlace,
									$value['fields']['description'],
									$indexToUse,
									array(
										'case'  => 'profile',
										'value' => $profile_category_id,
									)
								);
								$isText = false;
							} else {
								$isText = false;
							}

							echo '<td>';
							if ( $isText ) {
								$previousSelectedValue = 'null';
								if ( isset( $data[ $value['fields']['id'] ]['metakey'] ) && $data[ $value['fields']['id'] ]['metakey'] != 'null' ) {
									$previousSelectedValue = $data[ $value['fields']['id'] ]['metakey'];
								}
								$updatedDropdownHTML = str_replace( '{{*fieldID}}', $value['fields']['id'], $selectDropdownHTML );
								$updatedDropdownHTML = str_replace( 'value="' . $previousSelectedValue . '"', 'value="' . $previousSelectedValue . '" selected="selected"', $updatedDropdownHTML );
								echo $updatedDropdownHTML;
							}
							echo '</td>';
							echo '</tr>';
						}

						?>
					</tr>
					<tr>
						<?php
						if ( ! empty( $OfferAttributes ) ) {
							?>
							<th  class="ced_catch_profile_heading ced_catch_settings_heading">
								<label class="basic_heading"><?php _e( 'OFFER SPECIFIC', 'woocommerce-catch-integration' ); ?></label>
							</th>

						</tr>
							<?php
							foreach ( $OfferAttributes as $key => $value ) {

								$isText              = true;
								$profile_category_id = str_replace( ' ', '_', $profile_category_id );
								$field_id            = trim( $value['code'], '_' );

								$default  = isset( $data[ $profile_category_id . '_' . $value['code'] ] ) ? $data[ $profile_category_id . '_' . $value['code'] ] : '';
								$default  = isset( $default['default'] ) ? $default['default'] : '';
								$required = '';
								echo '<tr class="form-field _umb_brand_field ">';
								if ( $value['type'] == 'LIST' ) {
									$categoryFileInstance = new Class_Ced_Catch_Category();
									// $folderName = CED_CATCH_DIRPATH.'admin/catch/lib/json/';
									// $valueLists = $folderName.'Values_lists.json';
									$valueForDropdown = $value['values_list'];

									$tempValueForDropdown = array();
									foreach ( $valueForDropdown as $key1 => $_value ) {
										$tempValueForDropdown[ $_value['code'] ] = $_value['label'];

									}
									$valueForDropdown = $tempValueForDropdown;
									// $valueForDropdown = $valueForDropdown[$value['code']];
									/*
									if( $value['required'] )
									{*/
									$required = 'required';
									// }

									$productFieldInstance->renderDropdownHTML(
										$field_id,
										ucfirst( $value['label'] ),
										$valueForDropdown,
										$profile_category_id,
										$productID,
										$marketPlace,
										$value['label'],
										$indexToUse,
										array(
											'case'  => 'profile',
											'value' => $default,
										),
										$required
									);
									$isText = false;
								} elseif ( $value['type'] == 'TEXT' ) {

									if ( $value['required'] ) {
										$required = 'required';
									}
									if ( $value['label'] == 'Price' || $value['label'] == 'Discount Start Date' || $value['label'] == 'Discount End Date' || $value['label'] == 'Best Before Date' || $value['label'] == 'Expiry Date' || $value['label'] == 'Available Start Date' || $value['label'] == 'Available End Date' ) {
										$isText = false;
									}

									$productFieldInstance->renderInputTextHTML(
										$field_id,
										ucfirst( $value['label'] ),
										$profile_category_id,
										$productID,
										$marketPlace,
										$value['label'],
										$indexToUse,
										array(
											'case'  => 'profile',
											'value' => $default,
										),
										$required,
										''
									);
								}


								echo '<td>';
								if ( $isText ) {
									$previousSelectedValue = 'null';
									if ( isset( $data[ $profile_category_id . '_' . $value['code'] ] ) && $data[ $profile_category_id . '_' . $value['code'] ] != 'null' ) {

										$previousSelectedValue = $data[ $profile_category_id . '_' . $value['code'] ]['metakey'];
									}
									$updatedDropdownHTML = str_replace( '{{*fieldID}}', $profile_category_id . '_' . $value['code'], $selectDropdownHTML );
									$updatedDropdownHTML = str_replace( 'value="' . $previousSelectedValue . '"', 'value="' . $previousSelectedValue . '" selected="selected"', $updatedDropdownHTML );
									echo $updatedDropdownHTML;
								}
								echo '</td>';
								echo '</tr>';
							}
						}
						?>
					</tr>
					<tr>
						<?php
						if ( $profileID != '' && ! empty( $categorySpecifics ) ) {
							?>
							<th  class="ced_catch_profile_heading ced_catch_settings_heading">
								<label class="basic_heading"><?php _e( 'CATEGORY SPECIFIC', 'woocommerce-catch-integration' ); ?></label>
							</th>

						</tr>
							<?php
							foreach ( $categorySpecifics as $key => $value ) {
								$isText              = true;
								$profile_category_id = str_replace( ' ', '_', $profile_category_id );
								$field_id            = trim( $value['code'], '_' );

								$default  = isset( $data[ $profile_category_id . '_' . $value['code'] ] ) ? $data[ $profile_category_id . '_' . $value['code'] ] : '';
								$default  = isset( $default['default'] ) ? $default['default'] : '';
								$required = '';
								echo '<tr class="form-field _umb_brand_field ">';
								if ( $value['type'] == 'LIST' ) {
									$categoryFileInstance = new Class_Ced_Catch_Category();
									$folderName           = CED_CATCH_DIRPATH . 'admin/catch/lib/json/';
									$valueLists           = $folderName . 'Values_lists.json';
									$valueForDropdown     = file_get_contents( $valueLists );
									$valueForDropdown     = json_decode( $valueForDropdown, true );
									if ( empty( $valueForDropdown ) && $valueForDropdown == '' ) {
										$categoryFileInstance->ced_catch_valueLists( $shop_id );
									}
									$tempValueForDropdown = array();
									foreach ( $valueForDropdown['values_lists'] as $key1 => $_value ) {
										if ( $_value['code'] == $value['code'] ) {
											$tempValueForDropdown[ $_value['code'] ] = $_value['values'];
										}
									}
									$valueForDropdown = $tempValueForDropdown;
									$valueForDropdown = $valueForDropdown[ $value['code'] ];
									/*
									if( $value['required'] )
									{*/
									$required = 'required';
									// }

									$productFieldInstance->renderDropdownHTMLForCategorySpecifics(
										$field_id,
										ucfirst( $value['label'] ),
										$valueForDropdown,
										$profile_category_id,
										$productID,
										$marketPlace,
										$value['label'],
										$indexToUse,
										array(
											'case'  => 'profile',
											'value' => $default,
										),
										$required
									);
									$isText = false;
								} elseif ( $value['type'] == 'DECIMAL' || $value['type'] == 'INTEGER' || $value['type'] == 'TEXT' ) {

									/*
									if( $value['required'] )
									{*/
									$required = 'required';
									// }
									$productFieldInstance->renderInputTextHTML(
										$field_id,
										ucfirst( $value['label'] ),
										$profile_category_id,
										$productID,
										$marketPlace,
										$value['label'],
										$indexToUse,
										array(
											'case'  => 'profile',
											'value' => $default,
										),
										$required,
										'',
										$value['type']
									);
								} else {
									/*
									if( $value['required'] )
									{*/
									$required = 'required';
									// }

									$global_CED_catch_Render_Attributes->renderInputTextHTML(
										$field_id,
										ucfirst( $value['label'] ),
										$profile_category_id,
										$productID,
										$marketPlace,
										$value['label'],
										$indexToUse,
										array(
											'case'  => 'profile',
											'value' => $default,
										),
										$required
									);
								}

								echo '<td>';
								if ( $isText ) {
									$previousSelectedValue = 'null';
									if ( isset( $data[ $profile_category_id . '_' . $value['code'] ] ) && $data[ $profile_category_id . '_' . $value['code'] ] != 'null' ) {

										$previousSelectedValue = $data[ $profile_category_id . '_' . $value['code'] ]['metakey'];
									}
									$updatedDropdownHTML = str_replace( '{{*fieldID}}', $profile_category_id . '_' . $value['code'], $selectDropdownHTML );
									$updatedDropdownHTML = str_replace( 'value="' . $previousSelectedValue . '"', 'value="' . $previousSelectedValue . '" selected="selected"', $updatedDropdownHTML );
									echo $updatedDropdownHTML;
								}
								echo '</td>';
								echo '</tr>';
							}
						}
						?>
					</tbody>
				</table>
			</div>
		</div>
		<div>
			<button class="ced_catch_custom_button save_profile_button" name="ced_catch_profile_save_button" ><?php _e( 'Save Profile', 'woocommerce-catch-integration' ); ?></button>

		</div>
	</form>

