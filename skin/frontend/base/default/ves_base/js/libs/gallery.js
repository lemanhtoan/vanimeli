(function($) {
$(function() {
	// ======================= imagesLoaded Plugin ===============================
	// https://github.com/desandro/imagesloaded

	// $('#my-container').imagesLoaded(myFunction)
	// execute a callback when all images have loaded.
	// needed because .load() doesn't work on cached images

	// callback function gets image collection as argument
	//  this is the container

	// original: mit license. paul irish. 2010.
	// contributors: Oren Solomianik, David DeSandro, Yiannis Chatzikonstantinou
	// http://coveroverflow.com/a/11381730/989439
	function mobilemode() {
	    var check = false;
	    (function(a){if(/(android|ipad|playbook|silk|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4)))check = true})(navigator.userAgent||navigator.vendor||window.opera);
	    return check;
	}
	$.fn.imagesLoaded 		= function( callback ) {
	var $images = this.find('img'),
		len 	= $images.length,
		_this 	= this,
		blank 	= 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==';

	function triggerCallback() {
		callback.call( _this, $images );
	}

	function imgLoaded() {
		if ( --len <= 0 && this.src !== blank ){
			setTimeout( triggerCallback );
			$images.off( 'load error', imgLoaded );
		}
	}

	if ( !len ) {
		triggerCallback();
	}

	$images.on( 'load error',  imgLoaded ).each( function() {
		// cached images don't fire load sometimes, so we reset src.
		if (this.complete || this.complete === undefined){
			var src = this.src;
			// webkit hack from http://groups.google.com/group/jquery-dev/browse_thread/thread/eee6ab7b2da50e1f
			// data uri bypasses webkit log warning (thx doug jones)
			this.src = blank;
			this.src = src;
		}
	});

	return this;
	};

	// gallery container
	var $rgGallery			= $('#rg-gallery'),
	// carousel container
	$esCarousel			= $rgGallery.find('div.es-carousel-wrapper'),
	// the carousel items
	$items				= $esCarousel.find('ul > li'),
	// total number of items
	itemsCount			= $items.length;
	var $viewfull	= $('<a href="#" class="rg-view-full"></a>'),
	$viewthumbs	= $('<a href="#" class="rg-view-thumbs rg-view-selected"></a>');
	
	Gallery				= (function() {
			// index of the current item
		var current			= 0, 
			// mode : carousel || fullview
			mode 			= 'grid',
			showfirst       = false,
			// control if one image is being loaded
			anim			= false,
			init			= function() {
				
				// (not necessary) preloading the images here...
				if($("#gallery-preload-tmpl").length > 0) {
					var gallery_preload =  $('#gallery-preload-tmpl').html();
				} else {
					var gallery_preload = '<div class="rg-image-wrapper" style="display:none">';
				        gallery_preload += '        {{if itemsCount > 1}}';
				        gallery_preload += '            <div class="rg-image-nav">';
				        gallery_preload += '                <a href="#" class="rg-image-nav-prev">Previous Image")</a>';
				        gallery_preload += '                <a href="#" class="rg-image-nav-next">Next Image</a>';
				        gallery_preload += '            </div>';
				        gallery_preload += '        {{/if}}';
				        gallery_preload += '        <div class="rg-image"></div>';
				        gallery_preload += '        <div class="rg-loading"></div>';
				        gallery_preload += '        <div class="rg-caption-wrapper" style="display:none;">';
				        gallery_preload += '            <div class="rg-caption" style="display:none;">';
				        gallery_preload += '                <p></p>';
				        gallery_preload += '            </div>';
				        gallery_preload += '        </div>';
				        gallery_preload += '    </div>';
				}

				$items.add(gallery_preload).imagesLoaded( function() {
					// add options
					_addViewModes();
					
					// add large image wrapper
					_addImageWrapper();
						
					// show first image
					if(showfirst || mobilemode()) 
						_showImage( $items.eq( current ) );
						
				});
				
				// initialize the grid
				if( mode === 'grid' )
					_initGrid();
			},
			
			_initGrid	= function() {
				
				// we are using the elastislide plugin:
				// http://tympanus.net/codrops/2011/09/12/elastislide-responsive-carousel/
				$esCarousel.show();
				$esCarousel.find(".es-carousel ul").show();

				$esCarousel.find(".es-carousel ul li").click(function( event ) {
					$item = $(this);
					if( anim ) return false;
					anim	= true;
					// on click show image
					_showImage($item);
					// change current
					current	= $item.index();
					$viewfull.trigger('click');
					_showImageWrapper();
				});
				// set elastislide's current to current
				//$esCarousel.elastislide( 'setCurrent', current );
				
			},
			_showImageWrapper = function () {
				$(".rg-image-wrapper").fadeIn();
			},
			_hideImageWrapper = function () {
				$(".rg-image-wrapper").hide();
			},
			_addViewModes	= function() {
				
				// top right buttons: hide / show carousel
				
				$rgGallery.prepend( $('<div class="rg-view"/>').append( $viewfull ).append( $viewthumbs ) );
				
				$viewfull.on('click.rgGallery', function( event ) {
					$esCarousel.hide();
					$viewfull.addClass('rg-view-selected');
					$viewthumbs.removeClass('rg-view-selected');
					mode	= 'fullview';
					return false;
				});
				
				$viewthumbs.on('click.rgGallery', function( event ) {
					_initGrid();
					$viewthumbs.addClass('rg-view-selected');
					$viewfull.removeClass('rg-view-selected');

					mode	= 'grid';
					return false;
				});
				
				if( mode === 'fullview' )
					$viewfull.trigger('click');
				

			},
			_addImageWrapper= function() {
				
				// adds the structure for the large image and the navigation buttons (if total items > 1)
				// also initializes the navigation events
				
				$('#img-wrapper-tmpl').tmpl( {itemsCount : itemsCount} ).appendTo( $rgGallery );
				
				if( itemsCount > 1 ) {
					// addNavigation
					var $navPrev		= $rgGallery.find('a.rg-image-nav-prev'),
						$navNext		= $rgGallery.find('a.rg-image-nav-next'),
						$imgWrapper		= $rgGallery.find('div.rg-image');
						
					$navPrev.on('click.rgGallery', function( event ) {
						_navigate( 'left' );
						return false;
					});	
					
					$navNext.on('click.rgGallery', function( event ) {
						_navigate( 'right' );
						return false;
					});
				
					// add touchwipe events on the large image wrapper
					$imgWrapper.touchwipe({
						wipeLeft			: function() {
							_navigate( 'right' );
						},
						wipeRight			: function() {
							_navigate( 'left' );
						},
						preventDefaultEvents: false
					});

					$imgWrapper.on('click', function(event) {
						_hideImageWrapper();
						$viewthumbs.trigger('click');
					});
				
					$(document).on('keyup.rgGallery', function( event ) {
						if (event.keyCode == 39)
							_navigate( 'right' );
						else if (event.keyCode == 37)
							_navigate( 'left' );	
					});
					
				}
				
			},
			_navigate		= function( dir ) {
				
				// navigate through the large images
				
				if( anim ) return false;
				anim	= true;
				
				if( dir === 'right' ) {
					if( current + 1 >= itemsCount )
						current = 0;
					else
						++current;
				}
				else if( dir === 'left' ) {
					if( current - 1 < 0 )
						current = itemsCount - 1;
					else
						--current;
				}
				
				_showImage( $items.eq( current ) );
				
			},
			_showImage		= function( $item ) {
				
				// shows the large image that is associated to the $item
				
				var $loader	= $rgGallery.find('div.rg-loading').show();
				
				$items.removeClass('selected');
				$item.addClass('selected');
					 
				var $thumb		= $item.find('img'),
					largesrc	= $thumb.data('large'),
					zoomsrc 	= $thumb.data('zoom-image'),
					title		= $thumb.data('description');
				if(zoomsrc && zoomsrc != largesrc) {
					largesrc = zoomsrc;
				}
				$('<img/>').load( function() {
					
					$rgGallery.find('div.rg-image').empty().append('<img src="' + largesrc + '"/>');
					if( title )
						$rgGallery.find('div.rg-caption').fadeIn().children('p').empty().text( title );
					
					
					$loader.hide();
					
					anim	= false;
					
				}).attr( 'src', largesrc );
				
			},
			addItems		= function( $new ) {
			
				$esCarousel.find('ul').append($new);
				$items 		= $items.add( $($new) );
				itemsCount	= $items.length; 
				$esCarousel.elastislide( 'add', $new );
			
			};
		
		return { 
			init 		: init,
			addItems	: addItems
		};
	
	})();

	$(window).load(function() {
		if($(".rg-gallery").length > 0) {
			Gallery.init();
		}
		
	})
	/*
	Example to add more items to the gallery:
	
	var $new  = $('<li><a href="#"><img src="images/thumbs/1.jpg" data-large="images/1.jpg" alt="image01" data-description="From off a hill whose concave womb reworded" /></a></li>');
	Gallery.addItems( $new );
	*/
});
})(jQuery);