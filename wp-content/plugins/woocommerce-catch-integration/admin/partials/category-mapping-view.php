<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$file = CED_CATCH_DIRPATH . 'admin/partials/header.php';
if ( file_exists( $file ) ) {
	require_once $file;
}
$shop_id = $_GET['shop_id'];
// $catchCategorieslevel1 = file_get_contents( CED_CATCH_DIRPATH . 'admin/catch/lib/json/categoryLevel-1.json' );
$catchCategorieslevel1 = file_get_contents( CED_CATCH_DIRPATH . 'admin/catch/lib/json/categoryList.json' );

$catchCategorieslevel1 = json_decode( $catchCategorieslevel1, true );
$woo_store_categories  = get_terms( 'product_cat' );
//print_r($woo_store_categories);
?>
<div id="profile_create_message"></div>
<div class="ced_catch_category_mapping_wrapper" id="ced_catch_category_mapping_wrapper">
	<div class="ced_catch_store_categories_listing" id="ced_catch_store_categories_listing">
		<table class="wp-list-table widefat fixed striped posts ced_catch_store_categories_listing_table" id="ced_catch_store_categories_listing_table">
			<thead>
				<th><b><?php _e( 'Select Categories to be Mapped', 'woocommerce-catch-integration' ); ?></b></th>
				<th><b><?php _e( 'WooCommerce Store Categories', 'woocommerce-catch-integration' ); ?></b></th>
				<th colspan="3"><b><?php _e( 'Mapped to Catch Category', 'woocommerce-catch-integration' ); ?></b></th>
				<td><button class="ced_catch_custom_button"  name="ced_catch_refresh_categories" id="ced_catch_category_refresh_button" data-shop_id=<?php echo $_GET['shop_id']; ?> ><?php _e( 'Refresh Categories', 'woocommerce-catch-integration' ); ?></button></td>
			</thead>
			<tbody>
				<?php
				foreach ( $woo_store_categories as $key => $value ) {
					?>
					<tr class="ced_catch_store_category" id="<?php echo 'ced_catch_store_category_' . $value->term_id; ?>">
						<td>
							<input type="checkbox" class="ced_catch_select_store_category_checkbox" name="ced_catch_select_store_category_checkbox[]" data-categoryID="<?php echo $value->term_id; ?>"></input>
						</td>
						<td>
							<span class="ced_catch_store_category_name"><?php echo $value->name; ?></span>
						</td>
						<?php
						$category_mapped_to          = get_term_meta( $value->term_id, 'ced_catch_mapped_category_' . $shop_id, true );
						$alreadyMappedCategoriesName = get_option( 'ced_woo_catch_mapped_categories_name', array() );
						$category_mapped_name_to     = isset( $alreadyMappedCategoriesName[ $shop_id ][ $category_mapped_to ] ) ? $alreadyMappedCategoriesName[ $shop_id ][ $category_mapped_to ] : '';
						//print_r($category_mapped_name_to);

						if ( $category_mapped_to != '' && $category_mapped_to != null && $category_mapped_name_to != '' && $category_mapped_name_to != null ) {
							?>
							<td colspan="4">
								<span>
									<b><?php echo $category_mapped_name_to; ?></b>
								</span>
							</td>
							<?php
						} else {
							?>
							<td colspan="4">
								<span class="ced_catch_category_not_mapped">
									<b><?php _e( 'Category Not Mapped', 'woocommerce-catch-integration' ); ?></b>
								</span>
							</td>
							<?php
						}
						?>
					</tr>
					<tr class="ced_catch_categories" id="<?php echo 'ced_catch_categories_' . $value->term_id; ?>">
						<td></td>
						<td data-catlevel="1">
							<select class="ced_catch_level1_category ced_catch_select_category select2 ced_catch_select2 select_boxes_cat_map" name="ced_catch_level1_category[]" data-level=1 data-storeCategoryID="<?php echo $value->term_id; ?>" data-catchStoreId="<?php echo $_GET['shop_id']; ?>">
								<option value="">--<?php _e( 'Select', 'woocommerce-catch-integration' ); ?>--</option>
							<?php
							foreach ( $catchCategorieslevel1 as $key1 => $value1 ) {
								if ( isset( $value1['label'] ) && $value1['label'] != '' && empty( $value1['parent_code'] ) ) {
									?>
									<option value="<?php echo $value1['code']; ?>"><?php echo $value1['label']; ?></option>	
									<?php
								}
							}
							?>
							</select>
						</td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
	</div>
	<div class="ced_catch_category_mapping_header ced_catch_hidden" id="ced_catch_category_mapping_header">
		<a class="ced_catch_add_button" href="" id="ced_catch_cancel_category_button">
			<?php _e( 'Cancel', 'woocommerce-catch-integration' ); ?>
		</a>
		<button class="ced_catch_add_button" data-catchStoreID="<?php echo $_GET['shop_id']; ?>"  id="ced_catch_save_category_button">
			<?php _e( 'Save', 'woocommerce-catch-integration' ); ?>
		</button>
	</div>

</div>
