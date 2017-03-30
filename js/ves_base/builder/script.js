  /**
  * $Desc
  *
  * @version    $Id$
  * @package    wpbase
  * @author     WPOpal  Team <wpopal@gmail.com, support@wpopal.com>
  * @copyright  Copyright (C) 2014 wpopal.com. All Rights Reserved.
  * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
  * @addition this license does not allow theme provider using in themes to sell on marketplaces.
  * 
  * @website  http://www.wpopal.com
  * @support  http://www.wpopal.com/support/forum.html
  */
 
 /**
  * $Desc
  *
  * @version    $Id$
  * @package    wpbase
  * @author     WPOpal  Team <wpopal@gmail.com, support@wpopal.com>
  * @copyright  Copyright (C) 2014 wpopal.com. All Rights Reserved.
  * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
  * @addition this license does not allow theme provider using in themes to sell on marketplaces.
  * 
  * @website  http://www.wpopal.com
  * @support  http://www.wpopal.com/support/forum.html
  */
/* $.expr[":"].contains = $.expr.createPseudo(function(arg) {
    return function( elem ) {
        return $(elem).text().toUpperCase().indexOf(arg.toUpperCase()) >= 0;
    };

});
			
*/
var  current_id_tab = 1;
(function($) {
	$.fn.WPO_Modal = function(opts) {
		// default configuration
		var config = $.extend({}, {
			confirmdel:'Are you sure to delete?',
			id:'wpo-modalbox'
		}, opts);
		var $wpolayout = null;
  
	 
		this.each(function() {  
			 
			$wpolayout = $(this);		 	
 

		});
	 
		return this;
	};
	
})(jQuery);


/**
 * WPO_Widget Plugin
 */
