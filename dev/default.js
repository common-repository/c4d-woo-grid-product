var c4dwgp = {};
(function($){
	c4dwgp.products = function(id, params, loadmore, callback){
		var defaultParam = { 
			'action': 'c4d_woo_gp', 
			'c4dajaxgp': 1, 
			'category': ''
		},
		slider = $('#' + id),
		params = $.extend({}, defaultParam, params),
		dataName = 'category-' + params.category + params.page + params.count;
		if ($(c4dwgp[id]).data(dataName)) {
			c4dwgp.updateHtml(loadmore, id, $(c4dwgp[id]).data(dataName), callback);
		} else {
			$.get(c4d_woo_gp.ajax_url, params, function(res){
				$(c4dwgp[id]).data(dataName, res);
				c4dwgp.updateHtml(loadmore, id, res, callback);
			});	
		}
	};
	c4dwgp.updateHtml = function(loadmore, id, res, callback){
		if (typeof id != 'string') {
			id = id.attr('id');
		} 
		if (loadmore) {
			var newItems = $(res).find('.c4d-woo-gp__grid > div');
			if (newItems.length > 0) {
				$('#' + id).append(newItems.html());	
			}
		} else {
			$('#' + id).parent().html('<div id="'+id+'">' + $(res).find('.c4d-woo-gp__grid > div').html() + '</div>');	
		}
		$('#' + id).parents('.c4d-woo-gp').find('.c4d-woo-gp__loading').removeClass('active');
 		
 		if (callback) {
			callback(res);
		}
	};
	$(document).ready(function(){
		$(".c4d-woo-gp").each(function(){
			var id = $(this).find('.c4d-woo-gp__grid > div').attr('id'),
			self = this;
			// load by cate
			$(self).find('.c4d-woo-gp__categories span').each(function(index, value){
				$(this).on('click', function(event){
					event.preventDefault();
					var cate = this;
					$(cate).addClass('active').siblings().removeClass('active');
					$(self).find('.c4d-woo-gp__loading').addClass('active');
					$(self).css('min-height', $(self).height());
					c4dwgp.products(
						id, 
						{ 
							'count': c4dwgp[id].count,
							'category': $(cate).attr('data-category')
						}, 
						false, 
						function(){
							if ($(self).data('load-more-empty-' + $(cate).attr('data-category')) == '1') {
								$(self).addClass('load-more-empty');
							} else {
								$(self).removeClass('load-more-empty');
							}
							$(self).parents('c4d-woo-gp').find('.c4d-woo-gp__loadmore a').attr('data-page', 0);
							$(self).css('min-height', '0');
						}
					);
					return false;	
				});
			});
			$(self).find('.c4d-woo-gp__loadmore a').each(function(index, el){
				$(this).on('click', function(event){
					event.preventDefault();
					var loadButton = this,
					category = $(this).parents('.c4d-woo-gp').find('.c4d-woo-gp__categories span.active').attr('data-category');
					$(self).find('.c4d-woo-gp__loading').addClass('active');
					$(loadButton).parents('.c4d-woo-gp__loadmore').addClass('loading');
					c4dwgp.products(
						id, 
						{ 
							'category': category,
							'count': c4dwgp[id].count,
							'page': $(this).attr('data-page'),
							'loadmore': 1
						}, 
						true, 
						function(res){
							$(loadButton).parents('.c4d-woo-gp__loadmore').removeClass('loading');
							$(loadButton).attr('data-page', parseInt($(loadButton).attr('data-page')) + 1);
							if ($(res).find('.c4d-woo-gp__grid > div .item').length < 1) {
								$(self).addClass('load-more-empty');
								$(self).data('load-more-empty-' + category, 1);
							}
						}
					);
					return false;
				});
			});
		});
	});
})(jQuery);