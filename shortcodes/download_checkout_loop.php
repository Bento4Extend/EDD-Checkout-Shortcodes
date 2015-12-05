<?php 
class Checkout_loop
{
    /**
     * If you should add the script or not
     *
     * @var bool
     */
    private $addScript = false;
	private $args = array();
	private $atts = array();
	private $content ='';
	private $shortcode_index = 0;
	private $post_type = '';
	private $translation_array=array();

	
    public function __construct()
    {
        add_shortcode('download_checkout_loop', array($this, 'download_checkout_loop_function'));

        // wp_enqueue_scripts
        // If you use the below the CSS and JS file will be added on everypage
        // add_action( 'wp_enqueue_scripts', array($this, 'add_shortcode_scripts'));

        // Add styles and scripts to the page
        add_action('wp_footer', array($this, 'add_chekout_shortcode_scripts'),11);
		 
	}

    public function download_checkout_loop_function( $atts, $content = "" ) 
	{
	
	
		$atts = shortcode_atts( array(
			'show' => 'all',
			'limit' => -1,
			'page' => -1,
			'listing'=>-1,
			'tags'=>'no',
			'category'=>'no',
			'id'=>'-1',
			'search'=>'no',
			'ajax' => 'yes',
			'continue'=>'no',
			'max_price'=>-1,
			'min_price'=>-1,
			'text_empty' => 'not set',
			'page_limit'=>-1,
			'sort'=>1,
			'page_next'=>'>',
			'page_last'=>'>>',
			'page_previous'=>'<',
			'page_first'=>'<<',
			'page_number'=>'4',
			'css_page_nav'=>'css_page_nav',
			'css_number'=>'css_number',
			'css_number_selected'=>'',
			'css_dots'=>'',
			'text_dots'=>'...',
			'parameter'=>'eddcp'
		), $atts, 'download_checkout_loop' );
		
		$cart_items = edd_get_cart_contents();
		 
		 
		
		if(empty($cart_items))
		{
			if($atts['text_empty']!='' && $atts['text_empty']!='not set')
				return $atts['text_empty'];
			else if($atts['text_empty']!='not set')
				return 'The checkout box is empty.';
			else
				return '';
		}
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
		// Buid Query 
		if($atts['listing'] == 1 && $atts['page'] > 0 )
		{
			$atts['ajax'] = 'yes';
			 
		}
		if($atts['listing'] == 3 && $atts['page'] > 0)
		{
			$atts['continue'] = 'yes';
			$atts['ajax'] = 'yes';
			 
		}
		if($atts['listing'] == 2 && $atts['page'] > 0 )
			$atts['ajax'] = 'no';
		 
		if($atts['ajax'] == 'yes')	
			$this->addScript = true;
		 	
		
		// Get instance of edd 
		global $post,$wp_query,$post_type;
		
		$post_type = get_post_type( $post->ID );
		$tax_query = array(); 
		
		$page = ($atts['page']<1)?-1:$atts['page'];
		$paged = $_GET[$atts['parameter']] ? $_GET[$atts['parameter']] : 1;
		
		$args = array(
			'post_type' => 'download',
			 	
		);
		
		 
		$atts['sort'] = trim($atts['sort']);
		$sort_array = str_split($atts['sort']);
		 
		foreach ($sort_array as $sort)
		{
			switch ($sort) {
				case 1:
					
					$orderby = 'post__in';
					break;
				case 2:
					$orderby = 'post__in';
					$ids = array_reverse($ids); 
					break;
				case 3:
					$orderby['title']='ASC';
					break;
				case 4:
					$orderby['title']='DESC';
					break;
				case 5:
					$orderby['meta_value']='ASC';
					$args['meta_key']='edd_price';
					break;
				case 6:
					$orderby['meta_value']='DESC';
					$args['meta_key']='edd_price';
					break;
				default:
					$orderby['date']='DESC';
			}
		}
		if(!empty($orderby))
		{
			$args['orderby']=$orderby;
		}
		
		// Set Limits
		if($atts['limit'] > -1 && $atts['page'] > -1  )
		{
			$atts['page_limit']= round($atts['limit'] / $atts['page'], 0, PHP_ROUND_HALF_UP);
			$args['last_page_post'] = ($atts['limit'] % $atts['page']);
			 
		}
		if($atts['limit'] > -1 && $atts['page'] == -1 )
		{
			$page = $atts['limit'];
		}
		if($paged == $atts['page_limit'] && isset($args['last_page_post']) && $args['last_page_post'] > 0 )
		{
			$page = $args['last_page_post'] ;
		} 
		$args['posts_per_page'] = $page ;
		
		
		
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
		
		$args['post__in']=$ids;		
		if($page > 0)
		{
			$args['paged']=$paged;
		}
		if($atts['tags']!='no')
		{
			$tags = explode(',',$atts['tags']);
			 
			$tax_query[]=array(
				'taxonomy' => 'download_tag',
				'field'    => 'slug',
				'terms'    => $tags
			);
		}
		if($atts['category']!='no')
		{
			$category = explode(',',$atts['category']);
			 
			$tax_query[]=array(
				'taxonomy' => 'download_category',
				'field'    => 'slug',
				'terms'    => $category
			);
		}
		if($atts['id']!='-1')
		{
			$ids = explode(',',$atts['id']);
			 
			$args['post__in']=$ids;
		}
		if($atts['search']!='no')
		{
			$args['seach_excerpt'] = true; 
			$args['s']=$atts['search'];
		}
		 
		if(count($tax_query)>0)
		{
			$args['tax_query']=$tax_query;
		}
		$meta_query = array();
		if($atts['max_price'] > 0 )
		{
			$meta_query[] = array(
				'key'     => 'edd_price',
				'value'   => $atts['max_price'],
				'compare' => '<=',
				'type' => 'NUMERIC'
			);
		}
		
		if($atts['min_price'] > -1 )
		{
			$meta_query[] = array(
				'key'     => 'edd_price',
				'value'   => $atts['min_price'],
				'compare' => '>=',
				'type' => 'NUMERIC'
			);
		}
		if(count($meta_query)>0)
		{
			$args['meta_query']=$meta_query;
		}
		
	
		
		global $download_loop,$wp_query,$download_loop_index,$download_checkout_index;
		
		$download_checkout_index = 1;
		
		$download_loop = new WP_Query($args);
	
		$index = 1;
	 
		ob_start();
		echo '<div class="download-checkout-loop-outer" id="'.$this->shortcode_index.'" data-index="'.$this->shortcode_index.'">';
		 
		if($atts['ajax']=='yes')
			if($atts['continue'] == 'yes')
				echo '<div class="download-loader continue"></div>';
			else
				echo '<div class="download-loader"></div>';
			
		echo '<div class="download-list-container clearfix">';
		
		global $x;		
		
		$x = 1;
		
		if($download_loop->have_posts() )
		{
			while($download_loop->have_posts())
			{
				$download_loop->the_post(); 
				$download_loop_index = $this->shortcode_index;
				
				$price_list = edd_get_variable_prices( get_the_ID() );
				
				
				 
				
				$feat_image = wp_get_attachment_image_src( get_post_thumbnail_id(get_the_ID()));
				
				
				if($idscount[get_the_ID()] <= 1 && !edd_has_variable_prices( get_the_ID() ))
				{ 
					
					echo do_shortcode($filter_content);
					$index++;
					$download_checkout_index++;
				}
				else
				{
					
					global $current_price,$global_checkout_price_id;
					
					for ($x = 1; $x <= $idscount[get_the_ID()]; $x++) {
						
					
						$current_price = $price_list[$cart_array[get_the_ID()][$x-1]['options']['price_id']];
						$global_checkout_price_id = $cart_array[get_the_ID()][$x-1]['options']['price_id'];
						
						$current_price['price_id']= $global_checkout_price_id ;
						
						echo do_shortcode($filter_content); 
						$index++;
						$download_checkout_index++;
						$global_checkout_price_id ='';
					} 
				}
			
				 
				
			}
		}
		else
		{
			if($atts['text_empty']!='' && $atts['text_empty']!='not set')
				echo $atts['text_empty'];
			else if($atts['text_empty']!='not set')
				echo 'The checkout box is empty.';
			// $this->addScript = false;
		}
		echo '</div>';
		
		/*if($download_loop->max_num_pages < 1)
			 $this->addScript = false;
		*/
		if ($atts['limit'] >= $download_loop->post_count)
		{
			$atts['limit']=-1;
		}
		if($atts['page_limit']<0 || $atts['page_limit'] >= $download_loop->max_num_pages)
		{
			$atts['page_limit']= $download_loop->max_num_pages ; 
		}
		if($atts['page_limit']>0)
		{
			$args['page_limit']=$atts['page_limit'];
		}

		if($atts['css_number_selected']=='') 
			$atts['css_number_selected']=$atts['css_number'];
		if($atts['css_dots']=='') 
			$atts['css_dots']=$atts['css_number'];
		
		if($atts['page'] > 0 && $atts['continue'] == 'no' && $atts['page_limit'] > 1)
		{ 
			  
			echo '<div class="download-paginate '.$atts['ajax'].' '.$atts['css_page_nav'].'">';
				
				$format='?'.$atts['parameter'].'=%#%';
				$base = untrailingslashit(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH)).'%_%';
				if($paged > 1)
					echo '<a class="first page-numbers" href="'.str_replace('%_%','?'.$atts['parameter'].'=1',$base).'"><span class="'.$atts['css_number'].'">'.$atts['page_first'].'</span></a>';
				
				echo paginate_links_new( array(
					'base' => $base , 
					'format' =>$format,
					'current' =>$paged,
					'mid_size'=> $atts['page_number'],
					'total' => $atts['page_limit'],
					'prev_text'          =>  __($atts['page_previous']) ,
					'next_text'          => __($atts['page_next']),
					'css_number'=>$atts['css_number'],
					'css_number_selected'=>$atts['css_number_selected'],
					'css_dots'=>$atts['css_dots'],
					'text_dots'=>$atts['text_dots']
				) );
				
					if($paged < $atts['page_limit'])
						echo '<a data-page="'.$atts['page_limit'].'" class="last page-numbers" href="'.str_replace('%_%','?'.$atts['parameter'].'='.$atts['page_limit'],$base).'"><span class="'.$atts['css_number'].'">'.$atts['page_last'].'</span></a>';

			echo '</div>';
				

		}
		else if($atts['page'] > 0 && $atts['continue'] == 'yes' && $atts['ajax'] == 'yes' )
		{
			echo '<div id="page-end">';
			if($this->addScript)
				echo '<input type="hidden" name="page_num" id="page_num" value='.$paged.'>';
			echo '</div>';
		}
		echo '</div>';
		
		$this->atts = $atts;
		$this->args = $args;
		$this->content = $content;
		 
		$this->post_type = $post_type;
		 
		wp_reset_postdata();
		$download_loop = '';
		$download_checkout_index = '';
		$this->translation_array[] = array(
			'query_args' =>$this->args,
			'atts' =>$this->atts,
			'content'=>$this->content,
			'ajax_url' => admin_url('admin-ajax.php'),
			'post_type'=>$this->post_type
		);
		$this->shortcode_index++;
		return  ob_get_clean();
	}
	
    public function add_chekout_shortcode_scripts()
    {
		 
        if(!$this->addScript)
        {
            return false;
        }
		 
        wp_register_script( 'download_checkout_ajax',ROOT_CHECKOUT.'js/download_ajax.js',array('jquery'));
	
		
		wp_localize_script( 'download_checkout_ajax', 'global_checkout_args', $this->translation_array );
		wp_enqueue_script( 'download_checkout_ajax');
		
    }
}
$Checkout_loop = new Checkout_loop();