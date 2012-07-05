// remap jQuery to $
(function($){})(window.jQuery);


/* trigger when page is ready */
$(document).ready(function (){

	var sectionNav = $('#section-nav');
	var slideshows = $('.slideshow');
	
	var termsCheckbox = $('#agreeterms');
	
	termsCheckbox.on('click',function(){
		var btnSubmit = $('#btn-submit');
		if (this.checked == true){			
			btnSubmit.removeAttr('disabled');
		}else{
			btnSubmit.attr('disabled',true);
		}
	});
	
	
	slideshows.each(function(){
		var mySlideshow = $(this);
		var sectionID = mySlideshow.parents('.section').attr('id');
		
		var myPrev = $('#'+sectionID+' .slide-nav .prev');
		var myNext = $('#'+sectionID+' .slide-nav .next');
		
		$('.slides',mySlideshow).cycle({
			timeout: 0,
			fx:	'fade',
			speed: 500,
			prev:	myPrev,
			next: myNext
		});
		
	});

	
	$('ul li a',sectionNav).click(function (e) {
	  e.preventDefault();
	  $(this).tab('show');
	  
	  var myClass = $(this).attr('data-class');	  
	  sectionNav.attr('class','');
	  sectionNav.attr('class',myClass);
	  
	  //$('#'+myClass+' #slideshow #slides').cycle();
	  
	  
	});
	
	$('a[data-toggle="tab"]').on('shown', function (e) {
	  e.target // activated tab
	  e.relatedTarget // previous tab	  
	})
		
}); // end document.ready


/* optional triggers

$(window).load(function() {
	
});

$(window).resize(function() {
	
});

*/