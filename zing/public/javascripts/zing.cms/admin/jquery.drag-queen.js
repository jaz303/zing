/**
 * Drag Queen
 * 
 * (c) 2008-2010 Jason Frame [jason@onehackoranother.com]
 *
 * An extensible jQuery tree widget supporting multiple item selection,
 * drag and drop reordering, action replay, ghosting and cookie storage
 * of expanded nodes.
 *
 * @todo action replay
 * @todo cookie storage
 * @todo edge scrolling
 *
 * Asynchronous where it matters; override a couple of methods to drop
 * in lazy AJAX loading/reordering.
 */
(function($, context) {
	
	function modelId($node) {
		return parseInt($node.id.split('-').pop(), 10);
	};
	
	function DragWidget(root, options) {
		
		this.options			= $.extend({}, DragWidget.DEFAULTS, options || {});
		
		this.$root 				= $(root);
		this.root  				= this.$root[0];
		
		this.dragState			= 'off';
		this.locked				= 0;
		
		this.$contentWrapper	= this.$root.find('.tree-widget-content-wrapper');
		this.$content			= this.$root.find('.tree-widget-content');
		
		this.$content.find('> ul > li.tree-widget-node').data('drag-queen-root', true);
		
		this.bind();
		
		var self = this;
		this.$root.mousemove(function(evt) {
			if (self.dragState == 'active') {
				// TODO: scroll if overflow
				self.updateDragElement(evt);
			}
		}).hover(function() {}, function(evt) {
			self.destroyDragElement();
			self.dragState = 'off';
		});
		
	};
	
	DragWidget.nodeForElement = function(ele, ctor) {
		var node, $ele = $(ele).closest('.tree-widget-node');
		if (!(node = $ele.data('drag-queen-node'))) {
			node = new ctor($ele);
			$ele.data('drag-queen-node', node);
		}
		return node;
	};
	
	DragWidget.prototype = {
		lock: function() { this.locked++; },
		unlock: function() { this.locked--; },
		isLocked: function() { return this.locked > 0; },
		
		expandAll: function() { this.$root.find('.actuator.parent:not(.expanded)').click(); },
		contractAll: function() { this.$root.find('.actuator.parent.expanded').click(); },
		
		reparent: function() {
			this.$content.find('.tree-widget-node').each(function() {
				// only modify nodes for which children are loaded, otherwise
				// respect 'parent' state as reported by server.
				var $children = $('> ul', this);
				if ($children.length == 1) {
					var hasChildren = $children.find('li').length > 0,
						childrenVisible = $children.is(':visible');
					$('> .tree-widget-item .actuator', this)[hasChildren ? 'addClass' : 'removeClass']('parent')
															[childrenVisible ? 'addClass' : 'removeClass']('expanded');
				}
			});
		},
		
		bind: function() {
			var self = this;
			
			this.root.onselectstart = function() { return false; };
	        this.root.unselectable = 'on';
	        this.root.style.MozUserSelect = 'none';
			
			this.$root.find('a[rel=expand-all]').click(function() { self.expandAll(); return false; });
			this.$root.find('a[rel=contract-all]').click(function() { self.contractAll(); return false; });
			
			//
			// Highlight drop validity on hover
			
			this.$content.delegate('.tree-widget-item', 'mouseover', function() {
				if (self.dragState == 'active') {
					if (self.nodeFor(this).acceptsDrop(self.getSelectedNodes())) {
						$(this).addClass('target-valid');
					} else {
						$(this).addClass('target-invalid');
					}
				}
			});
			
			this.$content.delegate('.tree-widget-item', 'mouseout', function() {
				$(this).removeClass('target-valid').removeClass('target-invalid');
			});
			
			//
			// Spring-loading
			
			var springLoad = null;
			
			this.$content.delegate('.tree-widget-item .actuator:not(.expanded)', 'mouseover', function() {
				if (self.dragState == 'active') {
					var ele = this;
					springLoad = setTimeout(function() {
						if (springLoad) {
							$(ele).click();
						}
					}, 1000);
				}
			});
			
			this.$content.delegate('.tree-widget-item .actuator:not(.expanded)', 'mouseout', function() {
				springLoad = null;
			});
			
			//
			// Mouse down for drag start + add to selection
			
			this.$content.delegate('.tree-widget-item', 'mousedown', function(evt) {
				if ($(evt.target).is('.actuator')) return;
				if (self.isLocked()) return;
				if (!evt.metaKey) self.clearSelection();
				self.toggleSelected(this);
				self.dragState = 'waiting';
				self.dragStart = [evt.pageX, evt.pageY];
			});
			
			//
			// Drag start
			
			this.$content.delegate('.tree-widget-item', 'mousemove', function(evt) {
				if (self.dragState == 'waiting') {
					var dx = Math.abs(evt.pageX - self.dragStart[0]);
	                var dy = Math.abs(evt.pageY - self.dragStart[1]);
	                if (dx > 2 || dy > 2) {
	                    self.makeSelected(this);
						self.createDragElement(evt);
	                    self.dragState = 'active';
	                }
				}
			});
			
			//
			// Drop
			
			this.$content.delegate('.tree-widget-item', 'mouseup', function(evt) {
				
				var targetNode = self.nodeFor(this),
	                selectedNodes = self.getSelectedNodes();

	            if (self.dragState == 'active') {
	                self.destroyDragElement();
	                self.loadChildrenFor(targetNode, function(targetList) {
	                    if (targetNode.acceptsDrop(selectedNodes)) {
	                        targetNode.dropWillOccur(selectedNodes, function(outcome) {
	                            if (typeof outcome == 'string') {
	                                self.removeSelection();
									targetNode.getRootElement().find('> ul').html(outcome);
	                            } else if (outcome === true) {
	                                $.each(self.getSelection(), function(ele) {
	                                    $(this).closest('li').appendTo(targetList);
	                                });
	                            }
	                            if (outcome !== false) self.toggleVisibility(targetList, true);
	                            self.clearSelection();
								self.reparent();
	                        });
	                    }
	                });
	            }

	            self.dragState = 'off';
				
			});
			
			//
			// Reordering
			
			this.$content.delegate('.tree-widget-reorder-target', 'mouseover', function() {
				if (self.dragState == 'active') {
					if (self.nodeFor(this).acceptsInsertBefore(self.getSelectedNodes())) {
						$(this).addClass('target-valid');
					} else {
						$(this).addClass('target-invalid');
					}
				}
			});
			
			this.$content.delegate('.tree-widget-reorder-target', 'mouseout', function() {
				$(this).removeClass('target-valid').removeClass('target-invalid');
			});
			
			this.$content.delegate('.tree-widget-reorder-target', 'mouseup', function() {
			
				var targetNode = self.nodeFor(this),
					selectedNodes = self.getSelectedNodes();
					
				if (self.dragState == 'active') {
					self.destroyDragElement();
					if (targetNode.acceptsInsertBefore(selectedNodes)) {
						targetNode.insertBeforeWillOccur(selectedNodes, function(outcome) {
							if (typeof outcome == 'string') {
								self.removeSelection();
								targetNode.getParent().getRootElement().find('> ul').html(outcome);
							} else if (outcome === true) {
								$.each(self.getSelection(), function(ele) {
									$(this).closest('li').insertBefore(targetNode.getRootElement());
								});
							}
						});
						self.reparent();
					}
				}
				
				self.dragState = 'off';
				
			});
			
			//
			// Clicking
			
			this.$content.click(function(evt) {
				var $target = $(evt.target),
					$item	= $target.closest('.tree-widget-item');
					
				if ($item.length == 0) {
					return;
				}
				
				var node = DragWidget.nodeForElement(evt.target, self.options.nodeClass);
				
				if ($target.is('.parent.actuator')) {
					self.loadChildrenFor(node, function(childList) {
						self.toggleVisibility(childList);
					});
				} else if (node.onItemClick(evt) === false) {
					// Do nothing for now
				}
			});
		
		},
		
		//
		// Dragging
		
		createDragElement: function(evt) {
			var selection = this.getSelection();
			if (selection.length == 1 && this.options.useGhosting) {
				var $ghost = selection.closest('li').clone();
				$ghost.find('.tree-widget-item').removeClass('selected');
				
				this.$drag = $("<ul class='tree-widget-drag-ghost'></ul>").html($ghost)
																		  .css({width: this.$content.width()});
			} else {
				this.$drag = $("<div class='tree-widget-drag-badge' />").text(this.getSelection().length);
			}
			this.$contentWrapper.append(this.$drag);
			this.updateDragElement(evt);
		},
		
		updateDragElement: function(evt) {
			var offset = this.$contentWrapper.offset();
			
			this.$drag.css({left: '' + (evt.pageX - offset.left + 10) + 'px',
							top:  '' + (evt.pageY - offset.top  + 10) + 'px'});
		},
		
		destroyDragElement: function() {
			this.$drag.remove();
			this.$drag = null;
		},
		
		// ensure a node's children are loaded, firing callback on completion
	    // callback will receive jQuery object wrapping entire child list.
	    loadChildrenFor: function(node, after) {
	        var self = this, $root = node.getRootElement(), $children = $root.find('> ul');
	        if ($children.length == 0) {
	            node.loadChildren(function(html) {
	                $children = $('<ul style="display:none" />').appendTo($root);
					$children.html(html);
	                if (after) after($children);
	            });
	        } else {
	            if (after) after($children);
	        }
	    },

	    toggleVisibility: function(childList, show) {
	        var expander = childList.prev('.tree-widget-item').find('.parent.actuator');
			if (!show && childList.is(':visible')) {
	            childList.hide();
	            expander.removeClass('expanded');
	        } else {
	            childList.show();
	            expander.addClass('expanded');
	        }
	    },

	    //
	    // Nodes

	    nodeFor: function(ele) {
	        return DragWidget.nodeForElement(ele, this.options.nodeClass);
	    },

	    nodesFor: function(nodes) {
			var out = [];
			for (var i = 0; i < nodes.length; i++) {
				out.push(this.nodeFor(nodes[i]));
			}
			return out;
		},

	    //
	    // Selections

	    hasSelection: function() {
	        return this.getSelection().length > 0;
	    },

	    getSelection: function() {
	        return this.$content.find('.tree-widget-item.selected');
	    },

	    clearSelection: function() {
			this.getSelection().removeClass('selected');
	    },

	    makeSelected: function(ele) {
	        $(ele).addClass('selected');
	    },

	    toggleSelected: function(ele) {
	        $(ele).toggleClass('selected');
	    },

	    getSelectedNodes: function() {
	        return this.nodesFor(this.getSelection());
	    },

	    removeSelection: function() {
	        $.each(this.getSelection(), function(ele) {
	            $(this).closest('li').remove();
	        });
	    }
	};
	
	//
	// Node class implements "business logic" for tree behaviour
	// The default behaviour allows any node to be container and does not
	// dynamically load missing child nodes.
	
	DragWidget.Node = function($node) {
		this.$node = $node;
	};
	
	DragWidget.Node.prototype = {
		
		//
	    // Public API - methods you may wish to override
	
		/*
		 * Returns the ID for the this node.
		 * A node's ID is used for storing replay logs for later.
		 * This can be anything comparable with ==
		 * Default implementation is to split <li>'s id on "-" and return the
		 * final array component as an integer.
		 */
		getID: function() {
			if (typeof this.id == 'undefined') {
				this.id = parseInt(('' + (this.$node[0].id || '')).split('-').pop(), 10);
			}
			return this.id;
		},

		/*
		 * Returns true if all nodes passed in can be dropped on this node.
		 * Default implementation checks that the target node is a container and that
		 * the operation would not result in cycles.
		 */
	    acceptsDrop: function(nodes) {
	        if (!this.isContainer()) return false;
	        return this.ensureNoNodesOnAncestorChain(nodes);
	    },

	    /**
	     * Called when items have been dropped onto a node, after an acceptsDrop()
	     * test has been passed. A callback function is supplied in order to commit
	     * the drop; this enables asynchronous (e.g. AJAX) operation.
	     *
	     * after() takes a single parameter. Pass string to set the HTML for all
	     * child elements of the target node. Pass true if to indicate that the
	     * operation succeeded and that the widget should append the moved nodes to
	     * the new target itself. Pass false if the operation failed.
	     *
	     * If you're going to do anything that takes a significant amount of time
	     * it's probably best to lock() the widget - remember to unlock() after...
	     *
	     * !!! It is not permissible to modify the selection in this function !!!
	     *
	     * @param droppedNodes array of dropped nodes
	     * @param after call this function to commit/cancel the drop operation
	     */
	    dropWillOccur: function(droppedNodes, after) {
	        after(true);
	    },

		/**
		 * Returns true if all nodes in array can be insertd before this node.
		 * Default implementation checks that the operation would not result in cycles.
		 */
	    acceptsInsertBefore: function(nodes) {
	        return this.ensureNoNodesOnAncestorChain(nodes);
	    },
		
	    insertBeforeWillOccur: function(insertedNodes, after) {
	        after(true);
	    },

	    // Implement custom logic for loading child nodes here.
	    // after - call this function once you've loaded the children, passing their
	    //         representative HTML. this should be a series of <li>...</li> tags
	    //         with *no* surrounding <ul>...</ul> tags, or an empty string if no
	    //         children exist. you don't need to set up event handlers etc here,
	    //         this will be handled later.
	    loadChildren: function(after) {
	        after('');
	    },
	
		// Implement custom click handlers for this node's item here.
		// Check evt.target to find out what was actually clicked on.
		// You *must* explicitly return false if nothing was handled.
		onItemClick: function(evt) {
			return false;
		},

	    //
	    // Public API - methods you probably want to leave alone

	    // Returns the root <li/> wrapping the node element
	    getRootElement: function() {
	        return this.$node;
	    },

	    getParent: function() {
	        return this.isRoot()
					? null
					: DragWidget.nodeForElement(this.$node[0].parentNode, this.constructor);
	    },

	    //
	    // The following methods do not form part of the public API but are relied upon by
	    // the default Node implementation.

	    isRoot: function() {
	        return !! this.$node.data('drag-queen-root');
	    },

	    isContainer: function() {
	        return true;
	    },

	    equals: function(that) {
	        return this.$node[0] == that.$node[0];
	    },

	    ensureNoNodesOnAncestorChain: function(nodes) {
	        for (var i = 0; i < nodes.length; i++) {
	            var tmp = this;
	            while (tmp) {
	                if (tmp.equals(nodes[i])) return false;
	                tmp = tmp.getParent();
	            }
	        }
	        return true;
	    }
		
	};
	
	DragWidget.DEFAULTS = {
		nodeClass: 			DragWidget.Node,		// Constructor function used to create nodes
		allowMulipleDrag: 	true,					// Can multiple items be dragged?
		useGhosting: 		true					// Use a "ghost" copy of nodes when dragging a single node.
													// (dragging multiple nodes will always use a badge)
	};
	
	context.DragWidget = DragWidget;
	
})(jQuery, this);
