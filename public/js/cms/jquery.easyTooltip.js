/*
 * 	Easy Tooltip 1.0 - jQuery plugin
 *	written by Alen Grakalic
 *	http://cssglobe.com/post/4380/easy-tooltip--jquery-plugin
 *
 *	Copyright (c) 2009 Alen Grakalic (http://cssglobe.com)
 *	Dual licensed under the MIT (MIT-LICENSE.txt)
 *	and GPL (GPL-LICENSE.txt) licenses.
 *
 *	Built for jQuery library
 *	http://jquery.com
 *
 */

(function($SQ) {

	$SQ.fn.easyTooltip = function(options){

		// default configuration properties
		var defaults = {
			xOffset: 10,
			yOffset: 25,
			tooltipId: "easyTooltip",
			clickRemove: false,
			content: "",
			useElement: ""
		};

		var options = $SQ.extend(defaults, options);
		var content;

		this.each(function() {
			var title = $SQ(this).attr("title");
			$SQ(this).hover(function(e){
				content = (options.content != "") ? options.content : title;
				content = (options.useElement != "") ? $SQ("#" + options.useElement).html() : content;
				$SQ(this).attr("title","");
				if (content != "" && content != undefined){
					$SQ("body").append("<div id='"+ options.tooltipId +"'>"+ content +"</div>");
					$SQ("#" + options.tooltipId)
						.css("position","absolute")
						.css("top",(e.pageY - options.yOffset) + "px")
						.css("left",(e.pageX + options.xOffset) + "px")
						.css("display","none")
						.fadeIn("fast")
				}
			},
			function(){
				$SQ("#" + options.tooltipId).remove();
				$SQ(this).attr("title",title);
			});
			$SQ(this).mousemove(function(e){
				$SQ("#" + options.tooltipId)
					.css("top",(e.pageY - options.yOffset) + "px")
					.css("left",(e.pageX + options.xOffset) + "px")
			});
			if(options.clickRemove){
				$SQ(this).mousedown(function(e){
					$SQ("#" + options.tooltipId).remove();
					$SQ(this).attr("title",title);
				});
			}
		});

	};

})($SQ);
