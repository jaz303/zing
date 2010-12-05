String.prototype.substitute = function(subs, esc) {
  esc = false; // TODO: fix escaping function
  return this.replace(/\{(\w+)\}/g, function(matches, index) {
    var val = '' + typeof(subs[index]) == 'undefined' ? '' : subs[index];
    return esc ? escape(val) : val;
  });
};

function modelId(ele) {
	var res;
	while (ele) {
		if (ele.id && (res = ele.id.match(/\-(\d+)$/))) return parseInt(res[1]);
		ele = ele.parentNode;
	}
}

$(function() {
	
	$.fn.htmlWithBehaviours = function(content) {
		content = $(content);
		this.html(content);
		ff.behaviours.bind(this[0]);
	};
	
	$.fn.appendWithBehaviours = function(content) {
		content = $(content);
		this.append(content);
		ff.behaviours.bind(content);
		return this;
	};
	
	$.fn.clearOnFocus = function() {
		this.each(function() {
			var memo = $(this).val();
			$(this).focus(function() { if ($(this).val() == memo) $(this).val(''); });
			$(this).blur(function() { if ($(this).val() == '') $(this).val(memo); });
		});
	};

   // $.ajaxSetup({
   //   dataType: 'script',
   // 	 beforeSend: function(xhr) {
   //     xhr.setRequestHeader('Accept', 'text/javascript');
   // 	 }
   // });
   // 
   $('#activity-indicator').ajaxStart(function() {
       $(this).show();
   }).ajaxStop(function() {
       $(this).hide();
   });
	
	ff.init();   
});

