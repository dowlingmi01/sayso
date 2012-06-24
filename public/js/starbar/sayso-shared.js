$SQ(function(){

	(function($$SQ){
		$$SQ.fn.extend({

			percentWidth: function() {
				return Math.round(this.width() * 100 / this.parent().width());
			},

			annihilate: function() {
				this.attr('id', 'sb_oldElement_'+$SQ.randomString(10));
				this.removeClass();
				this.detach();
				this.empty();
			},

			// @todo temporary workaround for http://bugs.jquery.com/ticket/10460
			// remove this function and replace calls to .cleanHtml() with calls to .html() when jquery is fixed!
			cleanHtml: function() {
				return this.html().replace('<a xmlns="http://www.w3.org/1999/xhtml">', '').replace('</a>', '');
			},

			biggestHeight: function() {
				return Math.max(this.outerHeight(), this.outerHeight(true));
			}
		});
	})($SQ);

	$SQ.newWin = false;

	$SQ.openWindow = function (url, name, parameters) {
		var newWin = $SQ.newWin;

		if (newWin && !newWin.closed) {
			newWin.location.href = url;
		} else {
			newWin = window.open(url, 'newWin', parameters);
		}

		if (newWin && window.focus) newWin.focus();

		$SQ.newWin = newWin;
		return false;
	}
});
