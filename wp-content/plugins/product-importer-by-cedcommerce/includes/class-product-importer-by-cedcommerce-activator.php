<?php

/**
 * Fired during plugin activation
 *
 * @link       awshweta@gmail.com
 * @since      1.0.0
 *
 * @package    Product_Importer_By_Cedcommerce
 * @subpackage Product_Importer_By_Cedcommerce/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Product_Importer_By_Cedcommerce
 * @subpackage Product_Importer_By_Cedcommerce/includes
 * author     Shweta Awasthi <shwetaawasthi@cedcoss.com>
 */
class Product_Importer_By_Cedcommerce_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		$upload     = wp_upload_dir();
		$upload_dir = $upload['basedir'];
		$upload_dir = $upload_dir . '/upload_jsonFile';
		//echo $upload_dir;
		if (! is_dir($upload_dir)) {
			mkdir( $upload_dir, 0777 );
		}

		$upload     = wp_upload_dir();
		$upload_dir = $upload['basedir'];
		$upload_dir = $upload_dir . '/upload_order_jsonFile';
		//echo $upload_dir;
		if (! is_dir($upload_dir)) {
			mkdir( $upload_dir, 0777 );
		}
	}

}
