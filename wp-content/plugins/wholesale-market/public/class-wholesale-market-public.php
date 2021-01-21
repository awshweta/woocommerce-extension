<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       awshweta@gmail.com
 * @since      1.0.0
 *
 * @package    Wholesale_Market
 * @subpackage Wholesale_Market/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wholesale_Market
 * @subpackage Wholesale_Market/public
 * @author     Shweta Awasthi <shwetaawasthi@cedcoss.com>
 */
class Wholesale_Market_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wholesale_Market_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wholesale_Market_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wholesale-market-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wholesale_Market_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wholesale_Market_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wholesale-market-public.js', array( 'jquery' ), $this->version, false );

	}	

	/**
	 * This function is used for add checkbox(become a wholesale) field in registration form of woocommerce
	 * ced_add_checkbox_become_wholesale
	 *
	 * @return void
	 */
	public function ced_add_checkbox_become_wholesale() { ?>
		<p>
			<input type="checkbox" name="become_wholesale" id="become_wholesale" class="checkbox"/>
			<label for="become_wholesale"><?php _e( 'Become Wholesale Customer') ?>
			</label>
		</p>
	<?php } 

	// public function ced_validate_checkbox_field($username, $email, $errors ) {
	// 	if ( ! isset( $_POST['become_wholesale'] ) )
    //     $errors->add( 'become_wholesale_error', __( 'become_wholesale are not checked!', 'woocommerce' ) );
    // 	return $errors;
	// }
	public function ced_save_wholesale_checkbox_field($user_id) {
		if(isset($_POST['become_wholesale'])) {
			update_user_meta( $user_id, 'become_wholesale', sanitize_text_field($_POST['become_wholesale']) );
		}
	}

}