(function($) {
	$.fn.WPO_Widget = function(opts) {
		// default configuration
		var config = $.extend({}, {
			gutter:30,
			urlwidgets:  '',
			mdwidgets:  'wpo-widgetform',
			modaltitle: 'Widget Setting',
			backtolist:'Back to list',
			savetext : 'Save'
		}, opts);
		var $col = null;

 		var $this=this;

 		var $queeWidgetID = '';

 		var $callbackwidget = null;


 		function injectWrapperWidgets( id, buttons ){ 
			if( !$( "#"+id).length ) {
				var modal = $( '<div class="modal " style="display:none" id="'+id+'"></div>');
				var html = ' <div class="modal-inner" >';

				html += '  <div class="modal-dialog modal-lg">'; 
				html += ' 	    <div class="modal-content">'; 
				html += '	      <div class="modal-header clearfix">'; 
				html += '	        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>'; 
				html += '	        <h4 class="modal-title pull-left">'+config.modaltitle+'</h4>'; 

				html += '	      <div class="group-buttons showinform pull-right" style="padding-right:10px">'; 
				html += '	        <button type="button" class="btn btn-info backtolist">'+config.backtolist+'</button>'; 
				html += '	        <button type="button" class="btn btn-primary savechange ">'+config.savetext+'</button>';
				html += '	      </div>'; 


				html += '	      </div>'; 
				html += '	      <div class="modal-body">';




				html += '         <div class="wpo-widgetslist"></div><div class="wpo-widgetform"></div>';  
				html += '	      </div>'; 
		 
				html += '	      <div class="modal-footer">'; 
				html += '	        <button type="button" class="btn btn-info backtolist pull-left showinform">'+config.backtolist+'</button>'; 
				html += '	        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>'; 
				html += '	        <button type="button" class="btn btn-primary savechange showinform">Save</button>';
				html += '	      </div>'; 
			 
				html += '	    </div> '; 
				html += '	  </div> '; 
				html += '	</div>'; 
				modal.html( html );

				$('#content').append( modal );

				
			}		
		}

 		this.showWigetsList = function ( col ){
 			if( $('#'+config.mdwidgets ).find( ".wpo-widgetslist" ).length<=0 ){
			 	this.loadWidgets( true );
			}else {
				$( '#'+config.mdwidgets ).modal('toggle');	
				$('#'+config.mdwidgets + ' ' +'.modal-body .wpo-widgetslist').show();
				$('#'+config.mdwidgets + ' ' +'.modal-body .wpo-widgetform').hide();
			}	
 			
 		}
 		
 		this.loadWidgets = function( isshow, callback, datajson , callbackwidget, col ){
 			var col = col != null ? col : "";
 			var callbackwidget = callbackwidget != null ? callbackwidget : "";
 			$callbackwidget = callbackwidget;

 			injectWrapperWidgets( config.mdwidgets, false );
 			//$('#'+config.mdwidgets+" .showinform").hide();
 			$( '#'+config.mdwidgets + ' .modal-body .wpo-widgetform' ).hide();

 			if(isshow) {
 				WysiwygWidgetTools.openDialog(config.urlwidgets, this, callbackwidget, col);
 			}
 			
 			if(typeof callback == "function"){
				callback.call( this, datajson  );
			}
			/*
			$('#'+config.mdwidgets+" .backtolist").click( function(){
				$('#'+config.mdwidgets + ' ' +'.modal-body .wpo-widgetslist').show();
				$('#'+config.mdwidgets + ' ' +'.modal-body .wpo-widgetform').hide();
				$('#'+config.mdwidgets+" .showinform").hide();
			} );*/
 		}


 		this.editWidget = function( widget ){
 			if( widget.data('wgcfg').wkey){
 				widget_shortcode = widget.data('wgcfg').shortcode;
 				WysiwygWidgetTools.openFormDialog(config.urlwidgets, widget, widget_shortcode, this, $callbackwidget, widget.parent().parent());
	 		}
 		}

 		this.settingWidget = function( widget, col ){
 			if( widget.data('wgcfg').wkey){
 				var wgcfg = widget.data('wgcfg');
	  			var widgetArray = new WPO_DataWidget();
	  			for( var k in widgetArray ){
	  				if( $("[name="+k+"]", "#widget_settings").length > 0 ) {
	  					$("[name="+k+"]", "#widget_settings").val( wgcfg[k] ); 
	  				}
	  				if( k.indexOf('color') != -1 ){
	  					if( wgcfg[k] ){ 
	  						$("[name="+k+"]", "#widget_settings").css({'background-color':wgcfg[k]}); 
	  					}else{
	  						$("[name="+k+"]", "#widget_settings").css({'background-color':'#FFFFFF'}); 	
	  					}
	  				}
	  			}
 				
 				
	  			$( ".wpo-ijwidget" ).removeClass( "active" );	
	  			widget.addClass( 'active' );

	  			$("#widget_settings").show();
	  			jQuery("#filemanager-container").hide();
			    jQuery("#column_settings").hide();
			    jQuery("#row_settings").hide();

			    $(".modal-label").hide();
	  			$("#widget-mod-label").show();

			    $('#filemanager-modal .modal-footer button.btn-submit').hide();
			    $('#filemanager-modal .modal-footer button.btn-submit-widget_settings').show();
	  			$('#filemanager-modal').modal('show');

				//event.stopPropagation();
	 		}
 		}

 		this.editShortcode = function( widget, col ){
 			if( widget.data('wgcfg').wkey){
 				var wgcfg = widget.data('wgcfg');
	  			var widgetArray = new WPO_DataWidget();
	  			for( var k in widgetArray ){
	  				if( $("[name="+k+"]", "#widget_editshortcode").length > 0 ) {
	  					$("[name="+k+"]", "#widget_editshortcode").val( wgcfg[k] ); 
	  				}
	  			}

	  			$( ".wpo-ijwidget" ).removeClass( "active" );	
	  			widget.addClass( 'active' );
	  			$('#widget-modal').modal('show');

				//event.stopPropagation();
	 		}
 		}

 	 

 		this.cloneWidget = function( widget ){
 			var data = widget.data( 'wgcfg' );
 			var target = new WPO_DataWidget();
 			for( var k in target ){   
			 	target[k] = data[k];
			}

			target.wkey = this.getWidgetKey();

 
			this.createWidgetButton( widget.parent().parent(), null , target.shortcode );
			this.cloneFormData( target.wkey, data.wkey );	

 		}

 		this.getWidgetKey = function(){
 			var d = new Date();
			 return d.getTime();
 		}

 		this.createWidgetButton = function( col, widget , data ){
 			var widget = widget !=null?widget:'';
 			var data = data !=null?data:null;
	 		var name = '';
	 		var shortcode = '';
	 		var wkey = this.getWidgetKey();

			var dw = new WPO_DataWidget();
			dw.wkey = wkey;

			if(data !=null && jQuery.type(data) == "string"){
				shortcode = data;
			} else if(data !=null && jQuery.type(data.shortcode) != "undefined") {
				shortcode = data.shortcode;
				if(typeof(data.cls) != "undefined") {
					dw.cls = data.cls;
				}
				if(typeof(data.css) != "undefined") {
					dw.css = data.css;
				}
				if(typeof(data.sfxcls) != "undefined") {
					dw.sfxcls = data.sfxcls;
				}
				if(typeof(data.sfxa) != "undefined") {
					dw.sfxa = data.sfxa;
				}
				if(typeof(data.sfxaduration) != "undefined") {
					dw.sfxaduration = data.sfxaduration;
				}
				if(typeof(data.sfxadelay) != "undefined") {
				    dw.sfxadelay = data.sfxadelay;
				}
				if(typeof(data.sfxaoffset) != "undefined") {
				    dw.sfxaoffset = data.sfxaoffset;
				}
				if(typeof(data.sfxaiteration) != "undefined") {
				    dw.sfxaiteration = data.sfxaiteration;
				}
				if(typeof(data.bgcolor) != "undefined") {
				    dw.bgcolor = data.bgcolor;
				}
				if(typeof(data.bgimage) != "undefined") {
				    dw.bgimage = data.bgimage;
				}
				if(typeof(data.bgrepeat) != "undefined") {
				    dw.bgrepeat = data.bgrepeat;
				}
				if(typeof(data.bgposition) != "undefined") {
				    dw.bgposition = data.bgposition;
				}
				if(typeof(data.bgattachment) != "undefined") {
				    dw.bgattachment = data.bgattachment;
				}
				if(typeof(data.margin) != "undefined") {
					dw.margin = data.margin;
				}
				if(typeof(data.padding) != "undefined") {
					dw.padding = data.padding;
				}
				if(typeof(data.isajax) != "undefined") {
					dw.isajax = data.isajax;
				}
				if(typeof(data.desktop) != "undefined") {
					dw.desktop = data.desktop;
				}
				if(typeof(data.tablet) != "undefined") {
					dw.tablet = data.tablet;
				}
				if(typeof(data.mobile) != "undefined") {
					dw.mobile = data.mobile;
				}
				
			}

			dw.shortcode = shortcode;

			if(widget && jQuery.type(data) == "string" && jQuery.type(widget.data("wgcfg")) != "undefined") {
				var $w = widget;
				wkey = widget.data("wgcfg").wkey;
				dw.wkey = wkey;

			} else {
				var $w = $( '<div class="wpo-ijwidget" id="widget'+dw.wkey+'"></div>' );
			}

			$w.data( 'wgcfg', dw );

			$w.html(  buildWidgetHtml(shortcode) );

			$w.append('<div class="wpo-wedit-shortcode ptstooltip" data-toggle="tooltip" data-placement="top" title="Edit Shortcode"></div>');
			$w.append('<div class="wpo-wsetting ptstooltip" data-toggle="tooltip" data-placement="top" title="Settings"></div>');
			$w.append('<div class="wpo-wcopy ptstooltip" data-toggle="tooltip" data-placement="top" title="Duplicate"></div>');
			$w.append('<div class="wpo-wedit ptstooltip" data-toggle="tooltip" data-placement="top" title="Edit"></div>');
			$w.append('<div class="wpo-wdelete ptstooltip" data-toggle="tooltip" data-placement="top" title="Delete"></div>');
			$w.append( '<textarea class="wpo-cfginput" id="wpowidget'+dw.wkey+'" name="wpowidget['+dw.wkey+'][config]">'+dw.shortcode+'</textarea>' );
			// alert(  $w.name );
			//console.log("\n $w.name "+$w.wkey);
			
			$( ".wpo-wcopy", $w ).click( function(){   
				$this.cloneWidget(  $w, col );
			} );

			$( ".wpo-wedit", $w ).click( function(){  
				$this.editWidget( $w );
			} );

			$( ".wpo-wedit-shortcode", $w ).click( function(){  
				$this.editShortcode( $w );
			} );

			$( ".wpo-wsetting", $w ).click( function(event){  
				$this.settingWidget( $w );
				event.stopPropagation();
			} );

			$( ".wpo-wdelete", $w ).click( function(){  
				if( confirm(config.confirmdel) ){
					$w.remove();
				}
				
			} );

			$(col).find( ".wpo-content:first" ).append( $w );


			return wkey;

 		}

 		this.cloneFormData = function( ckey, rkey ){   
			$("#wpowidget"+ckey).val( $("#wpowidget"+rkey).val() );
 		}

 		this.loadWidgetByIds = function( ids ){
 			
 			$.each( ids, function(i, wkey){
 				$.ajax({
					url:  config.urlwidgetdata,
				 
					data:{
						rand:Math.random(),
						controller : config.controller,
	    				action : 'widgetdata',
	 					ajax : true,
						id_tab : current_id_tab,
						wkey:wkey
					},

					type:'POST'
				}).done(function( html ) {
			 	 	$("#wpowidget"+wkey).val( html );
				});
 			} );

 				 
 		}

 		this.widgetsAction = function( $wwidgets ){
 			$(".wpo-wg-button > div", $wwidgets ).click( function(){

				//  var wkey = $this.createWidgetButton( $(".wpo-col.active"), this ) ;
				$('#'+config.mdwidgets+" .ajaxloader").remove();
				$(this).append('<div class="ajaxloader"></div>');
 				$queeWidgetID = this;
 				//alert( $($queeWidgetID).parent().data('widget') );
 				$.ajax({
					url:  config.urlwidget,
					data: {
						wkey:'',
						rand:Math.random(),
						controller : config.controller,
	    				action : 'widgetform',
	    				wtype: $($queeWidgetID).data('widget'),
	 					ajax : true,
						id_tab : current_id_tab,
						form_key : window.FORM_KEY
					},
					type:'POST'
				}).done(function( html ) {
			 		$( '#'+config.mdwidgets + ' .modal-body .wpo-widgetform' ).html( html );
			 		$( '#'+config.mdwidgets + ' .modal-body .wpo-widgetform' ).show();
			 		$( '#'+config.mdwidgets + ' .modal-body .wpo-widgetslist' ).hide();

			 		$('#'+config.mdwidgets+" .showinform").show();
			 		$('#'+config.mdwidgets+" .ajaxloader").remove();
				}); 
 			} );
 		}

		 function widgetsFillter(){  

		 	$("#searchwidgets").keypress( function( event ){
			
				if ( event.which == 13 ) {
				    event.preventDefault();
				}
				var $this = this;
				setTimeout( function(){
			 		 if( $.trim($("#searchwidgets").val()) !="" ) {
						$(".widgets_list .wpo-wg-button").hide(); 
						$( "div.widget-title:contains("+$("#searchwidgets").val()+")" ).parent().parent().show();
					}else { 
					 	$(".widgets_list .wpo-wg-button").show();
					}

				 }, 300 );

			} );

		 	$( '.filter-option' ,"#filterbygroups").click( function(){
		 		$( '.filter-option' ,"#filterbygroups").removeClass( 'active' );
		 		$(this).addClass( 'active' );
		 		if( $(this).data('option') == 'all' ) {
		 			$(".widgets_list .wpo-wg-button").show();  
		 		}else {
		 			$(".widgets_list .wpo-wg-button").hide();  
		 			$('[data-group='+$(this).data('option')+']').show();
		 		}	
		 		return false; 
		 	} );

 		}

		this.each(function() {  
			injectWrapperWidgets( config.mdwidgets, false ); 

			$('#'+config.mdwidgets+" .savechange").click( function(){
				updateAllMessageForms();
				var k = $.trim($('[name=wkey]',$('#'+config.mdwidgets+" form") ).val()); 
 				 
				if( k == "" ){   
					var wkey = $this.createWidgetButton( $(".wpo-col.active"), $queeWidgetID  ) ;	
					$('[name=wkey]',$('#'+config.mdwidgets+" form") ).val( wkey ); 
					$queeWidgetID = '';
				} else {
					var wkey = k;
				}
				$($wpolayout).append('<div class="loading-setting"></div>');
				var p = "controller="+config.controller+"&action=savewidget&ajax=true&id_tab&"+current_id_tab;
				$.ajax({
					url:  config.urlwidget+"&action=save",
					data:$('#'+config.mdwidgets+" form").serialize()+"&wkey="+wkey+"&"+p,
					dataType:'json',
					type:'POST'
				}).done(function( widget ) {
					if( widget.wkey ){
						$("#wpowidget"+wkey).val(  widget.config );	
					}
	 				if( widget.name ){
	 					$("#wpowidget"+wkey).parent().find('.widget-desc').html( widget.name.substring(0, 100) );
	 					var data = $("#widget"+wkey).data('wgcfg');
	 					data.name = widget.name;
	 					$("#widget"+wkey).data('wgcfg', data);   
	 				}
	 				$(".loading-setting",$wpolayout).remove();
				}); 
				$('#'+config.mdwidgets).modal('toggle');
			} );
                        
                        
 			return this;
		});
                
                
		return this;
	};
	
})(jQuery);


