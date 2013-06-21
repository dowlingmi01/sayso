sayso.module.browserapp = (function(global, $, state, Handlebars) {
	$(global.document).on('sayso:state-login sayso:state-logout sayso:state-ready', initApp);

	var starbarId = 4;
	var userMode = "logged-out";
	var $nav, $sectionContainer, $section;
	var currentSection, currentTabContainers, timeoutIdSectionLoadingSpinner;


	function initApp() {

		if (state.state.loggedIn) {
			userMode = "logged-in";

			render();

		} else if (global.sayso.webportal) {
			userMode = "tour";

			state.apiCall({
				action_class : "markup",
				action : "getMarkup",
				starbar_id : starbarId,
				app: "browserapp",
				key : "nav"
			}, renderForTour); // renderForTour receives the response
		} else {
			// @todo do something for logged out mode, e.g. redirect to portal for login, or take them to tour page,
			// @todo  or render bar/icon and show login dialog if they click something
		}
	}


	function render() {
		$('body').append(state.state.starbar.markup);
		initNav();
	}


	function renderForTour(response) {
		if (
			!response
				|| !response.responses
				|| !response.responses.default
				|| !response.responses.default.variables
				|| !response.responses.default.variables.markup
			) {
			alert("Error loading files, please try again later.");
		}

		$('body').append(response.responses.default.variables.markup);

		// hide the hide button, since we're on the portal
		$('#sayso-nav-hide-button').hide();
		initNav();
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

	function processMarkupIntoContainer($container, markup, templateData, runPreTemplateHandlers) {
		var template;

		if (runPreTemplateHandlers) {
			var $tempContainer = $('<div></div>');

			$tempContainer.html(markup);

			prepareElements($tempContainer, "pre-template");

			// compile the markup into a handlebars template
			template = Handlebars.compile($tempContainer.html().replace("{{&gt;", "{{>"));
		} else {
			template = Handlebars.compile(markup);
		}

		// pass the api response (templateData) to the template as data, and render
		$container.append(template(templateData));

		// prepare sayso elements (passing on templateData to anything that may need it... tabs within tabs?)
		prepareElements($container, "post-template", templateData);
	}

	// "section-link" corresponds to elements that have the class "sayso-section-link" (as well as "sayso-element")
	// the "data" variable is, by default, simply $elem.data();
	var prepareHandlers = {
		// note that pre-template handlers are NOT run on markup/elements inside tabs
		// therefore, partials needed for tabs should be outside the tab context
		"pre-template": {
			"partial": function ($elem, data) {
				// partial found, register the
				Handlebars.registerPartial(data['partialId'], $elem.html());

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
					tabs[$tab.data('tab')] = $tab.html().replace("{{&gt;", "{{>");

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
            "reward-redeem": function ($elem, data) {
                $elem.click(function() {
                    //hit the game endpoint redeemReward
                    state.apiCall({
                        action_class : "game",
                        action : "redeemReward",
                        starbar_id : starbarId,
                        game_asset_id: data['id']
                    }, function(response){
                        //do something
                    }); // update game
                });
            },
            "reward-item": function ($elem, data) {
                $elem.click(function() {
                    //black out the div and display the {{cant_purchase_message}}
                    $("#sayso-section-reward-redeem").show();
                    $("#sayso-reward-item-redeem-step").html($("#sayso-reward-step-one").html());

                    $("#reward-name").html(data['rewardName']);
                    $("#reward-price").html(data['rewardPrice']);
                    $("#reward-img-src").html(data['rewardImgSrc']);
                    $("#reward-comment").html(data['rewardComment']);
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
		}
	};

	// the keys inside extraRequests correspond to both section and tab names
	// e.g. "polls" is a section and "polls-new" is a tab
	var extraRequests = {
		"polls": function (data) {
			return this["polls-new"](data);
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
        "poll": function (data) {
            return {
                "poll": {
                    starbar_id : starbarId,
                    action_class : "survey",
                    action : "getSurvey",
                    survey_id : data['surveyId']
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

})(this, jQuery, sayso.module.state, sayso.module.Handlebars)
;
