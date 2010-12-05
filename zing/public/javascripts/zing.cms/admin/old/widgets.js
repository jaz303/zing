ff.widgets = {
	
	REGISTRY: {},
	
	register: function(id, widget) { 
	    ff.widgets.REGISTRY[id] = widget; 
	},
	
	lookup: function(id) {
	    return ff.widgets.REGISTRY[id]; 
	},
	
	find: function(ele) {
		var p = $(ele).parents('.widget:first')[0];
		if(p) {
			return ff.widgets.lookup(p.id);
		} else {
			return null;
		}
	},
	
	create: function(extend) {

        var widget = function(container, options) {
            this.container = container;
            this.name = options.name;
            this.options = $.extend({}, this.getDefaultOptions(), options || {});
            this.init();
            this.setValue(options.value || null);
						$(container).addClass('widget');
            ff.widgets.register(container.id, this);
        }

        widget.prototype = {
            init: function() {},
            parseValue: function(v) { return v; },
            setValue: function(v) { this.value = this.parseValue(v); this.changed(); this.redraw(); },
            getValue: function() { return this.value; },
            redraw: function() { this.container.innerHTML = this.toHtml(); },
            changed: function() { $(this.container).change(); },
            getDefaultOptions: function() { return {}; },
            getOptions: function() { return this.options; },
            toHtml: function() { return ""; }
        }

        for (k in extend) widget.prototype[k] = extend[k];

        return widget;

    }
};

ff.widgets.AssetInput = ff.widgets.create({
    parseValue: function(asset) {
        if (!asset) return null;
        if (asset instanceof ff.AssetStub) return asset;
        return new ff.AssetStub(asset);
    },
    toHtml: function() {
		var html = "";
		
		if (!this.value) {
			html += "<b>no asset selected</b><br/>";
		} else {
			if(this.value.isWebImage()) {
				html += "<img class='preview' src='" + this.value.getPreviewUrl() + "' />";
			} else {
				html += " <span>" + this.value.getTitle() + "</span>";
			}
		}
		html += " <input type='hidden' name='" + this.name + "' value='" + (!this.value ? '' : this.value.getId()) + "' />";
		html += "<div class='actions'>";
		html += "<a href='#' onclick='ff.widgets.AssetInput.openDialog(this); return false;'><img src='/images/icons/silk/picture_add.png' alt='change' /> Change</a>";
		if(this.value) {
			html += "<a href='#' onclick='ff.widgets.find(this).setValue(null); return false;'><img src='/images/icons/silk/cross.png' alt='delete' /> Remove</a>";
		}
		html += "</div>";
		return html;
	}
});

ff.widgets.AssetInput.openDialog = function(me) {
 	var target = $(me).parents('.asset-input')[0];
	var widget = ff.widgets.lookup(target.id);
	var options = widget.getOptions();
	if(!options.target) {
		options = $.extend(options, {target:target.id});
	}
	if(widget.getValue() && widget.getValue().getId()) {
		options = $.extend(options, {asset_id:widget.getValue().getId()});
	}
	ff.dialog.assetSelect.open(options);
};

ff.widgets.UrlInput = ff.widgets.create({
	getDefaultOptions: function() {
		return {type: 'url'};
	},
    parseValue: function(url) { 
		if(!url) return null
		if(url instanceof ff.UrlStub) return url;
		return new ff.UrlStub(url);
	},
	type: function() {
		return this.options.type;
	},
	toHtml: function() {
    var html = "<a href='#' onclick='ff.widgets.UrlInput.openDialog(this);' title='" + (this.value && this.value.getUrl() ? (this.value.getUrl() + '; c') : 'C') + "lick to change'><img src='/images/icons/silk/world.png' alt='select' />";

		if(!this.value || !this.value.getUrl()) {
			html += " <b>no URL selected</b></a>";
		} else {
		    var url = this.value.getUrl().substr(0,20);
		    if (this.value.getUrl().length > 20) url += "...";
		    html += " " + url + "</a> <a href='" + this.value.getUrl() + "' target='_blank' title='View link in new window'><img src='/images/icons/silk/magnifier.png' alt='select' /> Open</a>"
				html += "<a href='#' onclick='ff.widgets.find(this).setValue(null); return false;'><img src='/images/icons/silk/cross.png' alt='delete' /> Remove</a>";
			}
			
			var val;
			if(!this.value) {
				val = null;
			} else if(this.type() == 'url') {
				val = this.value.getUrl();
			} else if(this.type() == 'node_id') {
				val = this.value.getNodeId();
			}
			html += "<input type='hidden' name='" + this.name + "' value='" + (val ? val : '') + "' />";
			
			return html;
    }
});

ff.widgets.UrlInput.openDialog = function(me) {
	var target = $(me).parents('.url-input')[0];
	var widget = ff.widgets.lookup(target.id);
	ff.dialog.content.open(target.id, widget.getOptions());
}