var WPO_DataRow = function () {
    this.index   =  0;
    this.wcls = '';
    this.cls   = '';
    this.incls   = '';
    this.css   = '';
    this.bgcolor = '';
    this.bgimage = '';
    this.bgrepeat = '';
    this.bgposition = '';
    this.bgattachment = '';
    this.padding = '';
    this.margin = '';
    this.nogutters = 0;
    this.fullwidth = 0;
    this.parallax = 0;
    this.iscontainer = 0;
    this.p_percent = '50%';
    this.p_scroll = '0.4';
    this.inbgcolor = '';
    this.inbgimage = '';
    this.inbgrepeat = '';
    this.inbgposition = '';
    this.inbgattachment = '';
    this.inparallax = 0;
    this.inp_percent = '50%';
    this.inp_scroll = '0.4';
    this.sfxcls = '';
    this.offcanvas = '';
}

var WPO_DataCol = function () {
    this.index = 0;
    this.cols = '';
    this.wcls = '';
    this.cls = '';
    this.incls = '';
    this.css   = '';
    this.sfxcls = '';
    this.bgcolor = '';
    this.bgimage = '';
    this.bgrepeat = '';
    this.bgposition = '';
    this.bgattachment = '';
    this.padding = '';
    this.margin = '';
    this.inrow = 0;
    this.lgcol = 4;
    this.mdcol = 4;
    this.smcol = 6;
    this.xscol = 12;
    this.offcol = '';
	     
}

var WPO_DataWidget = function () {
    this.cls = '';
    this.incls   = '';
    this.css   = '';
    this.bgcolor = '';
    this.bgimage = '';
    this.bgrepeat = '';
    this.bgposition = '';
    this.bgattachment = '';
	this.padding = '';
    this.margin = '';
    this.sfxcls = '';
    this.sfxa = '';
    this.sfxaduration = '';
    this.sfxadelay = '';
    this.sfxaoffset = '';
    this.sfxaiteration = '';
    this.wtype = '';
    this.wkey ='';
    this.shortcode = '';
    this.settings = '';
    this.widget = '';
    this.isajax = 0;
    this.desktop = 1;
    this.tablet = 1;
    this.mobile = 1;
    this.name = '';
         
}

jQuery(document).ready( function($){
    $('input.input-color').each( function(){
      	var $options = {
		    imageFolder: SKIN_URL+"ves_base/images/"
		  };
         $(this).mColorPicker($options);
    });
} ); 
 

function reloadLanguage( langcls ){
     $('.switch-langs').hide();

      $('.'+langcls).show();

      $(".group-langs ul li").click( function(){
           $('.switch-langs').hide();
            $('.'+ $(this).data('lang') ).show();
      } );
}
 
 function updateAllMessageForms(){
       for (instance in CKEDITOR.instances) {
           CKEDITOR.instances[instance].updateElement();
       }
  }
 /**
  * $Desc
  *
  * @version    $Id$
  * @package    wpbase
  * @author     WPOpal  Team <wpopal@gmail.com, support@wpopal.com>
  * @copyright  Copyright (C) 2014 wpopal.com. All Rights Reserved.
  * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
  * @addition this license does not allow theme provider using in themes to sell on marketplaces.
  * 
  * @website  http://www.wpopal.com
  * @support  http://www.wpopal.com/support/forum.html
  */

