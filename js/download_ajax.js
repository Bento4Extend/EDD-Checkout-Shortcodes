$(document).ready(function(){
	 
	var not_running = true;
	var not_to_load = false
	remove_links();
	console.log(global_checkout_args);
	console.log(global_checkout_args.length);
	console.log($.isArray(global_checkout_args));
	console.log(is_Continue(global_checkout_args));
	var conti_index = 0;				 
		conti_index = is_Continue(global_checkout_args) 
		if(conti_index !== false )
		{
			
		
			$(window).scroll(function(){
				if(!not_to_load)
				{ 
					var window_scroll = $(window).scrollTop();
					window_scroll = window_scroll + $(window).height();
					var end_scroll = $('[data-index='+conti_index+'].download-checkout-loop-outer #page-end').offset().top;
					
					 
					if( window_scroll  > end_scroll && not_running )
					{
						$('[data-index='+conti_index+'].download-checkout-loop-outer #page-end').find('.download-loader').show();
						not_running = false; 
						
						var current_page = $('[data-index='+conti_index+'].download-checkout-loop-outer #page_num').val();
						 
						current_page++;
						
						 
						data_post = { 
							page_num: current_page, 
							 
							action: "get_next_checkout_page",
							 
							continue : 'yes'
							
						}
						data_post = $.extend( data_post, get_global_data(global_checkout_args[conti_index]) );	
						
						/* Request for data */
						$.ajax({
						  method: "POST",
						  url:   get_ajax_url(global_checkout_args[conti_index]),
						  data: data_post ,
						  success: function(data)
						  {
							 
							$('[data-index='+conti_index+'].download-checkout-loop-outer .download-list-container').append(data.content.replace(/\\"/g, '"'));							
							if( data.max_page > current_page )
								$('[data-index='+conti_index+'].download-checkout-loop-outer #page_num').val(current_page);
							else
							{
								$('[data-index='+conti_index+'].download-checkout-loop-outer #page_num').val('notload');
								not_to_load = true
							}
							$('[data-index='+conti_index+'].download-checkout-loop-outer').find('.download-loader').hide();
							not_running = true;
						  },
						  dataType:'json'
						}).fail(function(){
							
							not_running = true;
						}); 
						
					}
				}
				
			}).scroll();
		} 
			$.each( global_checkout_args, function( key, value ) {
				if(value.atts.ajax == 'yes')
				{
				  $('[data-index='+key+'].download-checkout-loop-outer .download-paginate a.page-numbers').live( 'click', function(e){
					e.preventDefault();
					 
						var this_new=$(this);
						var download_index = this_new.closest('.download-checkout-loop-outer').attr('data-index');
						 
						this_new.closest('.download-checkout-loop-outer').find('.download-loader').show();
						 
						
						var current_page = this_new.closest('.download-paginate').find('.current').text();
						 
						if($(this).hasClass('prev'))
							current_page--;
						else if($(this).hasClass('next'))
							current_page++;
						else if($(this).hasClass('first'))
							current_page = 1;
						else if($(this).hasClass('last'))
							current_page = $(this).attr('data-page');
						else
							current_page = $(this).text()
						 
						data_post = { 
							page_num: current_page,  
							action: "get_next_checkout_page",
							continue : 'no', 
						}
						 
						data_post = $.extend( data_post, get_global_data(global_checkout_args[download_index]) );
						console.log(global_checkout_args);
						/* Request for data */
						$.ajax({
						  method: "POST",
						  url:  get_ajax_url(global_checkout_args[download_index]),
						  data: data_post ,
						  success: function(data)
						  {
							console.log(data);
							this_new.closest('.download-checkout-loop-outer').html(data.content.replace(/\\"/g, '"'));
							remove_links() 
				 
							this_new.closest('.download-checkout-loop-outer').find('.download-loader').hide();
							
						  },
						  dataType:'json'
						});
					 
					});	
				}
			});
			
		 
	 
});

function remove_links()
{
	$('.download-paginate.yes a').removeAttr('href','');
}
function is_Continue(param)
{
	var flag = false;
		$.each( param, function( key, value ) {
			if(value.atts.continue == 'yes' && flag == false)
				flag = key;
		});
		console.log(flag);
		return flag;
}

function get_global_data(param)
{
	
	data_post = { 
		args: param.query_args, 
		content: param.content,
		post_type : param.post_type,
		atts:param.atts
	}
	console.log(data_post);
	return data_post;
	
}

function get_ajax_url(param)
{
	return param.ajax_url;
}