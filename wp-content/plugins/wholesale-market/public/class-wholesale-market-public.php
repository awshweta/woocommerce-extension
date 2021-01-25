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
 * author     Shweta Awasthi <shwetaawasthi@cedcoss.com>
 */
class Wholesale_Market_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
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
		$this->version     = $version;

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
			<input type="hidden" id="become_wholesale_nonce" name="become_wholesale_nonce" value="<?php echo esc_attr(wp_create_nonce('generate-nonce')); ?>">
			<input type="checkbox" name="become_wholesale" id="become_wholesale" class="checkbox"/>
			<label for="become_wholesale"><?php esc_html_e( 'Become Wholesale Customer'); ?>
			</label>
		</p>
	<?php 
	} 

		
	/**
	 * This function is used to save checkbox(become a wholesale) value 
	 * ced_save_wholesale_checkbox_field
	 *
	 * @param  mixed $user_id
	 * @return void
	 */
	public function ced_save_wholesale_checkbox_field( $user_id) {
		if (isset( $_POST['become_wholesale_nonce'] ) && wp_verify_nonce( sanitize_text_field($_POST['become_wholesale_nonce'], 'become_wholesale_nonce' ) )) {
			if (isset($_POST['become_wholesale'])) {
				update_user_meta( $user_id, 'become_wholesale', sanitize_text_field($_POST['become_wholesale']) );
			}
		}
	}

	/**
	 * This function is used to get wholesale price
	 * ced_get_wholesale_price
	 *
	 * @return void
	 */
	public function ced_get_wholesale_price() {
		global $product;
		global $post;
		$product_type                      = $product->get_type();
		$display_wholesale_prices_customer = get_option('display_wholesale_prices');
		if ( 'yes' === get_option( 'check_wholesale_price' ) ) {
			if ('wholesaleCustomer' == $display_wholesale_prices_customer) {
				if (is_user_logged_in()) {
					if (get_user_meta( get_current_user_id(), 'become_wholesale', true) == 'approved') {
						if ('simple' == $product_type) {
							if (get_post_meta($post->ID, 'wholesale_price_for_simple_product', true) != '') {
								echo esc_attr(get_post_meta($post->ID, 'wholesale_price_for_simple_product', true) . get_woocommerce_currency_symbol()) . '</br>';
							}
						}
					}
				}
			}
			if ('allCustomer' == $display_wholesale_prices_customer) {
				if ('simple' == $product_type) {
					if (get_post_meta($post->ID, 'wholesale_price_for_simple_product', true) != '') {
						echo esc_attr(get_post_meta($post->ID, 'wholesale_price_for_simple_product', true) . get_woocommerce_currency_symbol()) . '</br>';
					}
				}
			}
		}
	}

	
	/**
	 * This function is used for display wholesale price on shop page
	 * ced_display_wholesale_price_shop_page
	 *
	 * @return void
	 */
	public function ced_display_wholesale_price_shop_page() {
		$this->ced_get_wholesale_price();
	}
	
	/**
	 * This function is used for display simple wholesale price on single page
	 * ced_display_wholesale_price_single_simple_product
	 *
	 * @return void
	 */
	public function ced_display_wholesale_price_single_simple_product() {
		$this->ced_get_wholesale_price();
	}
	
	/**
	 *  This function is used for display variable wholesale price on single page
	 * ced_show_variation_price
	 *
	 * @param  mixed $descriptions
	 * @param  mixed $product
	 * @param  mixed $variation
	 * @return void
	 */
	public function ced_show_variation_price( $descriptions, $product, $variation) {
		global $product;
		global $post;
		$variationData                     = $variation->get_data();
		$product_type                      = $product->get_type();
		$display_wholesale_prices_customer = get_option('display_wholesale_prices');
		if ( 'yes' === get_option( 'check_wholesale_price' ) ) {
			if ('wholesaleCustomer' == $display_wholesale_prices_customer) {
				if (is_user_logged_in()) {
					if ('approved' == get_user_meta( get_current_user_id(), 'become_wholesale', true)) {
						if ('variable' == $product_type) {
							//$descriptions['price_html'] = get_post_meta($variationData['id'], 'wholesale_price', true);
							if (get_post_meta('' != $descriptions['variation_id'], 'wholesale_price', true)) {
									$descriptions['price_html'] = get_post_meta($descriptions['variation_id'], 'wholesale_price', true) . get_woocommerce_currency_symbol() . '</br>';
							}
						}
					}
				}
			}
			if ('allCustomer' == $display_wholesale_prices_customer ) {
				if ('variable' == $product_type) {
					if ('' != get_post_meta($descriptions['variation_id'], 'wholesale_price', true)) {
						$descriptions['price_html'] = get_post_meta($descriptions['variation_id'], 'wholesale_price', true) . get_woocommerce_currency_symbol() . '</br>';
					}
				}
			}
		}
		return $descriptions;
	}
	
	/**
	 * This function is used for set price according to qty
	 * ced_display_wholesale_price_according_to_qty
	 *
	 * @param  mixed $desc
	 * @return void
	 */
	public function ced_display_wholesale_price_according_to_qty( $desc) {
		$display_wholesale_prices_customer = get_option('display_wholesale_prices');
		$set_wholesale_qty                 =  get_option('set_wholesale_qty');
		foreach ($desc->get_cart() as $key => $value) {
			if ( 'yes' === get_option( 'check_wholesale_price' ) ) {
				if ( 'yes' === get_option( 'wholesale_qty' ) ) {
					if ('wholesaleCustomer' == $display_wholesale_prices_customer ) {
						if (is_user_logged_in()) {
							if (get_user_meta( get_current_user_id(), 'become_wholesale', true) == 'approved') {
								if ('product_level' === $set_wholesale_qty) {
									if ('variation' == $value['data']->get_type()) {
										$get_wholesale_qty = get_post_meta($value['variation_id'], 'wholesale_min_qty', true);
										if ($get_wholesale_qty <= $value['quantity']) {
											if (get_post_meta($value['variation_id'], 'wholesale_price', true) != '') {
												$wholesale_price = get_post_meta($value['variation_id'], 'wholesale_price', true);
												$value['data']->set_price( $wholesale_price );
											}
										}
									}
									if ('simple' == $value['data']->get_type()) {
										$get_simple_wholesale_qty = get_post_meta($value['product_id'], 'wholesale_qty_for_simple_product', true);
										if ($get_simple_wholesale_qty <= $value['quantity']) {
											if (get_post_meta($value['product_id'], 'wholesale_price_for_simple_product', true) != '') {
												$wholesale_simple_price = get_post_meta($value['product_id'], 'wholesale_price_for_simple_product', true);
												$value['data']->set_price( $wholesale_simple_price );
											}
										}
									}
								}
								if ('all_product' === $set_wholesale_qty) {
									if ('variation' == $value['data']->get_type()) {
										$get_wholesale_qty = get_option('set_min_qty_for_all_product');
										if ($get_wholesale_qty <= $value['quantity']) {
											if (get_post_meta($value['variation_id'], 'wholesale_price', true) != '') {
												$wholesale_price = get_post_meta($value['variation_id'], 'wholesale_price', true);
												$value['data']->set_price( $wholesale_price );
											}
										}
									}
									if ($value['data']->get_type() == 'simple') {
										$get_wholesale_qty = get_option('set_min_qty_for_all_product');
										if ($get_wholesale_qty <= $value['quantity']) {
											if (get_post_meta($value['product_id'], 'wholesale_price_for_simple_product', true) != '') {
												$wholesale_price = get_post_meta($value['product_id'], 'wholesale_price_for_simple_product', true);
												$value['data']->set_price( $wholesale_price );
											}
										}
									}
								}
							}
						}
					}
					if ('allCustomer' == $display_wholesale_prices_customer) {
						if ('product_level' === $set_wholesale_qty) {
							if ('variation' == $value['data']->get_type()) {
								$get_wholesale_qty = get_post_meta($value['variation_id'], 'wholesale_min_qty', true);
								if ($get_wholesale_qty <= $value['quantity']) {
									if (get_post_meta($value['variation_id'], 'wholesale_price', true) != '') {
										$wholesale_price = get_post_meta($value['variation_id'], 'wholesale_price', true);
										$value['data']->set_price( $wholesale_price );
									}
								}
							}
							if ('simple' == $value['data']->get_type()) {
								$get_simple_wholesale_qty = get_post_meta($value['product_id'], 'wholesale_qty_for_simple_product', true);
								if ($get_simple_wholesale_qty <= $value['quantity']) {
									if (get_post_meta($value['product_id'], 'wholesale_price_for_simple_product', true) != '') {
										$wholesale_simple_price = get_post_meta($value['product_id'], 'wholesale_price_for_simple_product', true);
										$value['data']->set_price( $wholesale_simple_price );
									}
								}
							}
						}
						if ('all_product' === $set_wholesale_qty) {
							if ('variation' == $value['data']->get_type()) {
								$get_wholesale_qty = get_option('set_min_qty_for_all_product');
								if ($get_wholesale_qty <= $value['quantity']) {
									if (get_post_meta($value['variation_id'], 'wholesale_price', true) != '') {
										$wholesale_price = get_post_meta($value['variation_id'], 'wholesale_price', true);
										$value['data']->set_price( $wholesale_price );
									}
								}
							}
							if ('simple' == $value['data']->get_type()) {
								$get_wholesale_qty = get_option('set_min_qty_for_all_product');
								if ($get_wholesale_qty <= $value['quantity']) {
									if (get_post_meta($value['product_id'], 'wholesale_price_for_simple_product', true) != '') {
										$wholesale_price = get_post_meta($value['product_id'], 'wholesale_price_for_simple_product', true);
										$value['data']->set_price( $wholesale_price );
									}
								}
							}
						}

					}
				}
			}
		}
	}

}
