/*!
 * jQuery Cycle Lite Plugin
 * http://malsup.com/jquery/cycle/lite/
 * Copyright (c) 2008-2011 M. Alsup
 * Version: 1.3.1 (07-OCT-2011)
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 * Requires: jQuery v1.3.2 or later
 */
;(function($$SQ) {

var ver = 'Lite-1.3';

$$SQ.fn.cycle = function(options) {
	return this.each(function() {
		options = options || {};
		
		if (this.cycleTimeout) clearTimeout(this.cycleTimeout);
		this.cycleTimeout = 0;
		this.cyclePause = 0;
		
		var $$SQcont = $$SQ(this);
		var $$SQslides = options.slideExpr ? $$SQ(options.slideExpr, this) : $$SQcont.children();
		var els = $$SQslides.get();
		if (els.length < 2) {
			window.console && console.log('terminating; too few slides: ' + els.length);
			return; // don't bother
		}

		// support metadata plugin (v1.0 and v2.0)
		var opts = $$SQ.extend({}, $$SQ.fn.cycle.defaults, options || {}, $$SQ.metadata ? $$SQcont.metadata() : $$SQ.meta ? $$SQcont.data() : {});
		var meta = $$SQ.isFunction($$SQcont.data) ? $$SQcont.data(opts.metaAttr) : null;
		if (meta)
			opts = $$SQ.extend(opts, meta);
			
		opts.before = opts.before ? [opts.before] : [];
		opts.after = opts.after ? [opts.after] : [];
		opts.after.unshift(function(){ opts.busy=0; });
			
		// allow shorthand overrides of width, height and timeout
		var cls = this.className;
		opts.width = parseInt((cls.match(/w:(\d+)/)||[])[1]) || opts.width;
		opts.height = parseInt((cls.match(/h:(\d+)/)||[])[1]) || opts.height;
		opts.timeout = parseInt((cls.match(/t:(\d+)/)||[])[1]) || opts.timeout;

		if ($$SQcont.css('position') == 'static') 
			$$SQcont.css('position', 'relative');
		if (opts.width) 
			$$SQcont.width(opts.width);
		if (opts.height && opts.height != 'auto') 
			$$SQcont.height(opts.height);

		var first = 0;
		$$SQslides.css({position: 'absolute', top:0, left:0}).each(function(i) { 
			$$SQ(this).css('z-index', els.length-i) 
		});
		
		$$SQ(els[first]).css('opacity',1).show(); // opacity bit needed to handle reinit case
		if ($$SQ.browser.msie) els[first].style.removeAttribute('filter');

		if (opts.fit && opts.width) 
			$$SQslides.width(opts.width);
		if (opts.fit && opts.height && opts.height != 'auto') 
			$$SQslides.height(opts.height);
		if (opts.pause) 
			$$SQcont.hover(function(){this.cyclePause=1;}, function(){this.cyclePause=0;});

		var txFn = $$SQ.fn.cycle.transitions[opts.fx];
		txFn && txFn($$SQcont, $$SQslides, opts);
		
		$$SQslides.each(function() {
			var $$SQel = $$SQ(this);
			this.cycleH = (opts.fit && opts.height) ? opts.height : $$SQel.height();
			this.cycleW = (opts.fit && opts.width) ? opts.width : $$SQel.width();
		});

		if (opts.cssFirst)
			$$SQ($$SQslides[first]).css(opts.cssFirst);

		if (opts.timeout) {
			// ensure that timeout and speed settings are sane
			if (opts.speed.constructor == String)
				opts.speed = {slow: 600, fast: 200}[opts.speed] || 400;
			if (!opts.sync)
				opts.speed = opts.speed / 2;
			while((opts.timeout - opts.speed) < 250)
				opts.timeout += opts.speed;
		}
		opts.speedIn = opts.speed;
		opts.speedOut = opts.speed;

 		opts.slideCount = els.length;
		opts.currSlide = first;
		opts.nextSlide = 1;

		// fire artificial events
		var e0 = $$SQslides[first];
		if (opts.before.length)
			opts.before[0].apply(e0, [e0, e0, opts, true]);
		if (opts.after.length > 1)
			opts.after[1].apply(e0, [e0, e0, opts, true]);
		
		if (opts.click && !opts.next)
			opts.next = opts.click;
		if (opts.next)
			$$SQ(opts.next).bind('click', function(){return advance(els,opts,opts.rev?-1:1)});
		if (opts.prev)
			$$SQ(opts.prev).bind('click', function(){return advance(els,opts,opts.rev?1:-1)});

		if (opts.timeout)
			this.cycleTimeout = setTimeout(function() {
				go(els,opts,0,!opts.rev)
			}, opts.timeout + (opts.delay||0));
	});
};

function go(els, opts, manual, fwd) {
	if (opts.busy) return;
	var p = els[0].parentNode, curr = els[opts.currSlide], next = els[opts.nextSlide];
	if (p.cycleTimeout === 0 && !manual) 
		return;

	if (manual || !p.cyclePause) {
		if (opts.before.length)
			$$SQ.each(opts.before, function(i,o) { o.apply(next, [curr, next, opts, fwd]); });
		var after = function() {
			if ($$SQ.browser.msie)
				this.style.removeAttribute('filter');
			$$SQ.each(opts.after, function(i,o) { o.apply(next, [curr, next, opts, fwd]); });
			queueNext();
		};

		if (opts.nextSlide != opts.currSlide) {
			opts.busy = 1;
			$$SQ.fn.cycle.custom(curr, next, opts, after);
		}
		var roll = (opts.nextSlide + 1) == els.length;
		opts.nextSlide = roll ? 0 : opts.nextSlide+1;
		opts.currSlide = roll ? els.length-1 : opts.nextSlide-1;
	}
	
	function queueNext() {
		if (opts.timeout)
			p.cycleTimeout = setTimeout(function() { go(els,opts,0,!opts.rev) }, opts.timeout);
	}
};

// advance slide forward or back
function advance(els, opts, val) {
	var p = els[0].parentNode, timeout = p.cycleTimeout;
	if (timeout) {
		clearTimeout(timeout);
		p.cycleTimeout = 0;
	}
	opts.nextSlide = opts.currSlide + val;
	if (opts.nextSlide < 0) {
		opts.nextSlide = els.length - 1;
	}
	else if (opts.nextSlide >= els.length) {
		opts.nextSlide = 0;
	}
	go(els, opts, 1, val>=0);
	return false;
};

$$SQ.fn.cycle.custom = function(curr, next, opts, cb) {
	var $$SQl = $$SQ(curr), $$SQn = $$SQ(next);
	$$SQn.css(opts.cssBefore);
	var fn = function() {$$SQn.animate(opts.animIn, opts.speedIn, opts.easeIn, cb)};
	$$SQl.animate(opts.animOut, opts.speedOut, opts.easeOut, function() {
		$$SQl.css(opts.cssAfter);
		if (!opts.sync) fn();
	});
	if (opts.sync) fn();
};

$$SQ.fn.cycle.transitions = {
	fade: function($$SQcont, $$SQslides, opts) {
		$$SQslides.not(':eq(0)').hide();
		opts.cssBefore = { opacity: 0, display: 'block' };
		opts.cssAfter  = { display: 'none' };
		opts.animOut = { opacity: 0 };
		opts.animIn = { opacity: 1 };
	},
	fadeout: function($$SQcont, $$SQslides, opts) {
		opts.before.push(function(curr,next,opts,fwd) {
			$$SQ(curr).css('zIndex',opts.slideCount + (fwd === true ? 1 : 0));
			$$SQ(next).css('zIndex',opts.slideCount + (fwd === true ? 0 : 1));
		});
		$$SQslides.not(':eq(0)').hide();
		opts.cssBefore = { opacity: 1, display: 'block', zIndex: 1 };
		opts.cssAfter  = { display: 'none', zIndex: 0 };
		opts.animOut = { opacity: 0 };
	}
};

$$SQ.fn.cycle.ver = function() { return ver; };

// @see: http://malsup.com/jquery/cycle/lite/
$$SQ.fn.cycle.defaults = {
	animIn:		{},
	animOut:	   {},
	fx:		   'fade',
	after:		 null, 
	before:		null, 
	cssBefore:	 {},
	cssAfter:	  {},
	delay:		 0,	
	fit:		   0,	
	height:	   'auto',
	metaAttr:	 'cycle',
	next:		  null, 
	pause:		 0,	
	prev:		  null, 
	speed:		 1000, 
	slideExpr:	 null,
	sync:		  1,	
	timeout:	   4000 
};

})($SQ);