var ff = {
	
	DEFAULT_FLASH_TIMEOUT: 5000,
	
	behaviours: {
		_custom: [],
		bind: function(context) {	
			
			$('select.link-to').change(function() {
				window.location = $(this).val();
				return false;
			});
		
			// IE only - hide dotted lines around links
			if($.browser.msie) {
				$('a').focus(function() {
					this.hideFocus=true;
				});
			}
			
			// REVIEW - RJS Forms
			$('form.rjs-form', context).ajaxForm({ 
				dataType: 'script',
			 	beforeSend: function(xhr) {
		      xhr.setRequestHeader('Accept', 'text/javascript');
		   	}
			});
			
			// AJAX Links
			$('a.ajax-post', context).click(function() {
				var self = this;
				var locked = self.getAttribute('ajax-post-lock');
				if (locked) {
					alert('An operation in already in progress; please wait and retry.');
				} else {
					if($(self).hasClass('confirm')) {
						if(!confirm("Are you sure?")) {
							return false;
						}
					}	
					self.setAttribute('ajax-post-lock', 'true');
					$.ajax({
						type: 'POST',
						dataType: 'script',
						url: $(self).attr('href'),
						beforeSend: function(xhr) { xhr.setRequestHeader('Accept', 'text/javascript'); },
						complete: function() { self.removeAttribute('ajax-post-lock'); },
						data: {'_method':'POST'}
					});
				}
				return false;
			});
			
			// Zebra tables
			$('table.list tr', context).removeClass('odd');
			$('table.list tr:odd', context).addClass('odd');
			
			// Thickbox - not always loaded
			if (typeof(jQuery.fn.thickbox) != 'undefined') {
				$('a.thickbox, area.thickbox, input.thickbox', context).thickbox();
			}

			// Classify inputs
		  $('input', context).each(function() { $(this).addClass($(this).attr('type')); });
					
			// Asset select inputs
			$('.asset-input:not(.raw)', context).each(function() {
				new ff.widgets.AssetInput(this, eval("(" + $(this).html() + ")"));
			});
			
			// URL select inputs
			$('.url-input:not(.raw)', context).each(function() {
				new ff.widgets.UrlInput(this, eval("(" + $(this).html() + ")"));
			});
			
			// Textile editors
	  	$('.textile-editor').each(function() {
	  		this.textileEditor = new TextileEditor(this);
	  	});
	
			// Focus on first responder if present
			$('.first-responder').focus();
			
			// Sliders
			$('div.slider', context).slider({});
			
			// Expanders (e.g. for revealing text in news table cells)
			// .expandable should be a sibling of .expander
			// If no .expandable is found on click, a function, expandableMissing,
			// is called if it exists, and passed the expander element which was clicked.
			// This function should carry out any loading work, and also toggle the
			// visible status of the toggle buttons on success.
			$('.expandable', context).hide();
			$('.expander', context).click(function() {
			    var target = $('~ .expandable', this);
			    if (target.length > 0) {
			        $('img', this).toggle();
			        target.toggle();
			    } else if (typeof expandableMissing == 'function') {
			        expandableMissing(this);
			    }
			    return false;
			});

			//
			// Expandable text fields
		
			$('.expandable-text-field .need-more-space, .expandable-text-field .need-less-space', context).click(function() {
			
				var parent = $(this).parents('.expandable-text-field:first');
				var input = $('input,textarea:first', parent)[0], alt = null;
			
				$('.need-less-space, .need-more-space', parent).toggle();
			    if (input.nodeName.toLowerCase() == 'input') {
			        var alt = document.createElement('textarea');
			    } else {
			        var alt = document.createElement('input');
			        alt.type = 'text';
			    }
			    alt.name = input.name;
			    alt.style.width = (input.offsetWidth - 6) + 'px'; // HACK!
			
				$(alt).val($(input).val().replace(/(\r?\n)+/g, ' '));
				$(input).replaceWith(alt);
		
				return false;
			});
			
			$('a[rel=boxy]').boxy();

			// Custom behaviours
			for(var i = 0; i < ff.behaviours._custom.length; i++) {
				ff.behaviours._custom[i](context);
			}
		},
		register: function(f) {
			ff.behaviours._custom.push(f);
		}
	},
	
	init: function() {
		ff.behaviours.bind(document);
		
		if ($('#help-wrapper').length > 0) {
			$('#help-actuator-wrapper').css('display', 'inline');
			// $('#help-actuator').click(ff.help.toggle);
		}
		
		if ($('#notifications .flash').length > 0) {
			setTimeout(ff.hideBottomFlash, ff.DEFAULT_FLASH_TIMEOUT);
		}
	},
	
	dialog: {
	  
	  /** Functions to be called in window.opener from dialog boxes */
		// DEPRECATED
		// integration: {
		// 	
		// 	/**
		// 	 * Embed HTML within a rich-text editor instance
		// 	 *
		// 	 * @param id ID of editor
		// 	 * @param html HTML to embed at cursor
		// 	 */
		// 	embedHTML: function(id, html) {
		// 		ff.editors.get(id).insertContentAtCursor(html);
		// 	}
		// },
		
		content: {
			handle: null,
			target: null,
			callback: null,
			open: function(target, options) {
				var d = ff.dialog.content;
				options = options || {};
				d.target = target;
				d.callback = options.callback;
								
				var url = '/admin/cms/content/dialog';
				if(options.type) {
					url += '?type=' + options.type;
				}				
					
				if(d.handle) { d.close(); }
				d.handle = window.open(url, '_content_dialog', 'width=720,height=400,scrollbars=0,status=0,toolbar=0,resizable=0');				
				d.handle.focus(); 
			},
			close: function() {
				var d = ff.dialog.content;
				if(d.handle) { d.handle.close(); }
			},
			selected: function(url) {
				var d = ff.dialog.content;
				var widget = ff.widgets.lookup(d.target);
				
				if(widget) {
					widget.setValue(url);
				}
				if(d.callback) {
					d.callback(url, function() {
						d.close();
					});
				}
			}
		},
			
		assetSelect: {
			handle: null,
			callback: null,
			open: function(options) {
				var d = ff.dialog.assetSelect;
				if(d.handle) { 
					d.close();
				}
				 
				options  = options || {};
				d.callback = options.callback || function(ignore, c) { c(); };
															
				// TODO put options in url
				var url = '/admin/core/assets/dialog';
				var url_options = {target: options.target, multiple: options.multiple, mode: options.mode || 'select'};
				if(options.extension) url_options = $.extend(url_options, {extension: options.extension});
				if(options.asset_id)  url_options = $.extend(url_options, {asset_id: options.asset_id});
				if(options.content_class) url_options = $.extend(url_options, {content_class: options.content_class});		
						
				var n = new Array();
				for(var key in url_options) {
					var v = url_options[key];
					if(v === true) {
						v = '1';
					} else if(v === false) {
						v = '0';
					}
					
					n.push(key + "=" + v);
				}
				
				if(n.length > 0) {
					url += "?" + n.join('&');
				}
							
				d.handle = window.open(url, '_asset_manager', 'width=720,height=400,scrollbars=0,status=0,toolbar=0,resizable=0');
				d.handle.focus();
			},
			close: function() {
				var d = ff.dialog.assetSelect;
				if(d.handle) {
					d.handle.close();
				}
			},
			select: function(target, assets) {				
				var d = ff.dialog.assetSelect;				
				var widget = ff.widgets.lookup(target);
			
				if(widget) {
					if(assets.length > 1) {
						for (var i = 0; i < assets.length; i++) {
							widget.addValue(assets[i]);
						}
					} else {
						widget.setValue(assets[0]);
					}
				}

				d.callback(assets, function() {
					d.close();
				});
			},
			embed: function(target, asset, profile) {
				var d = ff.dialog.assetSelect;	
			}
		},	
					
		layoutSelect: {
			handle: null,
			target: null,
			callback: null, // function(layoutId, after) - callback is responsible for calling after()
			open: function(options) {				
				var d = ff.dialog.layoutSelect;
				if(d.handle) {
					d.close();
				}
				
				options = options || {};
				
				d.target   = options.target;
				d.callback = options.callback || function(ignore, c) { c(); };
				
				d.handle = window.open("/admin/cms/layouts/select", '_layout_selector', 'width=720,height=400,toolbar=0,resizable=0,scrollbars=1');
				d.handle.focus();
			},
			close: function() {
				var d = ff.dialog.layoutSelect;
				if(d.handle) {
					d.handle.close();
				}
			},
			selected: function(layoutId) {
				var d = ff.dialog.layoutSelect;
				
				if(d.target) {
					$('#' + d.target).val(layoutId);
				}
				
				d.callback(layoutId, function() {
					d.close();
				});
			}
		}
	},
	
	flash: function(type, message, timeout) {
		if (arguments.length < 3) timeout = ff.DEFAULT_FLASH_TIMEOUT;
		var jq = $("<div style='display:none' class='flash " + type + "'>" + message + "</div>").prependTo('#notifications').fadeIn();
		if (timeout) window.setTimeout(function() { jq.fadeOut(function() { $(this).remove(); }) }, timeout);
	},
	
	hideBottomFlash: function() {
		$('#notifications .flash:last').fadeOut(function() {
			$(this).remove();
			setTimeout(ff.hideBottomFlash, ff.DEFAULT_FLASH_TIMEOUT);
		});
	}
};

