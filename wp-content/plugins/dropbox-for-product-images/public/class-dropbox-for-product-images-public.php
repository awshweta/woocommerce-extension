<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       awshweta@gmail.com
 * @since      1.0.0
 *
 * @package    Dropbox_For_Product_Images
 * @subpackage Dropbox_For_Product_Images/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Dropbox_For_Product_Images
 * @subpackage Dropbox_For_Product_Images/public
 * @author     Shweta Awasthi <shwetaawasthi@cedcoss.com>
 */
class Dropbox_For_Product_Images_Public {

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
		 * defined in Dropbox_For_Product_Images_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Dropbox_For_Product_Images_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/dropbox-for-product-images-public.css', array(), $this->version, 'all' );

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
		 * defined in Dropbox_For_Product_Images_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Dropbox_For_Product_Images_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/dropbox-for-product-images-public.js', array( 'jquery' ), $this->version, false );

	}
	
	/**
	 * This function is used to show dropbox images as galery image
	 * ced_add_dropbox_image
	 *
	 * @return void
	 */
	public function ced_add_dropbox_image() {
		$id = get_the_ID();
		$get_url = get_post_meta($id,'url',1);
		$show_dropbox_image_front = get_post_meta($id, "add_feature_image", 1);
		$html = '<ol class="flex-control-nav flex-control-thumbs">';
		if($show_dropbox_image_front == "on") {
			if(!empty($get_url)) {
				foreach($get_url as $key=>$image_url) {
					if($key > 0) {
						$value = str_replace("dl=0","dl=1", $image_url);
						$html .= '<li><img src="'.$value.'" alt="image" class="flex-active"><li>';
					}
				}
				$html .= '</ol>';
				echo $html;
			}
		}
	}
	
	/**
	 * This function is used to show dropbox image on front end single page
	 * ced_dropbox_image_main_image
	 *
	 * @param  mixed $html
	 * @param  mixed $post_id
	 * @return void
	 */
	public function ced_dropbox_image_main_image($html, $post_id) {
		$thumbname_id = $post_id;
		$id =  get_the_ID();
		$get_url = get_post_meta($id,'url',1);
		$show_dropbox_image_front = get_post_meta($id, "add_feature_image", 1);
		if("on" == $show_dropbox_image_front) {
			if(!empty($get_url)) {
				foreach($get_url as $key=>$image_url) {
					if($key == 0) {
						$value = str_replace("dl=0","dl=1", $image_url); ?>
						<img src="<?php echo $value; ?>" alt="image">
					<?php }
				}
			}
		} else {
			return $html;
		}
	}

}
