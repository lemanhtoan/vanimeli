
function updateModalZindex( zvalue ) {
	zvalue = zvalue|0;
	if(jQuery('.modal-backdrop').length > 0 ) {
    	if(jQuery('.modal-backdrop').first().hasClass("in")) {
    		if(zvalue) {
    			jQuery('.modal-backdrop').css("z-index", zvalue);
    		} else {
    			jQuery('.modal-backdrop').attr("style", "");
    		}
    		
    	}
    }
}

var changeElFieldImage = function(obj, id) {
    var a = jQuery(obj).val().replace(base_media_url, "");
    a = a.replace(base_secure_media_url, "");
    jQuery(id).val(a);
    jQuery(id + "_image").attr( "src", jQuery(obj).val() );
    jQuery(id + "_image").attr( "alt", a);
    jQuery(id + "_link").attr( "href", jQuery(obj).val() );
};

var changeBgImagePath = function(obj, imageupload_selector) {
	
    var a = jQuery(obj).val().replace(base_media_url, "");
    a = a.replace(base_secure_media_url, "");
    jQuery(obj).parent().find(imageupload_selector).val(a);
};

function openEfinder(element, field_id, imageupload_selector, callback) {
	if(typeof(field_id) === "undefined") {
		var field_id = jQuery(element).data("filefield");
	}
	if(typeof(imageupload_selector) === "undefined") {
		var imageupload_selector = jQuery(element).data("imageupload");

		if(typeof(imageupload_selector) === "undefined") {
			imageupload_selector = ".imageuploaded";
		}
    	
    }

    
	if(typeof(elfinder_connector) === "undefined" || elfinder_connector == "") {
		elfinder_connector = jQuery('#elfinder').data("connector");
	}

	var options = {
                    resizable: false,
                    width: 900,
                    url : elfinder_connector,  // connector URL (REQUIRED)
                    customData : {
                        "form_key": FORM_KEY
                    },
                    commandsOptions:
                    {
                        getfile:
                        {
                            oncomplete: 'destroy'
                        }
                    },
                    handlers : {
                    	destroy: function(event, instance) {
                    		updateModalZindex( 0 );
                    	}
                    },
                    getFileCallback: function(file) {
                        console.log(file);
                        if(jQuery("#"+field_id).length > 0) {
                        	jQuery("#"+field_id).val(file);
                        	
                        	if(jQuery.isFunction(callback)) {
                        		callback(jQuery("#"+field_id), imageupload_selector);
							} else {
								changeBgImagePath(jQuery("#"+field_id), imageupload_selector);
							}
                        	
                        }
                        
                        jQuery('a.ui-dialog-titlebar-close[role="button"]').click();
                        jQuery('a.dialogelfinder-drag-close').click();
                        updateModalZindex( 0 );
                    }
                };

    if(jQuery.isFunction(callback)) {             
		var f = jQuery('<div></div>').dialogelfinder(options);
        updateModalZindex( 10140 );
	} else {
		jQuery(element).click(function(){
	        //$(element).parent.append();
	        var f = jQuery('<div></div>').dialogelfinder(options);
	        updateModalZindex( 10140 );
	    });
	}

    jQuery(document).on('click','.ui-icon-closethick',function(){
    	updateModalZindex( 0 );
    });
    jQuery(document).on('click','.dialogelfinder-drag-close',function(){
    	updateModalZindex( 0 );
    });
}


jQuery(document).ready(function($) {
	// Highlight any found errors
	$('.text-danger').each(function() {
		var element = $(this).parent().parent();
		
		if (element.hasClass('form-group')) {
			element.addClass('has-error');
		}
	});
	
	// tooltips on hover
	$('[data-toggle=\'tooltip\']').tooltip({container: 'body', html: true});

	if($(".fileupload").length > 0 ) {
		$(".fileupload").each(function(){
			openEfinder(this);
		})
	}
});


