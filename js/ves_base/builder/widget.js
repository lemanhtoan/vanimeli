/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
var Ves_Builder = function () {
    this.builder = null;
    this.callback = {};
    this.currentCol = null;
    this.currentWidget = null;
    this.currentShortcode = ''; 
}
var turnoffTinyMCEs = [];
var getContentTinyMCEs = [];
var getTinyMCEFields = [];
var VesBuilder = null;
var VesCallBack = {};
var VesCurrentCol = null;
var VesCurrentWidget = null;
var VesCurrentShortcode = "";

var WysiwygWidgetTools = {
    getDivHtml: function(id, html) {
        if (!html) html = '';
        return '<div id="' + id + '">' + html + '</div>';
    },

    onAjaxSuccess: function(transport) {
        if (transport.responseText.isJSON()) {
            var response = transport.responseText.evalJSON()
            if (response.error) {
                throw response;
            } else if (response.ajaxExpired && response.ajaxRedirect) {
                setLocation(response.ajaxRedirect);
            }
        }
    },

    openDialog: function(widgetUrl, objbuilder, callback, col ) {
        var objbuilder = objbuilder !=null?objbuilder:null;
        var callback = callback != null?callback:null;
        var col = col != null?col:null;

        if ($('widget_window') && typeof(Windows) != 'undefined') {
            Windows.focus('widget_window');
            return;
        }

        VesBuilder = new Ves_Builder();
        VesBuilder.currentShortcode = "";
        VesBuilder.currentWidget = null;
        VesBuilder.builder = objbuilder;
        VesBuilder.callback['widget'] = callback;
        VesBuilder.currentCol = col;

        this.dialogWindow = Dialog.info(null, {
            draggable:true,
            resizable:false,
            closable:true,
            className:'magento',
            windowClassName:"popup-window",
            title:Translator.translate('Add Widget...'),
            top:50,
            width:950,
            //height:450,
            zIndex:1000,
            recenterAuto:false,
            hideEffect:Element.hide,
            showEffect:Element.show,
            id:'widget_window',
            onClose: this.closeDialog.bind(this)
        });
        new Ajax.Updater('modal_dialog_message', widgetUrl, {evalScripts: true});
    },
    openFormDialog: function(widgetUrl, widget, widget_shortcode, objbuilder, callback, col ) {
        var widget_shortcode = widget_shortcode!=null?widget_shortcode:"";
        var widget = widget != null?widget:null;
        var objbuilder = objbuilder !=null?objbuilder:null;
        var callback = callback != null?callback:null;
        var col = col != null?col:null;

        VesBuilder = new Ves_Builder();
        VesBuilder.currentShortcode = widget_shortcode;
        VesBuilder.currentWidget = widget;
        VesBuilder.builder = objbuilder;
        VesBuilder.callback['widget'] = callback;
        VesBuilder.currentCol = col;


        if ($('widget_window') && typeof(Windows) != 'undefined') {
            Windows.focus('widget_window');
            return;
        }

        this.dialogWindow = Dialog.info(null, {
            draggable:true,
            resizable:false,
            closable:true,
            className:'magento',
            windowClassName:"popup-window",
            title:Translator.translate('Add Widget...'),
            top:50,
            width:950,
            //height:450,
            zIndex:1000,
            recenterAuto:false,
            hideEffect:Element.hide,
            showEffect:Element.show,
            id:'widget_window',
            onClose: this.closeDialog.bind(this)
        });
        new Ajax.Updater('modal_dialog_message', widgetUrl, {evalScripts: true});
    },
    closeDialog: function(window) {
        if(turnoffTinyMCEs.length > 0) {
            for(i=0; i < turnoffTinyMCEs.length; i++) {
                if(typeof turnoffTinyMCEs[i] == "function") {
                    turnoffTinyMCEs[i]();
                }
            }
            getContentTinyMCEs = [];
            getTinyMCEFields = [];
        }
        if (!window) {
            window = this.dialogWindow;
        }
        if (window) {
            // IE fix - hidden form select fields after closing dialog
            WindowUtilities._showSelect();
            window.close();
        }
    }
}

