(function($){
	
	// GLOBAL
	
	/*
	 * create a jQuery instance from a simplified zen-style string.
	 * $.createElement('div#bob.class1.class2[title="Some Title"]');
	 */
	$.createElement = function(selector, text){
		// assume open lt indicates straight html - just wrap that and return it
		if(/^</.test(selector)){
			return $(selector);
		}
		// get the tag name
		var tag = selector.match(/^\w+/)[0];
		// create the DOM element
		var dom = document.createElement(tag);
		// wrap it in a jQuery instance
		var element = $(dom);
		// get the class names and apply them
		var pattern = /\.(-?[\w]+[\w\d-]*)/g;
		var classes = pattern.exec(selector);
		while(classes != null){
		   element.addClass(classes[1]);
		   classes = pattern.exec(selector);
		}
		// get the id and apply it
		pattern = /#((-?\w+[\w\d-]*)+)/g;
		var id = pattern.exec(selector);
		if(id != null){
			element.attr('id', id[1]);
		}
		// get attributes and apply them
		pattern = /\[([^\]]+)/g;
		var attributes = pattern.exec(selector);
		while(attributes != null){
			// get the set in brackets
			var attribute = attributes[1];
			// find the attribute name
			var kp = /^([\w\d-]+)(=|$)/;
			var kr = kp.exec(attribute);
			if(kr != null){
				var key = kr[1];
				var value = '';
				// get a value (if present)
				var vp = /=["']?(.*?)["']?$/;
				var vr = vp.exec(attribute);
				if(vr != null){
					value = vr[1];
				}
				// apply the attribute name:value
				element.attr(key, value);	
			}
			// keep going
			attributes = pattern.exec(selector);
		}
		// get an text content (which can be HTML as well)
		pattern = /\{([^\}]+)/g;
		var content = pattern.exec(selector);
		if(content != null){
			element.html(content[1]);
		}
		// if optional text paramets is passed
		if(typeof text != 'undefined'){
			element.text(text);
		}
		// return
		return element;
	};
	
	// INSTANCE
	
	/*
	 * create and append element based on simple zen-style string
	 */
	$.fn.createElement = function(selector, text){
		var element = $.createElement(selector, text);
		this.append(element);
		return element;
	};
	/*
	 * checks to see if a match to the selector already exists
	 * if true, return match
	 * if false, create and return new element
	 */
	$.fn.findOrCreate = function(selector){
		if(this.find(selector).length == 0){
			return this.createElement(selector);
		}
		return this.find(selector);
	};
	/*
	 * get outerHTML
	 */
	$.fn.outerHTML = function() {
		return $('<div>').append(this.clone()).html();
	};
	/*
	 * sort elements in query set based on comprarison function
	 */
	$.fn.sortElements = function(comparator){
		var array = $.makeArray(this);
		array.sort(comparator);
		for(var i = 0, l = array.length; i < l; i++){
			var ref = array[i];
			ref.parentNode.appendChild(ref);
		};
		return this;
	};
	/*
	 * get tag name (no args) or test tag name against each argument for match (returns boolean)
	 */
	$.fn.tagName = function(){
		var nodeName = this.get(0).nodeName.toLowerCase();
		if(arguments.length == 0){
			return nodeName;
		};
		for(var i = 0, l = arguments.length; i < l; i++){
			if(arguments[i].toLowerCase() == nn) {
				return true;
			};
		};
		return false;
	};
	/*
	 * return content of element set - either value or innerHTML
	 */
	$.fn.content = function(){
		var method = this.tagName('input', 'textarea', 'select') ? 'val' : 'html';
		return this[method].apply(this, arguments);
	};
	/*
	 * does this selector have any matches in the DOM currently?
	 */
	$.fn.exists = function(selector){
		return this.length > 0;
	};
	/*
	 * returns boolean if element is in page
	 */
	$.fn.isInDOM = function(){
		return this.parents(document).length > 0;
	};
	/*
	 * returns boolean whether element is positioned within the visible viewport
	 */
	$.fn.isOnScreen = function(){		
		var win = $(window);		
		var viewport = {
			top : win.scrollTop(),
			left : win.scrollLeft()
		};
		viewport.right = viewport.left + win.width();
		viewport.bottom = viewport.top + win.height();		
		var bounds = this.offset();
		bounds.right = bounds.left + this.outerWidth();
		bounds.bottom = bounds.top + this.outerHeight();		
		return (!(viewport.right < bounds.left || viewport.left > bounds.right || viewport.bottom < bounds.top || viewport.top > bounds.bottom));		
	};
	/*
	 * walk up (parents) or down (children) DOM tree, number of steps passed
	 */
	$.fn.walk = function(index) {
		var p = this, m = 'children', n;
		if (index < 0) {
			index = -index;
			m = 'parent';
		}
		while (index-- > 0) {
			n = p[m]();
			if (n.length == 0) {
				return p;
			}
			p = n;
		}
		return p;
	};
	/*
	 * returns true if element has any ancestor that matches the selector passed
	 */
	$.fn.isIn = function(selector){
		//return this.parents(selector).length > 0;
		return this.parents().is(selector);
	};
	/*
	 * returns true if element matches the selector passed, or has any ancestor that matches
	 */
	$.fn.isOrIsIn = function(selector){
		//return this.is(selector) || this.parents(selector).length > 0;
		return this.parents().andSelf().is(selector);
	};
	/*
	 * makes best effort at returning numeric portion of an element's ID
	 */
	$.fn.parseID = function(){
		var string = this.attr('id');
		if(typeof string == 'undefined'){
			return null;
		};
		var parsed = parseInt(string);
		if(isFinite(parsed)) {
			return Math.abs(parsed);
		};
		string = string.toString();
		string = string.match(/\d+/);
		if(string != null){
			return parseInt(string[0]);
		}
		return null;
	};
	/*
	 * scans calling element for a data object with a set value whose name equals 'key', the crawls up the DOM until finding a match or returns null
	 */
	$.fn.getContainingData = function(key){
		if(typeof this.data(key) != 'undefined'){
			return this.data(key);
		}
		if(this.parent().length > 0){
			return this.parent().getContainingData(key);
		}
		return null;
	};
	/*
	 * hide/show based on boolean parameter
	 */
	$.fn.vis = function(show){
		if(show){
			this.show();
		} else {
			this.hide();
		}
	}
	/*
	 * adds and removes (swaps) classes
	 */
	$.fn.swapClass = function(remove, add){
		this.removeClass(remove).addClass(add);
	};
	/*
	 * toggles class based on boolean param
	 */
	$.fn.setClass = function(name, attach){
		if(attach === false){
			this.removeClass(name);
		} else {
			this.addClass(name);
		}
		return this;
	};
	/*
	 * replace attribute with regexp
	 */
	$.fn.replaceAttr = function(name, pattern, replacement){
		var value = this.attr(name);
		value = value.replace(pattern, replacement);
		this.attr(name, value);
		return this;
	};
	/*
	 * removes attributes from elements with optional white- and black-lists... all attributes if no arguments are passed
	 */
	$.fn.removeAttributes = function(only, except) {
		if (only) {
			only = $.map(only, function(item) {
				return item.toString().toLowerCase();
			});
		};
		if (except) {
			except = $.map(except, function(item) {
				return item.toString().toLowerCase();
			});
			if (only) {
				only = $.grep(only, function(item, index) {
					return $.inArray(item, except) == -1;
				});
			};
		};
		this.each(function() {
			var attributes;
			if(!only){
				attributes = $.map(this.attributes, function(item) {
					return item.name.toString().toLowerCase();
				});
				if (except) {
					attributes = $.grep(attributes, function(item, index) {
						return $.inArray(item, except) == -1;
					});
				};
			} else {
				attributes = only;
			}      
			var handle = $(this);
			$.each(attributes, function(index, item) {
				handle.removeAttr(item);
			});
		});
		return this;
	};
	/*
	 * renders an element unselectable
	 */
	$.fn.unselectable = function(){
		this.css({
			'-moz-user-select' : 'none',
			'-webkit-user-select' : 'none'
		});
		this.attr('unselectable', 'on');
		this.bind('selectstart', function(event){
			event.preventDefault();
			return false;
		});
		return this;
	};
	/*
	 * swaps the element with another, preserving child indices on both
	 */
	$.fn.swap = function(target) {
		target = $(target).get(0);
		var reference = this.get(0);
		var shim = document.createTextNode('');
		reference.parentNode.insertBefore(shim, reference);
		target.parentNode.insertBefore(reference, target);
		shim.parentNode.insertBefore(target, shim);
		shim.parentNode.removeChild(shim);
		return this;
	};
	/*
	 * prevents user input that does not match the passed regular expression
	 */
	$.fn.restrict = function(pattern, allowed){
		// default to allow backspace, delete, tab, escape, enter
		allowed = allowed || [0, 46, 8, 9, 27, 13]; 
		$(this).keypress(function(event){
			if(event.which) {
				var key = event.which;
				if($.inArray(key, allowed) > -1){
				  return true;   
				}
				var character = String.fromCharCode(key);
				if(pattern.test(character)){
					return true;
				}
				event.preventDefault();
				return false;
			};
		});
		return this;
	};
	/*
	 * allows user input of only digits - deprecate in favor of $.fn.restrict() ?
	 */
	$.fn.allowNumericInput = function(){
		this.keydown(function(e){
			// backspace, delete, tab, escape, enter
			var allowed = [46, 8, 9, 27, 13]; 
			var key = e.keyCode;
			if($.inArray(key, allowed) > -1){
				return true;
			}
			// arrows
			if(key >= 35 && key <= 39){
				return true;
			}
			// numpad
			if(key >= 96 && key <= 105){
				return true;
			}
			// numbers, not shifted
			if(key >= 48 && key <= 57){
				return !e.shiftKey;
			}
			e.preventDefault();
			return false;
		});
	};
	/*
	 * emulates the HTML5 placeholder attribute, if required
	 */
	var placeholderIsSupported = ('placeholder' in document.createElement('input'));
	$.fn.emulatePlaceholder = function(){
		if(!placeholderIsSupported){
			this.each(function(index, element){
				var handle = $(element);
				var placeholder = handle.attr('placeholder');           
				if(handle.val() == ''){
					handle.val(placeholder);    
				}
				handle.blur(function(e){
					var handle = $(this);
					if(handle.val() == ''){
						handle.val(placeholder);
					}
				});
				handle.focus(function(e){
					var handle = $(this);
					if(handle.val() == placeholder){
						handle.val('');
					}
				});
			});
		}
	};
	/*
	 * restores original value of element onblur if it has no value, clears value if it's equal to original value (similar to placeholder)
	 */
	$.fn.restorable = function(){
		this.each(function(index, element){
			var handle = $(this);
			handle.blur(function(e){
				var handle = $(this);
				var value = handle.val();
				if(value == ''){
					handle.val(this.defaultValue);
				}
			});
			handle.focus(function(e){
				var handle = $(this);
				var value = handle.val();
				if(value == this.defaultValue){
					handle.val('');
				}
			});
		});
	};
	/*
	 * returns a rectangle derived from element's position and dimensions
	 */
	$.fn.bounds = function(){
		var box = this.offset();
		box.right = box.left + this.outerWidth();
		box.bottom = box.top + this.outerHeight();
		return box;
	};
	/*
	 * returns boolean if element overlaps a point, rectangle or another element
	 */
	$.fn.hitTest = function(top, left, bottom, right){	
		switch(arguments.length){
			// if passing a single argument and it's a jQuery instance, compare overlap
			case 1 :
				if(typeof top == "object" && top instanceof $){
					var compare = top.offset();
					return this.hitTest(compare.top, compare.left, compare.top + top.outerHeight(), compare.left + top.outerWidth());			
				}
				break;
			// if passing 2 arguments, assume it's a Point rather than a Rect
			case 2 :
				bottom = top;
				right = left;
				break;
		};	
		var bounds = this.offset();
		bounds.right = bounds.left + this.outerWidth();
		bounds.bottom = bounds.top + this.outerHeight();             
		return (!(right < bounds.left || left > bounds.right || bottom < bounds.top || top > bounds.bottom));     
	};
	/*
	 * gets or sets the caret (or selection) for a focusable element
	 */
	$.fn.caret = function(start, end){
		var dom = this.get(0);
		// if no aguments, it's a getter
		if(arguments.length == 0){	
			// if standards-compliant browser, should be easy
			if(dom.selectionStart){
				return dom.selectionStart || 0;
			}
			// if IE, need to do some work
			if(dom.createTextRange){
				dom.focus();
				var range = dom.createTextRange();			
				var selection = document.selection.createRange();
				if (!range || !selection) {
					return 0;
				}
				try {
					range.setEndPoint('EndToStart', selection);
					return range.text.length;
				} catch (e) {
					
				}
				try {
					var dup = range.duplicate();
					range.moveToBookmark(selection.getBookmark());
					dup.setEndPoint('EndToStart', range);
					return dup.text.length;
				} catch(e){
					
				}
			}
			// no selection methods available or there was failure, just return 0
			return 0;
		}	
		// if there's an argument, set caret
		if(typeof end == 'undefined'){
			end = start;
		}
		if (dom.setSelectionRange) {
			dom.setSelectionRange(start, end);
		} else if (dom.createTextRange) {
			var range = dom.createTextRange();
			range.collapse(true);
			range.moveEnd('character', end);
			range.moveStart('character', start);
			range.select();
		}	
	};
	
	
	/* third party */
	
	// http://james.padolsey.com/javascript/regex-selector-for-jquery/
	$.expr[':'].regex = function(elem, index, match) {
		var matchParams = match[3].split(','),
			validLabels = /^(data|css):/,
			attr = {
				method: matchParams[0].match(validLabels) ? 
							matchParams[0].split(':')[0] : 'attr',
				property: matchParams.shift().replace(validLabels,'')
			},
			regexFlags = 'ig',
			regex = new RegExp(matchParams.join('').replace(/^\s+|\s+$/g,''), regexFlags);
		return regex.test(jQuery(elem)[attr.method](attr.property));
	};


})(jQuery);

