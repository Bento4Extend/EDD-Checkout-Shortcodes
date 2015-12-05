<?php 

function download_checkout_count_function($atts,$content)
{
	$cart_items = edd_get_cart_contents(); 
	if(empty($cart_items))
		return 0;
	else
		return count($cart_items);	
}	

add_shortcode('download_checkout_cart_count','download_checkout_count_function');

 