// 
// var ff = {
// 	
// 	
// 	behaviours: {
// 		bind: function(context) {
// 			// AJAX Links
// 			$('.ajax-post', context).click(function() {
// 				if($(this).hasClass('confirm')) {
// 					if(!confirm("Are you sure?")) {
// 						return false;
// 					}
// 					
// 					$.ajax({
// 						type: 'POST',
// 						dataType: 'script',
// 						url: $(this).attr('href'),
// 						beforeSend: function(xhr) { xhr.setRequestHeader('Accept', 'text/javascript'); }
// 					});
// 					
// 				}
// 				return false;
// 			});
//
// 			// Tiny MCE inputs
// 			$('.tiny-mce-input', context).each(function() {
// 				var options = eval("(" + $('.options', this).remove().html() + ")");
// 				var t = $('textarea', this)[0];
// 				new tinymce.Editor(t.id = (t.id || t.name || (t.id = DOM.uniqueId())), options).render();
// 			 	ff.editors.register(t.id, 'tiny_mce');
// 			});
// 		},
// 	},
// 		
// 	help: {
// 		
// 		toggle: function() {
// 			if ($(document.body).hasClass('help')) {
// 				ff.help.hide();
// 			} else {
// 				ff.help.show();
// 			}
// 		},
// 		
// 		show: function() {
// 			$('#sidebar-wrapper').hide();
// 			$(document.body).addClass('help');
// 			$('#help-actuator-caption').html('hide help');
// 			$('#help-wrapper').show();
// 		},
// 		
// 		hide: function() {
// 			$('#help-wrapper').hide();
// 			$(document.body).removeClass('help');
// 			$('#help-actuator-caption').html('show help');
// 			$('#sidebar-wrapper').show();
// 		}
// 		
// 	},
// 	
// 	editors: {
// 	  
// 	  instances: {},
// 	  
// 	  register: function(instanceId, type) {
// 	    ff.editors.instances[instanceId] = new ff.editors[type](instanceId);
// 	  },
// 	  
// 	  get: function(instanceId) {
// 	    return ff.editors.instances[instanceId];
// 	  },
// 	
// 		removeAll: function() {
// 			for(e in ff.editors.instances) {
// 				if(ff.editors.instances[e].remove) {
// 					ff.editors.instances[e].remove();
// 				}
// 			}
// 			ff.editors.instances = {};
// 		},
// 	  
// 	  textarea: function(id) {
// 	    this.id = id;
//       this.instance = function() { return $('#' + this.id); };
// 	    this.setContent = function(html) { this.instance().val(html); };
// 	    this.getContent = function(xhtml) { return this.instance().val(); };
// 	    this.insertContentAtCursor = function(html) { this.instance().val(this.getContent() + html); };
// 	  },
// 	  
// 	  ephox_editlive: function(id) {
// 	    this.id = id;
// 	    this.setContent = function(html) {};
// 	    this.getContent = function(xhtml) {};
// 	    this.insertContentAtCursor = function(html) {};
// 	  },
// 	  
// 	  tiny_mce: function(id) {
// 	    this.id = id;
// 	    this.instance = function() { return tinyMCE.get(this.id); }
// 	    this.setContent = function(html) { this.instance().setContent(html); };
// 	    this.getContent = function(xhtml) { return this.instance().getContent(); };
// 	    this.insertContentAtCursor = function(html) { this.instance().execCommand('mceInsertContent', false, html); }; 
// 	  	this.remove = function() { tinyMCE.execCommand('mceRemoveControl', false, this.id); };
// 		},
// 	  
// 	  fck: function(id) {
// 	    this.id = id;
// 	    this.instance = function() { return FCKeditorAPI.GetInstance(this.id); };
// 	    this.setContent = function(html) { this.instance().SetHTML(html); };
// 	    this.getContent = function(xhtml) { return xhtml ? this.instance().GetXHTML() : this.instance().GetHTML(); };
// 	    this.insertContentAtCursor = function(html) { this.instance().InsertHtml(html); };
// 	  } 
// 	}
// };
// 
ff.AssetStub = function(options) {
	$.extend(this, {name: '', icon_url: null, content_class: null}, options);
};

