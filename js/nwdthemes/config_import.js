/**
 * Nwdthemes All Extension
 *
 * @package     All
 * @author		Nwdthemes <mail@nwdthemes.com>
 * @link		http://nwdthemes.com/
 * @copyright   Copyright (c) 2014. Nwdthemes
 * @license     http://themeforest.net/licenses/terms/regular
 */

var configImport;

document.observe("dom:loaded", function () {

    configImport = {
        openDialog: function(importUrl) {
            if ($('configimport_window') && typeof(Windows) != 'undefined') {
                Windows.focus('configimport_window');
                return;
            }
            var that = this;
            this.dialogWindow = Dialog.info(null, {
                draggable:true,
                resizable:false,
                closable:true,
                className:'magento',
                windowClassName:"popup-window",
                title:Translator.translate('Load Configuration'),
                top:50,
                width:950,
                zIndex:1000,
                recenterAuto:false,
                hideEffect:Element.hide,
                showEffect:Element.show,
                id:'configimport_window',
                onClose: this.closeDialog.bind(this)
            });
            new Ajax.Updater(
                'modal_dialog_message',
                importUrl,
                {
                    evalScripts: true,
                    onComplete: function(response) {
                        that.dialogWindow.updateHeight();
                    }
                }
            );
        },
        closeDialog: function(window) {
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

});