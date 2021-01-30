<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              awshweta@gmail.com
 * @since             1.0.0
 * @package           Product_Importer_By_Cedcommerce
 *
 * @wordpress-plugin
 * Plugin Name:       Product Importer by CedCommerce
 * Plugin URI:        https://wordpress/wp-content/plugins/product-importer-by-cedcommerce/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Shweta Awasthi
 * Author URI:        awshweta@gmail.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       product-importer-by-cedcommerce
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PRODUCT_IMPORTER_BY_CEDCOMMERCE_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-product-importer-by-cedcommerce-activator.php
 */
function activate_product_importer_by_cedcommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-product-importer-by-cedcommerce-activator.php';
	Product_Importer_By_Cedcommerce_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-product-importer-by-cedcommerce-deactivator.php
 */
function deactivate_product_importer_by_cedcommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-product-importer-by-cedcommerce-deactivator.php';
	Product_Importer_By_Cedcommerce_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_product_importer_by_cedcommerce' );
register_deactivation_hook( __FILE__, 'deactivate_product_importer_by_cedcommerce' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-product-importer-by-cedcommerce.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_product_importer_by_cedcommerce() {

	$plugin = new Product_Importer_By_Cedcommerce();
	$plugin->run();

}
run_product_importer_by_cedcommerce();