ff.AssetStub.prototype = {
	
	getId: function() { return this.id; },
	getTitle: function() { return this.name; },
	getFileName: function() { return this.filename },
	getAlt: function() { return this.alt; },
	getDescription: function() { return this.description; },
	hasIcon: function() { return typeof(this.icon_url) == 'string'; },
	getIconUrl: function() { return this.icon_url; },
	isWebImage: function() { return this.content_class == 'web-safe-image'; },
	getImageProfile: function() { return this.image_profile; },
	getContentClass: function() { return this.content_class; },
	
	getPreviewUrl: function(imageProfile) {
		if (this.isWebImage()) {
			return "/assets/" + this.id + "/" + (imageProfile || "system-small-thumbnail");
		} else if (this.hasIcon()) {
			return this.getIconUrl();
		} else {
			return "...";
		}
	},
	
	getUrl: function(imageProfile) {
		var r = "/assets/" + this.id;
		if(this.isWebImage()) {
			var i = imageProfile || this.getImageProfile() || 'original';
			if(i) r += "/" + i;
		}
		return r;
	}

};

ff.UrlStub = function(options) {
	$.extend(this, {title: '', url: null, node_id: null}, options);
};

ff.UrlStub.prototype = {
	getTitle: function() { return this.title; },
	getUrl: function() { return this.url; },
	getNodeId: function() { return this.node_id; }
};

