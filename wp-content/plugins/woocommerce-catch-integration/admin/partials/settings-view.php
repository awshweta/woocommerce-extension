<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$file = CED_CATCH_DIRPATH . 'admin/partials/header.php';
if ( file_exists( $file ) ) {
	require_once $file;
}

if ( isset( $_POST['global_settings'] ) ) {
	$settings             = array();
	$settings             = get_option( 'ced_catch_global_settings', array() );
	 
	$settings[ $shop_id ] = isset( $_POST['ced_catch_global_settings'] ) ? $_POST['ced_catch_global_settings'] : array();
	update_option( 'ced_catch_global_settings', $settings );

	$metakeys = isset( $_POST['ced_catch_global_settings']['ced_catch_metakeys'] ) ? $_POST['ced_catch_global_settings']['ced_catch_metakeys'] : array();

	if ( isset( $_POST['ced_catch_global_settings']['ced_catch_inventory_scheduler'] ) ) {
		update_option( 'catch_auto_syncing' . $shop_id, 'on' );
		wp_clear_scheduled_hook( 'ced_catch_inventory_scheduler_job_' . $shop_id );
		wp_schedule_event( time(), 'ced_catch_15min', 'ced_catch_inventory_scheduler_job_' . $shop_id );
	} else {
		wp_clear_scheduled_hook( 'ced_catch_inventory_scheduler_job_' . $shop_id );
		update_option( 'catch_auto_syncing' . $shop_id, 'off' );
	}

	if ( isset( $_POST['ced_catch_global_settings']['ced_catch_auto_accept_order'] ) ) {
		update_option( 'ced_catch_auto_accept_order' . $shop_id, 'on' );
	} else {
		delete_option( 'ced_catch_auto_accept_order' . $shop_id );
	}

	$sync_existing_products = isset( $_POST['ced_catch_sync_existing_products'] ) ? sanitize_text_field( wp_unslash( $_POST['ced_catch_sync_existing_products'] ) ) : '';
	if ( ! empty( $sync_existing_products ) ) {
		$syncing_identifier = isset( $_POST['ced_catch_syncing_identifier'] ) ? $_POST['ced_catch_syncing_identifier'] : '';
		$ced_catch_syncing_identifier_type = isset( $_POST['ced_catch_syncing_identifier_type'] ) ? $_POST['ced_catch_syncing_identifier_type'] : '';
		if ( ! empty( $syncing_identifier ) ) {
			$ced_catch_sync_existing_product = get_option( 'ced_catch_sync_existing_product_data_' . $shop_id, array() );
			$ced_catch_sync_existing_product[ $shop_id ]['sync_existing_product']['default']    = null;
			$ced_catch_sync_existing_product[ $shop_id ]['sync_existing_product']['metakey']    = $syncing_identifier;
			$ced_catch_sync_existing_product[ $shop_id ]['sync_existing_product']['is_enabled'] = $sync_existing_products;
			$ced_catch_sync_existing_product[ $shop_id ]['ced_catch_syncing_identifier_type']['default'] = $ced_catch_syncing_identifier_type;
			update_option( 'ced_catch_sync_existing_product_data_' . $shop_id, $ced_catch_sync_existing_product );
			update_option( 'ced_catch_sync_existing_products_' . $shop_id, 'on' );
		} else {
			delete_option( 'ced_catch_sync_existing_product_data_' . $shop_id );
			delete_option( 'ced_catch_sync_existing_products_' . $shop_id );
		}
	} else {
		delete_option( 'ced_catch_sync_existing_product_data_' . $shop_id );
		delete_option( 'ced_catch_sync_existing_products_' . $shop_id );
	}
}

