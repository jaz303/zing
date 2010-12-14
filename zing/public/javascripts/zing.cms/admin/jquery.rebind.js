// jQuery Rebind
// (c) 2009 Jason Frame (jason@onehackoranother.com)
//
(function($, context) {
	
	var rebinds = [];
	function Rebind($) { this.$ = $; };
	
	Rebind.prototype = {
		_proxy: function(method, content, rebindContext) {
			content = $(content);
			this.$[method](content);
			Rebind.all(rebindContext || content);
			return this;
		},
		back: function() { return this.$; },
		html: function(content) { return this._proxy('html', content, this.$); },
		append: function(content) { return this._proxy('append', content); },
		prepend: function(content) { return this._proxy('prepend', content); }
	};
	
	Rebind.one = function(context) {
		for (var i = 0; i < rebinds.length; i++) {
			rebinds[i](context);
		}
	};
	
	Rebind.all = function(collection) {
		for (var i = 0; i < collection.length; i++) {
			Rebind.one(collection[i]);
		}
	};
	
	$.fn.rebind = function() {
		return new Rebind(this);
	};
	
	$.rebind = function(fn) {
		rebinds.push(fn);
	};
	
	context.Rebind = Rebind;
	
})(jQuery, this);