var TextileHelper = function(ele, editorSelector) {
	this.ele = ele;
	this.editorSelector = editorSelector;

	var self = this;

	$('.paste', this.ele).click(function() {
		this.focus();
		this.select();
	});
	
	$('.select-url', this.ele).click(function() {	
		ff.dialog.content.open(null, {
			callback: function(url, after) {
				var url = new ff.UrlStub(url);
				
				var url_val = null;
				
				if(url.getNodeId()) {
					url_val = "node://"+url.getNodeId();
				} else {
					url_val = url.getUrl();
				}
				
				$('.build-type-' + self.getBuildType() + ' input[name=url]', self.ele).val(url_val).keyup();
				$('.build-type-' + self.getBuildType() + ' input[name=label]', self.ele).val(url.getTitle()).keyup();
				after();
			}
		});
		return false;
	});
	
	$('.select-image', this.ele).click(function() {
		ff.dialog.assetSelect.open({
			mode: 'embed',
			content_class: 'web-safe-image',
			callback: function(asset, after) {
				var asset = new ff.AssetStub(asset[0]);
				$('.build-type-image input[name=image_asset_url]', self.ele).val(asset.getUrl()).keyup();
				$('.build-type-image input[name=alt]', self.ele).val(asset.getAlt()).keyup();
				after();
			}
		});
		return false;
	});
	
	$('.select-asset', this.ele).click(function() {
		ff.dialog.assetSelect.open({
			mode: 'select',
			callback: function(asset, after) {
				var asset = new ff.AssetStub(asset[0]);
				$('.build-type-asset input[name=asset_url]', self.ele).val(asset.getUrl()).keyup();
				$('.build-type-asset input[name=label]', self.ele).val(asset.getTitle()).keyup();
				self.setAsset(asset);
				after();
			}
		});
		return false;
	});
		
	$("select[name=build_type]", this.ele).change(function() {
    $('.build-type').hide();
    $('.build-type-' + $(this).val()).show();
  	self.refresh();
	}).change();
	
	$('.build-type input', this.ele).keyup(function() {
		self.refresh();
	}).blur(function() {
		self.refresh();
	});
	
	$('.build-type select', this.ele).change(function() { self.refresh(); });
	
  $('.paste-at-cursor', this.ele).click(function() {
		self.pasteAtCursor();
		return false;
	});
};

