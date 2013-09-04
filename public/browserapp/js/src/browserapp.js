sayso.module.browserapp = (function(global, $, state, api, Handlebars, comm, frameComm, config) {
	var starbarId;
	var userMode = "logged-out";
	var $nav, $sectionContainer, $section;
	var currentSection, currentTabContainers, timeoutIdSectionLoadingSpinner;
	var confirmBeforeClosingSection, $closeSectionConfirmationOverlay;
	var functionBackup = {};
	var portalListenersPaused = false;

	function initApp() {
		if ($nav && $nav.length) {
			$nav.remove();
		}

		if (state.state.loggedIn && state.state.starbar) {
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
		// render nav and initNav()
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
		} // else {}  potentially we can handle logged out mode here

		initNav();

		initStateListeners();

		if (userMode == "tour") {
			initPortalListeners();
		}
	}


	function initNav() {
		$nav = $('#sayso-app-container');
		$sectionContainer = $('#sayso-section-container');
		$section = $('#sayso-section');
		$closeSectionConfirmationOverlay = $('#sayso-close-section-confirmation-overlay');

		if (config.webportal) {
			// hide the hide button, since we're on the portal
			$('#sayso-nav-hide-button', $nav).hide();
		} else if (state.state.visibility == "stowed")
			hideNav(false);

		if (!config.webportal || userMode != "tour") {
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
		}

		for (var helper in handlebarsHelpers) {
			Handlebars.registerHelper(helper, handlebarsHelpers[helper]);
		}

		// Currently nav isn't run through the templating system... this should probably be done there
		if ('surveyCounts' in state.state
			&& 'mission' in state.state.surveyCounts
			&& state.state.surveyCounts.mission > 0
		) {
			var $spotlightButton = $('div.sayso-nav-theme-button-container[data-section=trailer]', $nav);
			$spotlightButton.data('section', 'missions');
			$spotlightButton.prepend('<div class="sayso-nav-theme-button-overlay-missions"></div>');
		}

		prepareElements();
	}


	/*
	 this function is called when:
	 1) initially preparing nav
	 2) a section is opened (i.e. new markup is loaded)

	 $container is the element we look for elements in
	 handlerCollection is "pre-template" or "post-template" (default)
	 */
	function prepareElements($container, templateData) {
		if (!$container) $container = $nav;

		var $elements = $('.sayso-element', $container);
		var $element;

		$elements.each(function() {
			$element = $(this);
			for (var elementType in elementHandlers) {
				if ($element.hasClass('sayso-' + elementType)) {
					elementHandlers[elementType]($element, $element.data(), templateData);
				}
			}
		});

		if (userMode != "logged-in") return;

		// update all updateTypes i.e. "game", "user", "notifications"
		updateElements($container);
		/*
		updateElements($container, "game");
		updateElements($container, "profile");

		// only notifications should animate on initial load... game and user data shouldn't animate initially
		// (not sure user profile data will ever animate anyway)
		if ($container === $nav)
			updateElements($container, "notifications", true);
		*/
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

		if (updateType != "visibility") {
			$elements.each(function() {
				$element = $(this);
				for (var handlerCollection in updateHandlers) {
					// if no updateType is specified, update all handlerCollections (i.e. "game", "profile", "notifications", ...)
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

		// handle visibility separately
		if ($container === $nav && (!updateType || updateType == "visibility")) {
			if (state.state.visibility == 'stowed' && !config.webportal) { // don't hide on portal
				hideNav(false);
			} else {
				showNav(false);
			}
		}
	}

	function hideNav(updateState) {
		if (updateState) { // user initiated visibility change here, trigger it and do nothing (listener will trigger animation)
			state.setVisibility("stowed");
		} else {

			// note that closeSection() returns false if a confirmation dialog appears to the user,
			// and the user chooses not to close the section, so we should stop here if that happens
			if (!closeSection()) return;

			$nav.addClass("sayso-app-container-stowed");
		}
	}

	function showNav(updateState) {
		if (updateState) { // user initiated visibility change here, trigger it and do nothing (listener will trigger animation)
			state.setVisibility("open");
		} else {
			$nav.removeClass("sayso-app-container-stowed");
		}
	}


	// to be called when switching to this browser tab/window
	// @todo call this function!
	function switchToWindow() {
		// update all state-bound elements, without animating them, since the update should have already appeared on another tab
		// @todo don't do this, or do it partially (e.g. notification icon?), if the starbar is hidden
		updateElements();
	}


	function initStateListeners() {
		for (var state in stateListeners) {
			$(global.document).on('sayso:state-' + state, stateListeners[state]);
		}
	}

	function initPortalListeners() {
		var oldHashChangeFunction = global.onhashchange;

		global.onhashchange = function() {
			var section;

			oldHashChangeFunction(); // call old function (i.e. the portal's hashChanged function)

			if (!portalListenersPaused) {
				closeSection();

				switch (global.location.hash) {
					case "#content/tour-polls":
						section = "polls";
						break;
					case "#content/tour-surveys":
						section = "surveys";
						break;
					case "#content/tour-spotlight":
						section = "trailer";
						break;
					case "#content/tour-giveaways":
						section = "promos";
						break;
					case "#content/tour-profile":
						section = "user-profile";
						break;
					case "#content/tour-account":
						section = "experience";
						break;
					case "#content/tour-rewards-center":
						section = "rewards";
						break;
					default:
						return;
				}

				openSection({
					section: section,
					skipHashChange: true
				});
			}

			portalListenersPaused = false; // listen next time
		}
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
			// note that closeSection() returns false if a confirmation dialog appears to the user,
			// and the user chooses not to close the section, so we should stop here if that happens
			if (!closeSection()) return;
			return;
		} else if (currentSection) {
			if (!closeSection()) return;
		}

		if (section == "survey") {
			enableConfirmationBeforeClosingSection("survey");
		}

		// user tried to open about section while the nav is stowed, so show the nav
		if (state.state.visibility == "stowed") {
			showNav(true);
			return;
		}

		if (userMode == "tour" && !clickedElementData['skipHashChange']) {
			portalListenersPaused = true;
			switch(section) {
				case "tour-about":
					global.location.hash = "#content/tour-intro";
					return; // Note the *return* and not a break. This is because we do not open a
				case "tour-polls":
				case "tour-surveys":
					global.location.hash = "#content/" + section;
					break;
				case "tour-trailer":
					global.location.hash = "#content/tour-spotlight";
					break;
				case "tour-promos":
					global.location.hash = "#content/tour-giveaways";
					break;
				case "tour-user-profile":
					global.location.hash = "#content/tour-profile";
					break;
				case "tour-experience":
					global.location.hash = "#content/tour-account";
					break;
				case "tour-rewards":
					global.location.hash = "#content/tour-rewards-center";
					break;
				default:
					return; // no access
			}
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

			if (userMode == "logged-in") {
				processMarkupIntoContainer($section, response.responses.markup.variables.markup, response.responses, "main");
			} else { // no template stuff on tour
				$section.append(response.responses.markup.variables.markup);
			}

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
		$sectionContainer.fadeTo(0, 0).hide();
		$sectionContainer.removeClass();
		$section.html("");
		currentSection = null;

		// reset the partials
		Handlebars.partials = {};

		// important to return true. In enableConfirmationBeforeClosingSection(), closeSection is overwritten with a function that returns false if the user cancels the close operation
		return true;
	}

	function openTab($tabContainer, tabName, templateData, clickedElementData) {
		var markup = "{{>"+tabName+"}}";

		if (!clickedElementData) clickedElementData = {};

		if ('partialId' in clickedElementData) markup = "{{>"+clickedElementData['partialId']+"}}";

		var $tab = $("div.sayso-tab[data-tab="+tabName+"]", $tabContainer).first().html('');

		$("div.sayso-tab", $tabContainer).hide();

		if (templateData) {
			processMarkupIntoContainer($tab, markup, templateData);
			$tab.show();
		} else {
			// no templateData passed, perform extra requests, if any
			if (tabName in extraRequests) {
				var extraRequestsForThisTab = extraRequests[tabName](clickedElementData);

				api.doRequests(extraRequestsForThisTab, function(response){
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

	function openSurvey(data) {
		if (!data['surveyId']) return;
		if ('surveySize' in data || !data['surveySize']) data['surveySize'] = "large"; // default to profile survey size
		data['section'] = 'survey';

		// using closeSection() to allow a survey link on the survey section itself
		// otherwise, in that situation, the link would just close the survey section (because opening the same section twice closes it)
		// note that closeSection() returns false if a confirmation dialog appears to the user,
		// and the user chooses not to close the section, so we should stop here if that happens
		if (!closeSection()) return;

		// add a class for the survey size for the css rules -- note that closeSection() removes all $sectionContainer's classes
		$sectionContainer.addClass('sayso-section-survey-' + data['surveySize']);

		openSection(data);
	}

	function openTrailer(data) {
		data['section'] = 'trailer';

		// using closeSection() to allow a survey link on the survey section itself
		// otherwise, in that situation, the link would just close the survey section (because opening the same section twice closes it)
		// note that closeSection() returns false if a confirmation dialog appears to the user,
		// and the user chooses not to close the section, so we should stop here if that happens
		if (!closeSection()) return;

		openSection(data);
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
			$poll.animate({'height': data.pollHeight}, 250);
			$loadingElement.fadeTo(250, 0, function() {
				$loadingElement.remove();
			});
		} else { // poll was already loaded and proper height was set, so just slide the existing poll back down
			$poll.slideDown(500);
		}
	}

	function createIframe(url, callback, useParam) {
		if (!url) url = '//' + config.baseDomain + '/browserapp/iframe.html';

		var $iframe = $('<iframe class="sayso-iframe" scrolling="no"></iframe>');
		var frameId = frameComm.setURL($iframe, url, useParam);
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
			var iframe = createIframe(null, function(unused, dataFromIframe) {
				frameComm.fireEvent(iframe.frame_id, 'init-action', {action: 'display-poll', starbarId: starbarId, starbar_short_name: state.state.starbar['short_name'], poll: poll});
			});

			$(global.document).on('sayso:iframe-poll-loaded', function(unused, dataFromIframe) {
				if( dataFromIframe.frame_id === iframe.frame_id ) {
					// receive (and set) poll height from iframe when it's done rendering
					if (dataFromIframe.data.height) {
						data.pollHeight = 40 + dataFromIframe.data.height;
						iframe.$element.css('height', dataFromIframe.data.height); // note that $poll is still the old height, and that is animated in openPoll

						if (data.iframeLoadCompleted) { // if the iframe was already loaded, we are just resizing
							$poll.animate({'height': dataFromIframe.data.height}, 100);
						} else {
							data.iframeLoadCompleted = true;
							openPoll($container, data, $loadingElement, true);
						}
					}
				}
			});

			$(global.document).on('sayso:iframe-poll-completed', function(unused, dataFromIframe) {
				if( dataFromIframe.frame_id === iframe.frame_id ) {
					completePoll($poll, poll);
				}
			});

			$poll.append(iframe.$element);
		});
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

		if (!$('#sayso-section-header ul li[data-tab="polls-completed"]').length) {
			$('#sayso-section-header ul').append('<li class="sayso-element sayso-tab-link" data-tab-container="sayso-section-body" data-tab="polls-completed">Completed</li>');
		}

		// show the completed tab *link* in case this is the first poll this user has completed
		$('#sayso-completed-tab-link', $section).show();
	}


	function enableConfirmationBeforeClosingSection (str) {
		if (!confirmBeforeClosingSection) {
			confirmBeforeClosingSection = true;

			var confirmBeforeClosingSectionMessage = "If you leave this " + str + ", you will lose any unsaved progress. Are you sure you want to leave this " + str + "?";

			if (global.onbeforeunload) {
				functionBackup.windowOnBeforeUnload = global.onbeforeunload;
			}

			global.onbeforeunload = function() { return confirmBeforeClosingSectionMessage };

			$closeSectionConfirmationOverlay.fadeTo(0, 0);
			$closeSectionConfirmationOverlay.show();
			$closeSectionConfirmationOverlay.fadeTo(200, 0.6);

			functionBackup.closeSection = closeSection;
			closeSection = function() {
				if (!confirm(confirmBeforeClosingSectionMessage)) // make sure user wants to leave
					return;

				disableConfirmationBeforeClosingSection();

				// return true to confirm that closeSection() succeeded
				return true;
			}

			$closeSectionConfirmationOverlay.click(function(event){
				event.stopPropagation();

				closeSection(); // closeSection (the new one, above) does the confirming
			});
		}
	}

	function disableConfirmationBeforeClosingSection (keepSectionOpen) {
		if (confirmBeforeClosingSection) {
			confirmBeforeClosingSection = false;

			if (functionBackup.windowOnBeforeUnload) {
				global.onbeforeunload = functionBackup.windowOnBeforeUnload;
				delete functionBackup.windowOnBeforeUnload;
			} else {
				global.onbeforeunload = null;
			}

			$closeSectionConfirmationOverlay.unbind('click');
			$closeSectionConfirmationOverlay.fadeTo(200, 0, function() {
				$closeSectionConfirmationOverlay.hide();
			});

			closeSection = functionBackup.closeSection;
			if (!keepSectionOpen)
				closeSection();
		}
	}


	function processMarkupIntoContainer($container, markup, templateData, initialPartial) {
		if (initialPartial) { // if specified, all the content must be contained in partials (i.e. script tags) inside the markup
			var $temp = $('<div></div>');
			$temp.html(markup);

			var $partials = $('script', $temp);
			var $partial;

			Handlebars.partials = {};

			$partials.each(function() {
				$partial = $(this);
				Handlebars.registerPartial($partial.data('partialId'), $partial.html())
			});

			$temp.remove();

			markup = "{{>"+initialPartial+"}}";
		}

		var template = Handlebars.compile(markup);

		if (typeof templateData !== "object" || !templateData) templateData = {};

		// always attach the state to the template
		templateData.state = state.state;
		templateData.extensionPresent = comm.extensionPresent;

		// pass the api response (templateData) to the template as data, and render
		$container.append(template(templateData));

		// prepare sayso elements (passing on templateData to anything that may need it...)
		prepareElements($container, templateData);
	}

	var handlebarsHelpers = {
		"console-log": function () {
			console.log(arguments);
		},
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
				return state.state.game.levels[state.state.game.level].name;
		},
        "user-level-name": function () {
            return state.state.game.levels[state.state.game.level].name;
        },
        "next-exp-threshold": function(game) {
            return game.levels[game.level+1].threshold;
        },
		"image-path": function(fileName) {
			return "/browserapp/images/" + state.state.starbar.short_name + "/" + fileName;
		},
		"record-field" : function(recordSet, recordId, fieldName) {
			var record = $.grep(recordSet, function (r){ return r.id === recordId; });
			if (record && fieldName in record)
				return record[fieldName];
		},
		"object-field" : function() {
			if (arguments.length < 3)
				return "";

			var someObject = arguments[0];

			for (var i = 1; i < arguments.length - 1; i++) {
				if (arguments[i] in someObject) {
					someObject = someObject[arguments[i]];
				} else {
					return "";
				}
			}

			return someObject; // no longer an object, in theory
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
        },
		'every-nth': function(context, every, options) { // adapted from http://jaketrent.com/post/every-nth-item-in-handlebars-loop/
			var fn = options.fn, inverse = options.inverse;
			var ret = "";
			if(context && context.length > 0) {
				for(var i=0, j=context.length; i<j; i++) {
					var modZero = i % every === 0;
					ret = ret + fn($.extend({}, context[i], {
						isModZero: modZero,
						isModZeroNotFirst: modZero && i > 0,
						isLast: i === context.length - 1
					}));
				}
			} else {
				ret = inverse(this);
			}
			return ret;
		}
	};

	// "section-link" corresponds to elements that have the class "sayso-section-link" (as well as "sayso-element")
	// the "data" variable is, by default, simply $elem.data();
	var elementHandlers = {
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
		"tab-link": function ($elem, data, templateData) {
			var templateDataToPass = null;

			if ('passTemplateDataToTab' in data && data['passTemplateDataToTab'])
				templateDataToPass = templateData;

			// note that there is no templateData passed in this case, since any needed data is requested via the api, from extraRequests
			// this includes re-opening the default tab, for example
			$elem.click(function(e) {
				e.preventDefault();
				openTab($('#' + data['tabContainer']), data['tab'], templateDataToPass, $elem.data());
			});
		},
		"scrollable": function ($elem, data) {
			// @todo
		},
		"tooltip": function ($elem, data) {
			// @todo show data['tooltipTitle'] 'neatly' when you roll over this element
			$elem.attr('title', data['tooltipTitle']); // hack
		},
		"placeholder": function () {
			$.placeholder.shim({context: $nav});
		},
		"hide-button": function ($elem) {
			$elem.click(function() {
				hideNav(true);
			});
		},
		"carousel-container": function ($elem) {
			var $groups = $('.sayso-carousel-group', $elem);
			var $prevButton = $('.sayso-carousel-prev', $elem);
			var $nextButton = $('.sayso-carousel-next', $elem);
			var currentIndex = -1;

			$groups.hide();

            if ($groups.length === 0) {
                $prevButton.hide();
                $nextButton.hide();
            }

			$groups.each(function(index) {
				var $group = $(this);
				if ($group.hasClass('sayso-carousel-active') || $('.sayso-carousel-active', $group).length > 0) {
					currentIndex = index;
					$group.show();
					return false;
				}
			});

			if (currentIndex == -1 && $groups.length) {
				goNext(); // show the first one
			}

			function goNext(amount) {
				if (typeof amount != "number") {
					amount = 1;
				}

				currentIndex += amount;
				currentIndex = currentIndex % $groups.length;

				$groups.hide();
				$groups.eq(currentIndex).show();
			}

			function goPrev(amount) {
				if (typeof amount != "number") {
					amount = 1;
				}

				currentIndex -= amount;
				if (currentIndex < 0)
					currentIndex += $groups.length;

				$groups.hide();
				$groups.eq(currentIndex).show();
			}

			if ($groups.length < 2) {
				$prevButton.addClass('sayso-disabled');
				$nextButton.addClass('sayso-disabled');
			} else {
				$prevButton.click(goPrev);
				$nextButton.click(goNext);
			}
		},
		"poll-container": function ($elem, data) {
			var $pollHeader = $('.sayso-poll-header', $elem);
			$pollHeader.click(function() {
				openPoll($elem, data); // note that openPoll takes the container ($elem) as a parameter, not $pollHeader
			});
		},
		"trailer-link": function ($elem, data) {
			$elem.click(function() {
				openTrailer(data);
			});
		},
		"trailer-video-container": function ($elem, data, templateData) {
			var trailer = templateData['trailers'].variables.survey;
			var $firstQuestionContainer = $('#sayso-trailer-first-question', $section);
			var $secondQuestionContainer = $('#sayso-trailer-second-question', $section);
			var $videoCarousel = $('#sayso-trailer-carousel-container', $section);

			var iframe = createIframe(null, function(unused, dataFromIframe) {
				frameComm.fireEvent(iframe.frame_id, 'init-action', {action: 'display-video', video_provider: "youtube", video_key: trailer['trailer_info']['video_key']});
			}, true);

			var firstQuestionId = trailer['questions'][0]['id'];
			var secondQuestionId = trailer['questions'][1]['id'];

			var firstChoiceId, secondChoiceId;

			$(global.document).on('sayso:iframe-video-start', function(unused, dataFromIframe) {
				if( dataFromIframe.frame_id === iframe.frame_id ) {
					$videoCarousel.addClass('sayso-trailer-carousel-container-lowered');
					$firstQuestionContainer.fadeTo(0, 0).show().fadeTo(200, 1);
				}
			});

			$(global.document).on('sayso:iframe-video-done', function(unused, dataFromIframe) {
				if( dataFromIframe.frame_id === iframe.frame_id ) {
					$('input', $firstQuestionContainer).change(function() {
						firstChoiceId = $(this).val();
						$('h2', $sectionContainer).hide();
						$firstQuestionContainer.hide();
						$secondQuestionContainer.show();
					});

					$('input', $secondQuestionContainer).change(function() {
						secondChoiceId = $(this).val();
						submitResponses(function() {

						})
					});

					$('.sayso-trailer-question-disabler', $sectionContainer).hide();
					$('input', $sectionContainer).removeAttr('disabled');
					$videoCarousel.fadeTo(0, 0).hide().removeClass('sayso-trailer-carousel-container-lowered');
				}
			});

			function submitResponses() {
				var request = {
					action_class : "survey",
					action : "updateSurveyResponse",
					starbar_id : starbarId,
					survey_id : trailer['id'],
					survey_response_id : trailer['survey_response_id'],
					survey_data: {
						answers: {}
					}
				};

				request.survey_data.answers[firstQuestionId] = firstChoiceId;
				request.survey_data.answers[secondQuestionId] = secondChoiceId;

				api.doRequest(request, function(response) {
					if (response.responses.default.variables.success) {
						var $textAndQuestionsContainer = $('#sayso-trailer-title-and-questions', $sectionContainer);
						$textAndQuestionsContainer.html('');
						processMarkupIntoContainer($textAndQuestionsContainer, "{{>trailer-completed}}", trailer);
						$videoCarousel.show().fadeTo(200, 1);
					}
				});
			}

			$elem.append(iframe.$element);
		},
		"survey-link": function ($elem, data) {
			$elem.click(function() {
				openSurvey(data);
			});
		},
		"survey-iframe-container": function ($elem, data, templateData) {
			var survey = templateData['survey'].variables.survey;
			var surveyLink = "//www.surveygizmo.com/s3/" + survey['external_id'] + "/" + survey['external_key'] +
				"?starbar_short_name=" + state.state.starbar['short_name'] +
				"&srid=" + survey['survey_response_id'] +
				"&size=" + survey['size'];
			if (state.state.profile.type === "test" || config.baseDomain !== "app.saysollc.com") {
				surveyLink += "&testing=true"
				if (config.baseDomain !== "app.saysollc.com")
					surveyLink += "&base_domain=" + config.baseDomain;
			} else {
				surveyLink += "&testing=false"
			}

			var iframe = createIframe(surveyLink, function(unused, dataFromIframe) {
				frameComm.fireEvent(iframe.frame_id, 'init-action', {action: 'display-survey', starbarId: starbarId, starbar_short_name: state.state.starbar['short_name'], survey: survey});
			}, true);

			$(global.document).on('sayso:iframe-survey-done', function(unused, dataFromIframe) {
				if( dataFromIframe.frame_id === iframe.frame_id ) {
					api.doRequest({
						action_class : "survey",
						action : "updateSurveyStatus",
						starbar_id : starbarId,
						survey_id : survey['id'],
						survey_response_id : survey['survey_response_id'],
						survey_status : dataFromIframe.data.survey_status
					}, function(response) {
						var finalTemplateData = response.responses;
						finalTemplateData.survey = survey;
						$elem.html('');
						processMarkupIntoContainer($elem, "{{>survey-"+dataFromIframe.data.survey_status+"}}", finalTemplateData);
						disableConfirmationBeforeClosingSection(true);
					});
				}
			});

			$elem.append(iframe.$element);
		},
		"disable-confirmation-before-closing-section": function($elem, data) {
			disableConfirmationBeforeClosingSection(data['keepSectionOpen']);
		},
		"get-satisfaction-iframe-container": function ($elem, data) {
			var iframe = createIframe(null, function(unused, dataFromIframe) {
				frameComm.fireEvent(iframe.frame_id, 'init-action', {action: 'display-get-satisfaction'});
			});

			$elem.append(iframe.$element);
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
			$elem.on('click', function() {
				var shippingData = {};
				var quantity = 0;

				//Dot notation not used due to reserved keyword 'type'
				if (templateData['type'] === "token") {
					quantity = $('select[name="sayso-reward-item-order-quantity-select"]', $nav).val();
					api.doRequest({
						action_class : "game",
						action : "redeemReward",
						starbar_id : starbarId,
						game_asset_id: templateData.id,
						shipping: shippingData,
						quantity: quantity
					}, function(response){
						if(response.error_code === 0) {
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
				$elem.off('click');
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
			//TODO: the twitter connect will need a two step process. one to hit the endpoint that gets the oauth, then the one to hit the connectSocialNetwork endpoiont
			switch (data["network"]) {
				case ("FB") :
					$elem.on("click", (function() {
						api.doRequest({
							action_class : "user",
							action : "connectSocialNetwork",
							starbar_id : starbarId,
							network : data["network"],
						}, function(response){
							if (response.responses.default.errors_returned === undefined) {
								if (response.responses.default.variables.success === true) {
									updateElements();
									$("#sayso-user-profile-social-link-" + data["network"]).css("background-position", "0 -66px");
									$("#sayso-user-profile-social-link-" + data["network"]).off("mouseenter");
									$("#sayso-user-profile-social-link-" + data["network"]).off("mouseleave");
									$("#sayso-user-profile-social-link-" + data["network"]).off("click");
								} else {
									var loginUrl = response.responses.default.variables.login_url;
									window.open(loginUrl);
								}
							} else {
								//notify of error
							}
						});
					}));

					$("#sayso-user-profile-social-link-FB").on("mouseenter", (function() {
						$("#sayso-user-profile-social-link-FB").css("background-position", "0 -66px");
					}));
					$("#sayso-user-profile-social-link-FB").on("mouseleave", (function() {
						$("#sayso-user-profile-social-link-FB").css("background-position", "0 0px");
					}));

					if (state.state.profile.user_socials.facebook !== undefined)
					{
						$("#sayso-user-profile-social-link-FB").css("background-position", "0 -66px");
						$("#sayso-user-profile-social-link-FB").off("mouseenter");
						$("#sayso-user-profile-social-link-FB").off("mouseleave");
						$("#sayso-user-profile-social-link-FB").off("click");
					}
					break;
				case ("TW") :
					$elem.on("click", (function() {
						api.doRequest({
							action_class : "user",
							action : "getTwitterOauthToken",
						}, function(response){
							if (response.responses.default.errors_returned === undefined) {
								if (response.responses.default.variables.success === true) {
									api.doRequest({
										action_class : "user",
										action : "connectSocialNetwork",
										starbar_id : starbarId,
										network : data["network"],
										oauth : {"oauth_token": response.responses.default.variables.oauth_token, "oauth_token_secret": response.responses.default.variables.oauth_token_secret}
									}, function(oauthResponse){
										if (oauthResponse.responses.default.errors_returned === undefined) {
											if (oauthResponse.responses.default.variables.success === true) {
												updateElements();
												$("#sayso-user-profile-social-link-" + data["network"]).css("background-position", "0 -66px");
												$("#sayso-user-profile-social-link-" + data["network"]).off("mouseenter");
												$("#sayso-user-profile-social-link-" + data["network"]).off("mouseleave");
												$("#sayso-user-profile-social-link-" + data["network"]).off("click");
											}
										}
									});
								} else {
									//notify error
								}
							}
						});
					}));



					$("#sayso-user-profile-social-link-TW").on("mouseenter", (function() {
						$("#sayso-user-profile-social-link-TW").css("background-position", "0 -66px");
					}));
					$("#sayso-user-profile-social-link-TW").on("mouseleave", (function() {
						$("#sayso-user-profile-social-link-TW").css("background-position", "0 0px");
					}));

					if (state.state.profile.user_socials.twitter !== undefined)
					{
						$("#sayso-user-profile-social-link-TW").css("background-position", "0 -66px");
						$("#sayso-user-profile-social-link-TW").off("mouseenter");
						$("#sayso-user-profile-social-link-TW").off("mouseleave");
						$("#sayso-user-profile-social-link-TW").off("click");
					}
					break;
			}
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
					}, { duration: 500, queue: true });
					$toBeShown.animate({
						left: '+=500'
					}, { duration: 500, queue: true });
				});
			}

			function slideLeft($shown, $toBeShown) {
				$(function () {
					$toBeShown.show();
					$shown.animate({
						left: '-=500'
					}, { duration: 500, queue: true });
					$toBeShown.animate({
						left: '-=500'
					}, { duration: 500, queue: true });
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
	};

	var stateListeners = {
		"game": function() {
			updateElements(null, "game");
		},
		"profile": function() {
			updateElements(null, "profile");
		},
		"visibility": function() {
			if (!config.webportal) {
				updateElements(null, "visibility");
			}
		},
		"notifications": function() {
			updateElements(null, "notifications");
		},
		"surveyCounts": function() {
			if ('surveyCounts' in state.state
				&& 'mission' in state.state.surveyCounts
				&& state.state.surveyCounts.mission > 0
			) {
				if (! $('.sayso-nav-theme-button-overlay-missions', $nav).length) {
					var $spotlightButton = $('div.sayso-nav-theme-button-container[data-section=trailer]', $nav);
					$spotlightButton.data('section', 'missions');
					$spotlightButton.prepend('<div class="sayso-nav-theme-button-overlay-missions"></div>');
				}
			} else {
				if ($('.sayso-nav-theme-button-overlay-missions', $nav).length) {
					$('.sayso-nav-theme-button-overlay-missions', $nav).remove();
					$('div.sayso-nav-theme-button-container[data-section=missions]', $nav).data('section', 'trailer');
				}
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
				if (data['display'] == "currency" && data['currencyType']) {
					$('.sayso-progress-bar-value', $elem).html(state.state.game.currencies[data['currencyType']].balance);
					if (data['currencyType'] == "experience") {
						$('.sayso-progress-bar', $elem).css('width', Math.floor(state.state.game.currencies[data['currencyType']].balance * 100 / state.state.game.levels[state.state.game.level+1].threshold) + "%");
					}
				}
			},
			"current-level": function ($elem, data, animate) {
				$elem.html("L" + state.state.game.level + ": " + state.state.game.levels[state.state.game.level].name);
			}
		},
		"profile": {
			// user profile handlers here
			"user-public-name": function ($elem, data, animate) {
                if (state.state.profile.public_name)
                    $elem.html(state.state.profile.public_name);
                else
                    $elem.html(state.state.game.levels[state.state.game.level].name);
                // this $elem should contain a user's name
            },
            "user-level-name": function ($elem, data, animate) { $elem.html(state.state.game.levels[state.state.game.level].name); },
			"user-image": function ($elem, data, animate) {}
		},
		"notifications": {
			// notification handlers here
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
		"survey": function (data) {
			return {
				"survey": {
					action_class : "survey",
					action : "getSurvey",
					starbar_id : starbarId,
					survey_id : data['surveyId'],
					send_questions : false,
					send_question_choices : false
				}
			}
		},
		"trailer": function (data) {
			return {
				"trailers": {
					action_class : "survey",
					action : "getSurveys",
					starbar_id : starbarId,
					survey_type : "trailer",
					survey_status : "new",
					always_choose : true,
					chosen_survey_id : ('surveyId' in data ? data['surveyId'] : null)
				}
			}
		},
		"missions": function (data) {
			return {
				"missions": {
					action_class : "survey",
					action : "getSurveys",
					starbar_id : starbarId,
					survey_type : "mission",
					survey_status : "new"
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

	return {
		initApp: initApp
	};
})(this, jQuery, sayso.module.state, sayso.module.api, sayso.module.Handlebars, sayso.module.comm, sayso.module.frameComm, sayso.module.config);
