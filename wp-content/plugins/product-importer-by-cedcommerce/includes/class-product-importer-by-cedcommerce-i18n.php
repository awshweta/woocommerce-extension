<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       awshweta@gmail.com
 * @since      1.0.0
 *
 * @package    Product_Importer_By_Cedcommerce
 * @subpackage Product_Importer_By_Cedcommerce/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Product_Importer_By_Cedcommerce
 * @subpackage Product_Importer_By_Cedcommerce/includes
 * author     Shweta Awasthi <shwetaawasthi@cedcoss.com>
 */
class  Product_Importer_By_Cedcommerce_I18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'product-importer-by-cedcommerce',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
