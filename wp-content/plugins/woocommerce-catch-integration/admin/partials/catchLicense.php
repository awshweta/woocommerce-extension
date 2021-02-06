<div class="ced_catch_loader">
	<img src="<?php echo CED_CATCH_URL . 'admin/images/loading.gif'; ?>" width="50px" height="50px" class="ced_catch_loading_img" >
</div>
<div class="success-admin-notices" ></div>
<div class="ced_catch_wrap">
	<script type="text/javascript" src="<?php echo plugin_dir_url( __FILE__ ); ?>../js/license.js"></script>
	<h2 class="ced_catch_setting_header ced_catch_bottom_margin"><?php _e( 'CATCH LICENSE CONFIGURATION', 'woocommerce-catch-integration' ); ?></h2>
	<div class="ced_catch_license_divs">
		<form method="post">
			<table class="wp-list-table widefat fixed striped ced_catch_config_table">
				<tbody>
					<tr>
						<th class="manage-column">
							<label><b><?php _e( 'Enter License Key', 'woocommerce-catch-integration' ); ?></b></label>
							<input type="text" value="" class="ced_catch_inputs" id="ced_catch_license_key">
						</th>
						<td>
							<input type="button" value="Validate" class="ced_catch_custom_button" id="ced_catch_save_license" class="button button-ced_catch">
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
<div>