(function($) {
	/**
 	* WPO_Layout Plugin
 	*/

	$.fn.WPO_Layout = function( opts, datajson ) {
		/*
		 * default configuration
		 */
		var config = $.extend({}, {
			containerselector: "#container",
			gutter	   :   30,
			coldwith   :   54,
			defaultcol :   3,
			col 	   :   12,
			confirmdel :   'Are you sure to delete?',
			urlwidgets :   'widgets.php',
			urlwidget  :   'form.php',
			urlwidgetdata: 'data.php',
			formsellector: '#frmsavealll',
			controller : 'AdminPtspagebuilderProfile'
		}, opts);

		/**
		 * store wrapper layout editor.
		 */
		var $wpolayout = null;

		/**
		 * store instances of WPO_Widget
		 */
		var $wpowidget = $("body").WPO_Widget( config );

		var $screenmode = 'large-screen';
		
		var $colkey = 'lgcol';

		/** 
		 * caculate total layout width based on column's width and gutter + number of columns 
		 */
		var layoutwidth = config.coldwith*config.col + ((config.col-1)*config.gutter);
	 	
	 	/**
	  	 *  preload widgets using for layout edtior and add some elements as suggest row to click add to new row.
	  	 */				
	  	function init(){ 
	  		$wpolayout.append('<div class="inner"></div>');
	  		$wpolayout.width( layoutwidth );
	  		addSuggestRow();
	  	};

	  		
	  	/**
	  	 * add new column element and append it before suggest column.
	  	 * 
	  	 * return Object col: jquery object of that column
	  	 */	
	  	function addColumn( scol, row, sub ){
	  		$( ".lg-col", $wpolayout ).removeClass( "active" );
	  		var col = $( '<div class="active lg-col wpo-col">\n\
                            <div class="inner clearfix"><div class="wpo-delete wpo-icon ptstooltip" data-toggle="tooltip" data-placement="top" title="Delete"></div>\n\
                            <div class="wpo-edit wpo-icon ptstooltip" data-toggle="tooltip" data-placement="top" title="Column Settings"></div>\n\
                            <div class="wpo-addrow wpo-icon ptstooltip" data-toggle="tooltip" data-placement="top" title="Add row"></div>\n\
                            <div class="wpo-addwidget wpo-icon ptstooltip" data-toggle="tooltip" data-placement="top" title="Add Widget"></div>\n\
                            <div class="wpo-content"></div></div></div>' );
	  		col.width( config.coldwith*3+2*config.gutter 	);
	  		col.insertBefore( scol );
	  		col.data( "colcfg", new WPO_DataCol() );

	  		bindingColEventS( col , row );		
	  		
	  		return col;
	  	}
	  	function bindColorPicker() {
	  		$('input.input-color').each( function(){
		      	var $options = {
				    imageFolder: SKIN_URL+"ves_base/images/"
				  };
		         $(this).mColorPicker($options);
		    });
	  	}
	  	/* binding events for column */
	  	function bindingColEventS( col, row ){
	  		var maxWidth = layoutwidth;
	  		if(config.containerselector && $(config.containerselector).length > 0) {
	  			var containerWidth = $(config.containerselector).width();
	  			if(containerWidth > maxWidth) {
	  				maxWidth = containerWidth;
	  			}
	  		}
	  		
	  		col.resizable({
			    stop: function(event, ui) {

				 	setTimeout( function(){
				 		var c =  Math.floor(config.col*col.width()/row.width())+1;
				 		maxWidth = row.width();
				 		if( c > config.col ){
				 			c = config.col;
				 		}
						updateColsWidth(col,c);
					
						 
					 }, 200 );	
		 	
			    },
			     handles: 'e',
			     maxWidth:maxWidth+config.gutter,
			     minWidth:config.coldwith
			}); 
	  		col.click( function(event) {
	  			// $( ".wpo-edit", col ).click();
  				$( ".wpo-col", $wpolayout ).removeClass( "active" );	
	  			col.addClass( 'active' );
	  			$( "#col-builder" ).hide();
	  			// event.stopPropagation();
	  		} );
			$( ".wpo-delete" ,col).click( function(){
				if( confirm(config.confirmdel) ){
					col.remove();  
		 			recalculateColsWidth( row );
		 		}
			} );

			// set default values or element'data 
			$( ".wpo-edit", col ).click( function(event){
	  			var cfg = col.data("colcfg") ;
	  			var colsArray = new WPO_DataCol();
	  			for( var k in colsArray ){
	  				if( $("[name="+k+"]", "#column_settings") ) {
	  					$("[name="+k+"]", "#column_settings").val( cfg[k] ); 
	  				}
	  				if( k.indexOf('color') != -1 ){
	  					if( cfg[k] ){ 
	  						$("[name="+k+"]", "#column_settings").css({'background-color':cfg[k]}); 
	  					}else{
	  						$("[name="+k+"]", "#column_settings").css({'background-color':'#FFFFFF'}); 	
	  					}
	  				}
	  			}
	  			$( ".wpo-col", $wpolayout ).removeClass( "active" );	
	  			col.addClass( 'active' );

	  			$("#column_settings").show();
	  			jQuery("#filemanager-container").hide();
			    jQuery("#row_settings").hide();
			    jQuery("#widget_settings").hide();

			    $(".modal-label").hide();
	  			$("#col-mod-label").show();

			    $('#filemanager-modal .modal-footer button.btn-submit').hide();
			    $('#filemanager-modal .modal-footer button.btn-submit-column_settings').show();
	  			$('#filemanager-modal').modal('show');
	  			/*
	  			var pos = $( ".wpo-edit", col ).offset();
	  			var l = pos.left;  
	  			$( "#col-builder" ).css( {top:pos.top-$( "#col-builder" ).height()/2, left:l} ).show();
				*/
	  			
	  			event.stopPropagation();
	  		} );

			$( ".wpo-addwidget", col ).click( function(){

				$wpowidget.loadWidgets( true, buildLayoutByJson, null, buildWidgetItem, col  );
			 	// $(".wpo-delete",banner).click( function(){ banner.remove(); alert('f'); } );
			 	//$('#mdwidgetbuttons').modal('show');
			  
			} );

			// bind sortable event to re-sort widgets inside.
			$( ".wpo-content", col ).sortable({
				connectWith: ".wpo-col .wpo-content",
				placeholder: "ui-state-highlight-widget",
				over:function(event, ui ){   ui.item.width(ui.placeholder.width() ) }

			}); 


			$( '.wpo-addrow', col ).click( function(){
				addRow( col.children( '.inner' ), true, col ); 
			} );

	  	}

	  	
	  	/* recaculate column width */
	  	function recalculateColsWidth( row ){
 	 	
			var childnum = $(row).children('.inner').children( ".wpo-col" ).length;
			var dcol = Math.floor( config.col/childnum );
			var a = config.col%childnum;

			$(row).children('.inner').children( ".wpo-col" ).each( function(i, col ) {
				if( a > 0 && (i == childnum-1) ){
		 			dcol = dcol+a;
				}
 				updateColsWidth( this, dcol );
	 		
			});
		 
		}

		/* recaculate column width */
	  	function recalculateColsWidthChild( row ){
 	 	
			var childnum = $(row).children('.child-inner').children( ".wpo-col" ).length;
			var dcol = Math.floor( config.col/childnum );
			var a = config.col%childnum;

			$(row).children('.child-inner').children( ".wpo-col" ).each( function(i, col ) {
				if( a > 0 && (i == childnum-1) ){
		 			dcol = dcol+a;
				}
 				updateColsWidth( this, dcol );
	 		
			});
		 
		}

		function updateColsWidth( col, dcol ){  
			 
	     	$(col).css( {'width':dcol/config.col*100 +'%'} );  
 			var data =  $(col).data( 'colcfg' );
			data[$colkey] = dcol;
			$(col).data( 'colcfg', data );
		}

	  	function createColForm( idx, col ){

	  	}

	  	/**
	  	 * add new row element and append it before suggest row.
	  	 * 
	  	 * return Object col: jquery object of that column
	  	 */	
	  	function addRow( srow, sub, incol ){

	  		if(sub) {
	  			var row = $( '<div class="wpo-row wpo-child-row lg-row"><div class="wpo-tool"><div class="wpo-child-sortable ptstooltip" data-toggle="tooltip" data-placement="top" title="Sort"></div></div><div class="inner child-inner clearfix"></div></div>' );
	  		} else {
	  			var row = $( '<div class="wpo-row lg-row"><div class="wpo-tool"><div class="wpo-sortable ptstooltip" data-toggle="tooltip" data-placement="top" title="Sort"></div></div><div class="inner parent-inner clearfix"></div></div>' );
	  		}
	  		var shortcode = $('<div class="wpo-wedit-shortcode ptstooltip" data-toggle="tooltip" data-placement="top" title="Edit Row Short Code"></div>');
	  		var edit = $('<div class="wpo-edit ptstooltip" data-toggle="tooltip" data-placement="top" title="Row Settings"></div>');
	  		var copy = $('<div class="wpo-copy ptstooltip" data-toggle="tooltip" data-placement="top" title="Duplicate"></div>');
	  		var del = $('<div class="wpo-delete ptstooltip" data-toggle="tooltip" data-placement="top" title="Delete"></div>');
	  		
	  		$(row).children(".wpo-tool").append(del).append(copy).append(edit).append(shortcode);	

	  		bindingRowEvents( row , sub, incol );
	  		
			//row.insertBefore( srow );
			if( sub !=null && sub== true ){
				srow.append( row );	
				srow.addClass('hd-widgets-func');
				addSuggestColumn( row );
				row.addClass( 'active' );
			}else {
				$wpolayout.children('.inner').append( row );	
				addSuggestColumn( row );
				$wpolayout.children('.inner').children( ".wpo-row").removeClass( "active" );	
				row.addClass( 'active' );
			}
			row.data( "rowcfg", new WPO_DataRow() );
			return row;
	  	};

	  	/* clone data from orginal for new */
	  	function cloneData( data, target ){
			for( var k in target ){   
			 	target[k] = data[k];
			}
	  		return target;
	  	}

  		/* ROW PROCESS: add and create a setting form */	
	  	function bindingRowEvents( row , srow, incol ){
	  		$(".wpo-tool .wpo-delete", row).click( function(){
	  			if( confirm(config.confirmdel) ){
	  				if( row.parent().children('.wpo-row').length<= 1 ){
	  					row.parent().removeClass( 'hd-widgets-func' );
	  				}
	  				
	  				row.remove();
	  			}
	  		} );

	  		$(".wpo-copy", row).click( function( event ){
	  			cloneLayoutInRow( row, srow, incol );
	  		} );

	  		$( ".wpo-wedit-shortcode", row ).click( function(){
	  			row.addClass( "active" );
				editRowShortcode( row );
			} );

	  		// set default values or element'data 
	  		$( ".wpo-edit", row ).click( function(event){
	  			var cfg = row.data("rowcfg") ; 
	  			var rowsArray = new WPO_DataRow();
	  			for( var k in rowsArray ){

	  				if( $("[name="+k+"]", "#row_settings") ) {
	  					$("[name="+k+"]", "#row_settings").val( cfg[k] ); 
	  				}

	  				if( k.indexOf('color') != -1 ){
	  					if( cfg[k] ){ 
	  						$("[name="+k+"]", "#row_settings").css({'background-color':cfg[k]}); 
	  					}else{
	  						$("[name="+k+"]", "#row_settings").css({'background-color':'#FFFFFF'}); 	
	  					}
	  				}
	  			}

	  			$( ".wpo-row", $wpolayout ).removeClass( "active" );
	  			row.addClass( 'active' );

	  			$("#row_settings").show();
	  			$(".modal-label").hide();
	  			$("#row-mod-label").show();

	  			jQuery("#filemanager-container").hide();
			    jQuery("#column_settings").hide();
			    jQuery("#widget_settings").hide();

			    $('#filemanager-modal .modal-footer button.btn-submit').hide();
			    $('#filemanager-modal .modal-footer button.btn-submit-row_settings').show();
	  			$('#filemanager-modal').modal('show');

				event.stopPropagation();
	  		} );

	  		row.click( function( event ){
	  			$( ".wpo-row", $wpolayout ).removeClass( "active" );
	  			row.addClass( "active" );
	  			$( "#row-builder" ).hide();
	  			event.preventDefault();

	  		} );

  			row.delegate( ".sg-parent-col", "click", function(){
  				addColumn( $(this), row );
	  		 	recalculateColsWidth( row );
		  		
		  	} );

		  	row.delegate( ".sg-sub-col", "click", function(){
		  		if($(this).hasClass("clicked")) {
		  			//Do nothing
		  		} else {
		  			addColumn( $(this), row );
	  		 		recalculateColsWidth( row );
		  		}
  				$(this).toggleClass("clicked");
		  		
		  	} );

	  		 
	  		// bind sortable for this to sort columns on current row and other rows.	

  			$( row ).children('.inner').sortable({
				connectWith: ".wpo-row > .inner", 
				placeholder: "ui-state-highlightcol",
	 			update:function( event, ui ){
	 			
	 			},
	 			
	 			remove:function(){
	 			 	var trow = $(this).parent();
 			   		 recalculateColsWidth( trow );
	 			},
	 			start:function( event, ui ){
	 		 		$( '.ui-state-highlightcol', row ).width( $(ui.item).width() );
	 			},
	 			receive: function( event, ui ) {
	 			   	 var trow = $(this).parent();
 			   		 recalculateColsWidth( trow );
	 			},
	 			cancel: ".wpo-sortable, .add-col, .wpo-child-sortable"
			});

		 	 		
	  	}
		function editRowShortcode ( row ){
			var shortcode = getRowShortCode(row);

			if( $("[name=shortcode]", "#row_editshortcode").length > 0 ) {
				$("[name=shortcode]", "#row_editshortcode").val( shortcode );
				initJSONEditor($("[name=shortcode]", "#row_editshortcode"), "#row_editshortcode");
			}
			$('#row-modal').modal('show');



 		}
		function cloneLayoutInRow( row, sub, incol ){
			var cr = addRow( sub==true?incol.children( '.inner' ):$(".add-row",$wpolayout), sub, incol );
			cr.data( 'rowcfg', cloneData(row.data( 'rowcfg'),new WPO_DataRow()) );
		 
			$(row).children('.inner').children( ".lg-col" ).each( function(){	
				var cc = addColumn( $(cr).children('.inner').children(".wpo-row.active .sg-col"), cr );
				 
  				 $(this).children('.inner').children('.wpo-content').children( '.wpo-ijwidget' ).each( function(){   
  				 	var rwd   = cloneData( $(this).data('wgcfg'), new WPO_DataWidget() );  
		  			rwd.wkey  = $wpowidget.getWidgetKey();
  				 	var cwkey = $wpowidget.createWidgetButton( cc, $("#wpo_"+rwd.wtype) , rwd );	
  				 	$wpowidget.cloneFormData( cwkey, $(this).data('wgcfg').wkey );	

  				} );
  		 		var rcd = $(this).data('colcfg'); 
  				cc.data( 'colcfg', cloneData(rcd,new WPO_DataCol()) );
  				
  				$(cc).css( {'width':(rcd.lgcol/config.col*100)+'%'} );  
  				if( $(this).children('.inner').children( '.wpo-row' ).length > 0 ){  

	  				$(this).children('.inner').children( '.wpo-row' ).each( function(){   
	  					cloneLayoutInRow( $(this), true, cc );
	  				});
  				}

			} );
	 	}

  		/**
	  	 * add suggest row using to click to this for adding new real row
	  	 */
	  	function addSuggestRow(){
	  		var srow = $(  '<div class="add-row sg-row"></div>' );
	  		$wpolayout.append( srow );
	  		srow.click( function(){
	  			addRow( this );
 				
	  		} );
	  	};

	  	/**
	  	 * add suggest column using to click to this for adding new real row
	  	 */
	  	function addSuggestColumn( row ){
	  		if($(row).hasClass("wpo-child-row")) {
	  			var scol = $(  '<div class="add-col sg-col sg-sub-col ptstooltip" data-toggle="tooltip" data-placement="top" title="Add Column"></div>' );
	  		} else {
	  			var scol = $(  '<div class="add-col sg-col sg-parent-col ptstooltip" data-toggle="tooltip" data-placement="top" title="Add Column"></div>' );
	  		}
	  		
	  		// scol.width( config.coldwith );
	  		$(row).children(".inner").append( scol );	
	  	 
	  	};


	  	/**
	  	 * binding some delegate events
	  	 */
	  	function bindDelegateEvents(){

 			$( ".popover" ).each( function(){
 				var pop = this;
 				$( '.popover-title',this ).click( function(){
 					$( pop ).hide();	
 				} );
 			} );

 			$(".button-alignments button").click( function (){
 				$screenmode = $(this).data('option');
 				$(".button-alignments button").removeClass('active');
 				$(this).addClass( 'active' );
 				updateColWidthByScreen();

 			} );
	  	}

	  	function updateColWidthByScreen(){
	  	
	  		switch( $screenmode ){
	  			case 'medium-screen':
	  				$colkey = 'mdcol';
	  				break;
	  			case 'tablet-screen':
	  				$colkey = 'smcol';
	  				break;
	  			case 'mobile-screen':
	  				$colkey = 'xscol';
	  				break;
	  			default: 
	  				$colkey = 'lgcol';	
	  		}

	  		$(".wpo-row",$wpolayout).each( function(){
	 			var _row = $(this);
	 			$( '.wpo-col', _row ).each( function(){
	 				var rcd = $(this).data('colcfg');
	 				$(this).css( {'width':rcd[$colkey]/config.col*100+'%'} );  
	 			});
		 	});		
	  	}

	 	/**
	 	 * add event triggers to operator in form of selected column and selected row 
	 	 */
	 	function triggerFormsEvent(){
	 		/* ROW SETTING HANDLER */

	 		$("#filemanager-modal .btn-submit-row_settings").click( function(event) {
		 		if( $(".wpo-row.active") ){
	 				var cfg = $(".wpo-row.active").data( 'rowcfg' );
	 				var rowsArray = new WPO_DataRow();
	 				for( var k in rowsArray ){
	 					var val = $("[name="+k+"]", "#row_settings").val(  ); 
	  					if( $("[name="+k+"]", "#row_settings").length > 0 ){
	  			 			cfg[k] = val;
	  			 		}
	  				}
	  				$(".wpo-row.active").data( 'rowcfg' ,cfg );
	 			}
	 			$('#filemanager-modal').modal('toggle');
 				$( "#row_settings" ).hide();
 				$(".modal-label").hide();
	  			$("#filemanager-mod-label").show();
	  			
	 			return false;
	  		})
	 		/* COLUMN SETTING HANDLER */
	 		$("#filemanager-modal .btn-submit-column_settings").click( function(event) {
		 		if( $(".wpo-col.active") ){
	 				var cfg = $(".wpo-col.active").data( 'colcfg' );
	 				var colsArray = new WPO_DataCol();
	 				for( var k in colsArray ){
	  				 	var val = $("[name="+k+"]", "#column_settings").val( ); 
	  			 		
	  					if( $("[name="+k+"]", "#column_settings").length > 0 ){
	  			 			cfg[k] = val;
	 			
	  			 		}

	  				}
	  				$(".wpo-col.active").data( 'colcfg' ,cfg );
		 		}
		 		$('#filemanager-modal').modal('toggle');
	 			$( "#column_settings" ).hide();
	 			$(".modal-label").hide();
	  			$("#filemanager-mod-label").show();
  				return false;
	  		})
	 	
	 		/* Widget SETTING HANDLER */
	 		$("#filemanager-modal .btn-submit-widget_settings").click( function(event) {
		 		if( $(".wpo-ijwidget.active") ){
	 				var wgcfg = $(".wpo-ijwidget.active").data( 'wgcfg' );
	 				var widgetArray = new WPO_DataWidget();
	 				for( var k in widgetArray ){
	  				 	var val = $("[name="+k+"]", "#widget_settings").val( ); 
	  					if( $("[name="+k+"]", "#widget_settings").length > 0 ){
	  			 			wgcfg[k] = val;
	  			 		}

	  				}
	  				$(".wpo-ijwidget.active").data( 'wgcfg' ,wgcfg );
	 			}
	 			$('#filemanager-modal').modal('toggle');
 				$( "#widget_settings" ).hide();
	 			return false;
	  		})

	  		/* Widget SHORTCODE HANDLER */
	 		$("#widget-modal .btn-submit-shortcode").click( function(event) {
		 		if( $(".wpo-ijwidget.active") ){
	 				var wgcfg = $(".wpo-ijwidget.active").data( 'wgcfg' );
	 				var widgetArray = new WPO_DataWidget();
	 				for( var k in widgetArray ){
	  				 	var val = $("[name="+k+"]", "#widget_editshortcode").val( ); 
	  					if( $("[name="+k+"]", "#widget_editshortcode").length > 0 ){
	  			 			wgcfg[k] = val;
	  			 		}

	  				}
	  				$(".wpo-ijwidget.active").data( 'wgcfg' ,wgcfg );
	 			}
	 			$('#widget-modal').modal('toggle');
	 			return false;
	  		})

	  		/* Row SHORTCODE HANDLER */
	 		$("#row-modal .btn-submit-shortcode").click( function(event) {
		 		if( $(".wpo-row.active") ){
  					if( $("[name=shortcode]", "#row_editshortcode").length > 0 ){
  						var widgetids = new Array(); 
  						var val = $("[name=shortcode]", "#row_editshortcode").val( ); 
  			 			var rows = $.parseJSON( val  );
	  					var new_row = renderLayoutInRowByShortcode( rows , widgetids, false );
  			 		}
	 			}
	 			$('#row-modal').modal('toggle');
	 			return false;
	  		})
	 		
	 	}

	 	
	 	/**
	 	 * build layout having rows, columns and widgets.
	 	 */
	 	function renderLayoutInRowByShortcode( rows, widgetids, sub, incol ){
	 		if(rows.length == 1) {
	 			// add row here
	 			$(rows).each( function() {
		  			// add row here
		  			var row = $(".wpo-row.active");
		  			$(".inner .wpo-col", row).remove();

		  			$( this.cols ).each( function(){ 

		  				var col = addColumn( $(row).children('.inner').children(".wpo-row.active .sg-col"), row, sub );

		  				$( this.widgets ).each( function(){
		  				 	$wpowidget.createWidgetButton( col, $("#wpo_"+this.wtype) , this );	
		  				 	widgetids.push( this.wkey );
		  				 	console.log( this );
		  				} );
		  				
		  				this.widgets = null;
		  				col.data( 'colcfg', this );
		  			    $(col).css( {'width':(this.lgcol/config.col*100)+'%'} );
	  					if( this.rows.length > 0 ){
		  					renderLayoutInRow( this.rows, widgetids, true, col ); 
		  				}
		  				this.rows = null;
		  			} );

		  			this.cols = null;
		  			row.data( 'rowcfg', this );
	  			} );
	 		}
	 		
  			return true;
	 	}

	 	/**
	 	 * build layout having rows, columns and widgets.
	 	 */
	 	function renderLayoutInRow( rows, widgetids, sub, incol ){
	 		$(rows).each( function() {
	  			// add row here
	  			var row = addRow( sub==true?incol.children( '.inner' ):$(".add-row",$wpolayout), sub, incol );
	  			$( this.cols ).each( function(){ 

	  				var col = addColumn( $(row).children('.inner').children(".wpo-row.active .sg-col"), row, sub );

	  				$( this.widgets ).each( function(){
	  					var shortcode = this.shortcode;
	  					if(typeof(shortcode) == "undefined" || shortcode == "" || shortcode == false) {
	  						shortcode = getWidgetShortcode(this.wkey);
	  					}

	  					this.shortcode = shortcode;

	  				 	$wpowidget.createWidgetButton( col, $("#wpo_"+this.wtype) , this );	
	  				 	widgetids.push( this.wkey );
	  				 	console.log( this );
	  				} );
	  				
	  				this.widgets = null;
	  				col.data( 'colcfg', this );
	  			    $(col).css( {'width':(this.lgcol/config.col*100)+'%'} );
  					if( this.rows.length > 0 ){
	  					renderLayoutInRow( this.rows, widgetids, true, col ); 
	  				}
	  				this.rows = null;
	  			} );

	  			this.cols = null;
	  			row.data( 'rowcfg', this );
  			} );
  			return true;
	 	}

	 	/**
	 	 *
	 	 *
	 	 */
	 	function buildLayoutByJson( json ) {  
	 		var widgetids = new Array(); 
	 		if( json ) {
		  		var rows = $.parseJSON( json  );
	  			renderLayoutInRow( rows , widgetids, false );
		  	}	 
		  	$wpolayout.fadeIn( 600 );	
		   	
		   	$($wpolayout).children(".inner" ).sortable({
				connectWith: ".layout-builder",
				placeholder: "ui-state-highlight",
				handle:'.wpo-sortable',
				cancel:'.wpo-icon, .wpo-content' 
			});
		   	
	   	//	$wpowidget.loadWidgetByIds( widgetids );	
	 	}
	 	function getWidgetKey(){
 			var d = new Date();
			return d.getTime();
 		}
	 	/**
	 	 *
	 	 *
	 	 */
	 	function buildWidgetItem( col, widget, content ) {
	 		var widget = widget !=null?widget:'';
	 		var name = '';
	 		var wkey = getWidgetKey();

			var dw = new WPO_DataWidget();
			dw.wkey = wkey;
			dw.shortcode = content;

			if(widget) {
				var $w = widget;
				wkey = widget.data("wgcfg").wkey;
				dw = widget.data("wgcfg");
				dw.settings = (typeof(widget.data("wgcfg").settings) !== "undefined")?widget.data("wgcfg").settings:'';
				dw.wkey = wkey;
				dw.shortcode = content;
			} else {
				var $w = $( '<div class="wpo-ijwidget" id="widget'+dw.wkey+'"></div>' );
			}

			$w.data( 'wgcfg', dw );

			$w.html(  buildWidgetHtml(content) );

			$w.append('<div class="wpo-wedit-shortcode ptstooltip" data-toggle="tooltip" data-placement="top" title="Edit Short Code"></div>');
			$w.append('<div class="wpo-wsetting ptstooltip" data-toggle="tooltip" data-placement="top" title="Settings"></div>');
			$w.append('<div class="wpo-wcopy ptstooltip" data-toggle="tooltip" data-placement="top" title="Duplicate"></div>');
			$w.append('<div class="wpo-wedit ptstooltip" data-toggle="tooltip" data-placement="top" title="Edit"></div>');
			$w.append('<div class="wpo-wdelete ptstooltip" data-toggle="tooltip" data-placement="top" title="Delete"></div>');
			$w.append( '<textarea class="wpo-cfginput" id="wpowidget'+dw.wkey+'" name="wpowidget['+dw.wkey+'][config]">'+dw.shortcode+'</textarea>' );
			// alert(  $w.name );
			
			$( ".wpo-wcopy", $w ).click( function(){   
				$wpowidget.cloneWidget(  $w, col );
			} );

			$( ".wpo-wedit", $w ).click( function(){  
				$wpowidget.editWidget( $w );
			} );

			$( ".wpo-wedit-shortcode", $w ).click( function(){  
				$wpowidget.editShortcode( $w );
			} );

			$( ".wpo-wsetting", $w ).click( function(){  
				$wpowidget.settingWidget( $w );
			} );

			$( ".wpo-wdelete", $w ).click( function(){  
				if( confirm(config.confirmdel) ){
					$w.remove();
				}
				
			} );

			if($('#widget'+dw.wkey).length > 0) {
				$('#widget'+dw.wkey).replaceWith( $w );
			} else {
				$(col).find( ".wpo-content" ).append( $w );
			}
			

			return wkey;
	 	}

	 
	 	/**
	 	 * initialize every element
	 	 */
		this.each(function() {  
			 
			$wpolayout = $(this);
		 
			
			init(); 

			/* add some global delegate events */
			bindDelegateEvents();

			/* add event triggers to operator in form of selected column and selected row */
			triggerFormsEvent();

			/* add trigger saving setting layout form */
		//	triggerSaveForm(); 
			/* preload all widgets types by ajax then calling buildLayoutByJson to render layout follow setting*/
			$wpowidget.loadWidgets( false, buildLayoutByJson, datajson, buildWidgetItem  );	
                	        
            $("body").delegate(".wpo-row", "mouseenter", function(){
                $(".ptstooltip").tooltip();
            });

		});
                
                
		return this;
	};
	


})(jQuery);

 function wysiwygExists() {
    return (typeof tinyMCE != 'undefined');
}

