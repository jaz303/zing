String.prototype.toSlug = function() {
	return this.toLowerCase().replace(/(^[^a-z0-9_-]+|[^a-z0-9_-]+$)/g, '').replace(/[^a-z0-9_-]+/g, '-');
}

jQuery.fn.slugular = function(update, mutator) {
	if (!mutator) mutator = function(val) {
		return val.toSlug();
	}
	var blockSlug = false;
	$(update).change(function() { blockSlug = this.value.length > 0; }).change();
	this.blur(function() {
		if (blockSlug) return;
		$(update).val(mutator(this.value));
	});
}

/**
 * Simple slider for jQuery
 *
 * Features min/max values and callbacks. No support for stepping.
 *
 * TODO: subtract border width when calculating width of scroller
 * TODO: support clicking on the scroller to jump value
 * TODO: support stepping
 * TODO: allow sliding to continue if mouse leaves region while sliding
 */
jQuery.fn.slider = function(options) {
  jQuery(this).each(function() {
    
    var me        = jQuery(this);
    var actuator  = jQuery('.actuator', this);
    var dragging  = false;
    var sWidth    = me.width();
    var aWidth    = actuator.width();
    var cOffset   = 0;
    var lastVal   = null
    var onSlide   = options.onSlide || function() {};
    var onChange  = options.onChange || function() {};
    var min       = options.min || 0;
    var max       = options.max || 100;
    
    var tgtUpdate = $('input[type=hidden]', this);
    if (typeof(options.value) == 'undefined' && tgtUpdate.length > 0) {
      options.value = tgtUpdate.val();
    }
    if (options.update) tgtUpdate.add($(options.update));
    
    var pointToValue = function(left) {
      return Math.floor(min + (left / (sWidth - aWidth) * (max - min)));
    }
    
    var valueToPoint = function(value) {
      return Math.floor((value - min) / (max - min) * (sWidth - aWidth));
    }
    
    var valueChanged = function(callback) {
      var newVal = pointToValue(parseInt(actuator.css('left')));
      if (newVal != lastVal) {
        lastVal = newVal;
        tgtUpdate.val(newVal);
        callback(newVal);
      }
    }
    
    var dragComplete = function() {
      valueChanged(onChange);
      dragging = false;
    }
    
    actuator.mousedown(function(evt) {
      evt.stopPropagation();
      cOffset = evt.pageX - actuator.offset().left;
      dragging = true;
    });

    me.mousemove(function(evt) {
      if (dragging) {
        var offset = me.offset();
        var realLeft = evt.pageX - offset.left - cOffset;
        if (realLeft < 0) realLeft = 0;
        if (realLeft > sWidth - aWidth) realLeft = sWidth - aWidth;
        actuator.css({left: realLeft});
        valueChanged(onSlide);
      }
    });

    me.hover(function() {}, function() { dragComplete(); });
    actuator.mouseup(function() { dragComplete(); });
    
    actuator.css({left: valueToPoint(options.value || Math.floor((min + max) / 2))});
  
  });
};

/**
 * Textarea/input selection querying/manipulation
 */
(function($) {
    
    $.fn.hasSelectedText = function() {
        var sel = this.getSelectedText();
        return typeof sel == 'string' && sel.length > 0;
    };
    
    $.fn.getSelectedText = function() {
        var t = this.filter('textarea,input')[0];
        if (!t) return null;
        if (document.selection) {
            return document.selection.createRange().text;
        } else if (typeof t.selectionStart == 'number') {
            return $(t).val().substring(t.selectionStart, t.selectionEnd);
        } else {
            return '';
        }
    };
    
    $.fn.appendText = function(text) {
        $(this).val($(this).val() + text);
        return this;
    };
    
    $.fn.insertText = function(text) {
        this.filter('textarea,input').each(function() {
            if (document.selection) {
                this.focus();
                document.selection.createRange().text = text;
            } else if (typeof this.selectionStart == 'number') {
                var val = $(this).val();
                $(this).val(val.substring(0, this.selectionStart) + text + val.substring(this.selectionEnd, val.length));
            } else {
                $(this).appendText(text);
            }
        });
        return this;
    };
    
})(jQuery);