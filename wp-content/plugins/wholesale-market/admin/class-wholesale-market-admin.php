<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       awshweta@gmail.com
 * @since      1.0.0
 *
 * @package    Wholesale_Market
 * @subpackage Wholesale_Market/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wholesale_Market
 * @subpackage Wholesale_Market/admin
 */
class Wholesale_Market_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wholesale-market-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wholesale-market-admin.js', array( 'jquery' ), $this->version, false );
	}
	
	/**
	 * This function is used to add wholesale market tab
	 * ced_add_setting_wholesale_market
	 *
	 * @param  mixed $settings_tabs
	 * @return void
	 */
	public function ced_add_setting_wholesale_market( $settings_tabs) {
		$settings_tabs['wholesale-market'] = __( 'Wholesale Market' );
		return $settings_tabs;
	}
	
	/**
	 * This function is used to create section
	 * get_sections_field
	 *
	 * @return void
	 */
	public function get_sections_field() {
	
		$sections = array(
			''         => __( 'General', 'my-textdomain' ),
			'inventory' => __( 'Inventory', 'my-textdomain' )
		);
				
		return apply_filters( 'woocommerce_get_sections_wholesale-market', $sections );
	}

	
	/**
	 * This function is used to get sections
	 * ced_get_sections
	 *
	 * @return void
	 */
	public function ced_get_sections() {
		
		global $current_section;

		$sections = $this->get_sections_field();

		if ( empty( $sections ) || 1 === count( $sections ) ) {
			return;
		}

		echo '<ul class="subsubsub">';

		$array_keys = array_keys( $sections );

		foreach ( $sections as $id => $label ) {
			echo '<li><a href="' . esc_attr(admin_url( 'admin.php?page=wc-settings&tab=wholesale-market&section=' . sanitize_title( $id ) )) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . esc_attr($label) . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
		}

		echo '</ul><br class="clear" />';
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function ced_get_settings( $current_section) {
		if ( 'inventory' == $current_section ) {
			
			$settings = apply_filters( 'wholesale-market_inventory_settings', array(
					
				array(
					'name' => __( 'Inventory' ),
					'type' => 'title',
					'desc' => '',
					'id'   => 'inventory-section',
				),

				array(
					'type'     => 'checkbox',
					'id'       => 'wholesale_qty',
					'name'     => __('enable min. qty setting'),
					'desc'     => __('enable min. qty for applying wholesale price'),
					'default'         => 'yes',
				),
					
				array(
					'title'    => __( 'Set Min qty' ),
					'id'       => 'set_wholesale_qty',
					'default'  => 'all_product',
					'type'     => 'radio',
					'desc_tip' => __( 'This option is important as it will affect how you input prices. Changing it will not update existing products.', 'woocommerce' ),
					'options'  => array(
						'product_level' => __( 'Set Min qty on product level' ),
						'all_product'  => __( 'Set common min qty for all products' ),
					),
				),

				array(
					'title'    => __( 'Set Min Wholesale Quantity' ),
					'id'       => 'set_min_qty_for_all_product',
					'default'  => '',
					'type'     => 'number',
					'custom_attributes' => array( 
						'min'		=> 0,
					),
					'value'            => 1,
					'desc_tip' => 'This option is used to set common quantity for all product',
				),
					
				array(
					'type' => 'sectionend',
					'id'   => 'inventory-section'
				),
					
			) );
					
		} else {
					
			$settings = apply_filters( 'wholesale-market_settings', array(
				
				array(
					'name' => __('General'),
					'type' => 'title',
					'desc' => '',
					'id'   => 'general-section',
				),

				array(
					'type'     => 'checkbox',
					'id'       => 'check_wholesale_price',
					'name'     => __('Enable wholesale pricing'),
					'desc'     => __('Enable/Disable wholesale pricing'),
					'default'         => 'yes',
				),

				array(
					'title'    => __( 'Display price to users' ),
					'id'       => 'display_wholesale_prices',
					'default'  => 'wholesaleCustomer',
					'type'     => 'radio',
					'desc_tip' => __( 'This option is important as it will affect how you input prices. Changing it will not update existing products.', 'woocommerce' ),
					'options'  => array(
						'allCustomer' => __( 'Display wholesale price to all users' ),
						'wholesaleCustomer'  => __( 'Display wholesale price to only wholesale customer', 'woocommerce' ),
					),
				),
				array(
					'title'    => __( ' Wholesale Price Suffix' ),
					'desc'     => __( 'Text Field to store what text should be shown with Wholesale Price.', 'woocommerce' ),
					'id'       => 'wholesale_price_suffix',
					'default'  => '',
					'type'     => 'text',
					'desc_tip' => true,
				),

				array(
					'type' => 'sectionend',
					'id'   => 'general-section'
				),
				
			) );
				
		}
		
		return apply_filters( 'woocommerce_get_settings_wholesale-market', $settings, $current_section);
	}

	/**
	 * Output the settings.
	 */
	public function ced_output_section() {
		global $current_section;
		$settings = $this->ced_get_settings($current_section);
		WC_Admin_Settings::output_fields( $settings );
	}
	
	/**
	 * This function is used to validate min qty setting field
	 * ced_validate_woocommerce_admin_settings_sanitize_option
	 *
	 * @param  mixed $value
	 * @param  mixed $option
	 * @param  mixed $raw_value
	 * @return void
	 */
	public function ced_validate_woocommerce_admin_settings_sanitize_option( $value, $option, $raw_value ) {
		$error   = false;
		$message = '';
		$name    = $option['id'];
		if ('set_min_qty_for_all_product' == $name) {
			if (0 > $value  || '' == $value) {
				$message =  '<div>Quantity field is required and can not be negative</div>';
				$error   = true;
			}
		} 
		if (true == $error) {
			wp_die(esc_attr($message));
		} else {
			return $value;
		}
	}

	/**
	 * Save settings.
	 */
	public function ced_save_setting() {
		global $current_section;
		$settings = $this->ced_get_settings($current_section);
		WC_Admin_Settings::save_fields( $settings );
	}
	
	/**
	 * This function is used for add wholesale price field for variable product
	 * ced_variation_wholesale_fields
	 *
	 * @param  mixed $loop
	 * @param  mixed $variation_data
	 * @param  mixed $variation
	 * @return void
	 */
	public function ced_variation_wholesale_fields( $loop, $variation_data, $variation) {
		if ( 'yes' === get_option( 'check_wholesale_price' ) ) {
			woocommerce_wp_text_input( 
				array( 
					'id'          => 'wholesale_price[' . $variation->ID . ']', 
					'label'       => __( 'Wholesale Price' ), 
					'placeholder' => '',
					'desc_tip'    => 'true',
					'description' => __('Enter Wholesale Price here'),
					'type'              => 'number',
					'custom_attributes' => array(
						'min' => 0,
					),
					'class' => 'variation_wholesale_price',
					'value'       => get_post_meta( $variation->ID, 'wholesale_price', true )
				)
			);

			woocommerce_wp_text_input( 
				array( 
					'id'          => 'wholesale_price_nonce_for_variation', 
					'label' => '',
					'placeholder' => '',
					'type'              => 'hidden',
					'class' => 'wholesale_price_nonce_for_variation',
					'value'       => wp_create_nonce('generate-nonce')
				)
			);
		
			if ( 'yes' === get_option( 'wholesale_qty' ) ) {
				if ( 'product_level' === get_option( 'set_wholesale_qty' ) ) {
					woocommerce_wp_text_input( 
						array( 
							'id'          => 'wholesale_qty[' . $variation->ID . ']', 
							'label'       => __( 'Wholesale Quantity' ), 
							'placeholder' => '',
							'desc_tip'    => 'true',
							'type'              => 'number',
							'custom_attributes' => array(
								'min' => 0,
							),
							'description' => __('Enter Wholesale Quantity here'),
							'value'       => get_post_meta( $variation->ID, 'wholesale_min_qty', true )
						)
					);
				}
			}
		}
	}
	
	/**
	 * This function is used for save wholesale price value for variable product
	 * ced_save_variation_wholesale
	 *
	 * @param  mixed $post_id
	 * @return void
	 */
	public function ced_save_variation_wholesale( $post_id) {
		if (isset( $_POST['wholesale_price_nonce_for_variation'] ) && wp_verify_nonce( sanitize_text_field($_POST['wholesale_price_nonce_for_variation'], 'wholesale_price_nonce_for_variation' ) )) {
			$wholesale_price = isset($_POST['wholesale_price'][ $post_id ]) ? sanitize_text_field($_POST['wholesale_price'][ $post_id ]) : '';
			$wholesale_qty   = isset($_POST['wholesale_qty'][ $post_id ]) ? sanitize_text_field($_POST['wholesale_qty'][ $post_id ]) : '';
			$check           = false;
			if ($wholesale_price < 0 || $wholesale_qty < 0) {
				$check = true;
				wp_die('Quantity and Price field can not be negative');
			}
			if (false == $check ) {
				if ( ! empty( $wholesale_price ) ) {
					update_post_meta( $post_id, 'wholesale_price', $wholesale_price );
				}
				if ( ! empty( $wholesale_qty ) ) {
					update_post_meta( $post_id, 'wholesale_min_qty', $wholesale_qty );
				}
			}
		}
	}
		
	/**
	 * This function is used for add wholesale price field for simple product
	 * ced_add_wholesale_price_simple_product
	 *
	 * @return void
	 */
	public function ced_add_wholesale_price_simple_product() {
		if ( 'yes' === get_option( 'check_wholesale_price' ) ) {
			woocommerce_wp_text_input( array(
				'id' => 'wholesale_price_for_simple_product',
				'label' => 'Wholesale Price',
				'type'              => 'number',
					'custom_attributes' => array(
						'min' => 0,
					),
				'description' => 'This is a custom field, you can write here anything you want.',
				'desc_tip' => 'true',
			) );
			woocommerce_wp_text_input( 
				array( 
					'id'          => 'wholesale_price_nonce_for_simple', 
					'label' => '',
					'placeholder' => '',
					'type'              => 'hidden',
					'class' => 'wholesale_price_nonce_for_simple',
					'value'       => wp_create_nonce('generate-nonce')
				)
			);
			if ( 'yes' === get_option( 'wholesale_qty' ) ) {
				if ( 'product_level' === get_option( 'set_wholesale_qty' ) ) {
					woocommerce_wp_text_input( 
						array( 
							'id' => 'wholesale_qty_for_simple_product',
							'label' => 'Wholesale Quantity',
							'type'              => 'number',
							'custom_attributes' => array(
								'min' => 0,
							),
							'description' => 'This is a custom field, you can write here anything you want.',
							'desc_tip' => 'true',
						)
					);
				}
			}
		}
	}
	
	/**
	 * This function is used for save wholesale price value for simple product
	 * ced_save_simple_product_wholesale_price
	 *
	 * @param  mixed $post_id
	 * @return void
	 */
	public function ced_save_simple_product_wholesale_price( $post_id ) {
		if (isset( $_POST['wholesale_price_nonce_for_simple'] ) && wp_verify_nonce( sanitize_text_field($_POST['wholesale_price_nonce_for_simple'], 'wholesale_price_nonce_for_simple' ) )) {
			$wholesale_price_simple_product   = isset($_POST['wholesale_price_for_simple_product']) ? sanitize_text_field($_POST['wholesale_price_for_simple_product']) : '';
			$wholesale_qty_for_simple_product = isset($_POST['wholesale_qty_for_simple_product']) ? sanitize_text_field($_POST['wholesale_qty_for_simple_product']) : '';
			$regular_price = isset($_POST['_regular_price']) ? sanitize_text_field($_POST['_regular_price']) : '';
			$check                            = false;
			if ($wholesale_price_simple_product < 0 || $wholesale_qty_for_simple_product < 0) {
				$check = true;
				wp_die('quantity and price field can not be negative');
			}
			if ($wholesale_price_simple_product > $regular_price) {
				$check = true;
				wp_die('wholesale price field must be less than Regular price');
			}
			if (false == $check) {
				if ( ! empty( $_POST['wholesale_price_for_simple_product'] ) ) {
					update_post_meta( $post_id, 'wholesale_price_for_simple_product', esc_attr( $wholesale_price_simple_product ) );
				}
				if ( ! empty( $_POST['wholesale_qty_for_simple_product'] ) ) {
					update_post_meta( $post_id, 'wholesale_qty_for_simple_product', esc_attr( $wholesale_qty_for_simple_product ) );
				}
			}
		}
	}
	
	/**
	 * This function is used for add custom column in user listing page
	 * ced_add_custom_wholesale_columns
	 *
	 * @param  mixed $column
	 * @return void
	 */
	public function ced_add_custom_wholesale_columns( $column) {
		$column['Wholesale'] = 'Wholesale';
		return $column;
	}
	
	/**
	 * This function is used for add value of wholesale column
	 * ced_add_value_wholesale_columns
	 *
	 * @param  mixed $output
	 * @param  mixed $column_name
	 * @param  mixed $user_id
	 * @return void
	 */
	public function ced_add_value_wholesale_columns( $output, $column_name, $user_id) { 
		$checkbox_value = get_user_meta( $user_id, 'become_wholesale', true);
		if ('on' == $checkbox_value ) {
			if ( 'Wholesale' == $column_name ) {
				return '<form id="approveform" method="get"><input type="hidden" name="user_id" value="' . $user_id . '"><input type="submit" data-id="' . $user_id . '" id="approve_customer_as_wholesale' . $user_id . '" class="approve_customer_as_wholesale" name="approve_customer_as_wholesale' . $user_id . '" value="approve""></form>';
			}
			return $output;
		}
	}
	
	/**
	 * This function is used for approve wholesale  Customer
	 * ced_approved_wholesale_customer
	 *
	 * @return void
	 */
	public function ced_approved_wholesale_customer() {
		$user_id = isset($_GET['user_id']) ? sanitize_text_field($_GET['user_id']) : '';
		if (isset($_GET['approve_customer_as_wholesale' . $user_id . ''])) {
			update_user_meta( $user_id, 'become_wholesale', 'approved');
			$result = wp_update_user( array( 'ID' => $user_id, 'role' => 'Wholesale-Customer' ) );
		}
	}
	
	/**
	 * This function is used for add role Wholesale-Customer
	 * ced_add_role_wholesale_customer
	 *
	 * @param  mixed $user_id
	 * @return void
	 */
	public function ced_add_role_wholesale_customer( $user_id) {
		add_role('Wholesale-Customer', __(
			'Wholesale-Customer'),
			array(
				'read'            => true, // Allows a user to read
			)
		);
	}
}