TextileHelper.prototype = {
	getEditor: function() {
		var e = $(this.editorSelector);
		return e[0].textileEditor;
	},
	
	getBuildTypeContainerName: function() { return ".build-type-" + this.getBuildType(); },
	
	getAlt: function()            { return $(this.getBuildTypeContainerName() + " input[name=alt]").val(); },
	getUrl: function() 						{ return $(this.getBuildTypeContainerName() + " input[name=url]").val(); },
	getLabel: function()			    { return $(this.getBuildTypeContainerName() + " input[name=label]").val(); },
	getImageAssetUrl: function()  { return $(this.getBuildTypeContainerName() + " input[name=image_asset_url]").val(); },
	getClass: function() 				  { return $(this.getBuildTypeContainerName() + " select[name=class]").val(); },
	getAssetUrl: function() 		  { return $(this.getBuildTypeContainerName() + " input[name=asset_url]").val(); },
	getSnippet: function()        { return $(this.getBuildTypeContainerName() + " select[name=snippet]").val(); },
	getEmail: function() 					{ return $(this.getBuildTypeContainerName() + " input[name=email]").val(); },
	
	getAsset: function() { return this.asset; },
	
	setAlt: function(val)             { $(this.getBuildTypeContainerName() + " input[name=alt]").val(val); },
	setUrl: function(val) 						{ $(this.getBuildTypeContainerName() + " input[name=url]").val(val); },
	setLabel: function(val)			      { $(this.getBuildTypeContainerName() + " input[name=label]").val(val); },
	setImageAssetUrl: function(val)   { $(this.getBuildTypeContainerName() + " input[name=image_asset_url]").val(val); },
	setClass: function(val) 				  { $(this.getBuildTypeContainerName() + " input[name=class]").val(val); },
	setAssetUrl: function(val)  		  { $(this.getBuildTypeContainerName() + " input[name=asset_url]").val(val); },

	setAsset: function(val) { this.asset = val; this.refresh(); },

	pasteAtCursor: function() {
		var e = this.getEditor();
		if((this.getBuildType() == 'link') && (this.getLabel().length == 0) && (e.getSelectedText().length > 0)) {
			if(confirm("Use selected text as caption?")) {
				this.setLabel(e.getSelectedText());
			}
			e.insertAtCursor(this.build());
		} else {
			e.insertAtCursor(this.build());
		}
	},
	getBuildType: function() {
		return $("select[name=build_type]", this.ele).val();
	},
	build: function() {
		switch(this.getBuildType()) {
			case 'link':
				return this.buildLink();
				break;
			case 'email':
				return this.buildEmail();
				break;
			case 'image':
				return this.buildImage();
				break;
			case 'asset':
				return this.buildAsset();
				break;
			case 'snippet':
				return this.buildSnippet();
				break;
		}
	},
	buildLink: function() {
		var url   = this.getUrl();
		var label = this.getLabel();
		
		var r = "";
		
		if(url.length > 0) {
			r += '"' + (label.length > 0 ? label : url) + '"' + ':' + url;
		}
			
		return r;
	},
	buildEmail: function() {
		var email = this.getEmail();
		var label = this.getLabel();
		
		var r = "";
		if(email.length > 0) {
			r += '"' + (label.length > 0 ? label : email) + '"' + ':' + "mailto:" + email;
		}
		
		return r;
	},
	buildImage: function() {
		var alt 			= this.getAlt();
		var asset_url = this.getImageAssetUrl();
		var url 		  = this.getUrl();
		var klass 		= this.getClass();
		
		var r = "";
		if(asset_url.length > 0) {
			r += '!';
			if(klass.length > 0) {
				r += '(' + klass + ')';
			}
			r += asset_url;
			if(alt.length > 0) {
				r += '(' + alt + ')'; 
			}
			r += '!';

			if(url.length > 0) {
				r += ':' + url;
			}
		}
		return r;
	},
	buildAsset: function() {
		var label     = this.getLabel();
		var asset_url = this.getAssetUrl();
		var asset			= this.getAsset();
		
		var r = "";
		if(asset_url.length > 0) {
			r += '"';
			if(asset && asset.getContentClass()) {
				r += "(content-class-" + asset.getContentClass() + ")";
			}
			r += (label.length > 0 ? label : asset_url) + '"';
			r += ':';
			r += asset_url;
		}
		return r;
	},
	buildSnippet: function() {
		var snippet = this.getSnippet();
		return "snippet://" + snippet;
	},
	refresh: function() {
		$('.paste', this.ele).val(this.build());
	}
};

var TextileEditor = function(ele) {
	this.ele = ele;
	this.t = $('textarea', this.ele);
	var self = this;
	$('.toolbar li.edit a', ele).click(function() {
		self.fireToolbarAction('edit');
		return false;
	});
	$('.toolbar li.preview a', ele).click(function() {
		self.fireToolbarAction('preview');
		return false;
	});
	
	var d = this.getDocument();
	d.open();
	d.write("<html><head><link rel='stylesheet' type='text/css' href='/stylesheets/content.css' /></head><body></body></html>");
	d.close();
	
	$('.find-replace input[name=replace]', ele).click(function() {
	    self.fireFindReplace(
	        $('.find-replace input[name=find_text]').val(),
	        $('.find-replace input[name=replace_text]').val(),
	        $('.find-replace input[name=use_regex]:checked').length > 0
	    );
	});
};

