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
 * @package    Wholesale_Market
 * @subpackage Wholesale_Market/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Wholesale_Market
 * @subpackage Wholesale_Market/includes
 * author     Shweta Awasthi <shwetaawasthi@cedcoss.com>
 */
class Wholesale_Market_I18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'wholesale-market',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