$attributes      = wc_get_attribute_taxonomies();
$attr_options    = array();
$added_meta_keys = get_option( 'ced_catch_selected_metakeys', false );
if ( $added_meta_keys && count( $added_meta_keys ) > 0 ) {
	foreach ( $added_meta_keys as $meta_key ) {
		$attr_options[ $meta_key ] = $meta_key;
	}
}
if ( ! empty( $attributes ) ) {
	foreach ( $attributes as $attributes_object ) {
		$attr_options[ 'umb_pattr_' . $attributes_object->attribute_name ] = $attributes_object->attribute_label;
	}
}

$renderDataOnGlobalSettings = get_option( 'ced_catch_global_settings', false );
// print_r($renderDataOnGlobalSettings);
require_once CED_CATCH_DIRPATH . 'admin/partials/ced-catch-metakeys-template.php';
?>
<form method="post" action="">
	<div class="navigation-wrapper">
		<div>
			
			<table class="wp-list-table widefat fixed  ced_catch_global_settings_fields_table">
				<thead>
					<tr>
						<th class="ced_catch_settings_heading">
							<label class="basic_heading">
								<?php _e( 'GENERAL DETAILS', 'woocommerce-catch-integration' ); ?>
							</label>
						</th>
					</tr>
				</thead>
				<tbody>	
						<!-- <tr>
						<?php
							$condition = isset( $renderDataOnGlobalSettings[ $shop_id ]['ced_catch_club_eligible'] ) ? $renderDataOnGlobalSettings[ $shop_id ]['ced_catch_club_eligible'] : '';
						?>
							<th>
								<label><?php _e( 'Club Catch Eligible', 'woocommerce-catch-integration' ); ?></label>
							</th>
							<td>
								 <select name="ced_catch_global_settings[ced_catch_club_eligible]" class="ced_catch_select ced_catch_global_select_box select_boxes" data-fieldId="ced_catch_club_eligible">
									<option value="null"><?php _e( '--Select--', 'woocommerce-catch-integration' ); ?></option>
									<option <?php echo ( $condition == 'true' ) ? 'selected' : ''; ?> value="true"><?php _e( 'Yes', 'woocommerce-catch-integration' ); ?></option>
									<option <?php echo ( $condition == 'false' ) ? 'selected' : ''; ?> value="false"><?php _e( 'No', 'woocommerce-catch-integration' ); ?></option>
								</select> 
							</td>
						</tr> -->
						<!-- <tr>
						<?php
							$weight = isset( $renderDataOnGlobalSettings[ $shop_id ]['ced_catch_tax'] ) ? $renderDataOnGlobalSettings[ $shop_id ]['ced_catch_tax'] : '';
						?>
							<th>
								<label><?php _e( 'GST%', 'woocommerce-catch-integration' ); ?></label>
							</th>
							<td>
								<input placeholder="<?php _e( 'Enter Tax Value', 'woocommerce-catch-integration' ); ?>" class="ced_catch_disabled_text_field ced_catch_inputs" type="text" value="<?php echo $weight; ?>" id="ced_catch_tax" name="ced_catch_global_settings[ced_catch_tax]"></input>
							</td>
						</tr>	 -->
						<tr>
							<?php
							$weight = isset( $renderDataOnGlobalSettings[ $shop_id ]['ced_catch_package_weight'] ) ? $renderDataOnGlobalSettings[ $shop_id ]['ced_catch_package_weight'] : '';
							?>
							<th>
								<label><?php _e( 'Package Weight', 'woocommerce-catch-integration' ); ?></label>
							</th>
							<td>
								<input placeholder="<?php _e( 'Enter Package Weight', 'woocommerce-catch-integration' ); ?>" class="ced_catch_disabled_text_field ced_catch_inputs" type="text" value="<?php echo $weight; ?>" id="ced_catch_package_weight" name="ced_catch_global_settings[ced_catch_package_weight]"></input>
							</td>
						</tr>
						<tr>
							<?php
							$length = isset( $renderDataOnGlobalSettings[ $shop_id ]['ced_catch_package_length'] ) ? $renderDataOnGlobalSettings[ $shop_id ]['ced_catch_package_length'] : '';
							?>
							<th>
								<label><?php _e( 'Package Length', 'woocommerce-catch-integration' ); ?></label>
							</th>
							<td>
								<input placeholder="<?php _e( 'Enter Package Length', 'woocommerce-catch-integration' ); ?>" class="ced_catch_disabled_text_field ced_catch_inputs" type="text" value="<?php echo $length; ?>" d="ced_catch_package_length" name="ced_catch_global_settings[ced_catch_package_length]"></input>
							</td>
						</tr>
						<tr>
							<?php
							$height = isset( $renderDataOnGlobalSettings[ $shop_id ]['ced_catch_package_height'] ) ? $renderDataOnGlobalSettings[ $shop_id ]['ced_catch_package_height'] : '';
							?>
							<th>
								<label><?php _e( 'Package Height', 'woocommerce-catch-integration' ); ?></label>
							</th>
							<td>
								<input placeholder="<?php _e( 'Enter Package Height', 'woocommerce-catch-integration' ); ?>" class="ced_catch_disabled_text_field ced_catch_inputs" type="text" value="<?php echo $height; ?>" id="ced_catch_package_height" name="ced_catch_global_settings[ced_catch_package_height]"></input>
							</td>
						</tr>
						<tr>
							<?php
							$width = isset( $renderDataOnGlobalSettings[ $shop_id ]['ced_catch_package_width'] ) ? $renderDataOnGlobalSettings[ $shop_id ]['ced_catch_package_width'] : '';
							?>
							<th>
								<label><?php _e( 'Package Width', 'woocommerce-catch-integration' ); ?></label>
							</th>
							<td>
								<input placeholder="<?php _e( 'Enter Package Width', 'woocommerce-catch-integration' ); ?>" class="ced_catch_disabled_text_field ced_catch_inputs" type="text" value="<?php echo $width; ?>" id="ced_catch_package_width" name="ced_catch_global_settings[ced_catch_package_width]">
							</td>
						</tr>
						<!-- <tr>
						<?php
							$condition = isset( $renderDataOnGlobalSettings[ $shop_id ]['ced_catch_product_condition'] ) ? $renderDataOnGlobalSettings[ $shop_id ]['ced_catch_product_condition'] : '';
						?>
							<th>
								<label><?php _e( 'Product Condition', 'woocommerce-catch-integration' ); ?></label>
							</th>
							<td>
								 <select name="ced_catch_global_settings[ced_catch_product_condition]" class="ced_catch_select ced_catch_global_select_box select_boxes" data-fieldId="ced_catch_product_condition">
									<option value="null"><?php _e( '--Select--', 'woocommerce-catch-integration' ); ?></option>
									<option <?php echo ( $condition == '11' ) ? 'selected' : ''; ?> value="11"><?php _e( 'New Product', 'woocommerce-catch-integration' ); ?></option>
									<option <?php echo ( $condition == '10' ) ? 'selected' : ''; ?> value="10"><?php _e( 'Refurbished Product', 'woocommerce-catch-integration' ); ?></option>
								</select> 
							</td>
						</tr> -->
						<tr>
							<?php
							$markup_type = isset( $renderDataOnGlobalSettings[ $shop_id ]['ced_catch_product_markup_type'] ) ? $renderDataOnGlobalSettings[ $shop_id ]['ced_catch_product_markup_type'] : '';
							?>
							<th>
								<label><?php _e( 'Markup Type', 'woocommerce-catch-integration' ); ?></label>
							</th>
							<td>
								<select name="ced_catch_global_settings[ced_catch_product_markup_type]" class="ced_catch_select ced_catch_global_select_box select_boxes"  data-fieldId="ced_catch_product_markup">
									<option value=""><?php _e( '--Select--', 'woocommerce-catch-integration' ); ?></option>
									<option <?php echo ( $markup_type == 'Fixed_Increased' ) ? 'selected' : ''; ?> value="Fixed_Increased"><?php _e( 'Fixed Increased', 'woocommerce-catch-integration' ); ?></option>
									<option <?php echo ( $markup_type == 'Fixed_Decreased' ) ? 'selected' : ''; ?> value="Fixed_Decreased"><?php _e( 'Fixed Decreased', 'woocommerce-catch-integration' ); ?></option>
									<option <?php echo ( $markup_type == 'Percentage_Increased' ) ? 'selected' : ''; ?> value="Percentage_Increased"><?php _e( 'Percentage Increased', 'woocommerce-catch-integration' ); ?></option>
									<option <?php echo ( $markup_type == 'Yes' ) ? 'selected' : ''; ?> value="Percentage_Decreased"><?php _e( 'Percentage Decreased', 'woocommerce-catch-integration' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th>
								<?php
								$markup_price = isset( $renderDataOnGlobalSettings[ $shop_id ]['ced_catch_product_markup'] ) ? $renderDataOnGlobalSettings[ $shop_id ]['ced_catch_product_markup'] : '';
								?>
								<label><?php _e( 'Markup Price', 'woocommerce-catch-integration' ); ?></label>
							</th>
							<td>
								<input placeholder="<?php _e( 'Enter Markup Price', 'woocommerce-catch-integration' ); ?>" class="ced_catch_disabled_text_field ced_catch_inputs" type="text" value="<?php echo $markup_price; ?>" id="ced_catch_product_markup" name="ced_catch_global_settings[ced_catch_product_markup]"></input>
							</td>
						</tr>
						
						<tr>
							<th class="ced_catch_settings_heading">
								<label class="basic_heading">
									<?php _e( 'SCHEDULER INFORMATION', 'woocommerce-catch-integration' ); ?>
								</label>
							</th>
						</tr>
						<tr>
							<?php
							$isScheduled = isset( $renderDataOnGlobalSettings[ $shop_id ]['ced_catch_inventory_scheduler'] ) ? $renderDataOnGlobalSettings[ $shop_id ]['ced_catch_inventory_scheduler'] : '';
							if ( $isScheduled == 'on' ) {
								$isScheduled = 'checked';
							} else {
								$isScheduled = '';
							}
							?>
							<th>
								<label><?php _e( 'Auto Sync Inventory', 'woocommerce-catch-integration' ); ?></label>
							</th>
							<td>
								<input type="checkbox" name="ced_catch_global_settings[ced_catch_inventory_scheduler]" <?php echo $isScheduled; ?>>
							</td>
						</tr>
						<tr>
							<?php
							$isScheduled = isset( $renderDataOnGlobalSettings[ $shop_id ]['ced_catch_auto_accept_order'] ) ? $renderDataOnGlobalSettings[ $shop_id ]['ced_catch_auto_accept_order'] : '';
							if ( $isScheduled == 'on' ) {
								$isScheduled = 'checked';
							} else {
								$isScheduled = '';
							}
							?>
							<th>
								<label><?php _e( 'Auto accept order', 'woocommerce-catch-integration' ); ?></label>
							</th>
							<td>
								<input type="checkbox" name="ced_catch_global_settings[ced_catch_auto_accept_order]" <?php echo $isScheduled; ?>>
							</td>
						</tr>
						<tr>
							<?php
							$isScheduled = isset( $renderDataOnGlobalSettings[ $shop_id ]['ced_catch_upload_pro_as_a_simple'] ) ? $renderDataOnGlobalSettings[ $shop_id ]['ced_catch_upload_pro_as_a_simple'] : '';
							if ( $isScheduled == 'on' ) {
								$isScheduled = 'checked';
							} else {
								$isScheduled = '';
							}
							?>
							<th>
								<label><?php _e( 'Upload Product As a Simple', 'woocommerce-catch-integration' ); ?></label>
							</th>
							<td>
								<input type="checkbox" name="ced_catch_global_settings[ced_catch_upload_pro_as_a_simple]" <?php echo $isScheduled; ?>>
							</td>
						</tr>
						<tr>
						<th>
							<label><?php esc_html_e( 'Sync Existing Products on Bestbuy on the basis of identifier', 'woocommerce-catch-integration' ); ?></label>
						</th>
						<td>
							<?php
							$sync_existing_products_data     = get_option( 'ced_catch_sync_existing_product_data_' . $shop_id, array() );
							$sync_existing_products_schedule = isset( $sync_existing_products_data[ $shop_id ]['sync_existing_product']['is_enabled'] ) ? $sync_existing_products_data[ $shop_id ]['sync_existing_product']['is_enabled'] : '';
							?>
							<select name="ced_catch_sync_existing_products" class="ced_catch_sync_existing_products">
								<option <?php echo ( '0' == $sync_existing_products_schedule ) ? 'selected' : ''; ?>  value="0"><?php esc_html_e( 'Disabled', 'woocommerce-catch-integration' ); ?></option>
								<option <?php echo ( 'yes' == $sync_existing_products_schedule ) ? 'selected' : ''; ?>  value="yes"><?php esc_html_e( 'Yes', 'woocommerce-catch-integration' ); ?></option>

							</select>
						</td>
					</tr>
					<?php
					$style = 'none';
					if ( $sync_existing_products_schedule == 'yes' ) {
						$style = 'contents';
					}
					?>
					<tr class="ced_catch_auto_sync_existing_products" style="display: <?php echo $style; ?>;">
						<th>
							<label><?php esc_html_e( 'Select the identifier type', 'woocommerce-catch-integration' ); ?></label>
						</th>
						<td>
							<?php
							$sync_existing_products_data = get_option( 'ced_catch_sync_existing_product_data_' . $shop_id, array() );
							$selected_id_type_for_syncing  = isset( $sync_existing_products_data[ $shop_id ]['ced_catch_syncing_identifier_type']['default'] ) ? $sync_existing_products_data[ $shop_id ]['ced_catch_syncing_identifier_type']['default'] : '';
							?>
							<select name="ced_catch_syncing_identifier_type">
								<option value="EAN" <?php echo ( 'EAN' == $selected_id_type_for_syncing ) ? 'selected' : ''; ?>>EAN</option>
								<option value="UPC" <?php echo ( 'UPC' == $selected_id_type_for_syncing ) ? 'selected' : ''; ?>>UPC</option>
							</select>
						</td>
						<th>
							<label><?php esc_html_e( 'Select the Metakey or Attribute where identifier is located', 'woocommerce-catch-integration' ); ?></label>
						</th>
						<td>
							<?php
							$sync_existing_products_data = get_option( 'ced_catch_sync_existing_product_data_' . $shop_id, array() );
							$selected_value_for_syncing  = isset( $sync_existing_products_data[ $shop_id ]['sync_existing_product']['metakey'] ) ? $sync_existing_products_data[ $shop_id ]['sync_existing_product']['metakey'] : '';
							?>
							<select name="ced_catch_syncing_identifier">
								<option value=""><?php esc_html_e( '--Select--' ); ?></option>
								<?php
								if ( is_array( $attr_options ) ) {
									foreach ( $attr_options as $attr_key => $attr_name ) {
										if ( trim( $selected_value_for_syncing == $attr_key ) ) {
											$selected = 'selected';
										} else {
											$selected = '';
										}
										?>
										<option value="<?php echo esc_attr( $attr_key ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_attr( $attr_name ); ?></option>
										<?php
									}
								}
								?>
							</select>
						</td>
						
					</tr>
					</tbody>
					


				</table>
			</div>

		</div>
		
		<div align="right">
			<button id="save_global_settings"  name="global_settings" class="ced_catch_custom_button profile_button" ><?php _e( 'Save', 'woocommerce-catch-integration' ); ?></button>
		</div>
	</form>
