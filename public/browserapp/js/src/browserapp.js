sayso.module.browserapp = (function(global, $, state, api, Handlebars, frameComm, config) {
	$(function(){
		$(global.document).on('sayso:state-login sayso:state-logout sayso:state-ready', initApp);
		if( state.ready )
			initApp();
	});

	var starbarId;
	var tabId = "abc"; // @todo set this to unique tab ID id using browser extension
	var userMode = "logged-out";
	var $nav, $sectionContainer, $section;
	var currentSection, currentTabContainers, timeoutIdSectionLoadingSpinner;

	function initApp() {
		if ($nav && $nav.length) {
			$nav.remove();
		}

		if (state.state.loggedIn) {
			userMode = "logged-in";

			starbarId = state.state.starbar.id;

			loadApp();
		} else if (config.webportal) {
			userMode = "tour";
			starbarId = config.defaultStarbarId;

			api.doRequest({
				action_class : "markup",
				action : "getMarkup",
				starbar_id : starbarId,
				app: "browserapp",
				key : "nav"
			}, loadApp); // render receives the response
		} else {
			// @todo do something for logged out mode, e.g. redirect to portal for login, or take them to tour page,
			// @todo  or render bar/icon and show login dialog if they click something
			// potentially call render() here
		}
	}

	function loadApp(response) {
		// render nav and initNav() if we're not in an iframe
		if (!state.in_iframe) {
			if (userMode == "logged-in") {
				$('body').append(state.state.starbar.markup);
			} else if (userMode == "tour") {
				if (
					!response
						|| !response.responses
						|| !response.responses.default
						|| !response.responses.default.variables
						|| !response.responses.default.variables.markup
					) {
					alert("Error loading tour, please try again later.");
				}

				$('body').append(response.responses.default.variables.markup);

				// hide the hide button, since we're on the portal
				$('#sayso-nav-hide-button').hide();
			} // else {}  potentially we can handle logged out mode here
			initNav();
		}

		if (userMode == "logged-in") {
			//initListeners();
		}
	}


	function initNav() {
		$nav = $('#sayso-app-container');
		$sectionContainer = $('#sayso-section-container');
		$section = $('#sayso-section');

		// close the section box if user clicks anywhere on the document...
		$(global.document).click(function(event) {
			// don't close if they just right-clicked
			if (event.button === 0){
				closeSection();
			}
		});

		// ... but don't close section box if the user clicks on the nav or inside it (note that the section box is inside $nav)
		$nav.click(function(event) {
			event.stopPropagation();
		});

		prepareElements();
	}


	/*
	 this function is called when:
	 1) initially preparing nav
	 2) a section is opened (i.e. new markup is loaded)

	 $container is the element we look for elements in
	 handlerCollection is "pre-template" or "post-template" (default)
	 */
	function prepareElements($container, handlerCollection, templateData) {
		if (!$container) $container = $nav;
		if (!handlerCollection) handlerCollection = "post-template";

		var $elements = $('.sayso-element', $container);
		var $element;

		$elements.each(function() {
			$element = $(this);
			for (var elementType in prepareHandlers[handlerCollection]) {
				if ($element.hasClass('sayso-' + elementType)) {
					prepareHandlers[handlerCollection][elementType]($element, $element.data(), templateData);
				}
			}
		});

		// update all updateTypes when doing post-template processing, i.e. "game", "user", "notifications"
		if (handlerCollection == "post-template") {
			updateElements($container, "game");
			updateElements($container, "user");

			// only notifications should animate on initial load... game and user data shouldn't animate initially
			// (not sure user profile data will ever animate anyway)
			if ($container === $nav)
				updateElements($container, "notifications", true);
		}

		for (var helper in handlebarsHelpers) {
			Handlebars.registerHelper(helper, handlebarsHelpers[helper]);
		}
	}


	/*
	 this function is called when:
	 1) initially preparing a container (after the prepareElements post-template process, called by prepareElements())
	 2) the state changes on this tab (e.g. points received, username changed, notification received, currently not set up)
	 3) the user switches to this tab and we want to update everything to the latest state (called by switchToTab())

	 $container is the element we look for elements in
	 updateType is what we want to update (e.g. "game", "user"), null or false for all
	 animate is if we want to animate the elements or not
	 */
	function updateElements($container, updateType, animate) {
		if (!$container) $container = $nav;

		var $elements = $('.sayso-element', $container);
		var $element;

		$elements.each(function() {
			$element = $(this);
			for (var handlerCollection in updateHandlers) {
				// if no updateType is specified, update all handlerCollections (i.e. "game", "user", "notifications", ...)
				if (!updateType || updateType == handlerCollection) {
					for (var elementType in updateHandlers[handlerCollection]) {
						if ($element.hasClass('sayso-' + elementType)) {
							updateHandlers[handlerCollection][elementType]($element, $element.data(), animate);
						}
					}
				}
			}
		});
	}


	// to be called when switching to this browser tab/window
	// @todo call this function!
	function switchToWindow() {
		// update all state-bound elements, without animating them, since the update should have already appeared on another tab
		// @todo don't do this, or do it partially (e.g. notification icon?), if the starbar is hidden
		updateElements();
	}


	// data is $(clicked element).data();
	// e.g. running $().data() on <div data-foo="bar" data-moo="car"> gives { foo: "bar", moo: "car" }
	function openSection(clickedElementData) {
		var section;
		var $sectionLoading;

		if (!clickedElementData || !clickedElementData['section']) return;

		if (userMode == "tour") {
			section = "tour-" + clickedElementData['section'];
		} else if (userMode == "logged-in") {
			section = clickedElementData['section'];
		} else {
			return;
		}

		if (section == currentSection) {
			closeSection();
			return;
		} else if (currentSection) {
			closeSection();
		}

		currentSection = section;
		currentTabContainers = {};

		$sectionContainer.addClass('sayso-section-' + section);
		$sectionContainer.fadeTo(200, 1);

		timeoutIdSectionLoadingSpinner = setTimeout(function(){
			$sectionLoading = $('<div class="sayso-loading"></div>');
			$section.prepend($sectionLoading);
		}, 200); // don't show loader if content loads in under 200ms

		var requests = {
			'markup': {
				action_class : "markup",
				action : "getMarkup",
				starbar_id : starbarId,
				app: "browserapp",
				key : "section-" + section
			}
		};

		// grab any extra requests associated with this section
		if (userMode == "logged-in" && section in extraRequests) {
			var extraRequestsForThisSection = extraRequests[section](clickedElementData); // extraRequests[section] is a function

			$.extend(requests, extraRequestsForThisSection);

		}

		api.doRequests(requests, function(response){
			// if the loader wasn't shown yet, don't show it
			clearTimeout(timeoutIdSectionLoadingSpinner);

			processMarkupIntoContainer($section, response.responses.markup.variables.markup, response.responses, true);
			// hide the loading element if we added it
			if ($sectionLoading) {
				$sectionLoading.fadeTo(100, 0, function(){
					$sectionLoading.remove();
					$sectionLoading = false;
				});
			}
		});
	}

	function closeSection() {
		$sectionContainer.fadeTo(0, 0);
		$sectionContainer.removeClass();
		$section.html("");
		currentSection = null;

		// @todo we probably should reset the Handlebars partials here?
	}

	function openTab($tabContainer, tabName, templateData, clickedElementData) {
		var tabs = currentTabContainers[$tabContainer.attr('id')];

		// tabs[tab] should exist, since it's added by the pre-template handler for tab-container
		if (!(tabName in tabs)) return;

		var markup = tabs[tabName];
		var $tab = $("div.sayso-tab[data-tab="+tabName+"]", $tabContainer).first().html('');;

		$("div.sayso-tab", $tabContainer).hide();

		if (templateData) {
			processMarkupIntoContainer($tab, markup, templateData, true);
			$tab.show();
		} else {
			// no templateData passed, perform extra requests, if any
			if (tabName in extraRequests) {
				var extraRequestsForThisTab = extraRequests[tabName](clickedElementData);

				api.doRequests(extraRequestsForThisTab, function(response){
					// if the loader wasn't shown yet, don't show it
					processMarkupIntoContainer($tab, markup, response.responses, true);
					$tab.show();
				});
			} else { // no templateData!
				processMarkupIntoContainer($tab, markup, {}, true);
				$tab.show();
			}
		}
	}

	function openPoll($container, data, $loadingElement, doneLoading) {
		// check if any polls are currently open (could possibly be the current container)
		var $pollContainerToClose = $container.parent().children('.sayso-poll-container-current');

		// if none are in the currently-open state, check if any polls are currently loading
		if (!$pollContainerToClose.length) {
			// if we just finished loading our container, we don't want to close the current container, so we should only check if siblings are loading
			if (doneLoading)
				$pollContainerToClose = $container.siblings('.sayso-poll-container-loading');
			// if we didn't just come from loadPoll(), the user may be closing the poll while it is loading (e.g. if the poll timed out from SG)
			else
				$pollContainerToClose = $container.parent().children('.sayso-poll-container-loading');
		}

		if ($pollContainerToClose.length) {
			// there should only be one, close it!
			closePoll($pollContainerToClose.first());

			if (data.surveyId == $pollContainerToClose.data('surveyId')) { // user clicked the currently open poll... just close it and do nothing
				return;
			}
		}

		var $poll = $('.sayso-poll', $container).first();

		// first click this session, or if the poll didn't load the last time
		if (!data.iframeLoadCompleted) {
			$loadingElement = $('<div class="sayso-loading"></div>');
			$poll.html('').append($loadingElement);

			$container.addClass("sayso-poll-container-loading");
			// set the width, otherwise animation is choppy: http://stackoverflow.com/questions/10471202/jquery-slidedown-is-not-smooth
			var estimatedPollHeight = 62 + parseInt(Math.ceil(data['surveyNumberOfAnswers'] / 2.0) * 32); // 62 base height + 32 per row of answers -- Note that this height estimate is updated after the iframe loads
			if (!$poll.css('height') || $poll.css('height') == "0px" || $poll.css('height') == "auto") $poll.css('height', estimatedPollHeight);

			$poll.slideDown(500);

			// loadPoll calls openPoll again when the iframe is done loading
			loadPoll($poll, $container, $loadingElement, data);

			return;
		} // else -- iframe is already loaded

		$container.addClass("sayso-poll-container-current");

		if ($container.hasClass("sayso-poll-container-loading")) { // we already did a slideDown to show the loading element, and we just finished loading
			$container.removeClass("sayso-poll-container-loading");
			$poll.animate({'height': data.pollHeight}, 250, function() {

			});
			$loadingElement.fadeTo(250, 0, function() {
				$loadingElement.remove();
			});
		} else { // poll was already loaded and proper height was set, so just slide the existing poll back down
			$poll.slideDown(500);
		}
	}

	function createIframe(url, callback) {
		var $iframe = $('<iframe class="sayso-iframe" scrolling="no"></iframe>');
		var frameId = frameComm.setURL($iframe, url);
		$(global.document).on('sayso:iframe-ready', function(unused, dataFromIframe) {
			if( dataFromIframe.frame_id === frameId ) {
				callback(unused, dataFromIframe)
			}
		});
		return { $element: $iframe, frame_id: frameId }
	}

	function loadPoll($poll, $container, $loadingElement, data) {
		// set up iframe listeners
		// set up iframe
		api.doRequest({
			action_class : "survey",
			action : "getSurvey",
			starbar_id : starbarId,
			survey_id : data['surveyId'],
			send_questions : true,
			send_question_choices : true
		}, function(response) {
			var poll = response['responses'].default.variables.survey;
			var iframe = createIframe('//' + config.baseDomain + '/browserapp/iframe.html', function(unused, dataFromIframe) {
				frameComm.fireEvent(iframe.frame_id, 'init-action', {action: 'display-poll', starbarId: starbarId, starbar_short_name: state.state.starbar['short_name'], poll: poll});
			});

			$(global.document).on('sayso:iframe-poll-loaded', function(unused, dataFromIframe) {
				if( dataFromIframe.frame_id === iframe.frame_id ) {
					// receive (and set) poll height from iframe when it's done rendering
					if (dataFromIframe.data.height) {
						data.pollHeight = 50 + dataFromIframe.data.height;
						iframe.$element.css('height', dataFromIframe.data.height); // note that $poll is still the old height, and that is animated in openPoll
						data.iframeLoadCompleted = true;
						openPoll($container, data, $loadingElement, true);
					}
				}
			});

			$(global.document).on('sayso:iframe-poll-completed', function(unused, dataFromIframe) {
				completePoll($poll, poll);
			});

			$poll.append(iframe.$element);
		});


		// wait for iframe to load
		// @todo replace setTimeout with iframe:ready bind/trigger or equivalent
		/*setTimeout(function() {
			$container.data('iframeLoadCompleted', true);
			data.iframeLoadCompleted = true;

			// receive (and set) poll height from iframe when it's done rendering
			data.pollHeight = 100 + Math.floor((Math.random()*200)); // @todo temporary random amount -- replace with amount returned from iframe
			$iframe.css('height', data.pollHeight);

			openPoll($container, data, $loadingElement, true);
		}, Math.floor((Math.random()*1000))); // fake load delay, up to 1 second*/
	}

	function closePoll($container) {
		var $poll = $('.sayso-poll', $container).first();
		$poll.slideUp(500);
		$container.removeClass("sayso-poll-container-loading sayso-poll-container-current");
	}

	function completePoll($poll, poll) {
		processMarkupIntoContainer($poll, "{{>poll-completed-footer}}", poll);
		$poll.children('.sayso-poll-footer').hide().fadeTo(1000, 1);
		updateElements(null, "game", true);

		// show the completed tab *link* in case this is the first poll this user has completed
		$('#sayso-completed-tab-link', $section).show();
	}

	function processMarkupIntoContainer($container, markup, templateData, runPreTemplateHandlers) {
		var template;

		if (typeof templateData !== "object") templateData = {};

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
			if (state.state.profile.public_name)
				return state.state.profile.public_name;
			else
				return state.state.game.level;
		},
        "next-exp-threshold": function(game) {
            return game.levels[game.level+1].threshold;
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
                    break;
                case '===':
                    return (v1 === v2) ? options.fn(this) : options.inverse(this);
                    break;
                case '<':
                    return (v1 < v2) ? options.fn(this) : options.inverse(this);
                    break;
                case '<=':
                    return (v1 <= v2) ? options.fn(this) : options.inverse(this);
                    break;
                case '>':
                    return (v1 > v2) ? options.fn(this) : options.inverse(this);
                    break;
                case '>=':
                    return (v1 >= v2) ? options.fn(this) : options.inverse(this);
                    break;
                case '||':
                    return (v1 || v2) ? options.fn(this) : options.inverse(this);
                    break;
                case '&&':
                    return (v1 && v2) ? options.fn(this) : options.inverse(this);
                    break;
                default:
                    return options.inverse(this);
                    break;
            }
        },
        "create-level-carousel": function(game) {
            var $row, $level;
            var $carouselContainer = $('<div></div>');
            var counter = 0;
            var topIndex = 7;
            $row = $('<div class="sayso-experience-levels-carousel-group" style="display: block; z-index: ' + topIndex + ';"></div>');
            for(var i=1;i<=Object.keys(game.levels).length;i++) {
                if(((counter % 5) === 0 && counter !== 0)) {
                    $carouselContainer.append($row);
                    topIndex--;
                    $row = $('<div class="sayso-experience-levels-carousel-group" style="z-index: ' + topIndex + ';"></div>');
                }
                if(i<game.level){
                    $level = $('<div class="sayso-element sayso-experience-level-item sayso-experience-level-item-earned" data-level-id="' + i + '" style="background-image: url(\'' + game.levels[i].img_url_small + '\')"></div>');
                }
                else if(i>game.level){
                    $level = $('<div class="sayso-element sayso-experience-level-item sayso-experience-level-item-next" data-level-id="' + i + '"></div>');
                }
                else {
                    $level = $('<div class="sayso-element sayso-experience-level-item sayso-experience-level-item-current" data-level-id="' + i + '" style="background-image: url(\'' + game.levels[i].img_url + '\')"></div>');
                    $level.html('<span class="sayso-experience-level-icon-current"></span>');
                }

                //Use partials and a post-template handler for the carousel. Otherwise this is gonna be hellacious.
                $row.append($level);
                if(i === Object.keys(game.levels).length) {
                    $carouselContainer.append($row);
                }
                counter++;
            }
            return $carouselContainer.html();
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
				Handlebars.registerPartial(data['partialId'], $elem.htmlForTemplate());

				// remove it from the markup so it doesn't go through the template processing (except as a partial)
				$elem.remove();
			},
			// go through each tab inside the tab container, moving its contents to the tab container's $().data('tabs') field
			// this is so we don't run it through the template system until we are render the tab
			"tab-container": function ($elem) {
				var $tab, $tabs;
				var tabs = {};
				$tabs = $('.sayso-tab', $elem);
				$tabs.each(function() {
					$tab = $(this);

					// need to use .htmlForTemplate() (see util.js) instead of html() to replace "{{&gt;" with "{{>" because jQuery is a big jerk
					tabs[$tab.data('tab')] = $tab.htmlForTemplate();

					// empty the tab, so it isn't processed by handlebars
					$tab.html("");
				});
				currentTabContainers[$elem.attr('id')] = tabs;
			}
		},
		"post-template": {
			"section-link": function ($elem, data) {
				$elem.click(function(e) {
					e.preventDefault();
					openSection(data);
				});
			},
			"tab-container": function ($elem, data, templateData) {
				// templateData is passed through from openSection to the default tab, so that we don't have to do another API request for the default (initial) tab
				openTab($elem, data['defaultTab'], templateData);
			},
			"tab-link": function ($elem, data) {
				// note that there is no templateData passed in this case, since any needed data is requested via the api, from extraRequests
				// this includes re-opening the default tab, for example
				$elem.click(function(e) {
					e.preventDefault();
					openTab($('#' + data['tabContainer']), data['tab'], null, $elem.data());
				});
			},
			"scrollable": function ($elem, data) {
				// @todo make $elem have a custom JS scrollbar!
				$elem.css('overflow', 'auto');
			},
			"tooltip": function ($elem, data) {
				// @todo show data['tooltipTitle'] 'neatly' when you roll over this element
				$elem.attr('title', data['tooltipTitle']); // hack
			},
            "placeholder": function ($elem) {
                if (!$.support.placeholder) {
                    var placeholder = $elem.attr('placeholder');
                    $elem.val(placeholder);
                }
            },
			"poll-container": function ($elem, data) {
				var $pollHeader = $('.sayso-poll-header', $elem);
				$pollHeader.click(function() {
					openPoll($elem, data); // note that openPoll takes the container ($elem) as a parameter, not $pollHeader
				});
			},
            "reward-item": function ($elem, data, templateData) {
                var rewardRecord = $.grep(templateData.rewards.records, function (r){ return r.id === data.rewardId; });
                if (rewardRecord) {
                    // Use rewardRecord[0] since .grep can return multiple results so it returns an array.
                    rewardRecord = rewardRecord[0];

                    if (rewardRecord.can_purchase) {
                        $elem.click(function() {
                            $("#sayso-reward-redeem-overlay", $nav).show();
                            processMarkupIntoContainer($("#sayso-reward-item-redeem-step", $nav), "{{>redeem_step_1}}", rewardRecord);
                        });
                    }
                    else {
                        $elem.mouseover(function() {
                            $(this).children(".sayso-reward-item-disabled").show();
                        });
                        $elem.mouseout(function() {
                            $(this).children(".sayso-reward-item-disabled").hide();
                        });
                        //Disable the appropriate elements.
                        $elem.find(".sayso-reward-item-redeem").addClass("disabled");
                        $elem.find(".sayso-reward-item-comment").addClass("disabled");
                    }
                }
            },
            "reward-item-redeem-submit" : function ($elem, data, templateData) {
                $elem.click(function() {
                    var currentBalance,
                        balanceAfterPurchase,
                        balancePercentAfterPurchase;
                    currentBalance = templateData.state.game.currencies.redeemable.balance;

                    balanceAfterPurchase = currentBalance - templateData.price;
                    balancePercentAfterPurchase = Math.round((balanceAfterPurchase/currentBalance)*100);

                    templateData.balance_after_purchase = balanceAfterPurchase;
                    templateData.balance_percent_after_purchase = balancePercentAfterPurchase;

                    $("#sayso-reward-item-redeem-step", $nav).html('');

                    //Dot notation not used due to reserved keyword 'type'
                    if (templateData['type'] === "token") {
                        processMarkupIntoContainer($("#sayso-reward-item-redeem-step", $nav), "{{>redeem_step_2_token}}", templateData);
                    }
                    else {
                        processMarkupIntoContainer($("#sayso-reward-item-redeem-step", $nav), "{{>redeem_step_2_shipping}}", templateData);
                    }
                });
            },
            "reward-item-order-submit" : function ($elem, data, templateData) {
                $elem.click(function() {
                    var shippingData = new Array();
                    var quantity = 0;

                    //Dot notation not used due to reserved keyword 'type'
                    if (templateData['type'] === "token") {
                        quantity = $('input[name="sayso-reward-item-order-quantity-select"]', $nav).val();
                        api.doRequest({
                            action_class : "game",
                            action : "redeemReward",
                            starbar_id : starbarId,
                            game_asset_id: templateData.id,
                            shipping: shippingData,
                            quantity: quantity
                        }, function(response){
                            if(response.error_code === 0) {
                                //TODO: Implement order success template.
                                updateElements($nav, "game");
                                $("#sayso-reward-item-redeem-step", $nav).html(''); //Clear the step container one last time.
                                processMarkupIntoContainer($("#sayso-reward-item-redeem-step", $nav), "{{>redeem_step_3_success}}", templateData);
                            }
                            else {
                                //TODO: Fix error alert to be more useful.
                                alert('There was an error processing your order, please try again later. Error: ' + response.error_message);
                            }
                        });
                    } else {
                        //prepare shippingData
                        //Stolen from OLD StarBar for form validation
                        var $step2 = $('.sayso-reward-step-two-shipping', $nav);
                        var formErrors = false;

                        //TODO: Fix validation to be legit
                        var inputElems = new Array();
                        var fields = ['first_name', 'last_name', 'address_1', 'address_2', 'city', 'state', 'country', 'zip', 'phone'];
                        var required_fields = ['first_name', 'last_name', 'address_1', 'city', 'country', 'zip'];
                        var $personalInfo = $step2.find('#sayso-reward-item-order-shipping-information');

                        if ($personalInfo.length) {
                            for (i = 0; i < fields.length; i++) {
                                inputElems[fields[i]] = $('input[name="order_'+fields[i]+'"]', $personalInfo);
                                shippingData['order_'+fields[i]] = inputElems[fields[i]].val();
                            }

                            for (i = 0; i < required_fields.length; i++) {
                                if (shippingData['order_'+required_fields[i]] == "") {
                                    formErrors = true;
                                    inputElems[required_fields[i]].css('border', '1px solid #F00');
                                } else {
                                    inputElems[required_fields[i]].css('border', '1px solid #CCC');
                                }
                            }
                        }
                        quantity = 1;

                        if(!formErrors) {
                            api.doRequest({
                                action_class : "game",
                                action : "redeemReward",
                                starbar_id : starbarId,
                                game_asset_id: templateData.id,
                                shipping: shippingData,
                                quantity: quantity
                            }, function(response){
                                if(response.error_code === 0) {
                                    //TODO: Implement order success template.
                                    updateElements($nav, "game");
                                    $("#sayso-reward-item-redeem-step", $nav).html(''); //Clear the step container one last time.
                                    templateData.shipping_data = shippingData;
                                    processMarkupIntoContainer($("#sayso-reward-item-redeem-step", $nav), "{{>redeem_step_3_success}}", templateData);
                                }
                                else {
                                    //TODO: Fix error alert to be more useful.
                                    alert('There was an error processing your order, please try again later. Error: ' + response.error_message);
                                }
                            });
                        }
                    }
                });
            },
            "reward-item-finished-submit": function ($elem) {
                $elem.click(function(e){
                    $("#sayso-reward-item-redeem-step", $nav).html('');
                    $("#sayso-reward-redeem-overlay", $nav).hide();
                });
            },
            "reward-redeem-overlay" : function ($elem) {
                $elem.click(function(e){
                    if (e.target === this) {
                        $(this).hide();
                        $(this).children().html('');
                    }
                })
            },
            "reward-step-two-token" : function ($elem, data, templateData) {
                //Setup our handlers for options and balance changing.
                var $select,
                    $balanceBarPercent,
                    $balanceBarValue,
                    canPurchaseCount,
                    currentBalance,
                    itemPrice,
                    options = {},
                    purchaseCap = 10; //Max tokens we are currently allowing.

                //Dirty, we should rename these elements
                $balanceBarPercent = $elem.find('.sayso-reward-item-redeem-order-bottom-right').find('.sayso-reward-item-progress-bar');
                $balanceBarValue = $elem.find('.sayso-reward-item-redeem-order-bottom-right').find('.sayso-reward-item-progress-bar-value');
                $select = $elem.find('select[name=sayso-reward-item-order-quantity-select]');
                itemPrice = templateData.price;
                currentBalance = templateData.state.game.currencies.redeemable.balance;
                canPurchaseCount = Math.min(Math.floor(currentBalance/itemPrice), purchaseCap);

                //Setup how many options they can buy
                for (var i=1;i<=canPurchaseCount;i++) {
                    options[i] = i;
                }
                //Append options
                $.each(options, function(key, value) {
                    $select.append($("<option></option>")
                        .attr("value", value).text(key));
                });
                //Update the UI to reflect changes
                $select.change(function(e) {
                    var pointsAfterPurchase,
                        percentAfterPurchase,
                        purchaseAmount,
                        purchaseCost;

                    purchaseAmount = $(this).val();
                    purchaseCost = itemPrice * purchaseAmount;
                    pointsAfterPurchase = currentBalance - purchaseCost;
                    percentAfterPurchase = Math.round((pointsAfterPurchase/currentBalance)*100);

                    $balanceBarPercent.css('width', percentAfterPurchase + '%');
                    $balanceBarValue.text(pointsAfterPurchase);
                });
            },
			//displays the next promo image
			"next-promo" : function ($elem, data) {
				$elem.click(function() {
					$("#" + data["thisImage"]).hide();
					$("#" + data["nextImage"]).show();
				});
			},
			//calls the user.connectSocialNetwork endpoint
			"social-connect" : function ($elem, data) {
				$elem.click(function() {
					api.doRequest({
						action_class : "user",
						action : "connectSocialNetwork",
						starbar_id : starbarId,
						network : data["network"],
						oauth : data["oauth"]
					}, function(response){
						if (response.responses.default.variables.success == true) {
							//it worked
						} else {
							//it failed
						}
					});
				});
			},
            "experience-level-item": function($elem, data) {
                var oldStyle = $elem.css('background-image');
                var game = state.state.game;
                if(game.level===data.levelId)
                {
                    $elem.html("<p>" + game.levels[data.levelId].threshold + "</p>");
                }

                $elem.mouseover(function(){
                    if(game.level!==data.levelId)
                    {
                        $(this).html("<p>" + game.levels[data.levelId].threshold + "</p>");
                    }
                    if(data.levelId>game.level)
                    {
                        $(this).css('background-image', 'url(' + game.levels[data.levelId].img_url_small + ')');
                    }
                });
                $elem.mouseout(function(){
                    if(game.level!==data.levelId)
                    {
                        $(this).html('');
                    }
                    if(data.levelId>game.level)
                    {
                        $(this).css('background-image', oldStyle);
                    }
                });
            },
            "experience-levels-container": function ($elem) {
                var left = new Array();
                var right = new Array();
                var $current, $next;

                function initCarousel() {
                    var i = 0;
                    $('.sayso-experience-levels-carousel-group', $elem).each(function() {
                        right.push($(this));
                        if(i>0){
                            $(this).css('left', '500px')
                        }
                        i++;
                    });
                    $current = right.shift();
                    $('#sayso-experience-levels-nav-right', $elem).click(function(){
                        showRightElement();
                    });

                    $('#sayso-experience-levels-nav-left', $elem).click(function(){
                        showLeftElement();
                    });
                }

                function showRightElement() {
                    if(right.length>=1){
                        left.unshift($current);
                        $next = right.shift();
                        slideLeft($current, $next);
                        $current = $next;
                    }
                }

                function showLeftElement() {
                    if(left.length>=1){
                        right.unshift($current);
                        $next = left.shift();
                        slideRight($current, $next);
                        $current = $next;
                    }
                }

                function slideRight($shown, $toBeShown) {
                    $(function () {
                        $toBeShown.show();
                        $shown.animate({
                            left: '+=500'
                        }, { duration: 500, queue: false });
                        $toBeShown.animate({
                            left: '+=500'
                        }, { duration: 500, queue: false });
                    });
                }

                function slideLeft($shown, $toBeShown) {
                    $(function () {
                        $toBeShown.show();
                        $shown.animate({
                            left: '-=500'
                        }, { duration: 500, queue: false });
                        $toBeShown.animate({
                            left: '-=500'
                        }, { duration: 500, queue: false });
                    });
                }

                initCarousel();
            },
			"about-help-link" : function ($elem, data) {
				$elem.click(function() {
					var value = "0 -" + data['backgroundTop'] + "px";
					$("#sayso-about-help-links").css("background-position", value);
				});

			}

		}
	};

	// these handlers are called when something in the state is updated. All these handlers receive the jquery element that has matched the update pattern,
	// and an animate boolean that determines whether the element should animate or not when showing the update
	// (e.g. when you switch to a tab in your browser, it shouldn't re-animate an increase in points from another tab)
	var updateHandlers = {
		"game": {
			// below handler corresponds to elements like <div class="sayso-element sayso-progress-bar-container"></div>
			"progress-bar-container": function ($elem, data, animate) {
				// do something to the element when updateElements(X, "game", animate) or updateElements(X, null, animate) or updateElements(X) is called
				// the animate variable on the previous line is passed to these handlers, and dictates whether the element should animate or not when showing the update

				// 1. read data['progressBarFor']
				// 2. if value from 1 is "currency", read data from state.games[starbarId].currencies[data['progressBarFor']]
				// 3. ???
				// 4. profit
			}
		},
		"user": {
			// user profile handlers here
			"user-public-name": function ($elem, data, animate) {
				// this $elem should contain a user's name
			},
			"user-image": function ($elem, data, animate) {}
		},
		"notification": {
			// notification handlers here
		},
		"visibility": {
			// visibility handlers here
		}
	};

	// the keys inside extraRequests correspond to both section and tab names
	// e.g. "polls" is a section and "polls-new" is a tab
	var extraRequests = {
		"polls": function (data) {
			// new polls
			var request = this["polls-new"](data);
			// count of completed polls
			request['countPollsCompleted'] = {
				action_class : "survey",
				action : "getSurveyCounts",
				starbar_id : starbarId,
				survey_type : "poll",
				survey_status : "completed"
			};
			return request;
		},
		"polls-new": function (data) {
			return {
				"polls": {
					action_class : "survey",
					action : "getSurveys",
					starbar_id : starbarId,
					survey_type : "poll",
					survey_status : "new"
				},
				"countPollsArchived": {
					action_class : "survey",
					action : "getSurveyCounts",
					starbar_id : starbarId,
					survey_type : "poll",
					survey_status : "archived"
				}
			}
		},
		"polls-completed": function (data) {
			return {
				"polls": {
					action_class : "survey",
					action : "getSurveys",
					starbar_id : starbarId,
					survey_type : "poll",
					survey_status : "completed"
				}
			}
		},
		"polls-archived": function (data) {
			return {
				"polls": {
					action_class : "survey",
					action : "getSurveys",
					starbar_id : starbarId,
					survey_type : "poll",
					survey_status : "archived"
				}
			}
		},
		"surveys": function (data) {
			// new surveys
			var request = this["surveys-new"](data);
			// count of completed surveys
			request['countSurveysCompleted'] = {
				action_class : "survey",
				action : "getSurveyCounts",
				starbar_id : starbarId,
				survey_type : "survey",
				survey_status : "completed"
			};
			return request;
		},
		"surveys-new": function (data) {
			return {
				"surveys": {
					action_class : "survey",
					action : "getSurveys",
					starbar_id : starbarId,
					survey_type : "survey",
					survey_status : "new"
				},
				"countSurveysArchived": {
					action_class : "survey",
					action : "getSurveyCounts",
					starbar_id : starbarId,
					survey_type : "survey",
					survey_status : "archived"
				}
			}
		},
		"surveys-completed": function (data) {
			return {
				"surveys": {
					action_class : "survey",
					action : "getSurveys",
					starbar_id : starbarId,
					survey_type : "survey",
					survey_status : "completed"
				}
			}
		},
		"surveys-archived": function (data) {
			return {
				"surveys": {
					action_class : "survey",
					action : "getSurveys",
					starbar_id : starbarId,
					survey_type : "survey",
					survey_status : "archived"
				}
			}
		},
        "rewards": function (data) {
            return {
                "rewards": {
                    starbar_id : starbarId,
                    action_class : "game",
                    action : "getStarbarGoods"
                }
            };
        },
		"user-profile" : function (data) {
			var request = [];
			request['countPolls'] = {
				action_class : "survey",
				action : "getSurveyCounts",
				starbar_id : starbarId,
				survey_type : "poll",
				survey_status : "new"
			};
			request['countSurveys'] = {
				action_class : "survey",
				action : "getSurveyCounts",
				starbar_id : starbarId,
				survey_type : "survey",
				survey_status : "new"
			};
			return request;
		}
	}
})(this, jQuery, sayso.module.state, sayso.module.api, sayso.module.Handlebars, sayso.module.frameComm, sayso.module.config);
