//noinspection ThisExpressionReferencesGlobalObjectJS
sayso.module.webportal = (function(global, $, state, api, Handlebars) {
    'use strict';

    var shared = {},
        initialized = false,
        app = 'webportal',
        title = 'Machinima | Recon - Powered by Say.So',
        version = '1.0',
        starbarId = 4,
        $loginButton = $('#login_button'),
        $loginDiv = $('#login'),
        $signOutButton = $('#sign_out'),
        $emailField = $('#email_field'),
        $passwordField = $('#password_field'),
        $forgotPassword = $('#forgot_password'),
		$contentContainer = $('#content_container');

    function initialize() {
        if (!initialized) {
            //For now, throw away the hash they navigated here with.
            if (window.location.hash) {
                window.location.hash = '';
            }
            if(!state.state.loggedIn) {
                loadMarkup('landing');
            }
        }
        //Bind our hashchange event.
        window.onhashchange = hashChanged;

        //Setup title
        $(document).attr('title', title);

        //TODO: Move all of these into portal-element.
        $loginButton.click(function() {
            doLogin();
        });
        $signOutButton.click(function() {
            if (state.state.loggedIn) {
                state.logout();
            }
        });
        $passwordField.keyup(function(event){
            if(event.keyCode === 13){
                $loginButton.click();
            }
        });
        $emailField.keyup(function(event){
            if(event.keyCode === 13){
                $loginButton.click();
            }
        });
        if (state.state.loggedIn) {
			loadMarkup('profile');
        }
        else {
            $loginDiv.show();
            $forgotPassword.show();
			$.placeholder.shim();
        }
        initialized = true;

		prepareElements();
	}

	function loadMarkup(key, $container) {
		if (!$container) {
            $container = $contentContainer;
        }

		api.doRequest({
			action_class : 'markup',
			action : 'getMarkup',
			starbar_id : starbarId,
			app: app,
			key : key
		}, function(response){
			$container.html('');
			processMarkupIntoContainer($container, response.responses['default'].variables.markup, {}, true);
		});
	}

	function prepareElements($container, handlerCollection, templateData) {
		if (!$container) $container = $contentContainer;
		if (!handlerCollection) {
            handlerCollection = "post-template";
        }

		var $elements = $('.portal-element', $container);
		var $element;

		$elements.each(function() {
			$element = $(this);
			for (var elementType in prepareHandlers[handlerCollection]) {
				if ($element.hasClass('portal-' + elementType)) {
					prepareHandlers[handlerCollection][elementType]($element, $element.data(), templateData);
				}
			}
		});

		for (var helper in handlebarsHelpers) {
			Handlebars.registerHelper(helper, handlebarsHelpers[helper]);
		}
	}

	function processMarkupIntoContainer($container, markup, templateData, runPreTemplateHandlers) {
		var template;

		if (typeof templateData !== "object") {
            templateData = {};
        }

		if (runPreTemplateHandlers) {
			var $tempContainer = $('<div></div>');

			$tempContainer.html(markup);

			prepareElements($tempContainer, "pre-template");

			// compile the markup into a handlebars template
			template = Handlebars.compile($tempContainer.htmlForTemplate());
		} else {
			template = Handlebars.compile(markup);
		}

		// always attach the state to the template
		templateData.state = state.state;

		// pass the api response (templateData) to the template as data, and render
		$container.append(template(templateData), {noEscape: true});

		// prepare sayso elements (passing on templateData to anything that may need it... tabs within tabs?)
		prepareElements($container, "post-template", templateData);
	}

    function doLogin(email, password) {
        if (email || password) {
            state.login(email, password, function(response) {
                //Do nothing with errors right now.
                if(response.result !== true) {
                    $('#login_failed').show();
                    setTimeout(function(){
                        $('#login_failed').fadeOut('slow');
                    }, 3000);
                    $passwordField.val('');
                    $passwordField.focus();
                }
            });
        }
        else {
            if($emailField.val() && $passwordField.val()) {
                state.login($emailField.val(), $passwordField.val(), function(response) {
                    //Do nothing with errors right now.
                    if(response.result !== true) {
                        $('#login_failed').show();
                        setTimeout(function(){
                            $('#login_failed').fadeOut('slow');
                        }, 3000);
                        $passwordField.val('');
                        $passwordField.focus();
                    }
                });
            }
        }
    }

    function stateLogin() {
        if(state.state.loggedIn)
        {
            loadMarkup('profile');
            $emailField.val('');
            $passwordField.val('');
            $loginDiv.hide();
            $forgotPassword.hide();
        }
        else
        {
            loadMarkup('landing');
            $loginButton.show();
            $forgotPassword.show();
        }
    }

    function stateLogout() {
        loadMarkup('log-out');
        $loginDiv.show();
        $forgotPassword.show();
    }

    function hashChanged() {
        if (initialized) {
            var hash = document.location.hash;
            if (hash === '') {
                //We have navigated to the home page or to /#
                if (state.state.loggedIn) {
                    loadMarkup('profile');
                }
                else {
                    loadMarkup('landing');
                }
            }
            else {
                hash = hash.substring(1);
            }
            var values = hash.split('/');
            if (values && values[0] === 'content') {
                loadMarkup(values[1]);
            }
            //TODO: Cs - Handle failure elegantly.
            //TODO: Cs - should we split this out into hash_manager if it gets large enough?
        }
    }

	function getBrowserNameVersion() {
		var bn = {};
		var ua = navigator.userAgent;
		if( !ua ) {
			bn.browser = "unknown";
			bn.isSupported = false;
			return bn;
		}
		var mobileStrs = ["Android", "webOS", "iPhone", "iPod", "iPad"];
		var identStrs = ["Chrome", "Safari", "Firefox", "MSIE"];
		var versionStrs = ["Chrome", "Version", "Firefox", "MSIE"];
		var minVer = [1.0, 5.01, 10.0, 8.0];
		var i, j;
		for( i = 0; i < mobileStrs.length; i++ )
			if( ua.indexOf(mobileStrs[i]) >= 0 ) {
				bn.isMobile = true;
				bn.isSupported = false;
				return bn;
			}
		bn.isMac = (ua.indexOf('Mac OS X') >= 0);
		for( i = 0; i < identStrs.length; i++ )
			if( (j = ua.indexOf(identStrs[i])) >= 0 ) {
				bn.browser = identStrs[i].toLowerCase();
				var ver = 0;
				if( (j = ua.indexOf(versionStrs[i])) > 0 ) {
					ver = parseFloat(ua.substring(j + versionStrs[i].length + 1));
					if( ver )
						bn.version = ver;
				}

				bn.isSupported = ver >= minVer[i];
				return bn;
			}
		bn.browser = "unknown";
		bn.isSupported = false;
		return bn;
	}

    shared.app = app;
    shared.version = version;

    $(document).on('sayso:state-ready', initialize);
    $(document).on('sayso:state-login', stateLogin);
    $(document).on('sayso:state-logout', stateLogout);

	var handlebarsHelpers = {
		"currency-name-highlighted": function(currency) {
			// @todo add description to game.currencies
			return new Handlebars.SafeString('' +
				'<span class="sayso-element sayso-highlight sayso-tooltip" ' +
				'data-tooltip-title="'+state.state.game.currencies[currency].description+'">' +
				state.state.game.currencies[currency].name +
				'</span>');
		},
		"currency-name": function(currency) {
			return state.state.game.currencies[currency].name;
		},
		"currency-name-highlighted-with-value": function(currency, value) {
			// @todo add description to game.currencies
			return new Handlebars.SafeString('' +
				'<span class="sayso-element sayso-highlight sayso-tooltip" ' +
				'data-tooltip-title="'+state.state.game.currencies[currency].description+'">' +
				(value ? value + " " : "") + state.state.game.currencies[currency].name +
				'</span>');
		},
		"currency-name-with-value": function(currency, value) {
			return (value ? value + " " : "") + state.state.game.currencies[currency].name;
		},
		"experience-percent": function(game) {
			var currentExp,
				currentLevel,
				currentLevelExp,
				nextLevelExp;

			currentExp = game.currencies.experience.balance;
			currentLevel = game.level;
			currentLevelExp = game.levels[currentLevel].threshold;
			nextLevelExp = game.levels[currentLevel+1].threshold;

			return Math.round(((currentExp-currentLevelExp)/(nextLevelExp-currentLevelExp))*100);
		},
		"user-public-name": function() {
			if (state.state.profile.public_name) {
                return state.state.profile.public_name;
            }
			else {
                return state.state.game.level;
            }
		},
		"image-path": function(fileName) {
			return "/browserapp/images/" + state.state.starbar.short_name + "/" + fileName;
		},
		"get-record-field" : function(recordSet, recordId, fieldName) {
			//dot notation (recordSet.recordId.fieldName) fails
			return recordSet[recordId][fieldName];
		},
		"compare": function(v1, operator, v2, options) {
			switch (operator) {
				case '==':
					return (v1 == v2) ? options.fn(this) : options.inverse(this);
				case '===':
					return (v1 === v2) ? options.fn(this) : options.inverse(this);
				case '<':
					return (v1 < v2) ? options.fn(this) : options.inverse(this);
				case '<=':
					return (v1 <= v2) ? options.fn(this) : options.inverse(this);
				case '>':
					return (v1 > v2) ? options.fn(this) : options.inverse(this);
				case '>=':
					return (v1 >= v2) ? options.fn(this) : options.inverse(this);
				case '||':
					return (v1 || v2) ? options.fn(this) : options.inverse(this);
				case '&&':
					return (v1 && v2) ? options.fn(this) : options.inverse(this);
				default:
					return options.inverse(this);
			}
		}
	};

	// "section-link" corresponds to elements that have the class "sayso-section-link" (as well as "sayso-element")
	// the "data" variable is, by default, simply $elem.data();
	var prepareHandlers = {
		// note that pre-template handlers are NOT run on markup/elements inside tabs
		// therefore, partials needed for tabs should be outside the tab context
		"pre-template": {
			"partial": function ($elem, data) {
				// partial found, register the
				Handlebars.registerPartial(data.partialId, $elem.htmlForTemplate());

				// remove it from the markup so it doesn't go through the template processing (except as a partial)
				$elem.remove();
			}
		},
		"post-template": {
			"template-image": function ($elem, data) {
				if (data['templateImageSrc'])
					$elem.attr('src', data['templateImageSrc']);
			},
			"tooltip": function ($elem, data) {
				// @todo show data['tooltipTitle'] 'neatly' when you roll over this element
				$elem.attr('title', data.tooltipTitle); // hack
			},
            "placeholder": function () {
                $.placeholder.shim();
            },
            "get-app-install": function ($elem) {
                $("#agreeterms", $elem).change(function(){
                    if($(this).is(':checked')){
                        $('#grab_it', $elem).addClass('enabled');
                        $('#grab_it', $elem).on('click', function(){
                            location.hash = 'content/get-app-confirmation';
                        });
                    } else {
                        $('#grab_it', $elem).removeClass('enabled');
                        $('#grab_it', $elem).off('click');
                    }
                });
            },
            "join-now": function ($elem) {
                var $emailField = $('#emailAddress_field', $elem),
                    $passwordField = $('#passwordOne_field', $elem),
                    $confirmationField = $('#passwordTwo_field', $elem),
                    $registerButton = $('#portal_join_now_button', $elem),
                    $getBrowserAppCheckbox = $("#install_browser_app", $elem),
                    $agreeTermsCheckbox = $("#agreeterms", $elem),
                    buttonActive = false;

                $agreeTermsCheckbox.on('click', activateSubmit);
                $emailField.on('keyup change', activateSubmit);
                $passwordField.on('keyup change', activateSubmit);
                $confirmationField.on('keyup change', activateSubmit);

                function activateSubmit() {
                    if(!validateFields()) {
                        if(!buttonActive) {
                            $registerButton.removeClass('join_now_button_disabled').addClass('join_now_button');
                            $registerButton.on('click', function(){
                                createAccount($emailField.val(), $passwordField.val(), $getBrowserAppCheckbox.is(':checked'));
                            });
                            buttonActive = true;
                        }
                    }
                    else {
                        if(buttonActive){
                            $registerButton.removeClass('join_now_button').addClass('join_now_button_disabled');
                            $registerButton.off('click');
                            buttonActive = false;
                        }
                    }
                }

                function validateFields() {
                    //TODO: Show the end user what is wrong.
                    var emailAddress = $emailField.val();
                    if( emailAddress.length < 1 ) {
                        return "Woops - Please enter your email address";
                    }

                    var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
                    if (!emailPattern.test(emailAddress)){
                        return "Woops - Please enter a valid email address";
                    }

                    var passwordOne = $passwordField.val();
                    if(passwordOne.length < 1) {
                        return "Woops - Please enter your password";
                    }

                    var passwordTwo = $confirmationField.val();
                    if(passwordOne !== passwordTwo ) {
                        return "Woops - Your passwords do not match.<br>Please reenter your password";
                    }
                    if(passwordOne.length < 6 || passwordOne.length > 12) {
                        return "Woops - Your password needs to have between 6 and 12 characters.<br>Please reenter your password";
                    }

                    if(!$agreeTermsCheckbox.is(':checked')) {
                        return "Woops - Please accept the terms and conditions";
                    }

                    return false;
                }

                function createAccount(emailAddress, password, getBrowserApp) {
                    api.doRequest({
                        action_class : 'registration',
                        action : 'createUser',
                        email : emailAddress,
                        password : password,
                        originating_starbar_id : starbarId
                    }, function(response){
                        var success = response.responses['default'].variables.user_id;
                        if (success) {
                            doLogin(emailAddress, password);
                            if (getBrowserApp) {
                                //Change hash so navigation works, if we just call loadMarkup here, it breaks UX
                                location.hash = 'content/get-app-confirmation';
                            }
                            else {
                                location.hash = 'content/thank-you-registration';
                            }
                        }
                    });
                }
            },

			"install-app" : function ($elem, data) {
				if( !document.location.href.match('content/get-app-confirmation') )
					document.location.hash = 'content/get-app-confirmation';

                var downloadLocation,
                    browser;

                downloadLocation = "//" + sayso.module.config.baseDomain + '/starbar/install/extension';
				browser = getBrowserNameVersion();

				function installChrome(){
					chrome.webstore.install(undefined, undefined, function(s) {console.log(s);});
				}

				if (browser.browser === "chrome") {
					var extId;

					switch (sayso.module.config.baseDomain) {
						case "app.saysollc.com" :
							extId = 'lpkeinfeenilbldefedbfcdhllhjnblc';
							break;
						case "app-qa.saysollc.com" :
							extId = 'dachmhjcknkhjkjpknneienbiolpoein';
							break;
						case "local.saysollc.com" :
							extId = 'kcgjipkjdgakogjmbekhghlhdgacajbh';
							break;
						case "app-dev.saysollc.com" :
							extId = 'fjgbjoknbfjhofpcdpfepjaicipncpob';
							break;
						case "app-demo.saysollc.com" :
							extId = 'poipmplbjibkncgkiaomennpegokfjom';
							break;
						case "staging.saysollc.com" :
							extId = 'dcdkmcnaenolmjcoijjggegpcbehgfkn';
							break;
					}
					downloadLocation = "https://chrome.google.com/webstore/detail/" +extId;
					$('head').append('<link rel="chrome-webstore-item" href="' + downloadLocation + '" />');
					$('#browser_install_instructions').html('<a href="#action/chrome-install" id="chrome-download">Click here to begin installation.</a> ');
					$("#chrome-download").bind("click", installChrome);
					$("#force-download").attr({"href": downloadLocation, "target": "_blank"});

				} else {
					if (browser.browser === "safari") {
						$('#browser_install_instructions').html('Please click on the Say.So package in the Safari downloads window to complete the instalation');
					}
					$("#force-download").attr("href", downloadLocation);
					location.href = downloadLocation;
				}
			}
		}
	};

    return shared;

})(this, jQuery, sayso.module.state, sayso.module.api, sayso.module.Handlebars);