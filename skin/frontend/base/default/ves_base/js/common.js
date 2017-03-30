var ves_pagebuilder_enabled = true;

//Ajax generate widget function
function ajaxGenerateWidget(widget, widgetJson, ajaxUrl) {
    var widget_shortcode = typeof(widgetJson.widget)!="undefined"?widgetJson.widget:'';
    var widget_key = typeof(widgetJson.wkey)!="undefined"?widgetJson.wkey:'';

    if(widget_shortcode) {
        jQuery.ajax({
            url: ajaxUrl,
            method: "POST",
            cache: true,
            dataType: 'json',
            data: {wkey: widget_key, shortcode: widget_shortcode},
            beforeSend: function(){
                jQuery(widget).html('<div class="ajax-loading-img">&nbsp;</div>');
            },
            success: function(dataResponse){
                jQuery(widget).hide().html(dataResponse.html).fadeIn();
            }
        });
    }
}

function loadScript(src,callback){
    var script = document.createElement("script");
    script.type = "text/javascript";
    if(callback)script.onload=callback;
    document.getElementsByTagName("head")[0].appendChild(script);
    script.src = src;
}
jQuery(document).ready(function($) {
    var browser = {
        isIe: function () {
            return navigator.appVersion.indexOf("MSIE") != -1;
        },
        navigator: navigator.appVersion,
        getVersion: function() {
            var version = 999; // we assume a sane browser
            if (navigator.appVersion.indexOf("MSIE") != -1)
                // bah, IE again, lets downgrade version number
                version = parseFloat(navigator.appVersion.split("MSIE")[1]);
            return version;
        }
    };
    if (typeof(WOW) != "undefined" && ( (browser.isIe() && browser.getVersion() > 9) || !browser.isIe() ) ) {
        wow = new WOW(
          {
            boxClass:     'ves-animate',      // default
            animateClass: 'animated', // default
            offset:       0,          // default
            mobile:       true,       // default
            live:         true        // default
          }
        )
        wow.init();
    }
    // Synchronise WoW
});

/*Init Accordion*/

