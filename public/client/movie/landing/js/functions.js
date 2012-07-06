// remap jQuery to $
(function($){})(window.jQuery);


/* trigger when page is ready */
$(document).ready(function (){
	var sectionNav = $('#section-nav');
	var slideshows = $('.slideshow');
	
	var termsCheckbox = $('#agreeterms');
	
	var btnSubmit = $('#btn-submit');
	
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
	  validateInstall();
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
	
	function validateInstall() {
		var msg = false;
		if( window.$SaySoExtension )
			msg = 'You already have Say.So installed';
		if( !saysoConf.bn.isSupported ) {
			if( bn.isMobile ) {
				msg = 'Sorry! Movie Say.So isn\'t yet available for mobile browsers. Join us via your computer when you can!';
			} else {
				msg = 'Sorry, your web browser doesn\'t support the cool features of Movie Say.So. For an optimal experience, use the latest versions of Chrome (www.google.com/chrome), Firefox (www.getfirefox.com) or Safari (www.apple.com/safari). And we support Internet Explorer 8 and above.';
			}
		}
		if( msg ) {
			$('#create-password').hide();
			$('#no-install p').html(msg);
			$('#no-install').show();
		}
	}
	var emailEl = $('#input-email'), passwordEl = $('#input-password'), confirmationEl = $('#input-confirmation');
	
	termsCheckbox.on('click', activateGrabIt);
	emailEl.on('keyup change', activateGrabIt);
	passwordEl.on('keyup change', activateGrabIt);
	confirmationEl.on('keyup change', activateGrabIt);
	
	function activateGrabIt() {
		if( !validateFields() )
			btnSubmit.removeAttr('disabled');
		else
			btnSubmit.attr('disabled',true);
	}
	
	function validateFields() {
		var emailadd = emailEl.val();
		if( emailadd.length < 1 )
			return "Woops - Please enter your email address";

		var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
		if (!emailPattern.test(emailadd))
			return "Woops - Please enter a valid email address";

		var pwd1 = passwordEl.val();
		if( pwd1.length < 1 )
			return "Woops - Please enter your password";

		var pwd2 = confirmationEl.val();
		if( pwd1 != pwd2 )
			return "Woops - Your passwords do not match.<br>Please reenter your password";
		if( pwd1.length < 6 || pwd1.length > 12 )
			return "Woops - Your password needs to have between 6 and 12 characters.<br>Please reenter your password";
		
		if( !termsCheckbox.is(':checked') )
			return "Woops - Please accept the terms and conditions"
				
		return false;
	}
	
	btnSubmit.on('click', onGrabIt);
	
	function onGrabIt(e) {
		e.preventDefault();
		var errMsg;
		if(!(errMsg = validateFields())) {
			var ajaxData = {
					client_name : 'movie',
					install_origination : 'p-1',
					user_agent_supported : true,
					install_url : document.location.href,
					location_token : saysoConf.locationCookie,
					referrer : document.referrer,
					user_email: emailEl.val(),
					user_password: passwordEl.val(),
					renderer: 'jsonp'
				};
			$.ajax( {
				url: '//' + saysoConf.baseDomain + '/starbar/install/user-password',
				dataType : 'jsonp',
				data : ajaxData,
				success: onPasswordResponse
			} )
		} else
			showWarning(errMsg);
	}
	
	function onPasswordResponse( response ) {
		if (response.status && response.status == "error") {
			showWarning(response.data.message);
		} else {
			if( !document.location.href.match('sayso-installing') )
				document.location.hash = 'sayso-installing';
			var downloadLocation = '//' + saysoConf.baseDomain + '/starbar/install/extension?install_token=' + response.data.install_token;
			$('#create-password').hide();
			$('#password-created').show();
			$('#download-retry').attr('href', downloadLocation);
			location.href = downloadLocation;
		}
	}
	
}); // end document.ready


/* optional triggers

$(window).load(function() {
	
});

$(window).resize(function() {
	
});

*/