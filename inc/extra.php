<?php 
if(!function_exists('add_checkout_scripts'))
{


	function add_checkout_scripts() {


	wp_register_script( 'edd-checkout-extendify-main-js',ROOT_CHECKOUT.'js/main.js', array('jquery'));
	$translation_array = array(
	'ajax_url' => admin_url('admin-ajax.php'),

	);
	wp_localize_script( 'edd-checkout-extendify-main-js', 'global_data', $translation_array );

	wp_enqueue_script('edd-checkout-extendify-main-js');
	wp_enqueue_style('edd-checkout-extendify-main-css',ROOT_CHECKOUT.'css/main.css');
	}
	 
	add_action( 'wp_enqueue_scripts', 'add_checkout_scripts' );
}
if(!function_exists('add_custom_css'))
{
function add_custom_css()
{
	echo '<style>';
	echo '.download-loader{ ';
	echo " background-image: url('".site_url()."/wp-admin/images/spinner-2x.gif');";
	echo '}';
	echo '</style>';
	
}

add_action('wp_head','add_custom_css');
} 
 
 

 
 

// Calculate Exact match 
if(!function_exists('checkout_is_equals_sensitive'))
{ 
	function checkout_is_equals_sensitive($string,$val)
	{
		if($val !='' && $string !='')
			return ($string == $val);
		else
			return true;
	}
}
if(!function_exists('checkout_is_equals'))
{ 
function checkout_is_equals($string,$val)
{
	$string = strtolower ($string);
	$val = strtolower ($val);
	if($val !='' && $string !='')
		return ($string == $val);
	else
		return true;
}
}
if(!function_exists('checkout_is_present'))
{ 
function checkout_is_present($string,$val)
{
	$string = strtolower ($string);
	$val = strtolower ($val);
	if($val !='' && $string !='')
		return (strpos($string, $val)!== false);
	else
		return true;
}
}

if(!function_exists('checkout_is_present_sensitive'))
{
function checkout_is_present_sensitive($string,$val)
{
	
	if($val !='' && $string !='')
		return (strpos($string, $val)!== false);
	else
		return true;
}
}

// Calculate equal condition 
if(!function_exists('checkout_eval_condition'))
{ 
function checkout_eval_condition($price,$equal='',$greater='',$less='') 
{
	$flag = true;
	
	$price=floatval($price);
	 
	if($equal=='' && $greater=='' && $less=='')
		$greater='0';
	if($equal!='')
	{
		$equal=floatval($equal);
		if($equal == $price)
			$flag=false;
	}
	if($flag == true && $greater != '')
	{
		$greater=floatval($greater);
		if($price > $greater )
			$flag=false;
	}
	if($flag == true && $less != '')
	{
		$less=floatval($less);
		if($price < $less )
			$flag=false;
	}
	// echo 'Less'.$less.'- Greater'.$greater.'- Equal'.$equal.'-- Price'.$price ;
	return !$flag;
	/* $opt='';
	 
	$string = htmlspecialchars_decode($string);
	 
	if(strpos($string,'>=') !== false)
		$opt='>=';
	else if(strpos($string,'<=') !== false)
		$opt='<=';
	else if(strpos($string,'>') !== false)
		$opt='>';
	else if(strpos($string,'<') !== false)
		$opt='<';
	if($opt!='')
		$string = str_replace($opt,"",$string);
	 
	switch($opt)
	{
		case '>=':
			return ($val >= $string);
		case '<=':
			return ($val <= $string);
		case '>':
			return ($val > $string);
		case '<':
			return ($val < $string);
		default:
			return ($val == $string);
	}*/
}
}


