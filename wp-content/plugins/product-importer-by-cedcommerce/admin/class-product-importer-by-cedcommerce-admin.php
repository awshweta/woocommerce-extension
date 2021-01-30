<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       awshweta@gmail.com
 * @since      1.0.0
 *
 * @package    Product_Importer_By_Cedcommerce
 * @subpackage Product_Importer_By_Cedcommerce/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Product_Importer_By_Cedcommerce
 * @subpackage Product_Importer_By_Cedcommerce/admin
 * author     Shweta Awasthi <shwetaawasthi@cedcoss.com>
 */
class Product_Importer_By_Cedcommerce_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * access   private
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
		global $hook_suffix;
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
		 * defined in Product_Importer_By_Cedcommerce_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Product_Importer_By_Cedcommerce_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/product-importer-by-cedcommerce-admin.css', array(), $this->version, 'all' );

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
		 * defined in Product_Importer_By_Cedcommerce_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Product_Importer_By_Cedcommerce_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/product-importer-by-cedcommerce-admin.js', array( 'jquery' ), $this->version, false );
		wp_localize_script($this->plugin_name, 'ajax_object',
			array( 
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			)
		);
		wp_localize_script(
			$this->plugin_name, 'myAjaxObject',
			array( 
				'nonce_verifify' => wp_create_nonce('nonce_verifify')
			) 
		);
	}
	
	/**
	 * This function is used for upload only json file
	 * ced_import_product
	 *
	 * @return void
	 */
	public function ced_import_product() {
		$message = '';
		if (isset( $_POST['nonce_verification'] ) && wp_verify_nonce( sanitize_text_field($_POST['nonce_verification'], 'nonce_verification' ) )) {
			if (isset($_POST['save_upload_file'])) {
				$filename   = isset($_FILES['file']['name']) ? basename(sanitize_text_field($_FILES['file']['name'])) : '';
				$filetype   = isset($_FILES['file']['type']) ? sanitize_text_field($_FILES['file']['type']) : '';
				$ext        = explode('.', $filename);
				$checkext   = $ext[1];
				$upload     = wp_upload_dir();
				$upload_dir = $upload['basedir'];
				$check_file = false;
				$upload_dir = $upload_dir . '/upload_jsonFile/' . $filename;
				if ('json' == $checkext) {
					$ufiles = get_option('uploaded_files');
					if ( empty( $ufiles ) ) {
						$ufiles  = array($filename);
						$message = 'file added successfully';
						update_option('uploaded_files', $ufiles);
					} else {
						if (is_array($ufiles)) {
							foreach ($ufiles as $key=>$value) {
								if ($value == $filename) {
									$message    = 'file already exist';
									$check_file = true;
									break;
								}
							}
						}
						if (false == $check_file) {
							$ufiles[] = $filename;
							$message  = 'file added successfully';
							update_option('uploaded_files', $ufiles);
						}
					}
					$temp_name = isset($_FILES['file']['tmp_name']) ? sanitize_text_field($_FILES['file']['tmp_name']) : '';
					move_uploaded_file($temp_name, $upload_dir);
				}
			}
		}

		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<div id="admin_notice"></div>
			<form class="wc-progress-form-content woocommerce-importer" enctype="multipart/form-data" method="post">
				<div><?php echo esc_attr($message); ?></div>
				<div>
					<input type="hidden" id="nonce_verification" name="nonce_verification" value="<?php wp_create_nonce('generate-nonce'); ?>"/>	
					<input type="file" id="upload" name="file"/>
					<button type="submit" class="button button-primary button-next" value="<?php esc_attr_e( 'Upload file' ); ?>" name="save_upload_file"><?php esc_html_e( 'Upload file'); ?></button>
				</div>
				<h1>Select File</h1>
				<input type="hidden" id="nonce_verification_file" name="nonce_verification_file" value="<?php echo esc_attr(wp_create_nonce('generate-nonce_for_files')); ?>"/>	
				<select id="selected_file">
					<?php 
						$uploaded_files = get_option('uploaded_files');
					if (is_array($uploaded_files)) {
						echo '<option value="" selected>--select--</option>';
						foreach ($uploaded_files as $key=>$value) {
							echo '<option value=' . esc_attr($value) . '>' . esc_attr($value) . '</option>';
						}
					}
					?>
				</select>
			</form>
			<div id="display_json_file_content"></div>
		</div>
	<?php
	}
	
	/**
	 * This function is used to create admin menu page
	 * ced_import_product_menu_page
	 *
	 * @return void
	 */
	public function ced_import_product_menu_page() {
		add_menu_page(
			'Import Product', //menu title
			'Import Product', //menu name
			'manage_options', // capabality
			'import_product', //slug
			array( $this, 'ced_import_product' ), //function
			0, 
			5 //position
		);
	}
	
	/**
	 * This function is for display json file data using wp_list_table
	 * ced_display_json_file_data
	 *
	 * @return void
	 */
	public function ced_display_json_file_data() {
		$nonce_verify = isset( $_POST['verify_nonce_for_file'] ) ? sanitize_text_field($_POST['verify_nonce_for_file']) : '';
		if ( wp_verify_nonce( $nonce_verify, 'nonce_verifify' )) {
			$filename = isset($_POST['file']) ? sanitize_text_field($_POST['file']) : '';
			require 'partials/Display_file_data.php';
			$upload                   = wp_upload_dir();
			$upload_dir               = $upload['basedir'];
			$upload_dir               = $upload_dir . '/upload_jsonFile/' . $filename;
			$file_data                = file_get_contents($upload_dir); 
			$fileData                 = json_decode($file_data, true);
			$Display_File_Data        = new Display_File_Data();
			$Display_File_Data->items = $fileData;
			$Display_File_Data->prepare_items();
			print_r($Display_File_Data->display());
			wp_die();
		}
	}
	
	/**
	 * This function is used to import product
	 * ced_import_json_file_data
	 *
	 * @return void
	 */
	public function ced_import_json_file_data() {
		$verify_nonce_for_import_single_product = isset( $_POST['verify_nonce_for_import_single_product'] ) ? sanitize_text_field($_POST['verify_nonce_for_import_single_product']) : '';
		if ( wp_verify_nonce( $verify_nonce_for_import_single_product, 'nonce_verifify' )) {
			$id       = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : '';
			$filename = isset($_POST['file']) ? sanitize_text_field($_POST['file']) : '';
			require 'partials/Display_file_data.php';
			$upload                   = wp_upload_dir();
			$upload_dir               = $upload['basedir'];
			$upload_dir               = $upload_dir . '/upload_jsonFile/' . $filename;
			$file_data                = file_get_contents($upload_dir); 
			$fileData                 = json_decode($file_data, true);
			$Display_File_Data        = new Display_File_Data();
			$Display_File_Data->items = $fileData;
			$Display_File_Data->import_product($id , $fileData);
			wp_die();
		}
	}
	
	/**
	 * This function is used to import bulk product
	 * ced_bulk_import_product
	 *
	 * @return void
	 */
	public function ced_bulk_import_product() {
		
		$verify_nonce_for_import_bulk = isset( $_POST['verify_nonce_for_import_bulk'] ) ? sanitize_text_field($_POST['verify_nonce_for_import_bulk']) : '';
		if ( wp_verify_nonce( $verify_nonce_for_import_bulk, 'nonce_verifify' )) {
			if ( isset($_POST['selected_id']) ) {
				$ids      = sanitize_key($_POST['selected_id']);
			}
			$filename = isset($_POST['file']) ? sanitize_text_field($_POST['file']) : '';
			require 'partials/Display_file_data.php';
			$upload                   = wp_upload_dir();
			$upload_dir               = $upload['basedir'];
			$upload_dir               = $upload_dir . '/upload_jsonFile/' . $filename;
			$file_data                = file_get_contents($upload_dir); 
			$fileData                 = json_decode($file_data, true);
			$Display_File_Data        = new Display_File_Data();
			$Display_File_Data->items = $fileData;
			if ( is_array( $ids ) ) {
				foreach ($ids as $key=>$id) {
					$Display_File_Data->import_product(sanitize_text_field($id), $fileData);
				}
			}
			wp_die();
		}
	}

}
