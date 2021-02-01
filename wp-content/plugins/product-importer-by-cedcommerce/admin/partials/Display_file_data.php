<?php 
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once  ABSPATH . 'wp-admin/includes/class-wp-list-table.php' ;
}


class Display_File_Data extends WP_List_Table {

	public $items;
	public $_column_headers;
	public $columns;

	/** Class constructor */
	public function __construct() {
		parent::__construct( [
			'singular' => __( 'Product', 'sp' ), //singular name of the listed records
			'plural'   => __( 'Products', 'sp' ), //plural name of the listed records
			'ajax'     => false //should this table support ajax?

		]);
	}
	
	/**
	 * Function prepare_items
	 *
	 * @return void
	 */
	public function prepare_items() {

		/** Process bulk action */
		$this->process_bulk_action();
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers =array($columns, $hidden, $sortable);
	}

	/**
	* Columns to make sortable.
	*
	* @return array
	*/
	public function get_sortable_columns() {
		$sortable_columns = array(
			'name' => array( 'name', true ),
		);
		return $sortable_columns;
	}
	
	/**
	 * Render the bulk edit checkbox
	 * 
	 * @param array $item
	 * 
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf(
		'<input type="checkbox" name="bulk-import[]" class="bulk-import" value="%s" />', $item['item']['item_id']
		);
	}
	
	/**
	 * Function column_default
	 *
	 * @param  mixed $item
	 * @param  mixed $column_name
	 * @return void
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) { 
			case 'images':
				return '<img src=' . $item['item'][$column_name][0] . ' alt="image">';
			case 'name':
				return $item['item'][$column_name];
			case 'item_sku':
				return $item['item'][$column_name];
			case 'price':
				return $item['item'][$column_name];
			case 'type': 
				if ( $item['item']['has_variation']) {
					return 'variable';
				} else {
					return 'simple';
				}
			case 'action':
				return '<input type="button" data-id=' . $item['item']['item_id'] . ' id="import"  name="import" value="import">';
			default:
				return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
		}
	}
	  
	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = [
			'cb' => '<input type="checkbox" />',
			'images' => __( 'Image' ),
			'name' => __( 'Title' ),
			'item_sku' => __( 'Sku' ),
			'price' => __( 'Price' ),
			'type' => __( 'Type' ),
			'action' => __( 'Action' ),
		];

		return $columns;
	}
	
	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = [
		'bulk-import' => 'Import'
		];
		return $actions;
	}
	
	/**
	 * This function is used to create product
	 * ced_create_product
	 *
	 * @param  mixed $product_data
	 * @param  mixed $parent
	 * @param  mixed $post_type
	 * @return void
	 */
	public function ced_create_product( $product_data, $parent, $post_type) {
		//print_r($product_data);
		if ($parent > 0) {
			$description = '';
		} else {
			$description = $product_data['item']['description'];
		}
		$id = wp_insert_post( array(
			'post_content' => sanitize_text_field($description),
			'post_title' => sanitize_text_field($product_data['item']['name']),
			'post_status' => 'publish',
			'post_parent' => sanitize_text_field($parent),
			'post_type' => $post_type
		)); 
		return $id; 
	}
		
	/**
	 * This function is used to create post meta data for product
	 * ced_create_post_meta
	 *
	 * @param  mixed $pid
	 * @param  mixed $product_data
	 * @return void
	 */
	public function ced_create_post_meta( $pid, $product_data) {
		update_post_meta( $pid, '_price', sanitize_text_field($product_data['item']['price']));
		update_post_meta( $pid, '_weight', sanitize_text_field($product_data['item']['weight']));
		update_post_meta( $pid, '_stock', sanitize_text_field($product_data['item']['stock']));
		update_post_meta( $pid, '_sku', sanitize_text_field($product_data['item']['item_sku']));
		update_post_meta( $pid, '_length', sanitize_text_field($product_data['item']['package_length']));
		update_post_meta( $pid, '_width', sanitize_text_field($product_data['item']['package_width']));
		update_post_meta( $pid, '_visibility', 'visible' );
		update_post_meta( $pid, '_stock_status', 'instock');
		update_post_meta( $pid, 'total_sales', '0' );
		update_post_meta( $pid, '_downloadable', 'no' );
		update_post_meta( $pid, '_virtual', 'no' );
		update_post_meta( $pid, '_regular_price', sanitize_text_field($product_data['item']['original_price']) );
		update_post_meta( $pid, '_sale_price', '' );
		update_post_meta( $pid, '_purchase_note', '' );
		update_post_meta( $pid, '_featured', 'no' );
		update_post_meta( $pid, '_height', '' );
		update_post_meta( $pid, '_sale_price_dates_from', '' );
		update_post_meta( $pid, '_sale_price_dates_to', '' );
		update_post_meta( $pid, '_sold_individually', '' );
		update_post_meta( $pid, '_manage_stock', 'no' );
		update_post_meta( $pid, '_backorders', 'no' );
	}
	