TextileEditor.prototype = {
	destroy: function() {
	},
	getDocument: function() {
		var i = $('iframe.preview', this.ele)[0];
		
		var d = null;
		if(i.contentDocument) {
			d = i.contentDocument;
		} else if (i.contentWindow) {
			d = i.contentWindow.document;
		} else {
			d = i.document;
		}
		
		return d;
	},
	insertAtCursor: function(content) {
		this.showEditor();
		var t= this.t[0];
		
		// IE support
		if(document.selection) {			
			t.focus();			
			var sel = document.selection.createRange();
			sel.text = content;
		} else {
			this.t.insertText(content);	
		}
	},
	getContent: function() {
		return this.t.val();
	},
	setContent: function(content) {
	    this.t.val(content);
	},
	getSelectedText: function() {
	    return this.t.getSelectedText();
	},
	setPreviewContent: function(html) {
		// $('iframe.preview', this.ele)[0].contentDocument.getElementsByTagName('body')[0].innerHTML = html;
		var d = this.getDocument();
		d.getElementsByTagName('body')[0].innerHTML = html;
	},
	showEditor: function() {
		$('iframe.preview', this.ele).hide();
		this.t.show();
		this.selectTab('edit');
	},
	showPreview: function() {
		var me = this;
		this.refreshPreview(function() {
			me.t.hide();
			$('iframe.preview', me.ele).show();
			me.selectTab('preview');
		});
	},
	fireFindReplace: function(find, replace, regExp) {
	    var flags;
	    if (regExp) {
	        if (!find.match(/^\/(.+?)\/([ig]*)$/)) {
	            tde('Illegal regular expression');
	            return;
	        }
	        find = RegExp.$1, flags = RegExp.$2;
	    } else {
	        find = find.replace(/([\\\^\.\+\?\*\(\)\[\]\$])/g, '\\$1');
	    }
	    
	    find = new RegExp(find, flags || 'ig');
	    
	    var subject = this.t.hasSelectedText() ? this.getSelectedText() : this.getContent();
	    var lines = subject.split("\n"), out = [];
	    
	    for (var i = 0; i < lines.length; i++) {
	        out.push(lines[i].replace(find, replace));
	    }
	    
	    if (this.t.hasSelectedText()) {
	        this.insertAtCursor(out.join("\n"));
	    } else {
	        this.setContent(out.join("\n"));
	    }
	},
	fireToolbarAction: function(action) {
		switch(action) {
			case 'edit':
				this.showEditor();
				break;
			case 'preview':
				this.showPreview();
				break;
		}
	},
	selectTab: function(action) {
		$('.toolbar li', this.ele).removeClass('active');
		$('.toolbar li.' + action, this.ele).addClass('active');
	},
	refreshPreview: function(after) {
		var me = this;
		$.post('/admin/core/textile/_preview', {content: this.getContent()},
			function(data, textStatus) {
				me.setPreviewContent(data);
				after();
			},
			'html'
		);
	}
};

var ListSection = function(ele, options) {
	options = options || {};
	
	this.ele = ele;
	
	var self = this;
	
	$('tr.template .raw', this.ele).removeClass('raw');
	
	if(options.addButton) {
		$(options.addButton).click(function() {
			self.addRow('default');
			return false;
		});
	}
	
	if(this.getNumberOfItems() > 0) {
		this._index = this.getNumberOfItems();
	} else {
		this.showEmptyRow();
	}
	
	this.bindRowBehaviours($('tbody tr.list-section-row', this.ele));
};

ListSection.prototype = {
	_index: 0,
	getNextIndex: function() {
		return this._index++;
	},
	hideEmptyRow: function() {
		$('tr.empty-row', this.ele).hide();
	},
	showEmptyRow: function() {
		$('tr.empty-row', this.ele).css('display','table-row');
	},
	addRow: function(template) {
		var tbody = $('tbody', this.ele);
		var tpl   = this.getTemplate(template);
		var idx   = this.getNextIndex();
		var self  = this;
		
		tpl = tpl.substitute({index: idx});
		
		var newRow = $("<tr class='list-section-row list-section-row-" + idx + "'></tr>");
		newRow.html(tpl);
		
		tbody.appendWithBehaviours(newRow);
		
		this.bindRowBehaviours(newRow);
		this.hideEmptyRow();
	},
	getTemplate: function(tpl) {
		return $('tbody tr.template-' + tpl).html();
	},
	getTemplateCount: function() {
		return $('tbody tr.template').size();
	},
	removeRow: function(idx) {
		$(".list-section-row-" + idx, this.ele).remove();
		if(this.getNumberOfItems() == 0) {
			this.showEmptyRow();
		}
	},
	getNumberOfItems: function() {
		return $('tbody tr.list-section-row', this.ele).size();
	},
	bindRowBehaviours: function(context) {
		var self = this;
		$('a.list-section-delete', context).click(function() {
			var idx = $(this).parents('tr.list-section-row:first').attr('class').match(/list-section-row-([0-9+])/)[1];
			self.removeRow(idx);
			return false;
		});
	}
};

$.fn.listSection = function(options) {
	this.each(function() {
		this.listSection = new ListSection(this, options);
	});
	return this;
};