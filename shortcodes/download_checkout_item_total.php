<?php 

function download_checkout_item_total_function($atts)
{
	$atts = shortcode_atts( array(
		'id' => ''
	), $atts, 'download_checkout_item_total' );
	
	global $x,$cart_array;
 
	
	if($atts['id']!='')
	{
		$id = $atts['id'] ;
	}
	else 
		$id = get_the_id();
	$options = $cart_array[get_the_ID()][($x-1)]['options'];
	$price = edd_get_cart_item_price( $id , $options );
	$price += edd_get_cart_item_tax( $id, $options, $price );
	$price = edd_currency_filter( edd_format_amount( $price ) );
	return $price;
}

add_shortcode('download_checkout_item_total','download_checkout_item_total_function');

