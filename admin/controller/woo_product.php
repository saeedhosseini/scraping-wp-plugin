<?php

function addProduct( $id ) {
	$currency = Currency::all();
	$tQ       = $currency->where( 'origin', 'lir' )
	                     ->where( 'exchange', 'rial' )->first();

	$productData = Product::query()->with( [
		'category',
		'dataAttributes',
		'dataAttributes.attributeName'
	] )->find( $id );

	if ( $productData ) {

		if ( $productData->product_woocommerce_id != null ) {
			$product = wc_get_product( $productData->product_woocommerce_id );
		} else {
			$product = new WC_Product_Variable();
		}

		$price         = 0;
		$name          = '';
		$attributes    = array();
		$imageID_Main  = - 1;
		$galleryImages = [];
		$description   = '';
		$brand         = '';

		$sizes = [];

		foreach ( $productData->dataAttributes as $attributeData ) {
			if ( $attributeData->attributeName->attr_name == 'price' && $tQ != null ) {
				//	$price = $attributeData->attr_value * $tQ->rate / 10;
				$price = $attributeData->attr_value * $tQ->rate;
			} else if ( $attributeData->attributeName->attr_name == 'name' ) {
				$name = $attributeData->attr_value;
			} else if ( $attributeData->attributeName->property == 'variant' ) {
				$variants  = json_decode( $attributeData->attr_value );
				$sizes     = $variants;
				$attribute = new WC_Product_Attribute();
				$attribute->set_name( $attributeData->attributeName->attr_name );
				$attribute->set_options( $variants );
				$attribute->set_position( 0 );
				$attribute->set_visible( true );
				$attribute->set_variation( true );
				$attributes[] = $attribute;
			} else if ( $attributeData->attributeName->attr_name == '_thumbnail' ) {
				$imageID_Main = rudr_upload_file_by_url( endSlash( $attributeData->attr_value ) );
				echo $imageID_Main;
			} else if ( $attributeData->attributeName->attr_name == 'gallery' ) {
				$galleries = json_encode( $attributeData->attr_value );
				foreach ( $galleries as $gallery ) {
					$galleryImages[] = rudr_upload_file_by_url( checkSizeAndResize( $gallery ) );
				}
			} else if ( $attributeData->attributeName->attr_name == 'description' ) {
				$description = $attributeData->attr_value;
			} else if ( $attributeData->attributeName->attr_name == 'brand' ) {
				$brand = $attributeData->attr_value;
			}
		}

		$product->set_name( $name );
		$product->set_price( $price );
		$product->set_regular_price( $price );
		$product->set_sale_price( $price );
		$product->set_category_ids([$productData->category->woo_category_id]);
		$desPlusBrand = "<li>Seller: $brand</li> " . $description;
		$product->set_short_description( $desPlusBrand );
		if ( $imageID_Main > 0 ) {
			$product->set_image_id( $imageID_Main );
		}
		$product->set_gallery_image_ids( $galleryImages );
		$product->set_attributes( $attributes );
		$product->set_manage_stock(false);
		//$product->set_sku()
		$id = $product->save();
		$productData->update( [
			'product_woocommerce_id' => $id
		] );


		foreach ( $sizes as $size ) {
			$variation = new WC_Product_Variation();
			$variation->set_parent_id( $product->get_id() );
			$variation->set_attributes( array( 'size' => $size ) );
			$variation->set_regular_price( $price );
			$variation->set_manage_stock(false);
			$variation->save();
		}

		return $id;
	}

}
