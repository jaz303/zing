var AssetDialog = function() {
	
};




AssetDialog.Dialog = function() {
	this.adapter = null;
	this.root = null;
	this.$root = null;
	this.sources = [];
};

AssetDialog.Dialog.prototype = {
	setAdapter: function(adapter) {
		this.adapter = adapter;
	},
	
	setRoot: function(ele) {
		this.root = ele;
		this.$root = $(ele);
	},
	
	addSource: function(source) {
		this.sources.push(source);
	},
	
	init: function() {
		alert('init');
	}
};