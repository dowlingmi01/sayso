sayso.module.browserapp = (function(global, $, state, Handlebars) {
	$(global.document).on('sayso:state-login sayso:state-logout sayso:state-ready', initApp);

	var starbarId = 4;
	var userMode = "logged-out";
	var request;
	var $nav, $sectionContainer, $section;
	var currentSection, timeoutIdSectionLoadingSpinner;

	function initApp() {

		if (state.state.loggedIn) {
			userMode = "logged-in";
			render();
		} else if (global.sayso.webportal) {
			userMode = "tour";

			request = {
				action_class : "markup",
				action : "getMarkup",
				starbar_id : starbarId,
				app: "browserapp",
				key : "nav"
			}
			state.apiCall(request, renderForTour);
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
		// hide the hide button... who hides the hiders?
		$('#sayso-nav-hide-button').hide();
		initNav();
	}

	function initNav() {
		$nav = $('#sayso-app-container');
		$sectionContainer = $('#sayso-section-container');
		$section = $('#sayso-section');
		prepareElements($nav);
	}

	function prepareElements(container) {
		if (!container) return;

		var elements = $('.sayso-element', container);
		var element;

		elements.each(function(index) {
			element = $(this);
			for (var elementType in elementHandlers) {
				if (element.hasClass('sayso-' + elementType)) {
					elementHandlers[elementType](element);
				}
			}

		})
	}

	// data is $(clicked element).data();
	// e.g. running $().data() on <div data-foo="bar" data-moo="car"> gives { foo: "bar", moo: "car" }
	function openSection(data) {
		var section;
		var $sectionLoading;

		if (!data || !data['section']) return;

		if (userMode == "tour") {
			section = "tour-" + data['section'];
		} else if (userMode == "logged-in") {
			section = data['section'];
		} else {
			// @todo do something for logged out mode, e.g. redirect to portal for login, or take them to tour page, or pop up a login section?
			section = 'login';
		}

		$sectionContainer.fadeTo(0, 0);

		$section.html("");
		$sectionContainer.removeClass().addClass('sayso-section-' + section);
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
			var extraRequestsForThisSection = extraRequests[section](data); // extraRequests[section] is a function

			for (var request in extraRequestsForThisSection) {
				state.apiAddRequest(request, extraRequestsForThisSection[request]);
			}
		}

		state.apiSendRequests(function(response){
			clearTimeout(timeoutIdSectionLoadingSpinner); // if the loader wasn't shown yet, don't show it
			var template = Handlebars.compile(response.responses.markup.variables.markup);
			$section.append(template(response.responses));

			$sectionContainer.removeClass().addClass('sayso-section-' + data.section);
			if ($sectionLoading) {
				$sectionLoading.fadeTo(100, 0, function(){
					$sectionLoading.remove();
					$sectionLoading = false;
				});
			}
		});

		currentSection = section;
	}

	function closeSection() {

	}

	var elementHandlers = {
		"section-link": function (elem) {
			elem.click(function() {
				openSection(elem.data());
				return;
			});
		}
	};

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
					survey_id : data['survey-id']
				}
			}
		}
	}

})(this, jQuery, sayso.module.state, sayso.module.Handlebars)
;