function getLayoutData( container ){
 			var output = new Array();	
 			jQuery( container ).children('.inner ').children(".wpo-row").each( function(){
 		 
	 			var _row = jQuery(this);
	 			var data = _row.data('rowcfg');
	 			//Check row options empty data then remove it
	 			var rowsArray = new WPO_DataRow();
	  			for( var k in rowsArray ){
	  				if(k !="cols" && typeof(data[k]) !== "undefined" && data[k] == "" ){
	  					delete data[k];
	  				}
	  			}//End Check row options empty data then remove it

	 			data.cols = new Array();
	 			jQuery(_row).children('.inner').children( '.wpo-col' ).each( function(){
	 				var _col = jQuery(this).data('colcfg');
	 				//Check column options empty data then remove it
		 			var colsArray = new WPO_DataCol();
		  			for( var j in colsArray ){
		  				if((j !="widgets" || j !="rows") && typeof(_col[j]) !== "undefined" && _col[j] == "" ){
		  					delete _col[j];
		  				}
		  			}//End Check column options empty data then remove it
	 				_col.widgets = new Array();

	 				jQuery(this).children('.inner').children('.wpo-content ').children( '.wpo-ijwidget' ).each( function() {  
	 					wd = jQuery(this).data("wgcfg");
	 					//Check widget options empty data then remove it
			 			var widgetsArray = new WPO_DataWidget();
			  			for( var i in widgetsArray ){
			  				if(typeof(wd[i]) !== "undefined" && wd[i] == "" ){
			  					delete wd[i];
			  				}
			  			}//End Check widget options empty data then remove it
			  			//Remove widget shortcode if shortcode was stored in widgets array
			  			if(jQuery('#wpowidget'+wd.wkey).length > 0 && ("" != jQuery('#wpowidget'+wd.wkey).val())){
			  				wd.shortcode = "";
			  			}//End Remove widget shortcode if shortcode was stored in widgets array

	 					_col.widgets.push( wd );
	 				} ); 
	 				_col.rows = new Array();
	 				if( jQuery(this).children('.inner').children( '.wpo-row' ).length > 0 ){
	 					_col.rows = getLayoutData( this );
	 				}
	 				data.cols.push( _col );
	 			} );
	 			output.push( data );  
		 	} );
	 		return output;	
}

