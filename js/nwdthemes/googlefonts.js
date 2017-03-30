/**
 * Nwdthemes All Extension
 *
 * @package     All
 * @author		Nwdthemes <mail@nwdthemes.com>
 * @link		http://nwdthemes.com/
 * @copyright   Copyright (c) 2014. Nwdthemes
 * @license     http://themeforest.net/licenses/terms/regular
 */

gFontPreview = Class.create();

gFontPreview.prototype = (function() { 
  return {
    initialize : function(htmlId)
    {
      this.htmlId = htmlId;
      this.fontElement = $(htmlId);
      this.previewElement = $("nwdthemes_gfont_preview" + htmlId);
      this.loadedFonts = "";

      this.refreshPreview();
      this.bindFontChange();
    },

    bindFontChange : function()
    {
      Event.observe(this.fontElement, "change", this.refreshPreview.bind(this));
      Event.observe(this.fontElement, "keyup", this.refreshPreview.bind(this));
      Event.observe(this.fontElement, "keydown", this.refreshPreview.bind(this));
    },

    refreshPreview : function()
    {
      if (this.fontElement.value == '') {
        $(this.previewElement).hide();
        return;
      }
      $(this.previewElement).show();
      if ( this.loadedFonts.indexOf( this.fontElement.value ) > -1 ) {
        this.updateFontFamily();
        return;
      }

      var ss = document.createElement("link");
      ss.type = "text/css";
      ss.rel = "stylesheet";
      ss.href = "//fonts.googleapis.com/css?family=" + this.fontElement.value;
      document.getElementsByTagName("head")[0].appendChild(ss);

      this.updateFontFamily();

      this.loadedFonts += this.fontElement.value + ",";
    },

    updateFontFamily : function()
    {
      $(this.previewElement).setStyle({ fontFamily: this.fontElement.value });
    }
  }
})();