/* Venustheme Frontend Common Js */
var accordian = {
    init: function(element_id){
        
        var $current_obj = jQuery(element_id),
                rotate_in =       $current_obj.data('rotate-in'),
                rotate_in_speed =       $current_obj.data('rotate-in-speed'),
                rotate_out =       $current_obj.data('rotate-out'),
                rotate_out_speed =       $current_obj.data('rotate-out-speed'),
                bg_in_y =       $current_obj.data('bg-in-y'),
                bg_in_opacity =       $current_obj.data('bg-in-opacity'),
                bg_in_scale =       $current_obj.data('bg-in-scale'),
                bg_in_speed =       $current_obj.data('bg-in-speed'),
                bg_out_y =       $current_obj.data('bg-out-y'),
                bg_out_opacity =       $current_obj.data('bg-out-opacity'),
                bg_out_scale =       $current_obj.data('bg-out-scale'),
                bg_out_speed =       $current_obj.data('bg-out-speed');

       
        if(rotate_in === "" || typeof(rotate_in) == "undefined") {
            rotate_in = "0deg";
        } 
        if(rotate_in_speed === "" || typeof(rotate_in_speed) == "undefined") {
            rotate_in_speed = 350;
        } 
        if(rotate_out === "" || typeof(rotate_out) == "undefined") {
            rotate_out = "135deg";
        } 
        if(rotate_out_speed === "" || typeof(rotate_out_speed) == "undefined") {
            rotate_out_speed = 350;
        } 
        if(bg_in_y === "" || typeof(bg_in_y) == "undefined") {
            bg_in_y = 0;
        } 
        if(bg_in_scale === "" || typeof(bg_in_scale) == "undefined") {
            bg_in_scale = 1;
        } 
        if(bg_in_speed === "" || typeof(bg_in_speed) == "undefined") {
            bg_in_speed = 800;
        }
        if(bg_out_y === "" || typeof(bg_out_y) == "undefined") {
            bg_out_y = -80;
        }
        if(bg_out_opacity === "" || typeof(bg_out_opacity) == "undefined") {
            bg_out_opacity = .75;
        }
        if(bg_out_scale === "" || typeof(bg_out_scale) == "undefined") {
            bg_out_scale = 1.2;
        } 
        if(bg_out_speed === "" || typeof(bg_out_speed) == "undefined") {
            bg_out_speed = 800;
        }  
        accordian.max_height(element_id);

        jQuery(element_id).on('click', '.menu-title', function(){

            if ( !jQuery(element_id).hasClass('press') ){

                var $this =         jQuery(this),
                    menuCat =       $this.data('menu'),
                    bg_effect = $this.data('bg-effect'),
                    $foodItems =    jQuery('.panel-item-content').filter('[data-menu="'+ menuCat +'"]');
                
                if(bg_effect === "" || typeof(bg_effect) == "undefined") {
                    bg_effect = 1;
                }

                if ( $this.hasClass('open') ){
    
                    // Animate The Things
                    $this.find('i').transition({ rotate: rotate_in }, rotate_in_speed);
                    if(bg_effect) {
                        $this.find('.bg').transition({y: bg_in_y, opacity: bg_in_opacity, scale: bg_in_scale }, bg_in_speed, 'ease');
                    }
                    // Handle classes
                    setTimeout(function(){
                        $this.removeClass('open').addClass('closed');
                    }, 50);
    
                    // Hide the Menu
                    $foodItems.transition({ height: 0, complete: function(){
                        $foodItems.attr({ 'style' : '' });
                    } }, 400);
    
                    
                    
                } else {
                    
                    // $j('section#accordian .open').not(this).trigger('click');
                    // Hacky get height
                    $foodItems.show();
                    var foodItems_height = $foodItems.height();
                    $foodItems.hide();
                    var speed = foodItems_height * 1.05;
                    
                    // Animate The Things

                    $this.find('i').transition({ rotate: rotate_out }, rotate_out_speed);
                    if(bg_effect) {
                        $this.find('.bg').transition({ y: bg_out_y, opacity: bg_out_opacity, scale: bg_out_scale }, bg_out_speed, 'ease');
                    }
                    // Handle classes
                    $this.addClass('open').removeClass('closed');
                    
                    // Show the Menu
                    $foodItems.show().height(0)
                        .css({ 'opacity' : 0 })
                        .transition({ opacity: 1, height: foodItems_height }, speed);
                    
                }
            } // End if isn't press
            
        });
        
        // Hovers for Menu
        
            jQuery(element_id + ' .menu-title').on({
                mouseenter: function(e){
                    
                        var $this = jQuery(this),
                        bg_effect = $this.data('bg-effect'),
                        in_press_y =       $this.data('in-press-y'),
                        in_press_opacity =       $this.data('in-press-opacity'),
                        in_press_speed =       $this.data('in-press-speed');

                        in_y =       $this.data('in-y'),
                        in_opacity =       $this.data('in-opacity'),
                        in_speed =       $this.data('in-speed');

                        if(bg_effect === "" || typeof(bg_effect) == "undefined") {
                            bg_effect = 1;
                        }
                        if(in_press_y == "" || typeof(in_press_y) == "undefined") {
                            in_press_y = -80;
                        }

                        if(in_press_opacity == "" || typeof(in_press_opacity) == "undefined") {
                            in_press_opacity = .2;
                        }

                        if(in_press_speed == "" || typeof(in_press_speed) == "undefined") {
                            in_press_speed = 1200;
                        }

                        if(in_y == "" || typeof(in_y) == "undefined") {
                            in_y = -80;
                        }

                        if(in_opacity == "" || typeof(in_opacity) == "undefined") {
                            in_opacity = .75;
                        }

                        if(in_speed == "" || typeof(in_speed) == "undefined") {
                            in_speed = 1200;
                        }

                        if(bg_effect) {
                            if ( jQuery(element_id).hasClass('press') ){
                                if ( $this.hasClass('closed') ){
                                    $this.find('.bg').stop().transition({ y: in_press_y, opacity: in_press_opacity }, in_press_speed, 'ease');
                                }                   
                            } else {
                                if ( $this.hasClass('closed') ){
                                    $this.find('.bg').stop().transition({ y: in_y, opacity: in_opacity }, in_speed, 'ease');
                                }                   
                            }
                        }
                },
                mouseleave: function(e){
                    var $this = jQuery(this),
                    bg_effect = $this.data('bg-effect'),
                    leave_y =       $this.data('out-y'),
                    leave_opacity =       $this.data('out-opacity'),
                    leave_speed =       $this.data('out-speed');

                    if(bg_effect === "" || typeof(bg_effect) == "undefined") {
                        bg_effect = 1;
                    }

                    if(leave_y == "" || typeof(leave_y) == "undefined") {
                        leave_y = 0;
                    }
                    if(leave_opacity == "" || typeof(leave_opacity) == "undefined") {
                        leave_opacity = 0;
                    }
                    if(leave_speed == "" || typeof(leave_speed) == "undefined") {
                        leave_speed = 600;
                    }

                    if ( bg_effect && $this.hasClass('closed') ){
                        $this.find('.bg').stop().transition({ y: leave_y, opacity: leave_opacity }, leave_speed, 'ease');
                    }
                }
            });
        
    },
    max_height: function(element_id){
        jQuery(element_id + ' .panel-item-content').show();
        
        jQuery(element_id + ' .panel-item-content').hide();
    }
}