function getRowShortCode( _row ) {
	var output = new Array();	
	var _row = jQuery(_row);
	var data = _row.data('rowcfg');
	data.cols = new Array();
	jQuery(_row).children('.inner').children( '.wpo-col' ).each( function(){
		var _col = jQuery(this).data('colcfg');
		_col.widgets = new Array();

		jQuery(this).children('.inner').children('.wpo-content ').children( '.wpo-ijwidget' ).each( function() {  
			wd = jQuery(this).data("wgcfg");
			_col.widgets.push( wd );
		} ); 
		_col.rows = new Array();
		if( jQuery(this).children('.inner').children( '.wpo-row' ).length > 0 ){
			_col.rows = getLayoutData( this );
		}
		data.cols.push( _col );
	} );
	output.push( data );

	var j = JSON.stringify( output );
	return j;
}

/**
	* add event triggers to operator in form of selected column and selected row 
*/
	 	

//triggerSaveForm();

function triggerSaveForm(){

	jQuery(".layout-builder-wrapper").each( function(){
			var output = getLayoutData( jQuery(this).find(".layout-builder") );
		    var j = JSON.stringify( output );
		    jQuery(".hidden-content-layout",this).html( j );
		    //if(wysiwygExists()) {
		    	//tinyMCE.activeEditor.setContent( j );
		    //}
	} );
}


