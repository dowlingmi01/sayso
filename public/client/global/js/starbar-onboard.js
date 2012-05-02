
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
		$SQ('.sso_panel').addClass('sso_hidden');
		$SQ('#sso_enter_password').removeClass('sso_hidden');
	}
	function validatePasswords() {
		var pwd1 = $SQ('#sso_fld_password').val();
		if( pwd1.length < 1 )
			return "Woops - Please enter your password";

		var pwd2 = $SQ('#sso_fld_password_vfy');
		if( pwd2.length ) {
			pwd2 = pwd2.val();
			if( pwd1 != pwd2 )
				return "Woops - Your passwords do not match.<br>Please reenter your password";
			if( pwd1.length < 6 || pwd1.length > 12 )
				return "Woops - Your password needs to have between 6 and 12 characters.<br>Please reenter your password";
		}
		return false;
	}

	function validateEmail() {
		var emailadd = $SQ('#sso_fld_client_email').val();
		if( emailadd.length < 1 )
			return "Woops - Please enter your email address";


       var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
       if (emailPattern.test(emailadd))
       	return false;
       return "Sorry - invalid Email address";
     }

	$SQ('.sso_password').each( function() {
		var pwd = $SQ(this);
		var txt = $SQ('#'+pwd.attr('id')+'_txt');
		pwd.hide()
		txt.focus( function() {
			txt.hide();
			pwd.show();
			pwd.focus();
		});
		pwd.blur( function() {
			if( pwd.val() == '' ) {
				pwd.hide();
				txt.show();
			}
		});
		pwd.keyup( function() {
			if( !validatePasswords() ) {
				$SQ('#sayso-get-app').removeClass('sso_theme_button_disabled');
				$SQ('span.sso_textError').fadeOut('slow');
			} else
				$SQ('#sayso-get-app').addClass('sso_theme_button_disabled');
		})
	})
	$SQ('.sso_username').each( function() {
		var user = $SQ(this);
		var txt = $SQ('#'+user.attr('id')+'_txt');
		user.hide()
		txt.focus( function() {
			txt.hide();
			user.show();
			user.focus();
		});
		user.blur( function() {
			if( user.val() == '' ) {
				user.hide();
				txt.show();
			}
		});

	})
	// Get the App!

	$SQ('#sayso-get-app').click(function(e) {
		var errMsg;
		if (!(errMsg = validateEmail())) {
			if(!(errMsg = validatePasswords())) {
				var installToken = $SQ('#sayso-install-token').attr('value');
				var ajaxOpts = {
					url : '//' + sayso.baseDomain + '/starbar/install/user-password',
					dataType : 'jsonp',
					data : {
						install_token: installToken,
						user_password: $SQ('#sso_fld_password').val(),
						renderer: 'jsonp'
					},
					success : function (response) {
						if (response.status && response.status == "error") {
							$SQ('span.sso_textError').html(response.data.message).fadeIn('slow');
						} else {
							if( !document.location.href.match('sayso-installing') )
								document.location.hash = 'sayso-installing';
							location.href = '//' + sayso.baseDomain + '/starbar/install/extension?install_token=' + installToken;
							$SQ('#sso_allow_install_text').text('Please allow the browser to install the app.');
							$SQ('.sso_panel').addClass('sso_hidden');
							$SQ('#sso_allow_install').removeClass('sso_hidden');
						}
					}
				};
				$SQ.ajax(ajaxOpts);
				e.preventDefault();
			} else {
				e.preventDefault();
				$SQ('span.sso_textError').html(errMsg).fadeIn('slow');
			}
		} else {
			e.preventDefault();
			$SQ('span.sso_textError').html(errMsg).fadeIn('slow');
		}
	});

	if ($SQ('#sso_wrapper input[type=radio]').is(':checked')) {
		agreedToTerms();
	}
})();