/* Offcanvas Sidebars */
jQuery(document).ready( function ($){
    if( $(".offcanvas-widget-siderbars").length > 0 ) { 
        //$('.offcanvas-sidebars-buttons button').hide();
        $( ".widget-sidebar" ).each( function(){
            $('[data-for="'+$(this).attr("id")+'"]').show();
            $(this).attr("id","ves-"+$(this).attr("id") ).addClass("offcanvas-widget-sidebar");
        } );
        $(".offcanvas-widget-sidebars-buttons button").bind( "click", function(){

            if( $(this).data("reffor") == "column-left" ){
                $(".offcanvas-widget-siderbars").removeClass("column-right-active");
            }else {
                $(".offcanvas-widget-siderbars").removeClass("column-left-active");
            }
            $(".offcanvas-widget-siderbars").toggleClass( $(this).data("reffor")+"-active" );
            $("#ves-"+$(this).data("for") ).toggleClass("canvas-show");
        } );
     }
    /** 
     * 
     * Automatic apply Parallax
     */

    if($(".ves-parallax").length > 0 && typeof($.fn.parallax) != "undefined") {
        $(".ves-parallax").css("background-attachment", "fixed");
        $(".ves-parallax").each( function () {
            var percent = $(this).data("parallax-percent");
            percent = (percent!='')?percent:'50%';
            var scroll = $(this).data("parallax-scroll");
            scroll = (scroll!='' && scroll!='0')?scroll:'0.4';
            $(this).parallax(percent, scroll);  
        })
    }
    /** 
     * 
     * Update collapse icon
     */
   if($(".widget-enable-collapse").length > 0) {
        $( ".widget-enable-collapse" ).each( function(){
            var parent_element = $(this).parent();
            $(this).on("hide.bs.collapse", function(){
                $(parent_element).find(".opener").html('<i class="fa fa-angle-down"></i>');
              });
            $(this).on("show.bs.collapse", function(){
                $(parent_element).find(".opener").html('<i class="fa fa-angle-up"></i>');
            });
        })
    }

    /** 
     * 
     * Automatic apply accordian
     */
    if($(".accordian-play").length > 0) {
        $(".accordian-play").each( function(){
            var element_id = $(this).attr("id");
            accordian.init("#" + element_id);
        });
    }

    /** 
     * 
     * Automatic apply colorbox popup
     */
    if($(".colorbox-play").length > 0) {
        $(".colorbox-play").each( function(){
            var element_id = $(this).attr("id");
            var popup_width = $(this).data('width');
            var popup_height = $(this).data('height');
            var popup_opacity = $(this).data('opacity');

            if(popup_width == "" || typeof(popup_width) == "undefined" ) {
                popup_width = false;
            }
            if(popup_height == "" || typeof(popup_height) == "undefined" ) {
                popup_height = false;
            }

            if(popup_opacity == "" || typeof(popup_opacity) == "undefined" ) {
                popup_opacity = 0.5;
            }

            if(popup_width && popup_height) {
                $(this).colorbox({
                    width: "'"+popup_width+"'", 
                    height: "'"+popup_height+"'",
                    overlayClose: true,
                    opacity: popup_opacity,
                    rel: false
                });
            } else if(popup_width && !popup_height) {
                $(this).colorbox({
                    width: "'"+popup_width+"'", 
                    overlayClose: true,
                    opacity: popup_opacity,
                    rel: false
                });
            } else if(popup_width && !popup_height) {
                $(this).colorbox({
                    height: "'"+popup_height+"'",
                    overlayClose: true,
                    opacity: popup_opacity,
                    rel: false
                });
            } else {
                $(this).colorbox({
                    overlayClose: true,
                    opacity: popup_opacity,
                    rel: false
                });
            }
        });
    }

    /** 
     * 
     * Automatic apply fancybox popup
     */
    if($(".fancybox-play").length > 0) {
        $(".fancybox-play").each( function(){
            var element_id = $(this).attr("id");
            var popup_width = $(this).data('width');
            var popup_height = $(this).data('height');
            var popup_opacity = $(this).data('opacity');

            if(popup_width == "" || typeof(popup_width) == "undefined" ) {
                popup_width = false;
            }
            if(popup_height == "" || typeof(popup_height) == "undefined" ) {
                popup_height = false;
            }

            if(popup_opacity == "" || typeof(popup_opacity) == "undefined" ) {
                popup_opacity = 0.5;
            }

            if(popup_width && popup_height) {
                $(this).fancybox({
                    width: "'"+popup_width+"'", 
                    height: "'"+popup_height+"'",
                    opacity: popup_opacity,
                    openEffect  : 'none',
                    closeEffect : 'none'
                });
            } else if(popup_width && !popup_height) {
                $(this).fancybox({
                    width: "'"+popup_width+"'", 
                    opacity: popup_opacity,
                    openEffect  : 'none',
                    closeEffect : 'none'
                });
            } else if(popup_width && !popup_height) {
                $(this).fancybox({
                    height: "'"+popup_height+"'",
                    opacity: popup_opacity,
                    openEffect  : 'none',
                    closeEffect : 'none'
                });
            } else {
                $(this).fancybox({
                    opacity: popup_opacity,
                    openEffect  : 'none',
                    closeEffect : 'none'
                });
            }
        });
    }
   
} );
