(function($){
			
	var GuestWishlist	= function(options){
		var object	= this;
		var defaults = {
			'elements':        {
				'add'		: '.link-wishlist',
				'remove'	: '#wishlist-sidebar .btn-remove, .btn-wishlist-remove',
				'cart'		: '.btn-wishlist-cart',
				'update'	: '.btn-wishlist-update'
			},
			'translate'		: {
				'add'		: 'Add to Wishlist',
				'remove'	: 'Remove from Wishlist',
				'error'		: 'Sorry, We can\'t process your request. Please try again in next time.',
				'confirm'	: 'Are you sure you would like to remove this item from the wishlist?',
				'login'		: 'Please login before add to wishlist.'
			},
			'area'			: {
				'cart'			: $('.checkout-cart-index .cart'),
				'wishlist'		: $('.wishlist-index-index .my-wishlist'),
				'sidebar'		: $('.block-wishlist'),
			},
			'count'			: {
				'wishlist'		: $('[href$="wishlist/"]', $('.header .links')),
				'cart'			: $('.top-link-cart', $('.header .links'))
			},			
			'url'			: {
				'remove'		: '',
				'add'			: ''
			},
			'added'			: {},			
			'selector_add'	: 'a[href*="wishlist/index/add/product/{product_id}/"]',
			'class_has_item': 'added',
			'number_col'	: 3,
			'showMsg'		: 1,
			'enable'		: 0,
			'useAjax'		: 1,
		};
		
		this.config = $.extend(defaults, options || {});
		/*
		if(!object.config.enableGuest){
			object.addMsg(object.config.translate.login, 1);
			return false;
		}*/
	 
		if(!object.config.useAjax || !object.config.enable){			
			return false;
		}
		
		$.each(this.config.added, function(product_id, item_id){
			var selector 	= object.config.selector_add.replace("{product_id}", product_id);			
			var title		= object.config.translate.remove;
			var remove_url	= object.config.url.remove.replace("{item_id}", item_id);
			var hasItem		= object.config.class_has_item;
			
			$(selector)
				.attr("href", remove_url)
				.attr("title", title)
				.addClass(hasItem)
				.html(title);			
		});
		
		$.each(this.config.elements, function(key,class_name){
			
			$(class_name).each(function(){
			
				$(this)
					.unbind("click")
					.removeAttr("onclick");
				
				switch(key){
					case 'add' :
						$(this).click(function(){
							
							if($(this).is('[href*="wishlist/index/fromcart/item/"]')){
								object.moveToWishlist(this);
							}else{
								if($(this).hasClass(object.config.class_has_item)){
									object.add(this, "remove");
								}else{
									object.add(this, "add");
								}
							}
							
							return false;
						});
						break;
					case 'remove' :
						$(this).click(function(){
							if(confirm(object.config.translate.confirm)){
								object.remove(this);
							}							
							return false;
						});
						break;
					case 'cart':
						$(this).click(function(){
							object.cart(this);						
							return false;
						});
						break;
					case 'update':
						
						$(this).click(function(){
							object.update(this);						
							return false;
						});
						break;
				}							
			});
			
		});
		
		this.moveToWishlist	= function(el){
			var url 		= $(el).attr('href');
			var data		= {};
			
			object.ajax(url, data, function(data, textStatus, jqXHR){
				$(el).closest("tr").remove();
				
				object.updateData(el, data);
			});
		};
		
		this.cart	= function(el){
			
			var url 		= $(el).attr('href');
			var data		= {};
			
			object.ajax(url, data, function(data, textStatus, jqXHR){
				if(!data.redirect){
					if($(el).closest("li").length){
						$(el).closest("li").remove();
					}
				}
								
				object.updateData(el, data);
			});
		};
		
		this.update	= function(el){
			
			var url 		= $(el).attr('href');
			
			var form		= $(el).closest('form');			
			var data		= form.length ? form.serialize() : {};			
			
			object.ajax(url, data, function(data, textStatus, jqXHR){								
				object.updateData(el, data);
			});
		};
		
		this.remove	= function(el){
			
			var url 		= $(el).attr('href');
			var data		= {};
			
			object.ajax(url, data, function(data, textStatus, jqXHR){
				
				if($(el).closest("li").length){
					$(el).closest("li").remove();
				}
				
				if($(el).closest("tr").length){
					$(el).closest("tr").remove();
				}
				
				object.updateData(el, data);
			});
		};
		
		this.add	= function(el, type){
			var form		= $(el).closest('form');
			var url 		= $(el).attr('href');
			var data		= form.length ? form.serialize() : {};
			
			if(form.length && form.attr('id') && type=="add"){
				var validate = new VarienForm(form.attr('id'));
				if(!validate.validator.validate()){
					$('html, body').animate({
                        scrollTop: $('.validation-failed').first().offset().top
                    }, 500);
					return;
				}
			}
			
			object.ajax(url, data, function(data, textStatus, jqXHR){
				var title		= type == "add" ? object.config.translate.remove : object.config.translate.add;
				var url			= type == "add" ? object.config.url.remove.replace("{item_id}", data.item_id) : object.config.url.add.replace("{product_id}", data.product_id);
				var hasClass	= object.config.class_has_item;
				$(el).hasClass(hasClass) ? $(el).removeClass(hasClass) : $(el).addClass(hasClass);
				
				
				$(el)
					.attr("href", url)					
					.attr('title', title)
					.html(title);
				
				object.updateData(el, data);
			});
		};
		
		this.ajax	= function(url, data, callback){
			
			data	= data || {};
			
			$.ajax({
				'url'			: url,	
				'type'			: 'POST',
				'dataType'		: 'json',	
				'data'			: data,		
				'beforeSend'	: function(jqXHR, settings){
					NProgress.start();
					NProgress.set(0.5);	
				},				
				'success'		: callback,
				'complete'		: function(jqXHR, textStatus){
					NProgress.done();
				},			
				'error': function(jqXHR, textStatus, errorThrown){
					object.addMsg(object.config.translate.error, 1);
					NProgress.done();
				}
			});
			
		};
		
		this.addMsg	= function(msg, type){
			var objectMsg	= $('.wishlist-notify');			
			
			if(type){
				objectMsg.addClass("wishlist-notify-error");
			}else{
				objectMsg.removeClass("wishlist-notify-error");
			}
			
			objectMsg.slideUp(500);
			
			objectMsg
				.html(msg)
				.slideDown(500);
			
			setTimeout(function () {
				objectMsg
					.slideUp(500);
				}, 7000);
		};
		
		this.updateData	= function(el, data){
			
			var config	= object.config;
			
			if(data.redirect){
				
				$.fancybox({
					'type' 	: 'ajax',
					'href'	: data.redirect							
				});				
				//document.location.href = data.redirect;
				return false;
			}
			
			if(data.msg && config.showMsg){
				object.addMsg(data.msg, data.error);
			}
			
			
			// update wishlist count
			if(data.wcount && config.count.wishlist.length){				
				config.count.wishlist.html(data.wcount).attr('title', data.wcount).effect("pulsate",{times:5}, 1000);
			}			
			
			// update cart count
			if(data.ccount && config.count.cart.length){
				config.count.cart.html(data.ccount).attr('title', data.ccount).effect("pulsate",{times:5}, 1000);				
			}
			
			// refesh cart content
			if(config.area.cart.length && data.emptyCart){							
				config.area.cart.html(data.cart);
			}	
			
			// refesh wishlist content
			if(config.area.wishlist.length){
				if(data.emptyWL){
					config.area.wishlist.html(data.wishlist);
				}else{
					
					var ulList	= $('ul.wishlist-list', config.area.wishlist);
					
					$.each(ulList, function(i,ul){
						var liList = $('li', ul);
						//liList.removeClass().addClass('item');
						
						if(liList.length != config.number_col){
							var ulLast	= ulList[ulList.length-1];
							$('li:last', ulLast).appendTo(ul);
							if(!$('li', ulLast).length){								
								ulLast.remove();
								ulList.splice(ulList.length-1,1);
							}							
						}									
					});
					
					$.each($('ul.wishlist-list', config.area.wishlist), function(){
						$('li', $(this)).removeClass().addClass('item');
						$('li:last', $(this)).addClass('last');
						$('li:first', $(this)).addClass('first');
					});					
					
				}
				
			}	
			
			// refesh sidebar
			if(config.area.sidebar.length){							
				config.area.sidebar
				.html($(data.wishlist_sidebar).html())				
				.effect("pulsate",{times:5}, 1000);
				
				$.each($('.btn-remove', $(config.area.sidebar)), function(){
					$(this)
						.unbind("click")
						.removeAttr("onclick");
					
					$(this).click(function(){
						if(confirm(object.config.translate.confirm)){							
							object.remove(this);
						}							
						return false;
					});
				});
			}			
			
		};		
	}
	
	$.fn.GuestWishlist = function(options){
		return new GuestWishlist(options);
	};
	
})(jQuery);