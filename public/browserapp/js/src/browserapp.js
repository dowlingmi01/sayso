sayso.module.browserapp = (function(global, $, state, comm, Handlebars) {
	$(global.document).on('sayso:state-login sayso:state-logout sayso:state-ready', initApp);

	var starbarId;
	var tabId = "abc"; // @todo set this to unique tab ID id using browser extension
	var userMode = "logged-out";
	var $nav, $sectionContainer, $section;
	var currentSection, currentTabContainers, timeoutIdSectionLoadingSpinner;


	function initApp() {
		// @todo fix line below... doesn't work on portal
		// starbarId = state.state.currentStarbarId;
		starbarId = 4;

		if ($nav && $nav.length) {
			$nav.remove();
		}

		if (state.state.loggedIn) {
			userMode = "logged-in";

			loadApp();
		} else if (global.sayso.webportal) {
			userMode = "tour";

			state.apiCall({
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

		state.apiAddRequest('markup', {
			action_class : "markup",
			action : "getMarkup",
			starbar_id : starbarId,
			app: "browserapp",
			key : "section-" + section
		});

		// grab any extra requests associated with this section
		if (userMode == "logged-in" && section in extraRequests) {
			var extraRequestsForThisSection = extraRequests[section](clickedElementData); // extraRequests[section] is a function

			for (var request in extraRequestsForThisSection) {
				state.apiAddRequest(request, extraRequestsForThisSection[request]);
			}
		}

		state.apiSendRequests(function(response){
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
			processMarkupIntoContainer($tab, markup, templateData);
			$tab.show();
		} else {
			// no templateData passed, perform extra requests, if any
			if (tabName in extraRequests) {
				var extraRequestsForThisTab = extraRequests[tabName](clickedElementData); // extraRequests[section] is a function

				for (var request in extraRequestsForThisTab) {
					state.apiAddRequest(request, extraRequestsForThisTab[request]);
				}
				state.apiSendRequests(function(response){
					// if the loader wasn't shown yet, don't show it
					processMarkupIntoContainer($tab, markup, response.responses);
					$tab.show();
				});
			} else { // no templateData!
				processMarkupIntoContainer($tab, markup);
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
			var estimatedPollHeight = 62 + parseInt(Math.ceil(data.surveyNumberOfAnswers / 2.0) * 32); // 62 base height + 32 per row of answers -- Note that this height estimate is updated after the iframe loads
			if (!$poll.css('height') || $poll.css('height') == "0px" || $poll.css('height') == "auto") $poll.css('height', estimatedPollHeight);

			$poll.slideDown(500);

			// loadPoll calls openPoll again when the iframe is done loading
			loadPoll($poll, $container, $loadingElement, data);

			return;
		} else {
			$container.addClass("sayso-poll-container-current");

			if ($container.hasClass("sayso-poll-container-loading")) { // we already did a slideDown to show the loading element
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
	}

	function loadPoll($poll, $container, $loadingElement, data) {
		// set up iframe listeners
		// set up iframe
		$iframe = $('<iframe class="sayso-poll-iframe" id="sayso-poll-iframe-'+data.surveyId+'"></iframe>');
		$iframe.css('height', '100%');
		$iframe.css('width', '100%');

		$poll.append($iframe);

		// wait for iframe to load
		// @todo replace setTimeout with iframe:ready bind/trigger or equivalent
		setTimeout(function() {
			$container.data('iframeLoadCompleted', true);
			data.iframeLoadCompleted = true;

			// receive (and set) poll height from iframe when it's done rendering
			data.pollHeight = 100 + Math.floor((Math.random()*200)); // @todo temporary random amount -- replace with amount returned from iframe
			$iframe.css('height', data.pollHeight);

			openPoll($container, data, $loadingElement, true);
		}, Math.floor((Math.random()*1000))); // fake load delay, up to 1 second
	}

	function closePoll($container) {
		var $poll = $('.sayso-poll', $container).first();
		$poll.slideUp(500);
		$container.removeClass("sayso-poll-container-loading sayso-poll-container-current");
	}

	function completePoll($container) {
		//@todo!

		// show the completed tab link in case this is the first poll this user has completed
		$('#sayso-completed-tab-link').show();
	}

	function processMarkupIntoContainer($container, markup, templateData, runPreTemplateHandlers) {
		var template;

		if (runPreTemplateHandlers) {
			var $tempContainer = $('<div></div>');

			$tempContainer.html(markup);

			prepareElements($tempContainer, "pre-template");

			// compile the markup into a handlebars template
			template = Handlebars.compile($tempContainer.html().replace(/{{&gt;/g, "{{>"));
		} else {
			template = Handlebars.compile(markup);
		}

		// always attach the game to the template
		templateData.game = state.state.game;

		// pass the api response (templateData) to the template as data, and render
		$container.append(template(templateData));

		// prepare sayso elements (passing on templateData to anything that may need it... tabs within tabs?)
		prepareElements($container, "post-template", templateData);
	}

	var handlebarsHelpers = {
		"currency-name-highlighted": function(currency) {
			// @todo add description to game.currencies
			return new Handlebars.SafeString('<span class="sayso-element sayso-highlight sayso-tooltip" data-tooltip-title="'+state.state.game.currencies[currency].description+'">'+state.state.game.currencies[currency].name+'</span>');
		},
		"currency-name": function(currency) {
			return state.state.game.currencies[currency].name;
		},
		"image-path": function(fileName) {
			return "/browserapp/images/" + state.state.starbar.short_name + "/" + fileName;
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
				Handlebars.registerPartial(data['partialId'], $elem.html().replace(/{{&gt;/g, "{{>"));

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

					// need to replace "{{&gt;" with "{{>" because jQuery is a big jerk
					tabs[$tab.data('tab')] = $tab.html().replace(/{{&gt;/g, "{{>");

					// empty the tab, so it isn't processed by handlebars
					$tab.html("");
				});
				currentTabContainers[$elem.attr('id')] = tabs;
			}
		},
		"post-template": {
			"section-link": function ($elem, data) {
				$elem.click(function() {
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
				$elem.click(function() {
					openTab($('#' + data['tabContainer']), data['tab'], null, $elem.data());
				});
			},
			"scrollable": function ($elem, data) {
				// @todo make $elem have a custom JS scrollbar!
			},
			"tooltip": function ($elem, data) {
				// @todo show data['tooltipTitle'] 'neatly' when you roll over this element
				$elem.attr('title', data['tooltipTitle']); // hack
			},
			"poll-container": function ($elem, data) {
				var $pollHeader = $('.sayso-poll-header', $elem);
				$pollHeader.click(function() {
					openPoll($elem, data); // note that openPoll takes the container ($elem) as a parameter, not $pollHeader
				});
			},
            "reward-item": function ($elem, data) {
                $elem.click(function() {
                    //black out the div and display the {{cant_purchase_message}}
                    $("#sayso-section-reward-redeem").show();
                    $("#sayso-reward-item-redeem-step").html($("#sayso-reward-step-one").html());

                    $("#reward-name").html(data['rewardName']);
                    $("#reward-price").html(data['rewardPrice']);
                    $("#reward-img").attr("src", data['rewardImgSrc']);
                    $("#reward-comment").html(data['rewardComment']);
                    $("#sayso-reward-item-redeem-submit").click(function(){
                        if (data['rewardType'] == "token")
                        {
                            $("#sayso-reward-item-redeem-step").html($("#sayso-reward-step-two-token").html());
                            var qty = $('input[name="sayso-reward-item-order-quantity-select"]').val()
                        } else {
                            $("#sayso-reward-item-redeem-step").html($("#sayso-reward-step-two-shipping").html());
                            //prepare shippingData
                            var shippingData = array();
                            shippingData["order_first_name"] = $('input[name="order_first_name"]').val();
                            shippingData["order_last_name"] = $('input[name="order_last_name"]').val();
                            shippingData["order_address_1"] = $('input[name="order_address_1"]').val();
                            shippingData["order_address_2"] = $('input[name="order_address_2"]').val();
                            shippingData["order_city"] = $('input[name="order_city"]').val();
                            shippingData["order_state"] =$('input[name="order_state"]').val();
                            shippingData["order_zip"] = $('input[name="order_zip"]').val();
                            shippingData["order_country"] = $('input[name="order_country"]').val();
                            shippingData["order_phone"] = $('input[name="order_phone"]').val();
                        }
                        $("#sayso-reward-redeem-submit").click(function(){
                            state.apiCall({
                                action_class : "game",
                                action : "redeemReward",
                                starbar_id : starbarId,
                                game_asset_id: data['gameAssetId'],
                                shipping: shippingData,
                                quantity: qty
                            }, function(response){
                                //do something
                            }); // update game

                        });
                    });
                });
            },

			//displays the next promo image
			"next-promo" : function ($elem, data) {
				$elem.click(function() {
					$("#" + data["thisImage"]).hide();
					$("#" + data["nextImage"]).show();
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
				// 2. if value from 1 is "currency", read data from state.games[starbarId].currencies[data['currencyType']]
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
			request['countPollsArchived'] = {
				action_class : "survey",
				action : "getSurveyCounts",
				starbar_id : starbarId,
				survey_type : "poll",
				survey_status : "archived"
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
        "rewards": function (data) {
            return {
                "rewards": {
                    starbar_id : starbarId,
                    action_class : "game",
                    action : "getStarbarGoods"
                }
            };
        }
	}

})(this, jQuery, sayso.module.state, sayso.module.comm, sayso.module.Handlebars)
;
