<?php 

function download_checkout_total_function($atts,$content)
{
	 

	return edd_cart_total(false);	
}	

add_shortcode('download_checkout_cart_total','download_checkout_total_function');

 