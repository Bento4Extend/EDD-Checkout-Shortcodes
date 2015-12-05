<?php 

/* Mangaing all ajax request here 

 

*/



/* Loda more post at end of page */
if(!function_exists('get_next_checkout_page'))
{ 
add_action('wp_ajax_nopriv_get_next_checkout_page', 'get_next_checkout_page');
add_action('wp_ajax_get_next_checkout_page', 'get_next_checkout_page');

function get_next_checkout_page()
{
	// Get page number 	 
	$paged = $_POST['page_num'];
	$request_header = getallheaders();
	$atts= $_POST['atts'];
	$args = $_POST['args'];
	$args['paged']=$paged;
	$content = $_POST['content']; 
	$post_type =  $_POST['post_type'];
	$continue = $_POST['continue'];
	// Set args as per page type
	 
	if($paged == $args['page_limit'] && isset($args['last_page_post']) && $args['last_page_post'] > 0 )
	{
		$args['posts_per_page'] = $args['last_page_post'] ;
	}  
	// print_r($args);
	global $download_loop,$wp_query,$download_loop_index,$download_checkout_index;
	$download_checkout_index=1;
	$download_loop = new WP_Query( $args );
	$result = array(); 
	$cart_items = edd_get_cart_contents();
	$ids=array();
	if(is_array($cart_items) && !empty($cart_items))
	{
		foreach($cart_items as $carst)
		{
			$ids[]=$carst['id'];
		}
	}
	global $idscount;
	$idscount = array_count_values($ids);
	
	global $cart_array;
	$cart_array= array();
	foreach($idscount as $k=>$v)
	{
		foreach($cart_items as $carst)
		{
			if($carst['id'] == $k)
			{
				$temp['options']=$carst['options'];
				$temp['quantity']=$carst['quantity'];
				$cart_array[$k][]=$temp;
			}
		}
	}
		
	if ( $download_loop->have_posts() ) {
		
		ob_start(); 
		if($continue == 'no')
		{
			echo '<div class="download-loader"></div>';
			echo '<div class="download-list-container clearfix">';
		}
		/* Build template for upcoming new posts */
		 
		 
		while ( $download_loop->have_posts() ) {
			$download_loop->the_post();
			$price_list = edd_get_variable_prices( get_the_ID() );
			$filter_content = str_replace("%%edd_id%%", get_the_ID(), $content);
			$filter_content = str_replace("%%edd_name%%", get_the_title(), $filter_content);
			$filter_content = str_replace("%%edd_url%%", get_permalink(), $filter_content);
			$filter_content = str_replace("%%edd_url_remove%%", esc_url(edd_remove_item_url( $download_checkout_index )), $filter_content);
				
			$filter_content = str_replace("%%edd_url_checkout%%", esc_url( edd_get_checkout_uri() ) , $filter_content);
			
			$feat_image = wp_get_attachment_image_src( get_post_thumbnail_id(get_the_ID()));
			$filter_content = str_replace("%%edd_url_image%%", $feat_image[0] ,$filter_content);  
			
			
			if($idscount[get_the_ID()] <= 1)
			{ 
				$filter_content = str_replace("%%edd_price%%", download_checkout_item_total_function(array()) ,$filter_content);  
				echo do_shortcode($filter_content);
				$index++;
				$download_checkout_index++;
			}
			else
			{
				global $x;
				global $current_price,$global_checkout_price_id;
				
				for ($x = 1; $x <= $idscount[get_the_ID()]; $x++) {
					global $current_price;
				
					$current_price = $price_list[$cart_array[get_the_ID()][$x-1]['options']['price_id']];
					$global_checkout_price_id = $cart_array[get_the_ID()][$x-1]['options']['price_id'];
					$current_price['price_id']= $global_checkout_price_id ;
						
					$filter_content = str_replace("%%edd_option_id%%", $current_price['price_id'] , $filter_content); 	
					$filter_content = str_replace("%%edd_price%%", download_checkout_item_total_function(array()) ,$filter_content);  
					echo do_shortcode($filter_content); 
					$index++;
					$download_checkout_index++;
				} 
			}
			 
		 
		}
		if(!isset($args['page_limit']) || $args['page_limit']<0)
		{
			$args['page_limit']= $download_loop->max_num_pages ; 
		}
		if($continue == 'no')
		{ 
			echo '</div>';	
			echo '<div class="download-paginate yes '.$atts['page_css_style'].'">';
			 
				$format='?'.$atts['parameter'].'=%#%';
				$base = untrailingslashit(parse_url($request_header['Referer'], PHP_URL_PATH)).'%_%';
				if($paged > 1)
						echo '<a class="first page-numbers" href="'.str_replace('%_%','?'.$atts['parameter'].'=1',$base).'"><span class="'.$atts['number_page_css_style'].'">'.$atts['page_first'].'</span></a>';

			 
			echo paginate_links_new( array(
				'base' => $base , 
				'format' =>$format,
				'current' =>$paged,
				'mid_size'=>$atts['page_number'],
				'total' => $args['page_limit'],
				'prev_text'          =>  __($atts['page_previous']) ,
				'next_text'          => __($atts['page_next']),
				'page_dots'=>$atts['page_dots'], 
				'number_page_css_style'=>$atts['number_page_css_style'],
				'number_page_css_style_selected'=>$atts['number_page_css_style_selected'],
				'number_page_css_style_dots'=>$atts['number_page_css_style_dots'],
			) );
			 
				if($paged < $args['page_limit'])
					echo '<a data-page="'.$args['page_limit'].'" class="last page-numbers" href="'.str_replace('%_%','?'.$atts['parameter'].'='.$args['page_limit'],$base).'"><span class="'.$atts['number_page_css_style'].'">'.$atts['page_last'].'</span></a>';
 
			echo '</div>';

			echo '<input type="hidden" name="page_num" id="page_num" value='.$paged.'>';
		}
	}
	 
 
	$result['content']= ob_get_clean();
	$result['max_page']= $args['page_limit'];
	echo json_encode($result,JSON_UNESCAPED_SLASHES);
	
	wp_reset_query();
	$download_loop = '';
	$price_loop_index = 0;
	$download_index = 1;
	die();
}
}