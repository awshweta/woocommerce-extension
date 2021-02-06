<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$shop_id = isset( $_GET['shop_id'] ) ? $_GET['shop_id'] : '';
update_option( 'ced_catch_shop_id', $shop_id );
global $wpdb;
$tableName = $wpdb->prefix . 'ced_catch_accounts';

$sql         = "SELECT * FROM `$tableName` WHERE `shop_id`=" . $shop_id;
$shopDetails = $wpdb->get_row( $sql, 'ARRAY_A' );
if ( isset( $_GET['section'] ) ) {

	$section = $_GET['section'];
}

update_option( 'ced_catch_active_shop', trim( $shop_id ) );


?>
<div class="ced_catch_loader">
	<img src="<?php echo CED_CATCH_URL . 'admin/images/loading.gif'; ?>" width="50px" height="50px" class="ced_catch_loading_img" >
</div>
<div class="success-admin-notices is-dismissible"></div>
<div class="navigation-wrapper">
	<ul class="navigation">
		<li>
			<a href="<?php echo admin_url( 'admin.php?page=ced_catch&section=accounts-view&shop_id=' . $shop_id ); ?>" class="
								<?php
								if ( $section == 'accounts-view' ) {
									echo 'active'; }
								?>
			"><?php _e( 'Account Details', 'woocommerce-catch-integration' ); ?></a>
		</li>
		<li>
			<a href="<?php echo admin_url( 'admin.php?page=ced_catch&section=settings-view&shop_id=' . $shop_id ); ?>" class="
								<?php
								if ( $section == 'settings-view' ) {
									echo 'active'; }
								?>
			"><?php _e( 'Settings', 'woocommerce-catch-integration' ); ?></a>
		</li>
		<li>
			<a class="
			<?php
			if ( $section == 'category-mapping-view' ) {
				echo 'active'; }
			?>
			" href="<?php echo admin_url( 'admin.php?page=ced_catch&section=category-mapping-view&shop_id=' . $shop_id ); ?>"><?php _e( 'Category Mapping', 'woocommerce-catch-integration' ); ?></a>
		</li>
		<li>
			<a class="
			<?php
			if ( $section == 'profiles-view' ) {
				echo 'active'; }
			?>
			" href="<?php echo admin_url( 'admin.php?page=ced_catch&section=profiles-view&shop_id=' . $shop_id ); ?>"><?php _e( 'Profile', 'woocommerce-catch-integration' ); ?></a>
		</li>
		<li>
			<a class="
			<?php
			if ( $section == 'products-view' ) {
				echo 'active'; }
			?>
			" href="<?php echo admin_url( 'admin.php?page=ced_catch&section=products-view&shop_id=' . $shop_id ); ?>"><?php _e( 'Products', 'woocommerce-catch-integration' ); ?></a>
		</li>
		<li>
			<a class="
			<?php
			if ( $section == 'import-status-view' ) {
				echo 'active'; }
			?>
			" href="<?php echo admin_url( 'admin.php?page=ced_catch&section=import-status-view&shop_id=' . $shop_id ); ?>"><?php _e( 'Import Status', 'woocommerce-catch-integration' ); ?></a>
		</li>
		
		<li>
			<a class="
			<?php
			if ( $section == 'orders-view' ) {
				echo 'active'; }
			?>
			" href="<?php echo admin_url( 'admin.php?page=ced_catch&section=orders-view&shop_id=' . $shop_id ); ?>"><?php _e( 'Orders', 'woocommerce-catch-integration' ); ?></a>
		</li>
	</ul>
	<?php
	if ( isset( $shopDetails['name'] ) ) {
		$shopData = json_decode( $shopDetails['shop_data'], true );
		$username = $shopData['contact_informations']['firstname'] . ' ' . $shopData['contact_informations']['lastname'];
		?>
		<span class="ced_catch_current_account_name"><?php echo '<b>Username</b> - <label><b>' . $username . '</b></label>'; ?></span>
		<?php
	}
	?>
</div>