var WysiwygWidget = {};
WysiwygWidget.Widget = Class.create();
WysiwygWidget.Widget.prototype = {

    initialize: function(formEl, widgetEl, widgetOptionsEl, optionsSourceUrl, widgetTargetId) {
        $(formEl).insert({bottom: WysiwygWidgetTools.getDivHtml(widgetOptionsEl)});
        this.formEl = formEl;
        this.widgetEl = $(widgetEl);
        this.widgetOptionsEl = $(widgetOptionsEl);
        this.optionsUrl = optionsSourceUrl;
        this.optionValues = new Hash({});
        this.widgetTargetId = widgetTargetId;

        this.buildWidgetForm(widgetEl);

        if(typeof(jQuery) != "undefined") {
            jQuery(this.widgetEl).on("change", this.loadOptions.bind(this) );
        } else {
            Event.observe(this.widgetEl, "change", this.loadOptions.bind(this));
        }
        //Event.observe(this.widgetEl, "change", this.loadOptions.bind(this));
        

        this.initOptionValue();
    },
    buildWidgetForm: function(widgetEl) {
        var widgets_list = $("wpo-widgetslist").innerHTML;
        if(widgets_list) {
            $$("#base_fieldset .widgets_list").remove();
            $$("#base_fieldset .form-list").each(Element.hide);
            $("base_fieldset").insert(widgets_list);
            this.widgetsFillter();
            this.widgetsAction(widgetEl);

        }
    },
    widgetsFillter:  function(){ //jQuery Function
        if(typeof(jQuery) == "undefined")
            return;

        jQuery("#base_fieldset .showinform").show();
        

        jQuery(".backtolist", "#base_fieldset").click( function(){
            jQuery("#base_fieldset .widgets-filter").toggle();
            jQuery("#base_fieldset .widgets_list").toggle();
        });

        jQuery("#searchwidgets").keypress( function( event ){
        
            if ( event.which == 13 ) {
                event.preventDefault();
            }
            var $this = this;
            setTimeout( function(){
                 if( jQuery.trim(jQuery("#searchwidgets").val()) !="" ) {
                    jQuery(".widgets_list .wpo-wg-button").hide(); 
                    jQuery( "div.widget-title:contains("+jQuery("#searchwidgets").val()+")" ).parent().parent().show();
                }else { 
                    jQuery(".widgets_list .wpo-wg-button").show();
                }

             }, 300 );

        } );

        jQuery( '.filter-option' ,"#filterbygroups").click( function(){
            jQuery( '.filter-option' ,"#filterbygroups").removeClass( 'active' );
            jQuery(this).addClass( 'active' );
            if( jQuery(this).data('option') == 'all' ) {
                jQuery(".widgets_list .wpo-wg-button").show();  
            }else {
                jQuery(".widgets_list .wpo-wg-button").hide();  
                jQuery('[data-group='+jQuery(this).data('option')+']').show();
            }   
            return false; 
        } );
    },
    widgetsAction : function(widgetEl ){
        if(typeof(jQuery) == "undefined")
            return;
        var $wwidgets = jQuery("#base_fieldset");
        var $obj = this;
        jQuery(".wpo-wg-button > div", $wwidgets ).click( function(){
            var widget_type = jQuery(this).data("widgettype");
            var options = $$('select#'+widgetEl+' option');
            var len = options.length;
            $obj.widgetEl.value = widget_type;
            for (var i = 0; i < len; i++) {
                if(options[i].value == widget_type) {
                    options[i].selected = true;
                }
            }
            jQuery('select#'+widgetEl).trigger("change");
        } );
    },
    getOptionsContainerId: function() {
        return this.widgetOptionsEl.id + '_' + this.widgetEl.value.gsub(/\//, '_');
    },

    switchOptionsContainer: function(containerId) {
        $$('#' + this.widgetOptionsEl.id + ' div[id^=' + this.widgetOptionsEl.id + ']').each(function(e) {
            this.disableOptionsContainer(e.id);
        }.bind(this));
        if(containerId != undefined) {
            this.enableOptionsContainer(containerId);
        }
        this._showWidgetDescription();
    },

    enableOptionsContainer: function(containerId) {
        $$('#' + containerId + ' .widget-option').each(function(e) {
            e.removeClassName('skip-submit');
            if (e.hasClassName('obligatory')) {
                e.removeClassName('obligatory');
                e.addClassName('required-entry');
            }
        });
        $(containerId).removeClassName('no-display');
    },

    disableOptionsContainer: function(containerId) {
        if ($(containerId).hasClassName('no-display')) {
            return;
        }
        $$('#' + containerId + ' .widget-option').each(function(e) {
            // Avoid submitting fields of unactive container
            if (!e.hasClassName('skip-submit')) {
                e.addClassName('skip-submit');
            }
            // Form validation workaround for unactive container
            if (e.hasClassName('required-entry')) {
                e.removeClassName('required-entry');
                e.addClassName('obligatory');
            }
        });
        $(containerId).addClassName('no-display');
    },

    // Assign widget options values when existing widget selected in WYSIWYG
    initOptionValues: function() {

        if (!this.wysiwygExists()) {
            return false;
        }

        var e = this.getWysiwygNode();
        if (e != undefined && e.id) {
            var widgetCode = Base64.idDecode(e.id);
            if (widgetCode.indexOf('{{widget') != -1) {
                this.optionValues = new Hash({});
                widgetCode.gsub(/([a-z0-9\_]+)\s*\=\s*[\"]{1}([^\"]+)[\"]{1}/i, function(match){
                    if (match[1] == 'type') {
                        this.widgetEl.value = match[2];
                    } else {
                        this.optionValues.set(match[1], match[2]);
                    }
                }.bind(this));

                this.loadOptions();
            }
        }
    },
    // Assign widget options values when existing widget selected in WYSIWYG
    initOptionValue: function(widgetCode) {
        var widgetCode = VesBuilder.currentShortcode!=null?VesBuilder.currentShortcode:"";
        if (widgetCode.indexOf('{{widget') != -1) {
                this.optionValues = new Hash({});
                widgetCode.gsub(/([a-z0-9\_]+)\s*\=\s*[\"]{1}([^\"]+)[\"]{1}/i, function(match){
                    if (match[1] == 'type') {
                        this.widgetEl.value = match[2];
                    } else {
                        this.optionValues.set(match[1], match[2]);
                    }
                }.bind(this));

                this.loadOptions();
        } else {
            jQuery("#base_fieldset .widgets-filter").show();
            jQuery("#base_fieldset .widgets_list").show();
        }
    },
    loadOptions: function() {

        if (!this.widgetEl.value) {
            this.switchOptionsContainer();
            return;
        }

        var optionsContainerId = this.getOptionsContainerId();
        if ($(optionsContainerId) != undefined) {
            this.switchOptionsContainer(optionsContainerId);
            return;
        }

        this._showWidgetDescription();

        var params = {widget_type: this.widgetEl.value, values: this.optionValues};
        new Ajax.Request(this.optionsUrl,
            {
                parameters: {widget: Object.toJSON(params)},
                onSuccess: function(transport) {
                    try {
                        WysiwygWidgetTools.onAjaxSuccess(transport);
                        this.switchOptionsContainer();
                        if ($(optionsContainerId) == undefined) {
                            this.widgetOptionsEl.insert({bottom: WysiwygWidgetTools.getDivHtml(optionsContainerId, transport.responseText)});

                            if(typeof(jQuery) != "undefined") { 
                                if(jQuery("#base_fieldset .showinform").length > 0) {
                                    jQuery("#base_fieldset .widgets-filter").hide();
                                    jQuery("#base_fieldset .widgets_list").hide();
                                }
                            }
            
                        } else {
                            this.switchOptionsContainer(optionsContainerId);
                        }
                    } catch(e) {
                        alert(e.message);
                    }
                }.bind(this)
            }
        );
    },

    _showWidgetDescription: function() {
        
        var noteCnt = this.widgetEl.next().down('small');
        var descrCnt = $('widget-description-' + this.widgetEl.selectedIndex);
        if(noteCnt != undefined) {
            var description = (descrCnt != undefined ? descrCnt.innerHTML : '');
            noteCnt.update(descrCnt.innerHTML);

            if(typeof(jQuery) != "undefined") {
                jQuery("#base_fieldset .widget-info").html(descrCnt.innerHTML);
            }
        }

    },

    insertWidget: function() {
        widgetOptionsForm = new varienForm(this.formEl);
        if(widgetOptionsForm.validator && widgetOptionsForm.validator.validate() || !widgetOptionsForm.validator){

            var formElements = [];
            var i = 0;
            Form.getElements($(this.formEl)).each(function(e) {
                if(!e.hasClassName('skip-submit')) {
                    formElements[i] = e;
                    i++;
                }
            });

            // Add as_is flag to parameters if wysiwyg editor doesn't exist
            //var params = Form.serializeElements(formElements);
            var params = Form.serializeElements(formElements,{hash:true,submit:false});

            if(typeof(tinyMCE) != "undefined" && getContentTinyMCEs.length > 0) {
                var field_name = "";
                for(i = 0; i < getContentTinyMCEs.length; i++) {
                    if(typeof getContentTinyMCEs[i] == "function" && typeof getTinyMCEFields[i] == "function") {
                        field_name = getTinyMCEFields[i]();
                        params[field_name] = getContentTinyMCEs[i]();
                        //params = params + "&"+field_name+"=" + getContentTinyMCEs[i]();
                    }
                    
                }
                getContentTinyMCEs = [];
                getTinyMCEFields = [];
            }
            /*
            if (typeof(tinyMCE) != "undefined" && tinyMCE.activeEditor) {
                //params = params + "&parameters[html]=" + tinyMCE.activeEditor.getContent();
            }*/
            //if (!this.wysiwygExists()) {
            params['as_is'] = 1;
            //params = params + '&as_is=1';
           // }
            new Ajax.Request($(this.formEl).action,
            {
                parameters: params,
                onComplete: function(transport) {
                    try {
                        WysiwygWidgetTools.onAjaxSuccess(transport);
                        Windows.close("widget_window");

                        if (typeof(tinyMCE) != "undefined" && tinyMCE.activeEditor) {
                            tinyMCE.activeEditor.focus();
                            if (this.bMark) {
                                tinyMCE.activeEditor.selection.moveToBookmark(this.bMark);
                            }
                        }

                        this.updateContent(transport.responseText);
                    } catch(e) {
                        alert(e.message);
                    }
                }.bind(this)
            });
        }
    },

    updateContent: function(content) {

        if(typeof(VesBuilder.callback['widget']) != "undefined" && typeof VesBuilder.callback['widget'] == "function") {
           VesBuilder.callback['widget'].call( VesBuilder.builder, VesBuilder.currentCol, VesBuilder.currentWidget, content  );
        }else if (this.wysiwygExists()) {
            this.getWysiwyg().execCommand("mceInsertContent", false, content);
        } else {
            var textarea = document.getElementById(this.widgetTargetId);
            updateElementAtCursor(textarea, content);
            varienGlobalEvents.fireEvent('tinymceChange');
        }
    },

    wysiwygExists: function() {
        return (typeof tinyMCE != 'undefined') && tinyMCE.get(this.widgetTargetId);
    },

    getWysiwyg: function() {
        return tinyMCE.activeEditor;
    },

    getWysiwygNode: function() {
        return tinyMCE.activeEditor.selection.getNode();
    }
    /*
    wysiwygExists: function() {
        return false;
    },

    getWysiwyg: function() {
        return null;
    },

    getWysiwygNode: function() {
        return null;
    }*/
}

WysiwygWidget.chooser = Class.create();
WysiwygWidget.chooser.prototype = {

    // HTML element A, on which click event fired when choose a selection
    chooserId: null,

    // Source URL for Ajax requests
    chooserUrl: null,

    // Chooser config
    config: null,

    // Chooser dialog window
    dialogWindow: null,

    // Chooser content for dialog window
    dialogContent: null,

    overlayShowEffectOptions: null,
    overlayHideEffectOptions: null,

    initialize: function(chooserId, chooserUrl, config) {
        this.chooserId = chooserId;
        this.chooserUrl = chooserUrl;
        this.config = config;
    },

    getResponseContainerId: function() {
        return 'responseCnt' + this.chooserId;
    },

    getChooserControl: function() {
        return $(this.chooserId + 'control');
    },

    getElement: function() {
        return $(this.chooserId + 'value');
    },

    getElementLabel: function() {
        return $(this.chooserId + 'label');
    },

    open: function() {
        $(this.getResponseContainerId()).show();
    },

    close: function() {
        $(this.getResponseContainerId()).hide();
        this.closeDialogWindow();
    },

    choose: function(event) {
        // Open dialog window with previously loaded dialog content
        if (this.dialogContent) {
            this.openDialogWindow(this.dialogContent);
            return;
        }
        // Show or hide chooser content if it was already loaded
        var responseContainerId = this.getResponseContainerId();

        // Otherwise load content from server
        new Ajax.Request(this.chooserUrl,
            {
                parameters: {element_value: this.getElementValue(), element_label: this.getElementLabelText()},
                onSuccess: function(transport) {
                    try {
                        WysiwygWidgetTools.onAjaxSuccess(transport);
                        this.dialogContent = WysiwygWidgetTools.getDivHtml(responseContainerId, transport.responseText);
                        this.openDialogWindow(this.dialogContent);
                    } catch(e) {
                        alert(e.message);
                    }
                }.bind(this)
            }
        );
    },

    openDialogWindow: function(content) {
        this.overlayShowEffectOptions = Windows.overlayShowEffectOptions;
        this.overlayHideEffectOptions = Windows.overlayHideEffectOptions;
        Windows.overlayShowEffectOptions = {duration:0};
        Windows.overlayHideEffectOptions = {duration:0};
        this.dialogWindow = Dialog.info(content, {
            draggable:true,
            resizable:true,
            closable:true,
            className:"magento",
            windowClassName:"popup-window",
            title:this.config.buttons.open,
            top:50,
            width:950,
            height:500,
            zIndex:1000,
            recenterAuto:false,
            hideEffect:Element.hide,
            showEffect:Element.show,
            id:"widget-chooser",
            onClose: this.closeDialogWindow.bind(this)
        });
        content.evalScripts.bind(content).defer();
    },

    closeDialogWindow: function(dialogWindow) {
        if(turnoffTinyMCEs.length > 0) {
            for(i=0; i < turnoffTinyMCEs.length; i++) {
                if(typeof turnoffTinyMCEs[i] == "function") {
                    turnoffTinyMCEs[i]();
                }
                
            }
            getContentTinyMCEs = [];
            getTinyMCEFields = [];
        }
        if (!dialogWindow) {
            dialogWindow = this.dialogWindow;
        }
        if (dialogWindow) {
            dialogWindow.close();
            Windows.overlayShowEffectOptions = this.overlayShowEffectOptions;
            Windows.overlayHideEffectOptions = this.overlayHideEffectOptions;
        }
        this.dialogWindow = null;
    },

    getElementValue: function(value) {
        return this.getElement().value;
    },

    getElementLabelText: function(value) {
        return this.getElementLabel().innerHTML;
    },

    setElementValue: function(value) {
        this.getElement().value = value;
    },

    setElementLabel: function(value) {
        this.getElementLabel().innerHTML = value;
    }
}
/*get widget type from shortcode*/
function getWidgetTypeByShortcode(short_code) {
    var widgetType = "";
    if (short_code.indexOf('{{widget') != -1) {
        short_code.gsub(/([a-z0-9\_]+)\s*\=\s*[\"]{1}([^\"]+)[\"]{1}/i, function(match){
            if (match[1] == 'type') {
                widgetType = match[2];
            }
        });
    }
    return widgetType;
}
