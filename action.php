<?php 
require_once( $_SERVER['DOCUMENT_ROOT'] . '/woocommerce/wp-config.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/woocommerce/wp-includes/wp-db.php' );
require_once( ABSPATH . 'wp-admin/includes/file.php' );
require_once( ABSPATH . 'wp-admin/includes/image.php' );


save_product();

function save_product(){
	$productId = $_POST['prodcutId'];
	$categoryId = $_POST['categoryId'];
	global $wpdb;
	$attribute=array('_regular_price','_sale_price','_featured','_price');
	$product=$wpdb->get_row("SELECT * FROM `tk_products` where id=".$productId);
	$productAttributes=$wpdb->get_results("SELECT PA.attr_value, Attr.attr_name, Attr.property FROM `tk_product_attributes` As PA INNER JOIN `tk_site_category_product_attributes` AS Attr WHERE PA.attr_id=Attr.id && PA.product_id=".$productId);
	if(!$product->product_woocommerce_id)
	{
	$product_id = wp_insert_post(array('post_title' =>getValue($productAttributes,'title'),'post_status' => 'publish','post_type' => "product"));
	$wpdb->update('tk_products', array( 'product_woocommerce_id' => $product_id),array('id'=>$productId));
	wp_set_object_terms( $product_id, 'simple', 'product_type' );
	}
	else $product_id=$product->product_woocommerce_id;
	
	// bind short description
	wp_update_post( array('ID' => $product_id, 'post_excerpt' => getValue($productAttributes,'post_excerpt') ) );

	// bind category of product
	$term_ids = array(); 
	$term_ids[]=(int)$categoryId;
	wp_set_object_terms( $product_id,$term_ids , 'product_cat' );
	
	//bind attribute
	foreach ( $attribute as $attr ) {
		 $value=getValue($productAttributes,$attr); 
		 if($value)
		   update_post_meta( $product_id, $attr , $value ); 
	 }
	//update_post_meta( $product_id, '_featured', 'yes' );
	$WC_product = new WC_Product($product_id);
	$sale_price=$WC_product->get_sale_price();
	$regular_price=$WC_product->get_regular_price();
	$_price=$WC_product->get_price();
	if(!$_price && !$regular_price && $sale_price ){
		$WC_product->set_regular_price($sale_price);
		$WC_product->set_price($sale_price);
		$WC_product->set_sale_price('');
        $WC_product->save();
	}
	if(!$_price && $regular_price ){
	 update_post_meta( $product_id, '_price' , $regular_price ); 
	}
	$WC_product = new WC_Product($product_id);
	$thumbnailUrl=getValue($productAttributes,'_thumbnail');
	if($thumbnailUrl){
		$thumbnail_id=uploadImage($thumbnailUrl);
	 set_post_thumbnail( $product_id, $thumbnail_id );  
	}
	  
	echo $term_ids ;
}


function getValue($data,$attr_name){
	foreach ( $data as $element ) {
        if ( $attr_name == $element->attr_name ) 
			if($element->property=='boolean'){
				//print_r($element->attr_value);
				return 'yes';
			} 
		else
            return $element->attr_value;
    }
}

function uploadImage( $image_url ){
	$temp_file = download_url( $image_url );
	if( is_wp_error( $temp_file ) ) {
		return false;
	}
	// move the temp file into the uploads directory
	 $file = array(
		 'name'     => basename( $image_url ),
		 'type'     => mime_content_type( $temp_file ),
		 'tmp_name' => $temp_file,
		 'size'     => filesize( $temp_file ),
	 );
	$sideload = wp_handle_sideload(
		$file,
		array(
			'test_form'   => false // no needs to check 'action' parameter
		));
	if( ! empty( $sideload[ 'error' ] ) ) {
		// you may return error message if you want
		return false;
	}
	// it is time to add our uploaded image into WordPress media library
	$attachment_id = wp_insert_attachment(
		array(
			'guid'           => $sideload[ 'url' ],
			'post_mime_type' => $sideload[ 'type' ],
			'post_title'     => basename( $sideload[ 'file' ] ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$sideload[ 'file' ]
	);

	if( is_wp_error( $attachment_id ) || ! $attachment_id ) 
		return false;
	// update metadata, regenerate image sizes
	wp_update_attachment_metadata($attachment_id,
								  wp_generate_attachment_metadata( $attachment_id, $sideload[ 'file' ] )
								 );
	return $attachment_id;
}  
     
?>