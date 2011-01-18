(function($, context) {

	var Loader = function() {
		this.css = [];
		this.js = [];
	};
	
	Loader.prototype = {
		addCSS: function(css) { this.css.push(css); },
		addJS: function(js) { this.js.push(js) ; }
	}
	
	context.AssetDialog = {
		Loader: Loader,
		DEPENDENCIES: {
			css: [],
		''	js: []
		}
	};
	
})(jQuery, this);
          