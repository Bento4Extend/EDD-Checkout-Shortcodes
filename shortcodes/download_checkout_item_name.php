<?php 

function download_checkout_item_name_function($atts)
{
	$atts = shortcode_atts( array(
		'limit' => -1,
		'id'=>''
	), $atts, 'download_checkout_item_name' );
	 
	
	
	if($atts['limit'] < 0)
	{	
		if($atts['id'] =='')
		{
			return get_the_title();
		}
		else
		{
			return get_the_title($atts['id']);
			 
		}	
		
	}
	else
	{	
		if($atts['id'] == '')
		{
			return substr(get_the_title(),0,$atts['limit']);
		}
		else
		{
			return substr(get_the_title($atts['id']),0,$atts['limit']);
		} 
		 
	}
}

add_shortcode('download_checkout_item_name','download_checkout_item_name_function');