	/**
	 * This function is used for create attribute for simple product
	 * ced_create_attribute
	 *
	 * @param  mixed $id
	 * @param  mixed $product_data
	 * @return void
	 */
	public function ced_create_attribute( $id, $product_data) {
		foreach ($product_data['item']['attributes'] as $attribute) {
			$attribute_name  = $attribute['attribute_name'];
			$attribute_value = $attribute['attribute_value'];

			$attributes[$attribute_name] = array(
				'name'          => sanitize_text_field($attribute_name),
				'value'         => sanitize_text_field($attribute_value),
				'position'      => 1,
				'is_visible'    => 1,
				'is_variation'  => 0,
				'is_taxonomy'   => 0
			);
			update_post_meta( $id, '_product_attributes', $attributes );
		}
	}
	
	/**
	 * This function is used to create post meta data for variations
	 * ced_create_post_meta_for_variation
	 *
	 * @param  mixed $variation_id
	 * @param  mixed $variation_key
	 * @param  mixed $tier_variation
	 * @param  mixed $variation_product
	 * @return void
	 */
	public function ced_create_post_meta_for_variation( $variation_id, $variation_key, $tier_variation, $variation_product) {
		update_post_meta($variation_id, '_price', sanitize_text_field($variation_product['price']));
		update_post_meta($variation_id, '_stock', sanitize_text_field($variation_product['stock']));
		update_post_meta($variation_id, '_sku', sanitize_text_field($variation_product['variation_sku']));
		update_post_meta($variation_id, '_variation_description', sanitize_text_field(''));
		update_post_meta($variation_id, '_tax_class', sanitize_text_field('parent'));
		update_post_meta($variation_id, '_visibility', 'visible');
		update_post_meta( $variation_id, '_stock_status', 'instock');
		update_post_meta( $variation_id, 'total_sales', '0' );
		update_post_meta( $variation_id, '_downloadable', 'no' );
		update_post_meta( $variation_id, '_virtual', 'no' );
		update_post_meta( $variation_id, '_regular_price', sanitize_text_field($variation_product['price']) );
		update_post_meta( $variation_id, '_sale_price', '' );
		update_post_meta( $variation_id, '_purchase_note', '' );
		update_post_meta( $variation_id, '_featured', 'no' );
		update_post_meta( $variation_id, '_height', '' );
		update_post_meta( $variation_id, '_sale_price_dates_from', '' );
		update_post_meta( $variation_id, '_sale_price_dates_to', '' );
		update_post_meta( $variation_id, '_sold_individually', '' );
		update_post_meta( $variation_id, '_manage_stock', 'no' );
		update_post_meta( $variation_id, '_backorders', 'no' );
		$variation_value = '';
		$attribute_name  = '';
		foreach ($tier_variation as $attr_key=>$attr_value) {
			$attribute_name = $attr_value['name'];
			for ($i=0; $i<count($attr_value['options']); $i++) {
				if ($variation_product['name'] == $attr_value['options'][$i]) {
					$variation_value = $attr_value['options'][$i];
					break;
				}
			}
		}
		update_post_meta($variation_id, 'attribute_' . strtolower($attribute_name) . '', $variation_value );
	}
	
	/**
	 * This function is used to add feature image in all product
	 * ced_add_image
	 *
	 * @param  mixed $pid
	 * @param  mixed $image
	 * @return void
	 */
	public function ced_add_image( $pid, $image) {
		// Add Featured Image to Post
		$image_url        = $image; // Define the image URL here
		$image_name       = basename( $image_url);
		$upload_dir       = wp_upload_dir(); // Set upload folder
		$image_data       = file_get_contents($image_url); // Get image data
		$unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name ); // Generate unique name
		$filename         = basename( $unique_file_name ); // Create image file name

		//Check folder permission and define file location
		if ( wp_mkdir_p( $upload_dir['path'] ) ) {
			$file = $upload_dir['path'] . '/' . $filename;
		} else {
			$file = $upload_dir['basedir'] . '/' . $filename;
		}

		// Create the image  file on the server
		file_put_contents( $file, $image_data );

		// Check image file type
		$wp_filetype = wp_check_filetype( $filename, null );

		// Set attachment data
		$attachment = array(
			'post_mime_type' => 'image/jpeg',
			'post_title'     => sanitize_file_name( $filename ),
			'post_content'   => '',
			'post_status'    => 'inherit'
		);

		// Create the attachment
		$attach_id = wp_insert_attachment( $attachment, $file, $pid );

		// Include image.php
		require_once ABSPATH . 'wp-admin/includes/image.php';

