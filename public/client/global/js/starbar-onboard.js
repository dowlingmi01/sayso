
(function () {
	var sayso = window.sayso;

	var elemSaysoContainer = $SQ('#sayso-container');
	var elemPage = $SQ('#sayso-onboard');
	var elemOverlay = $SQ('#sayso-onboard #sso_wrapper');
	var elemClose = $SQ('#sayso-onboard #sso_wrapper #sso_close');

	elemPage.height($SQ(window).height());
	elemPage.width($SQ(window).width());

	$SQ(window).resize(function() {
		elemOverlay.css('left','50%');
		elemOverlay.css('margin-left','-300px');
	});

	// close button for overlay
	elemClose.live('click',function(){
		elemSaysoContainer.hide();
	});

	elemOverlay.css('display','block');

	// Agree to terms

	$SQ('#sso_wrapper input[type=radio]').bind('change', function () {
		if ($SQ(this).is(':checked')) {
			agreedToTerms();
		}
	});

	function agreedToTerms () {
		$SQ('span.sso_textError').fadeOut('slow');
		$SQ('#sayso-get-app').removeClass('sso_theme_button_disabled');
	}

	// Get the App!

	$SQ('#sayso-get-app').click(function(e) {
		if ($SQ('#sso_wrapper input[type=radio]').is(':checked')) {
			if( !document.location.href.match('sayso-installing') )
				document.location.hash = 'sayso-installing';
			var token = $SQ('#sayso-install-token').attr('value');
			location.href = '//' + sayso.baseDomain + '/starbar/install/extension?install_token=' + token;
			if (navigator.userAgent.match('MSIE')) {
				// For IE users, prompt to restart browser
				elemOverlay.find('span.sso_main_content').html('<h3>Please restart Internet Explorer after installing the app to complete the installation.</h3><br/><br/><br/><br/>');
			} else {
				elemOverlay.find('span.sso_main_content').html('<h3>Please allow the browser to install the app.</h3><br/><br/><br/><br/>');
			}
			elemOverlay.find('form').html('');
			e.preventDefault();
		} else {
			e.preventDefault();
			$SQ('span.sso_textError').fadeIn('slow');
		}
	});

	if ($SQ('#sso_wrapper input[type=radio]').is(':checked')) {
		agreedToTerms();
	}
})();
