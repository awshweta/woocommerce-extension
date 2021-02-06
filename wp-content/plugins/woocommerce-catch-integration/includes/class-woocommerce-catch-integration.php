<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://cedcommerce.com
 * @since      1.0.0
 *
 * @package    Woocommerce_Catch_Integration
 * @subpackage Woocommerce_Catch_Integration/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Woocommerce_Catch_Integration
 * @subpackage Woocommerce_Catch_Integration/includes
 * @author     CedCommerce <plugins@cedcommerce.com>
 */
class Woocommerce_Catch_Integration {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Woocommerce_Catch_Integration_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WOOCOMMERCE_CATCH_INTEGRATION_VERSION' ) ) {
			$this->version = WOOCOMMERCE_CATCH_INTEGRATION_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'woocommerce-catch-integration';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Woocommerce_Catch_Integration_Loader. Orchestrates the hooks of the plugin.
	 * - Woocommerce_Catch_Integration_i18n. Defines internationalization functionality.
	 * - Woocommerce_Catch_Integration_Admin. Defines all hooks for the admin area.
	 * - Woocommerce_Catch_Integration_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**getOffers
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommerce-catch-integration-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommerce-catch-integration-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woocommerce-catch-integration-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-woocommerce-catch-integration-public.php';

		$this->loader = new Woocommerce_Catch_Integration_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Woocommerce_Catch_Integration_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Woocommerce_Catch_Integration_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Woocommerce_Catch_Integration_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		/* ADD MENUS AND SUBMENUS */
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'ced_catch_add_menus', 24 );
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'ced_catch_add_order_metabox', 24 );
		$this->loader->add_action( 'save_post', $plugin_admin, 'ced_catch_save_metadata', 24 );

		$this->loader->add_filter( 'ced_add_marketplace_menus_array', $plugin_admin, 'ced_catch_add_marketplace_menus_to_array', 14 );
		$this->loader->add_filter( 'ced_marketplaces_logged_array', $plugin_admin, 'ced_catch_marketplace_to_be_logged' );
		$this->loader->add_action( 'ced_catch_license_panel', $plugin_admin, 'ced_catch_license_panel' );
		$this->loader->add_action( 'wp_ajax_ced_catch_validate_licensce', $plugin_admin, 'ced_catch_validate_licensce_callback' );
		$this->loader->add_action( 'wp_ajax_nopriv_ced_catch_validate_licensce', $plugin_admin, 'ced_catch_validate_licensce_callback' );
		$this->loader->add_filter( 'ced_catch_license_check', $plugin_admin, 'ced_catch_license_check_function', 10, 1 );
		$this->loader->add_action( 'wp_ajax_ced_catch_authorise_account', $plugin_admin, 'ced_catch_authorise_account' );
		$this->loader->add_action( 'wp_ajax_ced_catch_category_refresh_button', $plugin_admin, 'ced_catch_category_refresh_button' );
		$this->loader->add_action( 'wp_ajax_ced_catch_fetch_next_level_category', $plugin_admin, 'ced_catch_fetch_next_level_category' );
		$this->loader->add_action( 'wp_ajax_ced_catch_profiles_on_pop_up', $plugin_admin, 'ced_catch_profiles_on_pop_up' );
		$this->loader->add_action( 'wp_ajax_ced_catch_fetch_next_level_category_add_profile', $plugin_admin, 'ced_catch_fetch_next_level_category_add_profile' );
		$this->loader->add_action( 'wp_ajax_ced_catch_save_profile_through_popup', $plugin_admin, 'ced_catch_save_profile_through_popup' );
		$this->loader->add_action( 'wp_ajax_ced_catch_map_categories_to_store', $plugin_admin, 'ced_catch_map_categories_to_store' );
		$this->loader->add_action( 'wp_ajax_ced_catch_change_account_status', $plugin_admin, 'ced_catch_change_account_status' );
		$this->loader->add_action( 'wp_ajax_ced_catch_fetch_custom_fields', $plugin_admin, 'ced_catch_fetch_custom_fields' );
		$this->loader->add_action( 'wp_ajax_ced_catch_process_bulk_action', $plugin_admin, 'ced_catch_process_bulk_action' );
		$this->loader->add_action( 'wp_ajax_ced_catch_manual_fetch_orders', $plugin_admin, 'ced_catch_manual_fetch_orders' );
		$this->loader->add_action( 'wp_ajax_ced_catch_save_operation_mode', $plugin_admin, 'ced_catch_save_operation_mode' );
		$this->loader->add_action( 'wp_ajax_ced_catch_get_import_status', $plugin_admin, 'ced_catch_get_import_status' );
		$this->loader->add_action( 'wp_ajax_ced_catch_get_import_report', $plugin_admin, 'ced_catch_get_import_report' );
		$this->loader->add_action( 'wp_ajax_ced_catch_get_integration_report', $plugin_admin, 'ced_catch_get_integration_report' );
		$this->loader->add_action( 'wp_ajax_ced_catch_process_catch_orders', $plugin_admin, 'ced_catch_process_catch_orders' );
		$this->loader->add_filter( 'cron_schedules', $plugin_admin, 'my_catch_cron_schedules' );
		$this->loader->add_filter( 'woocommerce_product_data_tabs', $plugin_admin, 'ced_catch_custom_product_tabs' );
		$this->loader->add_filter( 'woocommerce_product_data_panels', $plugin_admin, 'inventory_options_product_tab_content' );
		$this->loader->add_action( 'woocommerce_product_after_variable_attributes', $plugin_admin, 'ced_catch_render_product_fields', 10, 3 );
		$this->loader->add_action( 'woocommerce_save_product_variation', $plugin_admin, 'ced_catch_save_product_fields', 10, 2 );
		$this->loader->add_action( 'woocommerce_process_product_meta', $plugin_admin, 'ced_catch_save_product_fields', 10, 2 );

		global $wpdb;
		$tableName   = $wpdb->prefix . 'ced_catch_accounts';
		$sql         = "SELECT `shop_id` FROM `$tableName` WHERE `account_status` = 'active' ";
		$activeShops = $wpdb->get_results( $sql, 'ARRAY_A' );
		foreach ( $activeShops as $key => $value ) {
			$this->loader->add_action( 'ced_catch_inventory_scheduler_job_' . $value['shop_id'], $plugin_admin, 'ced_catch_inventory_schedule_manager' );
			$this->loader->add_action( 'ced_catch_integration_report_' . $value['shop_id'], $plugin_admin, 'ced_catch_update_integration_report' );
			$this->loader->add_action( 'ced_catch_Fetch_orders_' . $value['shop_id'], $plugin_admin, 'ced_catch_Fetch_orders' );
			$this->loader->add_action( 'ced_catch_Fetch_orders_by_id_' . $value['shop_id'], $plugin_admin, 'ced_catch_Fetch_orders_by_id' );
			$this->loader->add_action( 'ced_catch_sync_products_' . $value['shop_id'], $plugin_admin, 'ced_catch_sync_products' );
			$this->loader->add_action( 'ced_catch_sync_existing_products_' . $value['shop_id'], $plugin_admin, 'ced_catch_sync_products_using_identifier' );

		}

			$this->loader->add_action( 'wp_ajax_ced_catch_search_product_name', $plugin_admin, 'ced_catch_search_product_name' );
		$this->loader->add_action( 'wp_ajax_ced_catch_get_product_metakeys', $plugin_admin, 'ced_catch_get_product_metakeys' );
		$this->loader->add_action( 'wp_ajax_ced_catch_process_metakeys', $plugin_admin, 'ced_catch_process_metakeys' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Woocommerce_Catch_Integration_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Woocommerce_Catch_Integration_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
