<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       awshweta@gmail.com
 * @since      1.0.0
 *
 * @package    Dropbox_For_Product_Images
 * @subpackage Dropbox_For_Product_Images/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Dropbox_For_Product_Images
 * @subpackage Dropbox_For_Product_Images/admin
 * @author     Shweta Awasthi <shwetaawasthi@cedcoss.com>
 */
class Dropbox_For_Product_Images_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

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
		 * defined in Dropbox_For_Product_Images_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Dropbox_For_Product_Images_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/dropbox-for-product-images-admin.css', array(), $this->version, 'all' );

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
		 * defined in Dropbox_For_Product_Images_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Dropbox_For_Product_Images_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/dropbox-for-product-images-admin.js', array( 'jquery' ), $this->version, false );
		wp_localize_script($this->plugin_name, 'ajax_object',
			array( 
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			)
		);

	}
	
	public function ced_generate_token() {
		if(isset($_POST['save'])) {
			$api_key = isset($_POST['api_key']) ? $_POST['api_key']: '';
			$secret_key = isset($_POST['secret_key']) ? $_POST['secret_key']: '';
			$code = $_GET['code'];

			$data = array(
				'code'=>$code,
				"grant_type" => "authorization_code",
				'redirect_uri' => "http://localhost/wordpress/wp-admin/admin.php?page=create_token"	
			);
	
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,"https://api.dropbox.com/oauth2/token");
			$headers = array(
				"Authorization: Basic ". base64_encode($api_key.':'.$secret_key),
   				//"Content-Type: application/x-www-form-urlencoded",
			);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($data));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$server_output = curl_exec($ch);
			
			update_option("api_key", $api_key);
			update_option("secret_key", $secret_key );
			update_option("server_output", $server_output ); ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_attr_e("token generated successfully") ?></p></div>
			<?php curl_close ($ch);
		}
	 ?>
	 	<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form method="post">
				API KEY : <input type="text" id="api_key" name="api_key" value="">
				API Secret Key : <input type="text" id="secret_key" name="secret_key" value="">
				<input type="button" name="generate_token" id="generate_token" value="Authorization">
				<input type="submit" name="save" id="save" value="Save">
			</form>
		</div>
		
	<?php }
	
	/**
	 * This function is used to create admin menu page
	 * ced_menu_page_for_dropbox
	 *
	 * @return void
	 */
	public function ced_menu_page_for_dropbox() {
		add_menu_page(
			'Dropbox', //menu title
			'Dropbox', //menu name
			'manage_options', // capabality
			'create_token', //slug
			array( $this, 'ced_generate_token' ), //function
			0, 
			5 //position
		);
	}

	

	public function ced_dropbox_upload_image() {
		$get_api_key = get_option("api_key", 1);
		$get_secret_key = get_option("secret_key", 1);
		$get_server_output = get_option("server_output", 1);
		$uploaded_file = $_FILES['file'];
		$get_server_output = json_decode($get_server_output);
		$path ='/upload/'.$_FILES['file']['name'];
		$tmp_name = $_FILES['file']['tmp_name'];
		$acessToken = get_option('access_token', 1);
		$fp = fopen($tmp_name, 'rb');
		$size = filesize($tmp_name);
		$contentType = 'Content-Type: application/octet-stream';
		$args = 'Dropbox-API-Arg: {"path":"' . $path . '", "mode":"add","autorename": true,"mute": false,"strict_conflict": false}';
		$headers = array(
			'Authorization: Bearer ' . $get_server_output->access_token,
			$contentType, 
			$args
		);
		$upload_url = "files/upload";
		$ch = curl_init('https://content.dropboxapi.com/2/'.$upload_url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_PUT, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_INFILE, $fp);
		curl_setopt($ch, CURLOPT_INFILESIZE, $size);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		
		var_dump($response);
		if($response) {
			curl_close($ch);
			$curl = curl_init("https://api.dropboxapi.com/2/sharing/create_shared_link_with_settings");
			$headers = array(
				'Authorization: Bearer ' . $get_server_output->access_token,
				'Content-Type: application/json'
			);
			$data = array(
				'path'=>$path,
			);
			$data = json_encode($data);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($curl, CURLOPT_HEADER, 0);
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS,$data);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			$image_output = curl_exec($curl);
			$image_output = json_decode($image_output, true);
			$id = $_POST['id'];
			$get_url = get_post_meta($id,'url',1);
			if($image_output['url'] != "") {
				if(empty($get_url)) {
					$get_url = array($image_output['url']);
					update_post_meta($id, 'url', $get_url);
				} else {
					$get_url[] = $image_output['url'];
					update_post_meta($id, 'url', $get_url);
				}
			}
			curl_close($curl);
		}
		curl_close($ch);
		wp_die();
	}

	// public function ced_update_checkbox_show_dropbox_image() {
	// 	$id = get_the_ID();
	// 	$show_feature_image = isset($_POST['show_image']) ? $_POST['show_image'] : "";
	// 	update_post_meta($id, "add_feature_image", $show_feature_image);
	// }
	

	public function ced_metabox_for_image() { 
		$id = get_the_ID();
		echo $id;
		$check = get_post_meta($id, "add_feature_image", 1);
		if($check == "on") {
			$checked = "checked";
		} else {
			$checked = "";
		}
		?>
		<div>
			<div id="responce"></div>
			<form id="formData" method="post">
				<input type="hidden" id="nonce_verification" name="nonce_verification" value="<?php echo esc_attr(wp_create_nonce('nonce_verification')); ?>"/>	
				<p><input type="checkbox" id="show_image" data-id="<?php echo get_the_ID(); ?>" name="show_image" <?php echo $checked; ?>/>Enable images to be used as product images</p>
				<input type="file" id="upload"  name="file"/>
				<button type="button" data-id="<?php echo get_the_ID(); ?>" class="button button-primary button-next" id="uploadFile" name="upload_file" value="<?php esc_attr_e( 'Upload file' ); ?>" name="save_upload_file"><?php esc_html_e( 'Upload file'); ?></button>
			</form>
		</div>
	<?php 
		$get_url = get_post_meta($id,'url',1);
		if(!empty($get_url)) {
			foreach($get_url as $key=>$value_url) {
				$value = str_replace("dl=0","dl=1", $value_url); ?>
				<img src="<?php echo $value; ?>" alt="image" width="50px" height="50px">
			<?php }
		}
	}

	public function ced_add_metabox_for_image() {
		$screens = ['product'];
		foreach ( $screens as $screen ) {
			add_meta_box(
				'upload_file',              // Unique ID
				'Upload file',      		// Box title
				array($this, 'ced_metabox_for_image'),  // Content callback, must be of type callable
				$screen                           // Post type
			);
		}

	}

	public function ced_enable_to_show_feature_image() {
		$show_feature_image = isset($_POST['show_image']) ? $_POST['show_image'] : "";
		$id = isset($_POST['id']) ? $_POST['id'] : "";
		update_post_meta($id, "add_feature_image", $show_feature_image);
	}
	
}
