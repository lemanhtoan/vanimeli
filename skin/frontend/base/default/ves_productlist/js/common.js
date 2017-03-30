jQuery(document).ready( function ($){
    if(typeof(loaded_vesthemesettings) == "undefined" || !loaded_vesthemesettings) {
        /**
             *
             * Automatic apply  OWL carousel
             */
             if($(".owl-carousel-play").length > 0 ) {
                $(".owl-carousel-play .owl-carousel").each( function(){
                    var items_desktop = $(this).data( 'slide-desktop' );
                    var items_desktop_small = $(this).data( 'slide-desktop-small' );
                    var items_tablet = $(this).data( 'slide-tablet' );
                    var items_tablet_small = $(this).data( 'slide-tablet-small' );
                    var items_mobile = $(this).data( 'slide-mobile' );
                    var items_custom = $(this).data( 'slide-custom' );
                    var lazyload = $(this).data( 'lazyload' );

                //Desktop
                if(items_desktop && items_desktop != "false" && items_desktop != "0") {
                    items_desktop = JSON.parse("["+items_desktop+"]");
                } else if(items_desktop == "false" || items_desktop == "0") {
                    items_desktop = false;
                } else {
                    items_desktop = [1199,4];
                }
                //Desktop Small
                if(items_desktop_small && items_desktop_small != "false" && items_desktop_small != "0") {
                    items_desktop_small = JSON.parse("["+items_desktop_small+"]");
                } else if(items_desktop_small == "false" || items_desktop_small == "0") {
                    items_desktop_small = false;
                } else {
                    items_desktop_small = [979,3];
                }
                //Tablet
                if(items_tablet && items_tablet != "false" && items_tablet != "0") {
                    items_tablet = JSON.parse("["+items_tablet+"]");
                } else if(items_tablet == "false" || items_tablet == "0") {
                    items_tablet = false;
                } else {
                    items_tablet = [768,2];
                }
                //Tablet Small
                if(items_tablet_small && items_tablet_small != "false" && items_tablet_small != "0") {
                    items_tablet_small = JSON.parse("["+items_tablet_small+"]");
                } else if(items_tablet_small == "false" || items_tablet_small == "0") {
                    items_tablet_small = false;
                } else {
                    items_tablet_small = false;
                }
                //Mobile
                if(items_mobile && items_mobile != "false" && items_mobile != "0") {
                    items_mobile = JSON.parse("["+items_mobile+"]");
                } else if(items_mobile == "false" || items_mobile == "0") {
                    items_mobile = false;
                } else {
                    items_mobile = [479,1];
                }
                //Custom
                if(items_custom && items_custom != "false" && items_custom != "0") {
                    items_custom = JSON.parse("["+items_custom+"]");
                } else if(items_custom == "false" || items_custom == "0") {
                    items_custom = false;
                } else {
                    items_desktop = false;
                }

                //Custom
                if(lazyload && lazyload != "false" && lazyload != "0") {
                    lazyload = true;
                } else if(lazyload == "false" || lazyload == "0") {
                    lazyload = false;
                } else {
                    lazyload = false;
                }

                var config = {
                    navigation : $(this).data( 'navigation' ), // Show next and prev buttons
                    slideSpeed : $(this).data( 'slide-speed' ),
                    paginationSpeed : 400,
                    pagination : $(this).data( 'pagination' ),
                    autoPlay : $(this).data( 'auto' ),
                    lazyLoad: lazyload,
                    responsive: true,
                    autoWidth: false,
                    autoHeight: true,
                    itemsDesktop : items_desktop,
                    itemsDesktopSmall : items_desktop_small,
                    itemsTablet : items_tablet,
                    itemsTabletSmall : items_tablet_small,
                    itemsMobile : items_mobile,
                    itemsCustom : items_custom
                };
                var owl = $(this);
                if( $(this).data('slide-default') == 1 ){
                    config.singleItem = true;
                } else {
                    config.items = $(this).data( 'slide-default' );
                }
                $(this).owlCarousel( config );
                $('.owl-left',$(this).parent()).click(function(){
                  owl.trigger('owl.prev');
                  return false;
              });
                $('.owl-right',$(this).parent()).click(function(){
                    owl.trigger('owl.next');
                    return false;
                });
            } );
        }
        /**
         *
         * Automatic apply  Simple Easy Tabs
         */
         if($(".easytab-play .easytab").length > 0) {
            $(".easytab-play .easytab").each( function(){
                var tab_active_class = $(this).data( 'tab-active-class' );
                var panel_active_class = $(this).data( 'panel-active-class' );
                var animate = $(this).data( 'animate' );
                var animate_speed = $(this).data( 'speed' );
                var collapse = $(this).data( 'collapsible' );
                var is_cycle = $(this).data( 'cycle' );
                var default_tab = $(this).data( 'default-tab' );
                var transition_in = $(this).data( 'in-transition' );
                var transition_out = $(this).data( 'out-transition' );
                var transition_in_easing = $(this).data( 'in-easing' );
                var transition_out_easing = $(this).data( 'out-easing' );

                if(tab_active_class == "" || tab_active_class == null || typeof(tab_active_class) == 'undefined') {
                    tab_active_class = "active";
                }
                if(panel_active_class == "" || panel_active_class == null || typeof(panel_active_class) == 'undefined') {
                    panel_active_class = "active";
                }
                if(animate == "" || animate == null || typeof(animate) == 'undefined' || animate == 1 || animate == "true") {
                    animate = true;
                } else {
                    animate = false;
                }
                if(animate_speed == "" || animate_speed == null || typeof(animate_speed) == 'undefined') {
                    animate_speed = "normal";
                }
                if(collapse == "" || collapse == null || typeof(collapse) == 'undefined' || collapse == 0 || collapse == "false") {
                    collapse = false;
                } else {
                    collapse = true;
                }
                if(is_cycle == "" || is_cycle == null || typeof(is_cycle) == 'undefined' || is_cycle == 0 || is_cycle == "false") {
                    is_cycle = false;
                } else {
                    is_cycle  = true;
                }

                if(default_tab == "" || default_tab == null || typeof(default_tab) == 'undefined') {
                    default_tab = 'li:first-child';
                }

                if(transition_in == "" || transition_in == null || typeof(transition_in) == 'undefined') {
                    transition_in = 'fadeIn';
                }

                if(transition_in_easing == "" || transition_in_easing == null || typeof(transition_in_easing) == 'undefined') {
                    transition_in_easing = 'swing';
                }

                if(transition_out == "" || transition_out == null || typeof(transition_out) == 'undefined') {
                    transition_out = 'fadeOut';
                }

                if(transition_out_easing == "" || transition_out_easing == null || typeof(transition_out_easing) == 'undefined') {
                    transition_out_easing = 'swing';
                }

                var config = {
                    animate : animate,
                    animationSpeed: animate_speed,
                    collapsible: collapse,
                    panelActiveClass : panel_active_class,
                    tabActiveClass : tab_active_class,
                    cycle : is_cycle,
                    defaultTab: default_tab,
                    transitionIn: transition_in,
                    transitionInEasing: transition_in_easing,
                    transitionOut: transition_out,
                    transitionOutEasing: transition_out_easing
                };

                var easytab = $(this);

                $(this).easytabs(config);

            });
        }
        /*Init Bootstrap Tab */
        if($(".bootstap-tab-play").length > 0) {

            $(".bootstap-tab-play").each( function(){
                var default_tab = $(this).data("active");
                $(this).find("a").click(function (e) {
                  e.preventDefault()
                  $(this).tab('show')
              })

                if(default_tab && $(this).find(default_tab).length > 0) {
                    $(this).find(default_tab).tab('show')
                }
            });

        }
    }
} );