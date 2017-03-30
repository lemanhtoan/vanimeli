function initSwiperCarousel(options) {
    var autoplay_mode = false;
    var autoplayDisableOnInteraction = true;

    if(options.autoplay) {
        autoplay_mode = options.interval;
        autoplayDisableOnInteraction = false;
    }
    var swiper = new Swiper(options.class_filter, {
                            pagination: options.pagination,
                            nextButton: options.nextButton,
                            prevButton: options.prevButton,
                            scrollbar: options.scrollbar,
                            autoplay: autoplay_mode, //delay between transitions (in ms). If this parameter is not specified, auto play will be disabled
                            autoplayDisableOnInteraction: autoplayDisableOnInteraction,
                            speed: options.speed, //Duration of transition between slides (in ms)
                            slidesPerView: options.slide_by, //Number of slides per view (slides visible at the same time on slider's container).
                            slidesPerColumn: options.slide_in_col, //Number of slides per column, for multirow layout. Default: 1
                            slidesPerColumnFill: options.slide_col_fill, //Could be 'column' or 'row'. Defines how slides should fill rows, by column or by row
                            slidesPerGroup: options.slide_group, //Set numbers of slides to define and enable group sliding. Useful to use with slidesPerView > 1 . Default: 1
                            mousewheelControl: options.mousewheel, //Set to true to enable navigation through slides using mouse wheel
                            paginationClickable: true,
                            spaceBetween: options.space, //Distance between slides in px.
                            freeMode: options.freemode, //If true then slides will not have fixed positions
                            direction: options.direction, //Could be 'horizontal' or 'vertical' (for vertical slider).
                            loop: options.loop,
                            effect: options.effect //"slide", "fade", "cube" or "coverflow"
                           
                        });
    return swiper;
}

if(typeof(jQuery) != 'undefined') {
    (function($) {
        $(window).ready( function(){
            if($(".widget-swiper").length > 0) {
                $(".widget-swiper").each(function() {
                    var object_id = $(this).attr("id");
                    var autoplay = $(this).data("autoplay");
                    var interval = $(this).data("interval");
                    var speed = $(this).data("speed");
                    var limitview = $(this).data("limitview");
                    var space = $(this).data("space");
                    var direction = $(this).data("direction");
                    var loop = $(this).data("loop");
                    var effect = $(this).data("effect");
                    var pagination = $(this).data("pagination"); 
                    var nextButton = $(this).data("nextbutton");
                    var prevButton = $(this).data("prevbutton");
                    var scrollbar = $(this).data("scrollbar");
                    var freemode = $(this).data("freemode");
                    var slide_in_col = $(this).data("slideincol");
                    var slide_col_fill = $(this).data("slidecolfill");
                    var slide_group = $(this).data("slidegroup");
                    var mousewheel = $(this).data("mousewheel");

                    slide_in_col = (""==slide_in_col)?'1':slide_in_col;
                    slide_col_fill = (""==slide_col_fill)?'column':slide_col_fill;
                    slide_group = (""==slide_group)?'1':slide_group;
                    mousewheel = (1==mousewheel)?true:false;

                    limitview = (""==limitview)?'auto':limitview;
                    direction = (""==direction)?'horizontal':direction;
                    loop = (1==loop)?true:false;
                    effect = (""==effect)?'slide':effect;
                    freemode = (1==freemode)?true:false;
                    pagination = pagination?pagination:null;
                    nextButton = nextButton?nextButton:null;
                    prevButton = prevButton?prevButton:null;
                    scrollbar = scrollbar?scrollbar:null;

                    var options = {class_filter: "#"+object_id+" .swiper-container",
                                        autoplay: autoplay,
                                        interval: parseInt(interval),
                                        speed: parseInt(speed),
                                        slide_by: limitview,
                                        space: parseInt(space),
                                        freemode: freemode,
                                        direction: direction,
                                        loop: loop,
                                        effect: effect,
                                        slide_in_col: slide_in_col,
                                        slide_col_fill: slide_col_fill,
                                        slide_group: slide_group,
                                        mousewheel: mousewheel,
                                        pagination: pagination,
                                        nextButton: nextButton,
                                        prevButton: prevButton,
                                        scrollbar: scrollbar
                                    };
                    initSwiperCarousel( options );
                });
            }
        })
    })(jQuery);
} else {
   document.observe("dom:loaded", function() {
        if($$(".widget-swiper").length > 0) {
                $$(".widget-swiper").each(function(item) {
                    var object_id = item.readAttribute("id");
                    var autoplay = item.readAttribute("data-autoplay");
                    var interval = item.readAttribute("data-interval");
                    var speed = item.readAttribute("data-speed");
                    var limitview = item.readAttribute("data-limitview");
                    var space = item.readAttribute("data-space");
                    var direction = item.readAttribute("data-direction");
                    var loop = item.readAttribute("data-loop");
                    var effect = item.readAttribute("data-effect");
                    var freemode = item.readAttribute("data-freemode");
                    var pagination = item.readAttribute("data-pagination"); 
                    var nextButton = item.readAttribute("data-nextbutton");
                    var prevButton = item.readAttribute("data-prevbutton");
                    var scrollbar = item.readAttribute("data-scrollbar");
                    var slide_in_col = item.readAttribute("data-slideincol");
                    var slide_col_fill = item.readAttribute("data-slidecolfill");
                    var slide_group = item.readAttribute("data-slidegroup");
                    var mousewheel = item.readAttribute("data-mousewheel");

                    slide_in_col = (""==slide_in_col)?'1':slide_in_col;
                    slide_col_fill = (""==slide_col_fill)?'column':slide_col_fill;
                    slide_group = (""==slide_group)?'1':slide_group;
                    mousewheel = (1==mousewheel)?true:false;

                    limitview = (""==limitview)?'auto':limitview;
                    direction = (""==direction)?'horizontal':direction;
                    loop = (1==loop)?true:false;
                    effect = (""==effect)?'slide':effect;
                    freemode = (1==freemode)?true:false;
                    pagination = pagination?pagination:'null';
                    nextButton = nextButton?nextButton:'null';
                    prevButton = prevButton?prevButton:'null';
                    scrollbar = scrollbar?scrollbar:'null';

                    var options = {class_filter: "#"+object_id+" .swiper-container",
                                        autoplay: autoplay,
                                        interval: parseInt(interval),
                                        speed: parseInt(speed),
                                        slide_by: limitview,
                                        space: parseInt(space),
                                        freemode: freemode,
                                        direction: direction,
                                        loop: loop,
                                        effect: effect,
                                        slide_in_col: slide_in_col,
                                        slide_col_fill: slide_col_fill,
                                        slide_group: slide_group,
                                        mousewheel: mousewheel,
                                        pagination: pagination,
                                        nextButton: nextButton,
                                        prevButton: prevButton,
                                        scrollbar: scrollbar
                                    };
                    initSwiperCarousel( options );
                });
        }
    });
}
