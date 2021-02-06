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
 * @package    Dropbox_For_Product_Images
 * @subpackage Dropbox_For_Product_Images/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Dropbox_For_Product_Images
 * @subpackage Dropbox_For_Product_Images/includes
 * @author     Shweta Awasthi <shwetaawasthi@cedcoss.com>
 */
class Dropbox_For_Product_Images_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'dropbox-for-product-images',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