		// Define attachment metadata
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file );

		// Assign metadata to attachment
		wp_update_attachment_metadata( $attach_id, $attach_data );

		// And finally assign featured image to post
		set_post_thumbnail( $pid, $attach_id );
	}
	
	/**
	 * This function is used to create variable product attribute
	 * ced_create_variation_attribute
	 *
	 * @param  mixed $id
	 * @param  mixed $tier_variation
	 * @return void
	 */
	public function ced_create_variation_attribute( $id, $tier_variation) {
		foreach ($tier_variation as $attr_key=>$attr_value) {
			$attribute_name = $attr_value['name'];
			$str = '';
			$i = 0;
			for ($i=0; $i<count($attr_value['options'])-1; $i++) {
				$str .= $attr_value['options'][$i] . '|';
			}
			$str .= $attr_value['options'][$i];
			//var_dump($str);
			$attributes[$attribute_name] = array(
				'name'          => sanitize_text_field($attribute_name),
				'value'         => sanitize_text_field($str),
				'position'      => 1,
				'is_visible'    => 1,
				'is_variation'  => 1,
				'is_taxonomy'   => 0
			);
			//$attributes->set_variation(true);
			update_post_meta( $id, '_product_attributes', $attributes );
		}
	}
	
	/**
	 * This function is to import product
	 * import_product
	 *
	 * @param  mixed $id
	 * @param  mixed $fileData
	 * @return void
	 */
	public function import_product( $id, $fileData) {
		$parent    = 0;
		$post_type = 'product';
		$check     = false;
		$loop      = new WP_Query(array('post_type' => array('product')));
		if (is_array($loop->posts)) {
			foreach ( $loop->posts as $post_key=>$post_value) {
				foreach ($fileData as $key=>$value) {
					if ($id == $value['item']['item_id']) {
						$image = $value['item']['images'];
						if (get_post_meta($post_value->ID, '_sku', 1) == $value['item']['item_sku']) {
							$check = true;
							break;
						}
					}
				}
			}
		}
		if (false == $check) {
			foreach ($fileData as $key=>$value) {
				if ($id == $value['item']['item_id']) {
					$image = $value['item']['images'];
					$pid   = $this->ced_create_product($value, $parent, $post_type);
					?>
					<div class="notice notice-success is-dismissible">
						<p><?php esc_attr_e( 'product added successfully!'); ?></p>
					</div>
					<?php
					if (!empty($value['item']['images'])) {
						$this->ced_add_image($pid, $image[0]);
					}
					$this->ced_create_post_meta($pid, $value);
					if ($value['item']['has_variation']) {
						wp_set_object_terms( $pid, 'variable' , 'product_type' );
						$variation_post_type = 'product_variation';
						$variation           = $value['item']['variations'];
						$tier_variation      = $value['tier_variation'];
						$this->ced_create_variation_attribute($pid, $tier_variation);
						foreach ($variation as $variation_key=>$variation_product) {
							$variation_id = $this->ced_create_product($value, $pid, $variation_post_type);
							$this->ced_create_post_meta_for_variation($variation_id, $variation_key, $tier_variation, $variation_product);
							foreach ($tier_variation as $key=>$tier_value) {
								//print_r($tier_value);
								foreach ($tier_value['images_url'] as $image_key=>$image_value) {
									if ($variation_key == $image_key) {
										$this->ced_add_image($variation_id, $image_value);
									}
								} 
							}
						}
					} else {
						wp_set_object_terms( $pid, 'simple' , 'product_type' );
						if (!empty($value['item']['attributes'])) {
							$this->ced_create_attribute($pid, $value);
						}
					}
				}
			}
		} else { 
			?>
			<div class="notice notice-success is-dismissible">
					<p><?php esc_attr_e( 'product already exists!' ); ?></p>
				</div>
		<?php 
		}
	}
	
	/**
	 * This function is used for bulk action
	 * process_bulk_action
	 *
	 * @return void
	 */
	public function process_bulk_action() {

		//Detect when a bulk action is being triggered...
		if ( 'Import' === $this->current_action() ) {
	  
		  // In our file that handles the request, verify the nonce.
		  $nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
	  
			if ( ! wp_verify_nonce( $nonce, 'sp_import_product' ) ) {
			  die( 'Go get a life script kiddies' );
			} else {
				$product =  isset( $_GET['product'] ) ? sanitize_text_field( $_GET['product'] ) : '';
				self::import_product( absint($product  ) );
				wp_redirect( esc_url( add_query_arg() ) );
				exit;
			}
		}
	  
		// If the delete bulk action is triggered
		if ( ( isset( $_POST['action'] ) && 'bulk-import' == $_POST['action'] )
			|| ( isset( $_POST['action2'] ) && 'bulk-import' == $_POST['action2'] )
		) {
			$bulk_import =  isset( $_POST['bulk-import'] ) ? sanitize_text_field( $_POST['bulk-import'] ) : '';
			$import_ids = esc_sql($bulk_import);
	  
		  // loop over the array of record IDs and delete them
			foreach ( $import_ids as $id ) {
			  self::import_product( $id );
			}
	  
		  wp_redirect( esc_url( add_query_arg() ) );
		  exit;
		}
	}
}
