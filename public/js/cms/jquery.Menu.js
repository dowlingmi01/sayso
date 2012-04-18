
$SQ(document).ready(function () {
timer=0;

	$SQ('.cmsMenu > li > a').mouseover(function (event) {
		if (this == event.target) {
			$SQ(this).parent().toggleClass('clicked').children('ul').fadeToggle();
			$SQ(this).siblings().children().removeClass('clicked').find('ul').fadeOut();
			$SQ(this).parent().siblings().removeClass('clicked').find('ul').fadeOut();
		}
	}).addClass('a-top');
	$SQ('.cmsMenu ul > li > a').mouseover(function (event) {
		if (this == event.target) {
			$SQ(this).parent().toggleClass('clicked').children('ul').fadeToggle();
			$SQ(this).siblings().children().removeClass('clicked').find('ul').fadeOut();
			$SQ(this).parent().siblings().removeClass('clicked').find('ul').fadeOut();
		}
	}).addClass('a-sub');
	$SQ('.cmsMenu li:not(:has(ul)) a').mouseout(function (event) {
		if (this == event.target) {
			$SQ(this).parent().removeClass('clicked');
			$SQ(this).siblings().children().removeClass('clicked').find('ul').fadeOut();
			$SQ(this).parent().siblings().removeClass('clicked').find('ul').fadeOut();
		}
	}).removeClass();

	$SQ(".cmsMenu").mouseover(function() {
		clearTimeout(timer);
		});
	$SQ(".cmsMenu").mouseleave(function() {
			timer = window.setTimeout(function(){
				$SQ('.cmsMenu li > a').siblings().children().removeClass('clicked').find('ul').fadeOut();
				$SQ('.cmsMenu li > a').parent().siblings().removeClass('clicked').find('ul').fadeOut();
			}, 1000);
		}); 
		
	/* Highlight links in our menu which haven't been created yet */	
	$SQ('a[href="#url"]').addClass('nolink');

});