function buildWidgetHtml(shortcode) {
	var $widget_type = getWidgetTypeByShortcode(shortcode);
	var $html = "";
	var $widget_info = null;
	if($widget_type != "") {
		$widget_info = getWidgetsInfo( $widget_type );
		var widget_group = "other";
		var widget_code = $widget_type.replace("/","_");
		var widget_icon = "other";
		var widget_font_icon = "";
		var widget_title = $widget_type.replace("/"," - ");
		var widget_description = "";

		if($widget_info != null && $widget_info != "") {
			widget_group = $widget_info.group;
			widget_code = $widget_info.code;
			widget_icon = $widget_info.icon;
			widget_font_icon = $widget_info.font_icon;
			widget_title = $widget_info.title;
			widget_description = $widget_info.description;
		}

		$html = '<div class="wpo-wg-button" data-group="'+ widget_group +'">';
		$html += '<div data-widget="'+ widget_code + '" id="wpo_'+ widget_code + '">';
		$html += '<div class="wpo-wicon wpo-icon-'+ widget_icon + '">';
		if(widget_font_icon) {
			$html += '<i class="'+widget_font_icon+'" aria-hidden="true"/>'
		}
		$html += '</div>';
		$html += '<div class="widget-title">'+ widget_title + '</div>';
		$html += '<i class="widget-desc">'+ widget_description + '</i>';
		$html += '</div>';
		$html += '</div>';
	}
	return $html !="" ? $html:shortcode;
}

function testAnim(x) {
    jQuery('#demo-effect-block').removeClass().addClass(x + ' animated').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
      jQuery(this).removeClass();
    });
}

function animateDemoPreview(anim, t, i) {
    var a, n, o, r = jQuery("#effect-preview-block");
    var html = '<div id="effect-preview-block" style="display: none;"><div class="demo-block"><div id="demo-effect-block">Demo effect</div></div></div>';
    if(i && r.length) {
    	r.remove();
    	return false;
    }

    r = jQuery(html).appendTo(document.body);

    if(t){
    	r.css(t);
    }
    r.show();
    testAnim(anim);
    return true;
}
//Call json editor to parser json string
function initJSONEditor(textarea_element, wrapper_element) {

}

jQuery(document).ready(function ($) {
    var e = $(document);
    e.on("mouseenter", ".effect-list > option", function (e) {
        e.preventDefault();
        var t = $(this),
            i = t.parent().offset();
        i.left -= 330, animateDemoPreview(t.val(), i)
    }).on("mouseleave", ".effect-list > option", function () {
        animateDemoPreview("", "", !0)
    });
});