if(!function_exists('paginate_links_new'))
{
function paginate_links_new( $args = '' ) {
	global $wp_query, $wp_rewrite;

	// Setting up default values based on the current URL.
	$pagenum_link = html_entity_decode( get_pagenum_link() );
	$url_parts    = explode( '?', $pagenum_link );

	// Get max pages and current page out of the current query, if available.
	$total   = isset( $wp_query->max_num_pages ) ? $wp_query->max_num_pages : 1;
	$current = get_query_var( 'paged' ) ? intval( get_query_var( 'paged' ) ) : 1;

	// Append the format placeholder to the base URL.
	$pagenum_link = trailingslashit( $url_parts[0] ) . '%_%';

	// URL base depends on permalink settings.
	$format  = $wp_rewrite->using_index_permalinks() && ! strpos( $pagenum_link, 'index.php' ) ? 'index.php/' : '';
	$format .= $wp_rewrite->using_permalinks() ? user_trailingslashit( $wp_rewrite->pagination_base . '/%#%', 'paged' ) : '?paged=%#%';

	$defaults = array(
		'base' => $pagenum_link, // http://example.com/all_posts.php%_% : %_% is replaced by format (below)
		'format' => $format, // ?page=%#% : %#% is replaced by the page number
		'total' => $total,
		'current' => $current,
		'show_all' => false,
		'prev_next' => true,
		'prev_text' => __('&laquo; Previous'),
		'next_text' => __('Next &raquo;'),
		'end_size' => 0,
		'mid_size' => 2,
		'type' => 'plain',
		'add_args' => array(), // array of query args to add
		'add_fragment' => '',
		'before_page_number' => '',
		'after_page_number' => '',
		'number_page_css_style'=>'',
		'number_page_css_style_selected'=>'',
		'number_page_css_style_dots'=>'',
		'page_dots'=>''
	);
	
	$args = wp_parse_args( $args, $defaults );
	if($args['page_dots']=='no')
		$args['page_dots']='';
	if ( ! is_array( $args['add_args'] ) ) {
		$args['add_args'] = array();
	}

	// Merge additional query vars found in the original URL into 'add_args' array.
	if ( isset( $url_parts[1] ) ) {
		// Find the format argument.
		$format = explode( '?', str_replace( '%_%', $args['format'], $args['base'] ) );
		$format_query = isset( $format[1] ) ? $format[1] : '';
		wp_parse_str( $format_query, $format_args );

		// Find the query args of the requested URL.
		wp_parse_str( $url_parts[1], $url_query_args );

		// Remove the format argument from the array of query arguments, to avoid overwriting custom format.
		foreach ( $format_args as $format_arg => $format_arg_value ) {
			unset( $url_query_args[ $format_arg ] );
		}

		$args['add_args'] = array_merge( $args['add_args'], urlencode_deep( $url_query_args ) );
	}

	// Who knows what else people pass in $args
	$total = (int) $args['total'];
	if ( $total < 2 ) {
		return;
	}
	$current  = (int) $args['current'];
	$end_size = (int) $args['end_size']; // Out of bounds?  Make it the default.
	if ( $end_size < 0 ) {
		$end_size = 0;
	}
	$mid_size = (int) $args['mid_size'];
	if ( $mid_size < 0 ) {
		$mid_size = 2;
	}
	$add_args = $args['add_args'];
	$r = '';
	$page_links = array();
	$dots = false;

	if ( $args['prev_next'] && $current && 1 < $current ) :
		$link = str_replace( '%_%', 2 == $current ? '' : $args['format'], $args['base'] );
		$link = str_replace( '%#%', $current - 1, $link );
		if ( $add_args )
			$link = add_query_arg( $add_args, $link );
		$link .= $args['add_fragment'];

		/**
		 * Filter the paginated links for the given archive pages.
		 *
		 * @since 3.0.0
		 *
		 * @param string $link The paginated link URL.
		 */
		$page_links[] = '<a class="prev page-numbers '.$args['number_page_css_style'].'" href="' . esc_url( apply_filters( 'paginate_links', $link ) ) . '">' . $args['prev_text'] . '</a>';
	endif;
	for ( $n = 1; $n <= $total; $n++ ) :
	
		// echo $n.'-'.$current.'-'.$mid_size;
		// var_dump($dots);
		 
		if ( $n == $current ) :
			$page_links[] = "<span class='page-numbers current ".$args['number_page_css_style_selected']."'>" . $args['before_page_number'] . number_format_i18n( $n ) . $args['after_page_number'] . "</span>";
			$dots = true;
		else :
			if ( ( $args['show_all'] || ( $n <= $end_size || ( $current && $n >= $current - $mid_size && $n <= $current + $mid_size ) || $n > $total - $end_size ) ) && $n >= $current - $mid_size ) :
				$link = str_replace( '%_%', 1 == $n ? '' : $args['format'], $args['base'] );
				$link = str_replace( '%#%', $n, $link );
				if ( $add_args )
					$link = add_query_arg( $add_args, $link );
				$link .= $args['add_fragment'];

				/** This filter is documented in wp-includes/general-template.php */
				$page_links[] = "<a class='page-numbers ".$args['number_page_css_style']."' href='" . esc_url( apply_filters( 'paginate_links', $link ) ) . "'>" . $args['before_page_number'] . number_format_i18n( $n ) . $args['after_page_number'] . "</a>";
				$dots = true;
			elseif ( ($dots && ! $args['show_all']) || $n == ( $current - $mid_size - 1) ) :
				$page_links[] = '<span class="page-numbers dots '.$args['number_page_css_style_dots'].'">' . $args['page_dots']. '</span>';
				$dots = false;
			endif;
		endif;
	endfor;
	if ( $args['prev_next'] && $current && ( $current < $total || -1 == $total ) ) :
		$link = str_replace( '%_%', $args['format'], $args['base'] );
		$link = str_replace( '%#%', $current + 1, $link );
		if ( $add_args )
			$link = add_query_arg( $add_args, $link );
		$link .= $args['add_fragment'];

		/** This filter is documented in wp-includes/general-template.php */
		$page_links[] = '<a class="next page-numbers '.$args['number_page_css_style'].'" href="' . esc_url( apply_filters( 'paginate_links', $link ) ) . '">' . $args['next_text'] . '</a>';
	endif;
	switch ( $args['type'] ) {
		case 'array' :
			return $page_links;

		case 'list' :
			$r .= "<ul class='page-numbers'>\n\t<li>";
			$r .= join("</li>\n\t<li>", $page_links);
			$r .= "</li>\n</ul>\n";
			break;

		default :
			$r = join("\n", $page_links);
			break;
	}
	return $r;
}
}