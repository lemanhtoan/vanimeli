/**
 * Nwdthemes All Extension
 *
 * @package     All
 * @author		Nwdthemes <mail@nwdthemes.com>
 * @link		http://nwdthemes.com/
 * @copyright   Copyright (c) 2014. Nwdthemes
 * @license     http://themeforest.net/licenses/terms/regular
 */


widgetHeading = Class.create();

widgetHeading.prototype = {

	initialize : function(htmlId)
	{
		this.htmlId = htmlId;
		this.toggleLink = $(htmlId+'_show');
		this.toggleFields();
		this.bindEvents();
	},

	bindEvents : function()
	{
		Event.observe(this.toggleLink, "click", this.toggleFields.bind(this));
	},

	toggleFields : function(event)
	{
		if (event) {
			Event.stop(event);
			this.toggleTitle();
		}
		var arr = $("widget_options_form").getElements(),
			id = this.htmlId;
		arr.each(function(item) {
			if ( $(item).id.indexOf(id) == 0 ) {
				$(item).up(1).toggle();
			}
		});
	},

	toggleTitle: function()
	{
		if ( this.toggleLink.innerHTML == 'show' )
			this.toggleLink.innerHTML = 'hide';
		else
			this.toggleLink.innerHTML = 'show';
	}

}