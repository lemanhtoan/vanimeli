/************************/

// Menu

/************************/ 

jQuery( document ).ready(function($) {



$('.open-menu').on('click', function(e){

	e.preventDefault();

	$(this).toggleClass('isActive');

	$('.mobile-menu-wrap').slideToggle();

});

$('li.has-submenu > a').on('click', function(e){

	e.preventDefault();

});



});


jQuery(document).ready(function($) {
  var owl = $("#latest_offers");
  owl.owlCarousel({
      itemsCustom : [
        [0, 1],

        [450, 1],

        [600, 2],

        [700, 3],

        [1000, 3],

        [1200, 3],

        [1400, 3],

        [1600, 3]
      ],
      navigation : true,
      navigationText: 	["<i class='fa fa-chevron-left'></i>","<i class='fa fa-chevron-right'></i>"],
      pagination: false
  });
  var owl = $("#recently_viewed_products");
  owl.owlCarousel({
      itemsCustom : [
        [0, 1],

        [450, 1],

        [600, 2],

        [700, 3],

        [1000, 3],

        [1200, 3],

        [1400, 3],

        [1600, 5]
      ],
      navigation : true,
      navigationText:   ["<i class='fa fa-chevron-left'></i>","<i class='fa fa-chevron-right'></i>"],
      pagination: false
  });

$("#clients-slider").owlCarousel({

  autoPlay: 3000, //Set AutoPlay to 3 seconds

  items : 5,

  itemsDesktop : [1199,5],

  itemsDesktopSmall : [979,4],

  itemsMobile: 	[479,1],

  navigation : true,

  navigationText: 	["<i class='fa fa-chevron-left'></i>","<i class='fa fa-chevron-right'></i>"],

  pagination: false

});

if($('.search-box').length){
$('.search-box').on('click',function(){
$(this).closest('li').toggleClass('current');
//$('#search-overlay').slideToggle();
 $("#search-overlay").animate({
                width: "toggle"
            });
return false;
});
}

});





