<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$file = CED_CATCH_DIRPATH . 'admin/partials/header.php';
if ( file_exists( $file ) ) {
	require_once $file;
}
?>

<div class="ced_catch_account_configuration_wrapper">	
	<div class="ced_catch_account_configuration_fields">		
		<table class="wp-list-table widefat fixed striped ced_catch_account_configuration_fields_table">
			<tbody>				
				<tr>
					<th>
						<label><?php _e( 'Store Id', 'woocommerce-catch-integration' ); ?></label>
					</th>
					<td>
						<label><?php echo $shopDetails['shop_id']; ?></label>
					</td>
				</tr>
				<tr>
					<th>
						<label><?php _e( 'Store Name', 'woocommerce-catch-integration' ); ?></label>
					</th>
					<td>
						<label><?php echo $shopDetails['name']; ?></label>
					</td>
				</tr>
				<tr>
					<th>
						<label><?php _e( 'Store Location', 'woocommerce-catch-integration' ); ?></label>
					</th>
					<td>
						<label><?php echo $shopDetails['location']; ?></label>
					</td>
				</tr>
				<tr>
					<th>
						<label><?php _e( 'Account Status', 'woocommerce-catch-integration' ); ?></label>
					</th>
					<td>
						<?php
						if ( isset( $shopDetails['account_status'] ) && $shopDetails['account_status'] == 'inactive' ) {
							$inactive = 'selected';
							$active   = '';
						} else {
							$active   = 'selected';
							$inactive = '';
						}
						?>
						<select class="ced_catch_select select_boxes" id="ced_catch_account_status">
							<option><?php _e( '--Select Status--', 'woocommerce-catch-integration' ); ?></option>
							<option value="active" <?php echo $active; ?>><?php _e( 'Active', 'woocommerce-catch-integration' ); ?></option>
							<option value="inactive" <?php echo $inactive; ?>><?php _e( 'Inactive', 'woocommerce-catch-integration' ); ?></option>
						</select>
						<a class="ced_catch_update_status_message" data-id="<?php echo $shopDetails['id']; ?>" id="ced_catch_update_account_status" href="javascript:void(0);"><?php _e( 'Update Account Status', 'woocommerce-catch-integration' ); ?></a>
					</td>
				</tr>			
			</tbody>
		</table>
		

	</div>

</div>
