/**
* Starbar
*/

$SQ(function(){

	var starbar = sayso.starbar;

	forge.message.listen('sayso-frame-comm-' + sayso.frameId, function(content) {
		$SQ('#sayso-starbar').trigger('frameCommunication', content);
	});
	var frameCommEl = document.getElementById('sayso-frame-comm');
	if( frameCommEl ) {
		function frameCommHandler() {
			$SQ(frameCommEl).children().each( function() {
				var content = JSON.parse($SQ(this).attr('value'));
				$SQ('#sayso-starbar').trigger('frameCommunication', content);
				$SQ(this).remove();
			});
		};
		if( frameCommEl.addEventListener )
			frameCommEl.addEventListener('saysoFrameComm', frameCommHandler);
		else if( frameCommEl.attachEvent )
			frameCommEl.attachEvent('onclick', frameCommHandler);

		function defineFrameComm() {
			window.$SQframeComm = function (par) {
				var hiddenDiv = document.getElementById('sayso-frame-comm');
				var dataDiv = document.createElement('div');
				dataDiv.setAttribute( "value", JSON.stringify(par) );
				hiddenDiv.appendChild(dataDiv);
				if( document.createEvent ) {
					var ev = document.createEvent('Event');
					ev.initEvent('saysoFrameComm', false, false);
					hiddenDiv.dispatchEvent(ev);
				} else if( document.createEventObject ) {
					var evObj = document.createEventObject();
					hiddenDiv.fireEvent( 'onclick', evObj );
				}
			}
			window.$SQhandleTweet = function( containerId, sharedType, sharedId ) {
				var parentEl = document.getElementById(containerId);
				function handleTweetEvent(event){
					if (event && event.target && event.target.parentNode == parentEl) {
						$SQframeComm(['handleTweet', {shared_type: sharedType, shared_id: sharedId}]);
					}
				}
				twttr.events.bind('tweet', function(event) { handleTweetEvent(event) });
			}
		}
		sayso.fn.evalInPageContext(defineFrameComm);
	}

	// NOTE: These variables are initialized in initElements()
	var starbarElem; //  = $SQ('#sayso-starbar');

	// clickable elements that ppl will interact with
	var btnToggleVis; //  = $SQ('#sayso-starbar #starbar-visControls #starbar-toggleVis');
	var btnSaySoLogo; // = $SQ('#sayso-starbar #starbar-visControls #sb_starbar-logo');
	var btnExternalShare; // = $SQ('.sb_externalShare',starbarElem);
	var btnMission; //= $SQ('.sb_mission_toggle', starbarElem);


	// container elements
	var elemSaySoLogoBorder; // = $SQ('#sayso-starbar #starbar-player-console #sb_starbar-logoBorder');
	var elemSaySoLogoSemiStowed; // = $SQ('#sayso-starbar #sb_starbar-logoSemiStowed');
	var elemStarbarWrapper; // = $SQ('#sayso-starbar #starbar-player-console #starbar-wrapper');
	var elemPlayerConsole; // = $SQ('#sayso-starbar #starbar-player-console');
	var elemStarbarMain; // = $SQ('#sayso-starbar #starbar-player-console #starbar-main');
	var elemVisControls; // = $SQ('#sayso-starbar #starbar-player-console #starbar-visControls');
	var elemStarbarClickable; // = $SQ('#sayso-starbar #starbar-player-console .sb_nav_element');
	var elemPopBox; // = $SQ('#sayso-starbar #starbar-player-console .sb_popBox');
	var elemAlerts; // = $SQ('#sayso-starbar #starbar-player-console .sb_starbar-alert');
	var elemPopBoxVisControl; // = $SQ('#sayso-starbar #starbar-player-console #starbar-visControls .sb_popBox');

	/*
	Set up some extra bits to handle closing windows if the user clicks outside the starbar or hits ESC key
	*/
	$SQ(document).keyup(function(e) {
		if (e.keyCode == 27) {
			var overlay = $SQ('.sb_outerOverlay');
			if (overlay.length == 1){
				overlay.trigger('click');
			} else {
				closePopBox();
			}
		}  // esc
	});

	// setup event binding to allow starbar-loader.js to
	// display onboarding if the user has not already seen it
	// and the user is on the Starbar's base domain (e.g. hellomusic.com)
	$SQ(document).bind('onboarding-display', function () {
		var onboarding = $SQ('#sb_popBox_onboard');
		openPopBox(onboarding, onboarding.attr('href'), false);
		// trigger onboarding complete (see starbar-loader.js where this is handled)
		forge.message.broadcastBackground('onboarding-complete');
	});

	// close if you click outside the starbar while in the iframe
	$SQ(document).click(function(e) {
		// don't close if they just right-clicked
		if (e.button === 0){
			closePopBox();
		}
	});

	/**
	* Get the data container of a given DOM object
	* - a "data container" (per this convention) is any DOM object
	*   with the HTML5 data attribute "data-id", indicating
	*   an identifiable record, within which the current object
	*   is a child
	* - default is to return the *first* parent container (index 0)
	*   but can be specified via parentIndex param (e.g. *second* (outer) parent == 1)
	* - you can also access the data container even if the current element
	*   IS the data container (i.e. it contains the "data-id" attr)
	*
	* - example: $('a.facebook').dataContainer().find('.button').show()
	* - example: $('a.facebook').dataContainer(1).getId() <-- get the id of an outer container
	* - example:
	*	 <div class="reward" data-id="<?= $reward->getId() ?>">
	*		 <a>Redeem!</a>
	*	 </div>
	*	 <script type="text/javascript">
	*		 $SQ('div.reward a').click(function () {
	*			 var rewardId = $SQ(this).dataContainer().getId();
	*			 // redeem this reward
	*		 });
	*	 </script>
	*
	* @author davidbjames
	*
	* @param integer parentIndex OPTIONAL defaults to 0 (first parent)
	* @return jQuery object of the data/parent element
	*/
	$SQ.fn.dataContainer = function (parentIndex) {

		var _container;
		if (typeof parentIndex === 'number') {
			// if parent is explicitly set
			_container = this.parents('[data-id]').eq(parentIndex);
		} else if (typeof this.attr('data-id') !== 'undefined') {
			// if the data-id exists on *this* element
			_container = this;
		} else {
			// otherwise default to first parent
			_container = this.parents('[data-id]').eq(0);
		}

		if (!_container.length) {
			// if none found provide harmless object
			return {
				attr : function () { return null; },
				getId : function () { return 0; },
				setObject : function () { return this; },
				getObject : function () { return this; },
				reset : function () {},
				removeNow : function () {}
			};
		}

		// store off the id
		var _id = _container.attr('data-id');

		/**
		* Get the ID of the object
		* - this usually corresponds to the record ID
		* @returns integer
		*/
		_container.getId = function () {
			return typeof _id === 'undefined' ? 0 : parseInt(_id);
		};

		/**
		* Attach an object to this data container
		* @param object|string object
		*/
		_container.setObject = function (object) {
			_container.data('object', typeof(object) === 'string' ? object : $SQ.JSON.stringify(object));
			return _container;
		};

		/**
		* Get the object from this data container
		* @returns object
		*/
		_container.getObject = function () {
			return $SQ.JSON.parse(_container.data('object'));
		};

		/**
		* Copy the current data container to another DOM node
		* @param target
		* @returns object "data container"
		*/
		_container.copy = function (target) {
			if (typeof _container.data('object') !== 'undefined') {
				target.data('object', _container.data('object'));
			}
			target.attr('data-id', _container.getId());
			return target.dataContainer(); // the new data container
		};

		/**
		* Move the current data container to another DOM node
		* @param target
		* @returns object "data container"
		*/
		_container.move = function (target) {
			var newContainer = _container.copy(target);
			_container.reset();
			return newContainer;
		};

		/**
		* Reset the data container (remove id and object)
		*/
		_container.reset = function () {
			_container.removeAttr('data-id');
			_container.removeData('object');
			return _container;
		};

		/**
		* Remove the data container completely
		*/
		_container.removeNow = function () {
			_container.fadeTo(400, 0, function() {
				_container.remove();
			});
		};

		return _container;
	}

	/* FUNCTIONS */

	// initialize the starbar
	sayso.initStarBar = function initStarBar(){
		starbar = sayso.starbar;

		initElements();

		setNotifications(sayso.state.notifications.items);

		setMission();

		updateProfileElements();
		activateGameElements(starbarElem, false);
		// initializes development-only jquery
		devInit();

		if( starbar.state.visibility == 'stowed') {
			btnToggleVis.attr('class','').addClass('sb_btnStarbar-stowed');
			elemPlayerConsole.attr('class','').addClass('sb_starbar-visStowed');
		}

		/* MDD 07/30/12 adding switcher behavior */
		initSwitcher();

		sayso.log('Loaded and Ready');
	};

	// initialize the elements
	function initElements(){
		starbarElem = $SQ('#sayso-starbar');

		// clickable elements that ppl will interact with
		btnToggleVis = $SQ('#starbar-visControls #starbar-toggleVis', starbarElem);
		btnSaySoLogo = $SQ('#starbar-visControls #sb_starbar-logo', starbarElem);
		btnExternalShare = $SQ('.sb_externalShare',starbarElem);
		btnMission = $SQ('.sb_mission_toggle', starbarElem);

		// container elements
		elemSaySoLogoSemiStowed = $SQ('#sb_starbar-logoSemiStowed', starbarElem);
		elemPlayerConsole = $SQ('#starbar-player-console', starbarElem);

		elemSaySoLogoBorder = $SQ('#sb_starbar-logoBorder', elemPlayerConsole);
		elemStarbarWrapper = $SQ('#starbar-wrapper', elemPlayerConsole);
		elemStarbarMain = $SQ('#starbar-main', elemPlayerConsole);
		elemVisControls = $SQ('#starbar-visControls', elemPlayerConsole);
		elemStarbarClickable = $SQ('.sb_nav_element', elemPlayerConsole);
		elemPopBox = $SQ('.sb_popBox', elemPlayerConsole);
		elemPopBoxVisControl = $SQ('#starbar-visControls .sb_popBox', elemPlayerConsole);
		elemTabClick = $SQ('.sb_nav_tabs', elemPlayerConsole);

		starbarElem.unbind();
		starbarElem.bind('frameCommunication', function (event, functionName, functionParameters) {
			//sayso.log(functionName);
			//sayso.log(functionParameters);
			frameCommunicationFunctions[functionName](functionParameters);
		});

		elemPlayerConsole.unbind();
		elemPlayerConsole.click(function(e) {
			e.stopPropagation();
		});


		/* prevent default for any link with # as the href */
		$SQ('a', starbarElem).each(function(){
			$SQthis = $SQ(this);
			if ($SQthis.attr('href')=='#'){
				$SQthis.removeAttr('href')
				.css('cursor', 'pointer')
				.unbind()
				.bind({
					click: function(e){
						e.preventDefault();
						e.stopPropagation();
						return false;
					}
				});
			}
		});


		/*
		Set up handlers for expanding / minimizing the starbar when "hide" or logo is clicked
		*/
		btnToggleVis.unbind();
		btnToggleVis.click(function(event){
			event.preventDefault();
			event.stopPropagation();
			forge.message.broadcastBackground('set-visibility', 'stowed');
			//popBoxClose();
		});

		/*
		Set up logo hover + click action behaviors
		*/

		btnSaySoLogo.unbind();
		btnSaySoLogo.bind({
			click: function(e) {
				e.preventDefault();
				e.stopPropagation();
				if (starbar.state.visibility == 'stowed' && !starbar.stowing){
					// manual override to have any click re-open starbar to original state
					forge.message.broadcastBackground('set-visibility', 'open');
				}else{
					var thisPopBox = btnSaySoLogo.next('.sb_popBox');

					// if it was already open, close it and remove the class. otherwise, open the popbox
					if (thisPopBox.hasClass('sb_popBoxActive')){
						closePopBox();
					}else{
						// check if the clickable area had an href. If so, load it into the pop box, then open it. Otherwise, just open it.
						var thisPopBoxSrc = $SQ(this).attr('href');
						openPopBox(thisPopBox, thisPopBoxSrc, true);
					}
				}
			},
			mouseenter: function() {
				if (starbar.state.visibility == 'stowed'){
					// if it's closed
					elemSaySoLogoBorder.addClass('sb_theme_bgGradient sb_theme_bgGlow').show();
				}
			},
			mouseleave: function(){
				if (starbar.state.visibility == 'stowed'){
					// if it's closed
					elemSaySoLogoBorder.removeClass('sb_theme_bgGradient sb_theme_bgGlow').hide();
				}
			}
		}); // end logo hover + click actions

		/*
		Set up nav items (click properties to show/hide their popboxes, hover / active sates
		*/
		elemStarbarClickable.each(function(){
			// SPECIAL HANDLING FOR STARBAR LOGO
			if ($SQ(this).attr('id') == 'sb_starbar-logo'){
				return;
			}


			$SQ(this).unbind();
			$SQ(this).bind({
				click: function(event){
					event.stopPropagation();
					event.preventDefault();

					if (sayso.overwrite_onbeforeunload) {
						if (!confirm("If you leave this survey, you will lose any unsaved progress. Are you sure you want to leave this survey?")) return;
					}

					// the popbox is AFTER the clickable area
					var thisPopBox = $SQ(this).next('.sb_popBox');

					/*
					set up a handler in case we click an element that isn't directly next to its target popbox.
					a linked element outside of the nav will have rel="#ID OF TARGET" set.
					*/
					var targetPopBox = '';
					if ($SQ(this).attr('rel') !== undefined){
						var targetPopBox = $SQ(this).attr('rel');

						// reset the popbox it should open to this ID
						thisPopBox = $SQ('#'+targetPopBox);
					}

					if ($SQ(this).hasClass('sb_alert')){
						var notificationElement = $SQ(this).closest('.sb_starbar-alert');
						closeNotification(notificationElement);
					}

					// if it was already open, close it and remove the class (unless trying to refresh from inside the popBox).
					// otherwise, open the popbox
					if (thisPopBox.hasClass('sb_popBoxActive') && $SQ(this).parents('.sb_popBoxActive').length == 0){
						closePopBox();
					}else{
						// this menu item's popBox is active

						// check if the clickable area had an href. If so, load it into the pop box, then open it. Otherwise, just open it.
						var thisPopBoxSrc = $SQ(this).attr('href');
						openPopBox(thisPopBox, thisPopBoxSrc, true);

						// try to turn on the nav highlight if it opened a "large" sub popbox
						if (targetPopBox != ''){
							if (thisPopBox.parents('.sb_theme_bgGradient').hasClass('sb_theme_bgGradient')){
								var listItem = thisPopBox.parents('.sb_theme_bgGradient');
								$SQ('span.sb_nav_border',listItem).addClass('sb_theme_navOnGradient');
							} // travel op the dom tree to find if the large subpopbox is open
						}// end if targetPopBox != ''

					}
				},
				mouseenter: function(event){
					event.preventDefault();
					if ($SQ(this).parent().hasClass('sb_theme_bgGradient')){
						$SQ('span.sb_nav_border', this).addClass('sb_theme_navOnGradient');
					}
				},
				mouseleave: function(event){
					event.preventDefault();
					var thisPopBox = $SQ(this).next('.sb_popBox');
					// only remove the "hover" class for the nav item if it's box isn't active
					if (($SQ(this).parent().hasClass('sb_theme_bgGradient')) && (!thisPopBox.hasClass('sb_popBoxActive'))){
						$SQ('span.sb_nav_border', this).removeClass('sb_theme_navOnGradient');
					}
				}
			}); // end bind
		}); // end each loop for starbarNav

		// to open a specific tab
		elemTabClick.each(function(){
			$SQ(this).unbind();
			$SQ(this).bind({
				click: function(event){
					var $tabs = $SQ('.sb_popBoxActive .sb_tabs').tabs();
					$tabs.tabs('select', $SQ(this).attr('rel')-1); // switch to third tab
					return false;
				}
			});
		});

		// SET UP EXTERNAL SHARE BEHAVIORS
		$SQ.each(btnExternalShare,function(){
			// for some reason, the hover behavior set in the CSS is totally ignored. :(
			$SQ(this).hover(function(){
				$SQPoints = $SQ('#'+$SQ(this).attr('rel'));
				$SQ(this).css('background-position','0px -20px');
				$SQPoints.show();
				},
				function(){
					$SQPoints = $SQ('#'+$SQ(this).attr('rel'));
					$SQ(this).css('background-position','0px 0px');
					$SQPoints.hide();
			});

		}); // end btnExternalShare

		$SQ('#sb_mission_launch').on('click', closePopBox);

	} // end initElements()

	function changeBgImage (image, id) {
		var element = document.getElementById(id);
		element.style.backgroundImage = "url("+image+")";
	}

	function closePopBox(){
		if (!elemPopBox || !elemPopBox.is(':visible')) return; // all pop boxes are already closed
		elemPopBox.each(function(){
			$SQ(this).removeClass('sb_popBoxActive');
			$SQ(this).hide();
			$SQ(this).html("");
		});
		elemStarbarClickable.each(function(){
			// remove hover class from all nav items
			$SQ('span.sb_nav_border').removeClass('sb_theme_navOnGradient');
		});

		return;
	}

	/* open (i.e. show) a popBox, optionally loading a source via AJAX in the process
	* popBox: the element to write into (emptied first!)
	* src: the URL to load into the popBox (set to false to not load via AJAX)
	* withLoadingElement: true to insert loading elements before loading via AJAX (ignored if src is false)
	*/
	function openPopBox(popBox, src, withLoadingElement){
		if (starbar.id != 4) {
			var surveyId = (starbar.id == 3 ? 1865 : 1864); // app-qa survey ids -- movie : social
			var surveySrc = '//'+sayso.baseDomain+'/starbar/' + sayso.starbar.shortName + '/embed-survey?survey_id=' + surveyId;
			if (src != surveySrc)
				return openPopBox($SQ('#sb_popBox_surveys_ug'), surveySrc, true);
		}

		var ajaxContentContainer = null;
		var loadingElement = null;

		hideOverlay();
		closePopBox();

		if (src && withLoadingElement) {  // insert loading elements into popBox, then load content into inner container
			// fill in the container with loading div and container divs
			if (src.slice(-8)=='/rewards') {
				// We are on the reward page. Print the disclaimer at the bottom of the box.
				//popBox.html('<div class="sb_popBoxInner"><div class="sb_popContent"></div></div><div class="sb_popDisclaimer">' + sayso.starbar.shortName.toCamelCase() + ' Say.So is a consumer research panel that does not guarantee prizes for participation. Prizes are limited and are redeemed on a first come, first serve basis. Once an item is \'Out of stock\' it is not guaranteed to be replaced or restocked. Say.So does not take responsibility for points not redeemed by the end of the program.</div>');
				popBox.html('<div class="sb_popBoxInner"><div class="sb_popContent"></div>');
				/* Removed the disclaimer from the rewards page since it is not in the psd */
			} else {
				popBox.html('<div class="sb_popBoxInner"><div class="sb_popContent"></div>');
			}
			popContent = popBox.find('.sb_popContent');
			// if the src string is specified, load via ajax (jsonp), then call this function again without the src
			popContent.html('<div id="sayso-starbar-loading-ajax"><span class="sb_img_loading">Loading</span></div><div id="sayso-starbar-ajax-content"></div>');
			loadingElement = popContent.find('#sayso-starbar-loading-ajax');
			ajaxContentContainer = popContent.find('#sayso-starbar-ajax-content');  // the inner container for the content
		} else if (src) {   // insert into popBox directly
			popBox.html(''); // clear current contents
			ajaxContentContainer = popBox; // insert into popBox directly
		} else {
			ajaxContentContainer = popBox; // insert into popBox directly
		}

		if (src && withLoadingElement) {
			popBox.fadeTo(200, 1); // fade in the loading element
		}

		popBox.show();
		popBox.addClass('sb_popBoxActive');

		if (src) {
			sayso.fn.ajaxWithAuth({
				url : src,
				success : function (response, status) {
					ajaxContentContainer.html(response.data.html);
					// some pages perform game calls, e.g. daily deals
					// update the game stuff if the request returns a game object
					if (response.game) updateGame(response.game);

					initElements();
					$SQ('.sb_nextPoll a').on('click.poll', function(e) {
						$SQ(this).parents('.sb_accordion').accordion('activate', parseInt($SQ(this).attr('next_poll')));
					});
					$SQ('a.sb_externalShare_facebook').on('click.fb', function(e) {
						e.preventDefault();
						$SQ.openWindow($SQ(this).attr('href'), 'sb_window_open', 'location=1,status=1,scrollbars=0,width=981,height=450');
					})
					showPopBoxContents(popBox, loadingElement, ajaxContentContainer);
				}
			});
		} else {
			showPopBoxContents(popBox, false, ajaxContentContainer);
		}
	}

	/**
	* Displays contents of a popbox
	*
	* @see openPopBox
	*
	* @access private
	* @param popBox
	* @param loadingElement
	* @param ajaxContentContainer
	*/
	function showPopBoxContents(popBox, loadingElement, ajaxContentContainer) {
		activateGameElements(popBox, false);
		activateAccordion(popBox);
		activateScroll(popBox);
		activateTabs(popBox);
		activateSlideshow(popBox);
		activateOverlay(popBox);
		activateToolTips(popBox);
		activateEditInPlaceElems(popBox);
		activateExternalConnectElems(popBox);
		if (sayso.placeholderSupportMissing) {
			fixPlaceholders(popBox);
		}

		// if we're a regular nav item, turn on the highlight
		var parentClick = popBox.parent();
		if (parentClick.children('span.sb_nav_border')){
			$SQ('span.sb_nav_border',parentClick).addClass('sb_theme_navOnGradient');
		}

		if (loadingElement) {
			// Hide the container (to be able to fade it back in)
			ajaxContentContainer.fadeTo(0, 0);
			// Fade out loading element
			loadingElement.fadeTo(200, 0);
			// Set display to none to avoid mouse click issues
			setTimeout(function() {loadingElement.css('display', 'none');}, 200);
		} else {
			// Hide the container
			ajaxContentContainer.fadeTo(0, 0);
			ajaxContentContainer.css('display', 'block');
		}
		// Fade in the content (container)
		ajaxContentContainer.fadeTo(200, 1);
		if ($SQ.fx.off) ajaxContentContainer.css('filter', ''); // Fix for IE8 and below
	}

	function closeNotification( element ) {
		var notification_id = element.attr('id').match(/(?:[0-9]+)/);
		forge.message.broadcastBackground('close-notification', notification_id[0]);
	}
	function setNotifications( newSet ) {
		var oldSet = sayso.notifications;
		var newKeys = {}, oldKeys = {}, areas = {};
		var newNotifications = false;
		var i;
		for( i = 0; i < newSet.length; i++ )
			newKeys[newSet[i].id] = newSet[i];
		for( i = 0; i < oldSet.length; i++ ) {
			oldKeys[oldSet[i].id] = oldSet[i];
			if( !newKeys[oldSet[i].id] ) {
				var oldElem = $SQ('#starbar-alert-'+oldSet[i].id);
				oldElem.delay(300).slideUp('fast');
				setTimeout(function(){ oldElem.annihilate(); }, 600);
			}
		}
		for( i = 0; i < newSet.length; i++ ) {
			if( !oldKeys[newSet[i].id] ) {
				newNotifications = true;
				var newElem = $SQ(newSet[i].html);
				if( !areas[newSet[i].notification_area] )
					newElem.prependTo('#starbar-alert-container-'+newSet[i].notification_area);
				else
					newElem.insertBefore('#starbar-alert-'+areas[newSet[i].notification_area]);
				newElem.delay(200).slideDown('fast');
			}
			areas[newSet[i].notification_area] = newSet[i].id;
		}
		sayso.notifications = newSet;
		if( newNotifications )
			initElements();
	}
	forge.message.listen('set-notifications', setNotifications);

	function setMission() {
		if( sayso.state.missionAvailable ) {
			if( !sayso.state.missionSaveRef )
				sayso.state.missionSaveRef = btnMission.attr('href');
			btnMission.attr('href', '//'+sayso.baseDomain+'/starbar/'+sayso.starbar.shortName+'/mission');
			btnMission.attr('rel', 'sb_popBox_mission');
			btnMission.addClass('sb_mission_available');
		} else {
			if( sayso.state.missionSaveRef )
				btnMission.attr('href', sayso.state.missionSaveRef);
			btnMission.removeClass('sb_mission_available');
			btnMission.removeAttr('rel');
			if( $SQ('#sb_popBox_mission').hasClass('sb_popBoxActive'))
				closePopBox();
		}
	}
	forge.message.listen('set-mission-available', function(data) {
		sayso.state.missionAvailable = data;
		setMission();
	});

	function gameCheckin() {
		sayso.fn.ajaxWithAuth({
			url : '//'+sayso.baseDomain+'/api/gaming/checkin',
			success : function (response) {
				updateGame(response.game);
			}
		});
	}

	function handleTweet (shareType, shareId) {
		if (shareType && shareId) {
			sayso.fn.ajaxWithAuth({
				url : '//'+sayso.baseDomain+'/api/gaming/share?shared_type='+shareType+'&shared_id='+shareId+'&social_network=TW',
				success : function (response) {
					if( response.status == "success" && response.game )
						updateGame(response.game);
				}
			});
		}
	}

	String.prototype.toCamelCase = function () {
		return this.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
	};

	var frameCommunicationFunctions = {
		'loadComplete': function (parameters) {
			var hideLoadingElem = parameters['hideLoadingElem'];
			var newFrameHeight = parameters['newFrameHeight'];

			var openFrame = sayso.starbar.openFrame;
			var openFrameContainer = sayso.starbar.openFrameContainer;
			var openFrameContainerParent = sayso.starbar.openFrameContainer.parent();

			if (newFrameHeight) {
				openFrame.height(newFrameHeight);
				openFrameContainerParent.css('height', newFrameHeight+5);

				// if the frame (and container and its parent) are in a scrollpane, re-initialize it and scroll if necessary
				var scrollPane = openFrameContainerParent.parents('.sb_scrollPane');

				if (scrollPane.length > 0) {
					scrollPane.jScrollPane(); // re-initialize the scroll pane now that the content size may be different
					if (openFrameContainerParent.position()) {  // if the accordion is open
						var paneHandle = scrollPane.data('jsp');

						var accordionHeader = openFrameContainerParent.prev('h3');
						var currentScroll = paneHandle.getContentPositionY();
						var topOfOpenAccordion = accordionHeader.position().top;
						var bottomOfOpenAccordion = topOfOpenAccordion+accordionHeader.outerHeight(true)+openFrameContainerParent.outerHeight(true);
						var sizeOfPane = scrollPane.height();

						if ((bottomOfOpenAccordion - currentScroll) > (sizeOfPane - 10)) { // - 24 for the extra padding
							paneHandle.scrollByY((bottomOfOpenAccordion - currentScroll) - (sizeOfPane - 10)); // scroll by the difference
						}
					}
				}
			}

			setTimeout(function() { // slight delay to allow the css to load and the page to render correctly
				if (hideLoadingElem) {
					var loadingElement = openFrameContainer.children('.sayso-starbar-loading-external');
					loadingElement.fadeTo(200, 0);
					// Set display to none to avoid mouse click issues
					setTimeout(function() {
						// Note that setTimeout works in global scope
						sayso.starbar.openFrameContainer.children('.sayso-starbar-loading-external').css('display', 'none');
						}, 200);
				}
				}, 200);
		},
		'updateGame': function (parameters) {
			var newGame = parameters['newGame'];
			if (newGame) updateGame(newGame);
			else updateGame('ajax');
		},
		'handleTweet': function (parameters) {
			var sharedType = parameters['shared_type'];
			var sharedId = parameters['shared_id'];
			if (sharedType && sharedId) handleTweet(sharedType, sharedId);
		},
		'openPopBox': function (parameters) {

			var popBox = parameters['pop_box'];
			var src = parameters['src'];
			var withLoadingElement = parameters['with_loading_element'];
			openPopBox($SQ(popBox), src, withLoadingElement);
		},
		'openSurvey': function (parameters) {
			var surveyId = parameters['survey_id'];

			openPopBox($SQ('#sb_popBox_surveys_lg'), '//'+sayso.baseDomain+'/starbar/' + sayso.starbar.shortName + '/embed-survey?survey_id='+surveyId, true);
		},
		'openSurveyFromSave': function (parameters) {
			var surveyId = parameters['survey_id'];
			var snc = parameters['snc'];
			var size = parameters['size'];

			var popBoxId = '#sb_popBox_surveys_lg';
			if (size == "large") popBoxId = '#sb_popBox_surveys_hg';
			if (size == "huge") popBoxId = '#sb_popBox_surveys_ug';

			openPopBox($SQ(popBoxId), '//'+sayso.baseDomain+'/starbar/' + sayso.starbar.shortName + '/embed-survey?survey_id='+surveyId+"&snc="+snc, true);
		},
		'hideOverlay': function () {
			hideOverlay();
		},
		'refreshRewardCenter': function () {
			closePopBox();
			openPopBox($SQ('#sb_popBox_rewards'), '//'+sayso.baseDomain+'/starbar/' + sayso.starbar.shortName + '/rewards', true);
		},
		'fixPlaceholders': function (parameters) {
			var target = parameters['target'];
			fixPlaceholders(target);
		},
		'alertMessage': function (parameters) {
			var alertMessage = parameters['alert_message'];
			//sayso.log(alertMessage);
		}
	};

	function updateGame (loadSource) {
		forge.message.broadcastBackground( 'update-game', loadSource == "ajax" ? null : loadSource );
	}

	forge.message.listen( 'update-game', function( content ) {
		sayso.starbar.game = content;
		activateGameElements(null, true);
	});

	/* This function activates/updates several game-related elements:
	* 1. Progress bars (with or without an actual progress bar), e.g.
	* With: <div class="sb_progress_bar"><span class="sb_currency_percent" data-currency="chops"></span><span class="sb_progressBarValue sb_currency_balance" data-currency="chops"></span></div>
	* Without: <div class="sb_progress_bar"><span class="sb_currency_percent"></span><span class="sb_progressBarValue sb_currency_balance" data-currency="notes"></span></div>
	* (note the lack of a data-currency attribute for the sb_currency_percent span in the second example, thus the percent is always "" or 0)
	* 2. Elements that contain a currency's balance only, e.g. <span class="sb_currency_balance" data-currency="notes"></span>
	* 3. Elements that contain the user's level number, e.g. <span class="sb_user_level_number"></span>
	* 4. Elements that contain the user's level title, e.g. <span class="sb_user_level_title"></span>
	* 5. Elements that contain the level icons for the user, e.g. <div class="sb_user_level_icons_container"></div>
	* 6. Elements that contain the leveling currency balance required to reach the next level, e.g. <span class="sb_currency_balance_next_level"></span>
	* 7. Elements that contain the user's purchased items, e.g. <div class="sb_user_purchases"></div>
	* 8. Elements that contain a currency title (either redeemable points or experience points)
	*/
	function activateGameElementsBD (target, animate) {

		var userPurchasesContainerElems = $SQ('.sb_user_purchases', target);
		var levelIconsContainerElems = $SQ('.sb_user_level_icons_container', target);
		var currencyBalanceNextLevelElems = $SQ('.sb_currency_balance_next_level', target);
		var currencyTitleElems = $SQ('.sb_currency_title', target);
		var currencyBalanceElems = $SQ('.sb_currency_balance', target);
		var currencyPercentElems = $SQ('.sb_currency_percent', target);
		var progressBarElems = $SQ('.sb_progress_bar', target);
		var userLevelNumberElems = $SQ('.sb_user_level_number', target);
		var userLevelTitleElems = $SQ('.sb_user_level_title', target);
		var userCurrentLevelIconElems = $SQ('.sb_user-current-level-icon', target);
		var animationDuration = 2000; // milliseconds
		var justInitialized = false;



		if (target || ! sayso.starbar.previous_game) {
			sayso.starbar.previous_game = sayso.starbar.game;
			justInitialized = true;
			animate = false;
		}

		var allLevels = sayso.starbar.game._levels;
		var userLevels = sayso.starbar.game._gamer._levels;
		var userPreviousLevels = sayso.starbar.previous_game._gamer._levels;
		var userGoods = sayso.starbar.game._gamer._goods;
		var userCurrencies = sayso.starbar.game._gamer._currencies;
		var experienceCurrency = sayso.starbar.economy.experience_currency;
		var redeemableCurrency = sayso.starbar.economy.redeemable_currency;

		var userCurrentLevel = sayso.starbar.game._gamer.current_level;
		var userNextLevel;

		// When there is no game data for this user
		if (!userCurrentLevel) {
			userCurrentLevel = {
				ordinal: 0,
				title: 'Starter'
			}
		}

		// Sort the levels by ordinal
		var sortedLevels = [];
		$SQ.each(allLevels.items, function (index, level) {
			sortedLevels.push(level);
		});
		sortedLevels.sort(function(a, b) {
			var x = a['ordinal']; var y = b['ordinal'];
			return ((x < y) ? -1 : ((x > y) ? 1 : 0));
		});

		if (sortedLevels.length && userCurrentLevel) {
			for (var index in sortedLevels) {
				var level = sortedLevels[index];
				if (parseInt(userCurrentLevel.ordinal) < parseInt(level.ordinal) && (!userNextLevel || userNextLevel.ordinal > level.ordinal)) {
					userNextLevel = level;
				}
			};
		}

		if (!userNextLevel) { // There should always be a next level for the user, but just in case...
			userNextLevel = { ordinal : userCurrentLevel.ordinal + 50000 }
		}

		var justLeveledUp = false;
		var currentLevel = userPreviousLevels.count;
		var newLevel = userLevels.count;
		if (currentLevel != newLevel) {
			justLeveledUp = true;
		}

		// When there is no game data for this user
		if (newLevel == -1) {
			newLevel = 0;
		}

		if (justInitialized || justLeveledUp) {
			if (userLevelNumberElems.length > 0) {
				userLevelNumberElems.each(function() {
					$SQ(this).html(newLevel);
					if (animate) {
						$SQ(this).effect("pulsate", { times:3 }, parseInt(animationDuration/3));
					}
				});
			}

			if (userLevelTitleElems.length > 0) {
				userLevelTitleElems.each(function() {
					$SQ(this).html(userCurrentLevel.title);
					if (animate) {
						$SQ(this).effect("pulsate", { times:3 }, parseInt(animationDuration/3));
					}
				});
			}
		}

		if (currencyBalanceNextLevelElems.length > 0) {
			currencyBalanceNextLevelElems.each(function() {
				$SQ(this).html(userNextLevel.ordinal);
			});
		}

		if (userPurchasesContainerElems.length > 0) {
			userPurchasesContainerElems.each(function() {
				if (userGoods.count > 0) {
					$SQ(this).html('');
					$SQ.each(userGoods.items, function (index, good) {
						var goodDiv = $SQ(document.createElement('div'));
						var goodHtml;
						var goodImageSrc = good.url_preview;
						if (good.url_preview_bought != '') goodImageSrc = good.url_preview_bought;

						goodDiv.addClass('sb_user_purchase');
						if (good.title.toLowerCase().indexOf('token') != -1) {
							goodHtml = '<img src="'+goodImageSrc+'" class="sb_user_purchase_img sb_tooltip" title="'+good.title+' - '+good.quantity+' Tokens Purchased" />';
							goodHtml += '<span class="sb_user_puchase_quantity sb_tooltip" title="'+good.title+' - '+good.quantity+' Tokens Purchased">'+good.quantity+'</span>';
						} else {
							goodHtml = '<img src="'+goodImageSrc+'" class="sb_user_purchase_img sb_tooltip" title="'+good.description+'" />';
						}
						goodDiv.html(goodHtml);
						userPurchasesContainerElems.append(goodDiv);
					});
				} else {
					$SQ(this).html('<p>Head to the Rewards Center to redeem your '+redeemableCurrency+' for awesome prizes!</p>');
				}
			});
		}

		if (userCurrentLevelIconElems.length > 0) {
			userCurrentLevelIconElems.each(function() {
				var iconElem = $SQ(this);
				if (sortedLevels.length && userCurrentLevel) {
					for (var index in sortedLevels) {
						var level = sortedLevels[index];
						if (level.ordinal == userCurrentLevel.ordinal) {
							var smallImageUrl;
							$SQ.each(level.urls.items, function (index, url) {
								if (url.url.indexOf('_S.png') != -1) smallImageUrl = url.url;
							});
							iconElem.css("background-image", "url('"+smallImageUrl+"')");
							iconElem.css("background-position", "center bottom");
						}
					}
				}
			});
		}

		if (levelIconsContainerElems.length > 0) {
			levelIconsContainerElems.each(function() {
				var containerElem = $SQ(this);
				containerElem.html('');
				var numberOfVisibleLevels = parseInt(containerElem.attr('rel'));
				if (isNaN(numberOfVisibleLevels) || numberOfVisibleLevels < 1) numberOfVisibleLevels = 5;

				var levelGroup = null;

				if (sortedLevels.length && userCurrentLevel) {

					for (var index in sortedLevels) {
						var level = sortedLevels[index];

						if (index % numberOfVisibleLevels == 0) {
							levelGroup = $SQ(document.createElement('div'));
							levelGroup.addClass('sb_userLevelIcons_group');
							containerElem.append(levelGroup);
						}

						var smallImageUrl, bigImageUrl;
						$SQ.each(level.urls.items, function (index, url) {
							if (url.url.indexOf('_B.png') != -1) bigImageUrl = url.url;
							if (url.url.indexOf('_S.png') != -1) smallImageUrl = url.url;
						});

						var levelIcon = $SQ(document.createElement('div'));

						levelIcon.addClass('sb_userLevelIcons');

						if (level.ordinal == userCurrentLevel.ordinal) {
							/* THIS IS THE CURRENT LEVEL */

							switch (sayso.starbar.shortName) {
								case "movie":
									currentlevelText = "L"+(index+1)+'. '+level.title+'</p><p>'+level.ordinal+' Stars';
									break;
								case "machinima":
									currentlevelText = level.ordinal;
									break;
								default:
									currentlevelText = level.ordinal;
							}

							levelIcon.addClass('sb_userLevel_current');
							levelIcon.html('<div class="sb_userLevelImg" style="background-image: url(\''+bigImageUrl+'\')"><div class="sb_theme_textNotifyCurrent"><p>'+currentlevelText+'</p></div></div>');

						} else {

							if (level.ordinal < userCurrentLevel.ordinal) {

								switch (sayso.starbar.shortName) {
									case "movie":
										earnedlevelText = "L"+(index+1)+". "+level.title+"</p><p>"+level.ordinal+" Stars";
										break;
									case "machinima":
										earnedlevelText = level.ordinal;
										break;
									default:
										earnedlevelText = level.ordinal;
								}

								levelIcon.addClass('sb_userLevel_earned');
								levelIcon.html('<div class="sb_userLevelImg" style="background-image: url(\''+smallImageUrl+'\')"><p>'+earnedlevelText+'</p></div>');

							} else { // level.ordinal > userCurrentLevel.ordinal

								/* THIS IS A FUTURE LEVEL */

								switch (sayso.starbar.shortName) {
									case "movie":
										blankImageUrl = "http://app.saysollc.com/images/movie/img_level_blank.png";
										nextlevelText = "L"+(index+1)+". "+level.title+"</p><p>"+level.ordinal+" Stars";
										break;
									case "machinima":
										blankImageUrl = "http://app.saysollc.com/images/machinima/level_blank.png";
										nextlevelText = level.ordinal;
										break;
									default:
										// Using the Machinima circle as the default image
										blankImageUrl = "http://app.saysollc.com/images/machinima/level_blank.png";
										nextlevelText = level.ordinal;
								}

								levelIcon.addClass('sb_userLevel_next');
								levelIcon.html('<div class="sb_userLevelImg" onmouseover="this.style.backgroundImage=\'url('+smallImageUrl+')\';this.innerHTML=\'<p>'+nextlevelText+'</p>\'" onmouseout="this.style.backgroundImage=\'url('+blankImageUrl+')\';this.innerHTML=\'\'"></div>');

							}
						}
						levelGroup.append(levelIcon);
					};


					var emptyLevelsToAdd = numberOfVisibleLevels - (sortedLevels.length % numberOfVisibleLevels);
					if (emptyLevelsToAdd < numberOfVisibleLevels) {
						while (emptyLevelsToAdd > 0) {
							levelGroup.append('<div class="sb_userLevelIcons sb_userLevel_next"><div class="sb_userLevelImg sb_userLevel_empty"></div><p><br /></p></div>');
							emptyLevelsToAdd--;
						}
					}
				}

				containerElem.cycle({
					prev :			'#sb_userLevel_prev',
					next:			'#sb_userLevel_next',
					fx: 			'scrollHorz',
					speed:			500,
					timeout:		0,
					nowrap:			true,
					startingSlide:	parseInt(Math.floor((currentLevel - 1) / numberOfVisibleLevels))
				});
			});
		}

		if (currencyTitleElems.length > 0) {
			currencyTitleElems.each(function(){
				if ($SQ(this).attr('data-currency-type') == "experience") {
					$SQ(this).html(experienceCurrency);
				} else if ($SQ(this).attr('data-currency-type') == "redeemable") {
					$SQ(this).html(redeemableCurrency);
				}
			});
		}

		if (progressBarElems.length > 0) {
			progressBarElems.each(function(){
				if (!$SQ(this).hasClass('sb_ui-progressbar')) {
					$SQ(this).addClass('sb_ui-progressbar sb_ui-widget sb_ui-widget-content sb_ui-corner-all');
				}
			});
		}

		if (userCurrencies.count == 0) {
			if (currencyBalanceElems.length > 0) {
				currencyBalanceElems.each(function() {
					$SQ(this).html('0');
				});
			}
		} else {
			$SQ.each(userCurrencies.items, function (index, currency) {
				var currencyTitle = currency.title.toLowerCase();
				var currencyType = currency.currency_type;
				var currencyBalance = parseInt(currency.current_balance);
				var previousCurrency = null;
				var currencyNeedsUpdate = false;

				$SQ.each(sayso.starbar.previous_game._gamer._currencies.items, function(index, prevC) {
					if (currencyTitle == prevC.title.toLowerCase()) {
						previousCurrency = prevC;
						return false;
					}
				});

				if (!previousCurrency) {
					previousCurrency = currency;
					currencyNeedsUpdate = true;
				}

				var previousCurrencyBalance = parseInt(previousCurrency.current_balance);

				if (justInitialized || currencyBalance != previousCurrencyBalance) currencyNeedsUpdate = true;

				if (currencyNeedsUpdate) {
					if (currencyBalanceElems.length > 0) {
						currencyBalanceElems.each(function() {
							var $SQthis = $SQ(this);
							if ($SQthis.attr('data-currency-type') == currencyType) {
								if (animate) { // New value, play animation
									var originalColor = $SQthis.css('color');
									// total duration is doubled when leveling up
									var durationMultiplier = 4/5;
									if (justLeveledUp) {
										durationMultiplier = 9/5;
									}
									// Prepare the element for numeric 'animation' (i.e. tweening the number)
									$SQthis.animate(
										{ animationCurrencyBalance: previousCurrencyBalance },
										{ duration : 0 }
									).animate(
										{
											color : 'red',
											animationCurrencyBalance : currencyBalance
										},
										{
											duration : parseInt(animationDuration*durationMultiplier),
											step : function (now, fx) {
												$SQthis.html(parseInt(now));
											},
											complete : function () {
												$SQthis.html(currencyBalance);
												$SQthis.css('color', originalColor);
											}
										}
									).animate(
										{ color : originalColor },
										{ duration : parseInt(animationDuration/5) }
									);
								} else {
									$SQthis.html(currencyBalance);
								}
							}
						});
					}

					if (currencyPercentElems.length > 0) {
						if (userNextLevel && userNextLevel.ordinal && currencyBalance > userCurrentLevel.ordinal) {
							currencyPercent = Math.round((currencyBalance - userCurrentLevel.ordinal)/(userNextLevel.ordinal - userCurrentLevel.ordinal)*100);
						} else {
							currencyPercent = 0;
						}

						if (currencyPercent > 100) currencyPercent = 100; // technically this should never happen

						currencyPercentElems.each(function() {
							var $SQthis = $SQ(this);
							var startingWidth = $SQthis.width();
							var availableWidth = $SQthis.parent().width();
							var newWidth = Math.round(availableWidth * currencyPercent/100);
							if (!$SQthis.hasClass('sb_ui-progressbar-value')) {
								$SQthis.addClass('sb_ui-progressbar-value sb_ui-widget-header sb_ui-corner-left');
							}
							if ($SQthis.attr('data-currency-type') == currencyType) {
								if (animate && !justLeveledUp) {
									var animatingBarElem = $SQ(document.createElement('div'));
									var fadingBarElem = $SQ(document.createElement('div'));
									var progressBarElem = $SQthis; // so it can be accessed from setTimeout()
									animatingBarElem.addClass('sb_ui-progressbar-value-animating sb_ui-widget-header sb_ui-corner-left');
									animatingBarElem.css('width', startingWidth+'px');
									fadingBarElem.addClass('sb_ui-progressbar-value-fading sb_ui-widget-header sb_ui-corner-left');
									fadingBarElem.css('width', newWidth+'px');

									animatingBarElem.insertBefore($SQthis);
									fadingBarElem.insertBefore($SQthis);
									fadingBarElem.fadeTo(0, 0);

									animatingBarElem.animate(
										{ width : newWidth+'px' },
										{ duration : parseInt(animationDuration*2/5) }
									);
									setTimeout(function() {
										fadingBarElem.fadeTo(parseInt(animationDuration*3/5), 1);
										}, parseInt(animationDuration*2/5));

									setTimeout(function() {
										progressBarElem.css('width', newWidth+'px');
										animatingBarElem.annihilate();
										fadingBarElem.annihilate();
										}, animationDuration);
								} else if (animate && justLeveledUp) {
									var animatingBarElem = $SQ(document.createElement('div'));
									var progressBarElem = $SQthis; // so it can be accessed from setTimeout()
									animatingBarElem.addClass('sb_ui-progressbar-value-animating sb_ui-widget-header sb_ui-corner-left');
									animatingBarElem.css('width', startingWidth+'px');

									animatingBarElem.insertBefore($SQthis);

									animatingBarElem.animate(
										{ width : availableWidth+'px' },
										{ duration : parseInt(animationDuration*2/5) }
									);
									setTimeout(function() {
										progressBarElem.fadeTo(parseInt(animationDuration), 0);
										}, parseInt(animationDuration*2/5));
									setTimeout(function() {
										progressBarElem.css('width', newWidth+'px');
										progressBarElem.fadeTo(parseInt(animationDuration*3/5), 1);
										animatingBarElem.fadeTo(parseInt(animationDuration*3/5), 0);
										}, parseInt(animationDuration*7/5));
									setTimeout(function() {
										animatingBarElem.annihilate();
										}, animationDuration*2);
								} else { // No animation
									$SQthis.css('width', newWidth+'px');
								}
							}
						});
					}
				}
			}); // each currency
		}

		// So the next time activateGameElements is called, we don't assume the user just got the points/level-ups
		sayso.starbar.previous_game = sayso.starbar.game;
	} // activateGameElementsBD

	function activateGameElements (target, animate) {
		if( sayso.starbar.game._gamer ) {
			activateGameElementsBD( target, animate );
			return;
		}

		var userPurchasesContainerElems = $SQ('.sb_user_purchases', target);
		var levelIconsContainerElems = $SQ('.sb_user_level_icons_container', target);
		var currencyBalanceNextLevelElems = $SQ('.sb_currency_balance_next_level', target);
		var currencyTitleElems = $SQ('.sb_currency_title', target);
		var currencyBalanceElems = $SQ('.sb_currency_balance', target);
		var currencyPercentElems = $SQ('.sb_currency_percent', target);
		var progressBarElems = $SQ('.sb_progress_bar', target);
		var userLevelNumberElems = $SQ('.sb_user_level_number', target);
		var userLevelTitleElems = $SQ('.sb_user_level_title', target);
		var userCurrentLevelIconElems = $SQ('.sb_user-current-level-icon', target);
		var animationDuration = 2000; // milliseconds
		var justInitialized = false;



		if (target || ! sayso.starbar.previous_game || sayso.starbar.previous_game._gamer) {
			sayso.starbar.previous_game = sayso.starbar.game;
			justInitialized = true;
			animate = false;
		}

		var game = sayso.starbar.game;
		var previous_game = sayso.starbar.previous_game;

		if( !game.levels[game.level+1] )
			game.levels[game.level+1] = { threshold: game.levels[game.level] + 50000 };

		var justLeveledUp = game.level != previous_game.level;

		if (justInitialized || justLeveledUp) {
			if (userLevelNumberElems.length > 0) {
				userLevelNumberElems.each(function() {
					$SQ(this).html(game.level);
					if (animate) {
						$SQ(this).effect("pulsate", { times:3 }, parseInt(animationDuration/3));
					}
				});
			}

			if (userLevelTitleElems.length > 0) {
				userLevelTitleElems.each(function() {
					$SQ(this).html(game.levels[game.level].name);
					if (animate) {
						$SQ(this).effect("pulsate", { times:3 }, parseInt(animationDuration/3));
					}
				});
			}
		}

		if (currencyBalanceNextLevelElems.length > 0) {
			currencyBalanceNextLevelElems.each(function() {
				$SQ(this).html(game.levels[game.level+1].threshold);
			});
		}

		if (userPurchasesContainerElems.length > 0) {
			userPurchasesContainerElems.each(function() {
				if (!$SQ.isEmptyObject(game.purchasables)) {
					$SQ(this).html('');
					$SQ.each(game.purchasables, function (index, good) {
						var goodDiv = $SQ(document.createElement('div'));
						var goodHtml;
						var goodImageSrc = good.img_url_preview;
						if (good.img_url_preview_bought) goodImageSrc = good.img_url_preview_bought;

						goodDiv.addClass('sb_user_purchase');
						if (good.type == 'token') {
							goodHtml = '<img src="'+goodImageSrc+'" class="sb_user_purchase_img sb_tooltip" title="'+good.name+' - '+good.quantity+' Tokens Purchased" />';
							goodHtml += '<span class="sb_user_puchase_quantity sb_tooltip" title="'+good.name+' - '+good.quantity+' Tokens Purchased">'+good.quantity+'</span>';
						} else {
							goodHtml = '<img src="'+goodImageSrc+'" class="sb_user_purchase_img sb_tooltip" title="'+good.name+'" />';
						}
						goodDiv.html(goodHtml);
						userPurchasesContainerElems.append(goodDiv);
					});
				} else {
					$SQ(this).html('<p>Head to the Rewards Center to redeem your '+game.currencies.redeemable.name+' for awesome prizes!</p>');
				}
			});
		}

		if (userCurrentLevelIconElems.length > 0) {
			userCurrentLevelIconElems.each(function() {
				var iconElem = $SQ(this);
				iconElem.css("background-image", "url('"+game.levels[game.level].img_url_small+"')");
				iconElem.css("background-position", "center bottom");
			});
		}

		if (levelIconsContainerElems.length > 0) {
			levelIconsContainerElems.each(function() {
				var containerElem = $SQ(this);
				containerElem.html('');
				var numberOfVisibleLevels = parseInt(containerElem.attr('rel'));
				if (isNaN(numberOfVisibleLevels) || numberOfVisibleLevels < 1) numberOfVisibleLevels = 5;

				var levelGroup = null;

				for (var index = 0; index < game.max_level; index++) {
					var level = game.levels[index+1];

					if (index % numberOfVisibleLevels == 0) {
						levelGroup = $SQ(document.createElement('div'));
						levelGroup.addClass('sb_userLevelIcons_group');
						containerElem.append(levelGroup);
					}

					var levelIcon = $SQ(document.createElement('div'));

					levelIcon.addClass('sb_userLevelIcons');

					if (level.ordinal == game.level) {
						/* THIS IS THE CURRENT LEVEL */

						switch (sayso.starbar.shortName) {
							case "movie":
								currentlevelText = "L"+(index+1)+'. '+level.name+'</p><p>'+level.threshold+' Stars';
								break;
							case "machinima":
								currentlevelText = level.threshold;
								break;
							default:
								currentlevelText = level.threshold;
						}

						levelIcon.addClass('sb_userLevel_current');
						levelIcon.html('<div class="sb_userLevelImg" style="background-image: url(\''+level.img_url+'\')"><div class="sb_theme_textNotifyCurrent"><p>'+currentlevelText+'</p></div></div>');

					} else {

						if (level.ordinal < game.level) {

							switch (sayso.starbar.shortName) {
								case "movie":
									earnedlevelText = "L"+(index+1)+". "+level.name+"</p><p>"+level.threshold+" Stars";
									break;
								case "machinima":
									earnedlevelText = level.ordinal;
									break;
								default:
									earnedlevelText = level.ordinal;
							}

							levelIcon.addClass('sb_userLevel_earned');
							levelIcon.html('<div class="sb_userLevelImg" style="background-image: url(\''+level.img_url_small+'\')"><p>'+earnedlevelText+'</p></div>');

						} else { // level.ordinal > userCurrentLevel.ordinal

							/* THIS IS A FUTURE LEVEL */

							switch (sayso.starbar.shortName) {
								case "movie":
									blankImageUrl = "http://app.saysollc.com/images/movie/img_level_blank.png";
									nextlevelText = "L"+(index+1)+". "+level.name+"</p><p>"+level.threshold+" Stars";
									break;
								case "machinima":
									blankImageUrl = "http://app.saysollc.com/images/machinima/level_blank.png";
									nextlevelText = level.threshold;
									break;
								default:
									// Using the Machinima circle as the default image
									blankImageUrl = "http://app.saysollc.com/images/machinima/level_blank.png";
									nextlevelText = level.threshold;
							}

							levelIcon.addClass('sb_userLevel_next');
							levelIcon.html('<div class="sb_userLevelImg" onmouseover="this.style.backgroundImage=\'url('+level.img_url_small+')\';this.innerHTML=\'<p>'+nextlevelText+'</p>\'" onmouseout="this.style.backgroundImage=\'url('+blankImageUrl+')\';this.innerHTML=\'\'"></div>');

						}
					}
					levelGroup.append(levelIcon);
				};


				var emptyLevelsToAdd = numberOfVisibleLevels - ((game.max_level) % numberOfVisibleLevels);
				if (emptyLevelsToAdd < numberOfVisibleLevels) {
					while (emptyLevelsToAdd > 0) {
						levelGroup.append('<div class="sb_userLevelIcons sb_userLevel_next"><div class="sb_userLevelImg sb_userLevel_empty"></div><p><br /></p></div>');
						emptyLevelsToAdd--;
					}
				}

				containerElem.cycle({
					prev :			'#sb_userLevel_prev',
					next:			'#sb_userLevel_next',
					fx: 			'scrollHorz',
					speed:			500,
					timeout:		0,
					nowrap:			true,
					startingSlide:	parseInt(Math.floor((game.level - 1) / numberOfVisibleLevels))
				});
			});
		}

		if (currencyTitleElems.length > 0) {
			currencyTitleElems.each(function(){
				if ($SQ(this).attr('data-currency-type') == "experience") {
					$SQ(this).html(game.currencies.experience.name);
				} else if ($SQ(this).attr('data-currency-type') == "redeemable") {
					$SQ(this).html(game.currencies.redeemable.name);
				}
			});
		}

		if (progressBarElems.length > 0) {
			progressBarElems.each(function(){
				if (!$SQ(this).hasClass('sb_ui-progressbar')) {
					$SQ(this).addClass('sb_ui-progressbar sb_ui-widget sb_ui-widget-content sb_ui-corner-all');
				}
			});
		}

		$SQ.each(game.currencies, function (currencyType, currency) {
			var currencyNeedsUpdate = false;
			var previousCurrencyBalance;
			
			if( previous_game.currencies[currencyType] ) {
				previousCurrencyBalance = previous_game.currencies[currencyType].balance
			} else {
				currencyNeedsUpdate = true;
				previousCurrencyBalance = game.currencies[currencyType].balance
			}

			if (justInitialized || game.currencies[currencyType].balance != previousCurrencyBalance) currencyNeedsUpdate = true;

			if (currencyNeedsUpdate) {
				if (currencyBalanceElems.length > 0) {
					currencyBalanceElems.each(function() {
						var $SQthis = $SQ(this);
						if ($SQthis.attr('data-currency-type') == currencyType) {
							if (animate) { // New value, play animation
								var originalColor = $SQthis.css('color');
								// total duration is doubled when leveling up
								var durationMultiplier = 4/5;
								if (justLeveledUp) {
									durationMultiplier = 9/5;
								}
								// Prepare the element for numeric 'animation' (i.e. tweening the number)
								$SQthis.animate(
									{ animationCurrencyBalance: previousCurrencyBalance },
									{ duration : 0 }
								).animate(
									{
										color : 'red',
										animationCurrencyBalance : game.currencies[currencyType].balance
									},
									{
										duration : parseInt(animationDuration*durationMultiplier),
										step : function (now, fx) {
											$SQthis.html(parseInt(now));
										},
										complete : function () {
											$SQthis.html(game.currencies[currencyType].balance);
											$SQthis.css('color', originalColor);
										}
									}
								).animate(
									{ color : originalColor },
									{ duration : parseInt(animationDuration/5) }
								);
							} else {
								$SQthis.html(currency.balance);
							}
						}
					});
				}

				if (currencyType == 'experience' && currencyPercentElems.length > 0) {
					if (currency.balance > game.levels[game.level].threshold) {
						currencyPercent = Math.round((currency.balance - game.levels[game.level].threshold)/(game.levels[game.level+1].threshold - game.levels[game.level].threshold)*100);
					} else {
						currencyPercent = 0;
					}

					if (currencyPercent > 100) currencyPercent = 100; // technically this should never happen

					currencyPercentElems.each(function() {
						var $SQthis = $SQ(this);
						var startingWidth = $SQthis.width();
						var availableWidth = $SQthis.parent().width();
						var newWidth = Math.round(availableWidth * currencyPercent/100);
						if (!$SQthis.hasClass('sb_ui-progressbar-value')) {
							$SQthis.addClass('sb_ui-progressbar-value sb_ui-widget-header sb_ui-corner-left');
						}
						if ($SQthis.attr('data-currency-type') == currencyType) {
							if (animate && !justLeveledUp) {
								var animatingBarElem = $SQ(document.createElement('div'));
								var fadingBarElem = $SQ(document.createElement('div'));
								var progressBarElem = $SQthis; // so it can be accessed from setTimeout()
								animatingBarElem.addClass('sb_ui-progressbar-value-animating sb_ui-widget-header sb_ui-corner-left');
								animatingBarElem.css('width', startingWidth+'px');
								fadingBarElem.addClass('sb_ui-progressbar-value-fading sb_ui-widget-header sb_ui-corner-left');
								fadingBarElem.css('width', newWidth+'px');

								animatingBarElem.insertBefore($SQthis);
								fadingBarElem.insertBefore($SQthis);
								fadingBarElem.fadeTo(0, 0);

								animatingBarElem.animate(
									{ width : newWidth+'px' },
									{ duration : parseInt(animationDuration*2/5) }
								);
								setTimeout(function() {
									fadingBarElem.fadeTo(parseInt(animationDuration*3/5), 1);
									}, parseInt(animationDuration*2/5));

								setTimeout(function() {
									progressBarElem.css('width', newWidth+'px');
									animatingBarElem.annihilate();
									fadingBarElem.annihilate();
									}, animationDuration);
							} else if (animate && justLeveledUp) {
								var animatingBarElem = $SQ(document.createElement('div'));
								var progressBarElem = $SQthis; // so it can be accessed from setTimeout()
								animatingBarElem.addClass('sb_ui-progressbar-value-animating sb_ui-widget-header sb_ui-corner-left');
								animatingBarElem.css('width', startingWidth+'px');

								animatingBarElem.insertBefore($SQthis);

								animatingBarElem.animate(
									{ width : availableWidth+'px' },
									{ duration : parseInt(animationDuration*2/5) }
								);
								setTimeout(function() {
									progressBarElem.fadeTo(parseInt(animationDuration), 0);
									}, parseInt(animationDuration*2/5));
								setTimeout(function() {
									progressBarElem.css('width', newWidth+'px');
									progressBarElem.fadeTo(parseInt(animationDuration*3/5), 1);
									animatingBarElem.fadeTo(parseInt(animationDuration*3/5), 0);
									}, parseInt(animationDuration*7/5));
								setTimeout(function() {
									animatingBarElem.annihilate();
									}, animationDuration*2);
							} else { // No animation
								$SQthis.css('width', newWidth+'px');
							}
						}
					});
				}
			}
		}); // each currency

		// So the next time activateGameElements is called, we don't assume the user just got the points/level-ups
		sayso.starbar.previous_game = sayso.starbar.game;
	} // activateGameElements

	function updateProfileElements() {
		var user = sayso.state.user.data;
		var userSocials;

		if (user && user._user_socials && user._user_socials.items)
			userSocials = user._user_socials.items;

		// Update Profile Image and connect icons
		if (userSocials) {
			$SQ.each(userSocials, function (index, userSocial) {
				var connectIcon = $SQ('#sb_profile_'+userSocial.provider);
				if (connectIcon && connectIcon.hasClass('sb_unconnected')) {
					connectIcon.unbind();
					connectIcon.attr('href', '');
					connectIcon.removeClass('sb_unconnected');
					connectIcon.addClass('sb_connected');
				}
				if (userSocial.provider == 'facebook') {
					var profileImages = $SQ('img.sb_userImg');
					if (profileImages.length > 0) {
						profileImages.each(function(){
							$SQ(this).attr('src', '//graph.facebook.com/'+userSocial.identifier+'/picture?type=square');
						});
					}
				}
			});
		}

		// Update Username
		if (user.username)
			$SQ('.sb_user_title').removeClass('sb_user_level_title').html(user.username);
	}

	function updateProfile() {
		forge.message.broadcastBackground( 'update-profile');
	}

	forge.message.listen( 'update-profile', function( content ) {
		sayso.state.user.data = content;
		updateProfileElements();
	});

	function activateTabs(target){
		// only set up the tabs if they're there
		if ($SQ('.sb_tabs', target).length > 0){
			$SQ('.sb_tabs', target).each(function(){
				$SQ(this).tabs({
					show: function(event, ui){
						// re-call the scrollbar to re-initialize to avoid the "flash" of narrow content.
						activateScroll(target);

						// adding ID to determine which tab is selected
						$SQ('ul.sb_ui-tabs-nav', this).attr('id','');
						$SQ('ul.sb_ui-tabs-nav', this).attr('id','sb_ui-tabs-nav_'+eval(ui.index+1));

						// reset child tabs to 0
						$SQ('.sb_tabPane ul.sb_ui-tabs-nav', this).attr('id','');
					}
				});
			});
		}
	}

	function activateScroll(target){
		// first, resize the scrollpane dynamically to fit whatever height it lives in (.content.height() - .header.height())
		var contentHeight = $SQ('.sb_popContent', target).height();
		var headerHeight = 0;

		// add height of the header + any margins / paddings
		if ($SQ('.sb_popContent .sb_header', target).length > 0){
			headerHeight = $SQ('.sb_popContent .sb_header',target).biggestHeight();
		}else{
			headerHeight = 0;
		}

		// recalculate if we're using 2 column layout. e.g the user profile popup
		if ($SQ('.sb_popContent .sb_column60', target).length > 0){
			headerHeight = $SQ('.sb_popContent  .sb_column60 .sb_header',target).biggestHeight();
		}

		if ($SQ('.sb_popContent .sb_column40', target).length > 0){
			headerHeight = $SQ('.sb_popContent  .sb_column60 .sb_header',target).biggestHeight();
		}

		var ajaxContainer = $SQ('.sb_popContent #sayso-starbar-ajax-content', target);
		if (ajaxContainer.length > 0){
			headerHeight += parseInt(ajaxContainer.css('margin-top')) + parseInt(ajaxContainer.css('margin-bottom'));
		}

		var panes = $SQ('.sb_scrollPane',target);
		panes.each(function(i) {
			// Add height of all the paragraphs (or anything with the class "sb_tabHeader" really)
			var paragraphs = $SQ('.sb_tabHeader', $SQ(this).parent());
			var paragraphHeight = 0;
			paragraphs.each(function(i) {paragraphHeight += $SQ(this).biggestHeight();});

			// special rule to handle if there are 2 columns in a popbox, check to see if any doesn't have a header, if it doesn't, change the height of the scroll.
			var parent = $SQ(this).parent();

			if (parent.children(':first').hasClass('sb_scrollPane')){
				$SQ(this).css('height',contentHeight);
			}else{
				$SQ(this).css('height',contentHeight-(paragraphHeight+headerHeight));
			}

			$SQ(this).jScrollPane();
		});
	}

	function activateAccordion(target){
		if ($SQ('.sb_tabs', target).length > 0){
			$SQ('.sb_tabs .sb_tabPane', target).each(function(){
				$SQ('.sb_accordion',this).accordion({
					collapsible: true, // Accordion can have all its divs be closed simultaneously
					active: false, // All accordion divs are closed by default
					// find the link that caused the accordian to open, take the href, and set the src of the inner iframe to it
					changestart: function(event, ui){
						var activeLink = ui.newHeader.find('a');
						var frameHeight = parseInt(activeLink.attr('iframeHeight'));
						var activeFooter = ui.newContent.find('.sb_nextPoll');
						var link = "";

						// Hide the footer (share links, next survey links)
						if (activeFooter){
							activeFooter.fadeTo(0, 0);
						}

						// Load the iframe if not already loaded
						if (activeLink.length > 0 && activeLink.attr('loaded') != "true") {
							// The iframe's height is calculated and set by the controller, use it to set the size of the accordion
							ui.newContent.css('height', frameHeight+5);

							// Add the authentication info to the request
							link = activeLink.attr('href');
							link = ('https:' == document.location.protocol ? 'https:' : 'http:') + link; // Add http: or https:
							if (link.indexOf("?") == -1)
								link += "?";
							else
								link += "&";
							link += "user_id="+sayso.starbar.user.id+"&user_key="+sayso.starbar.user.key+"&starbar_id="+ sayso.starbar.id;

							// The container for the new iFrame is in the link's 'ref' attribute
							var iFrameContainerId = activeLink.attr('rel');
							sayso.fn.insertCommunicationIframe(link, iFrameContainerId, 470, parseInt(activeLink.attr('iframeHeight')), "no");

							activeLink.attr('loaded', 'true');
						}

						// Fade in the footer
						if (activeFooter){
							setTimeout(function(){
								activeFooter.fadeTo(500, 1);
								}, 2000);
						}
					},
					change: function (event, ui){
						var scrollPane = $SQ(this).parents('.sb_scrollPane')
						scrollPane.jScrollPane(); // re-initialize the scroll pane now that the content size may be different
						if (ui.newHeader.position()) {  // if the accordion is open
							var paneHandle = scrollPane.data('jsp');

							var currentScroll = paneHandle.getContentPositionY();
							var topOfOpenAccordion = ui.newHeader.position().top;
							var bottomOfOpenAccordion = topOfOpenAccordion+ui.newHeader.outerHeight(true)+ui.newContent.outerHeight(true);
							var sizeOfPane = scrollPane.height();

							if ((bottomOfOpenAccordion - currentScroll) > (sizeOfPane - 10)) { // - 24 for the extra padding
								paneHandle.scrollByY((bottomOfOpenAccordion - currentScroll) - (sizeOfPane - 10)); // scroll by the difference
							}
						}
					}
				});
			});
		}else{
			$SQ('.sb_accordion', target).accordion({
				collapsible: true
			});
		}

		return;
	}

	function activateSlideshow(target){
		if ($SQ('#sb_slideshow', target).length > 0){
			$SQ('#sb_slideshow', target).cycle({
				timeout: 0,
				speed: 500,
				next: '#sb_slideshow .sb_next',
				prev: '#sb_slideshow .sb_prev'
			});
		}
	}

	function activateOverlay(target){
		var overlay = $SQ('.sb_outerOverlay', target);
		if (overlay.length == 1){
			enableConfirmBeforeUnload();
			overlay.unbind();
			overlay.bind('click', function (event) {
				if (confirm("If you leave this survey, you will lose any unsaved progress. Are you sure you want to leave this survey?")) {
					hideOverlay();
					closePopBox();
				}
			});
		}
		overlay = $SQ('#sb_innerOverlay', target);
		if (overlay.length == 1){
			overlayBackground = $SQ('.sb_innerOverlayBackground', overlay);
			overlayBackground.bind('click', function (event) {
				overlay.fadeOut(200);
			});
		}
	}

	function activateToolTips(target){
		elemTooltip = $SQ('.sb_tooltip', target);

		// tooltip binding
		elemTooltip.each(function(){
			$SQ(this).easyTooltip();
		});
	}

	function activateEditInPlaceElems(target) {
		// jquery edit in place
		var elemJEIP = $SQ('.sb_jeip', target);

		// set up the EIP elements
		elemJEIP.each(function(){
			$SQ(this).eip(
				"//"+sayso.baseDomain+"/api/user/save-in-place?user_id="+sayso.starbar.user.id+"&user_key="+sayso.starbar.user.key+"&starbar_id="+ sayso.starbar.id,
				{
					savebutton_text		: "save",
					savebutton_class	: "sb_theme_button",
					cancelbutton_text	: "cancel",
					cancelbutton_class	: "sb_theme_button sb_theme_button_grey",
					after_save			: updateProfile
				}
			);
		});
	}

	function activateExternalConnectElems(target) {
		var elemExternalConnect = $SQ('.sb_unconnected', target);

		// connect with facebook or twitter
		elemExternalConnect.each(function(){
			$SQ(this).unbind().bind({
				click: function(event){
					event.preventDefault();
					event.stopPropagation();

					var windowParameters = 'location=1,status=1,scrollbars=0';
					switch($SQ(this).attr('id')) {
						case "sb_profile_facebook":
							windowParameters += ',width=981,height=440';
							break;

						case "sb_profile_twitter":
							windowParameters += ',width=750,height=550';
							break;
					}
					var link = $SQ(this).attr('href');
					if (link.indexOf("?") == -1)
						link += "?";
					else
						link += "&";
					link += "user_id="+sayso.starbar.user.id+"&user_key="+sayso.starbar.user.key+"&starbar_id="+ sayso.starbar.id;

					return $SQ.openWindow(link, 'sb_window_open', windowParameters);
				}
			});
		});
	}

	function fixPlaceholders (target) {
		$SQ("input[placeholder]", target).each(function(index){
			var inputElem = $SQ(this);
			var placeholder = inputElem.attr('placeholder');

			if (! inputElem.val()) {
				inputElem.css('color', '#AAA');
				inputElem.val(placeholder);
			}

			inputElem.focus(function() {
				if (inputElem.val() === placeholder) {
					inputElem.val('');
					inputElem.css('color', 'black');
				}
			});

			inputElem.blur(function() {
				if (! inputElem.val()) {
					inputElem.css('color', '#AAA');
					inputElem.val(placeholder);
				}
			});
		});
	}

	function devInit(){


	}

	function enableConfirmBeforeUnload () {
		if (!sayso.overwrite_onbeforeunload) {
			if (window.onbeforeunload) {
				sayso.old_onbeforeunload = window.onbeforeunload;
			}
			sayso.overwrite_onbeforeunload = true;
			window.onbeforeunload = confirmBeforeLeavingSurvey;
		}
	}

	function confirmBeforeLeavingSurvey () {
		return "If you leave this survey, you will lose any unsaved progress.";
	}

	function revertConfirmBeforeUnload () {
		if (sayso.overwrite_onbeforeunload) {
			if (sayso.old_onbeforeunload) {
				window.onbeforeunload = sayso.old_onbeforeunload;
				sayso.old_onbeforeunload = null;
			} else {
				window.onbeforeunload = null;
			}
			sayso.overwrite_onbeforeunload = false;
		}
	}

	function hideOverlay() {
		var overlay = $SQ('.sb_outerOverlay', starbarElem);
		if (overlay.length == 1) {
			revertConfirmBeforeUnload();
			overlay.fadeTo(200, 0);
			setTimeout(function () {
				overlay.annihilate();
				}, 200);
		}
	}

	// animates the starbar-player-console bar based on current state
	forge.message.listen('set-visibility', function( visibility ) {
		starbar.state.visibility = visibility;
		switch (visibility){
			case 'stowed':
				stowBar();
				break;
			case 'open':
				openBar();
				break;
		}
	});

	function stowBar () {
		closePopBox();
		elemStarbarMain.fadeTo('fast', 0);
		btnToggleVis.attr('class','').addClass('sb_btnStarbar-closed');
		btnSaySoLogo.css('backgroundPosition','3px 0px');

		if (sayso.disableJqueryEffects) {
			starbar.stowing = true;
			elemPlayerConsole.attr('class','').addClass('sb_starbar-visClosed');
			setTimeout(function () {
				btnToggleVis.attr('class','').addClass('sb_btnStarbar-stowed');
				elemPlayerConsole.attr('class','').addClass('sb_starbar-visStowed');
				starbar.stowing = false;
				}, 1000);
		} else {
			elemPlayerConsole.animate(
				{ width: '100' },
				500,
				function() {
					// Animation complete.
					elemPlayerConsole.attr('class','').addClass('sb_starbar-visClosed');
					elemSaySoLogoSemiStowed.parent().show();
					elemSaySoLogoSemiStowed.fadeTo(0, 1);
					elemPlayerConsole.fadeTo(500, 0);
					setTimeout(function () {
						elemPlayerConsole.hide();
						}, 510);
					setTimeout(function () {
						btnToggleVis.attr('class','').addClass('sb_btnStarbar-stowed');
						btnSaySoLogo.css('backgroundPosition','');
						elemPlayerConsole.css('width','');
						elemPlayerConsole.attr('class','').addClass('sb_starbar-visStowed');
						elemPlayerConsole.show();
						elemPlayerConsole.fadeTo(157, 1); // 157 found to work best for some bizarre reason
						elemSaySoLogoSemiStowed.fadeTo(500, 0);
						}, 1000);
					setTimeout(function () {
						elemSaySoLogoSemiStowed.parent().hide();
						}, 1500);
				}
			);
		}
	}

	function openBar () {
		btnToggleVis.attr('class','');
		elemSaySoLogoBorder.hide();
		elemVisControls.hide();
		elemPlayerConsole.addClass('sb_starbar-visBG');
		if (sayso.disableJqueryEffects) {
			elemPlayerConsole.attr('class','').addClass('sb_starbar-visOpen');
			elemStarbarMain.fadeTo('fast', 1);
			elemVisControls.fadeTo('fast', 1);
			btnToggleVis.attr('class','').addClass('sb_btnStarbar-open');
		} else {
			elemPlayerConsole.animate(
				{ width: '100%' },
				500,
				function() {
					// Animation complete.
					elemPlayerConsole.attr('class','').addClass('sb_starbar-visOpen');
					elemStarbarMain.fadeTo('fast', 1);
					elemVisControls.fadeTo('fast', 1);
					btnToggleVis.attr('class','').addClass('sb_btnStarbar-open');
				}
			);
		}
	}

	// flag so we know this file has loaded
	sayso.starbar.loaded = true;

	// initialize switcher component - using named function for global ref
	function initSwitcher(){

		// fake-preload images
		var preload = function(){
			for(var i = 0; i < arguments.length; i++){
				new Image().src = arguments[i];
			}
		};

		/* switcher behavior @MDD 07/25/12 */
		var Switcher = {

			// elements
			panel : null,
			main : null,
			container : null,
			tab : null,
			drawer : null,
			slider : null,
			loader : null,

			// misc variables
			pngOffset : 10,  // transparent pixels surrounding profile area background graphic
			tabHeight : 17,
			paddingOffset : 8,  // top + bottom padding of drawer element
			differential : 7,  // needed to offset panel height measurement routine
			loaderOffset : 51,  // space to show while loading

			// flags
			isCreated : false,
			isLoaded : false,
			isShowing : false,

			// listeners
			onLoad :  function(response, status){

				Switcher.isLoaded = true;

				// render element invisibly, measure, fade out loader, animate to correct height, fade in elements
				Switcher.loader.hide();
				var fader = $SQ('<div>');
				fader.css('opacity', 0);
				fader.html(response.data.html);
				Switcher.slider.append(fader);
				var updatedHeight = Switcher.getContentHeight() + Switcher.getPanelHeight();
				fader.hide();
				Switcher.loader.show();
				Switcher.slider.animate({
					'height' : updatedHeight
					}, function(){
						Switcher.loader.remove();
						fader.show();
						fader.fadeTo(500, 1);
				});

				Switcher.assignBehavior();

			},

			log : function(message){
				$SQ(document.body).append(message + '<br />');
			},

			// get height of panel itself
			getPanelHeight : function(){
				// can't use height (or any derivation) because some profile areas have extra space pulled beneath window.  instead measure window and top
				var top = Switcher.panel.offset().top;
				var windowHeight = $SQ(window).height();
				return windowHeight - top + Switcher.differential;
			},

			// get height of actual content
			getContentHeight : function(){
				return Switcher.slider.get(0).scrollHeight;
			},

			/* methods */

			// assign initial handlers, preload backgrounds
			prepare : function(){

				// preload the tab - chrome must think it should be rendered so use positioning to hide
				var preloadedTab = $SQ('<div>');
				preloadedTab.addClass('starbar-switcher-tab');
				preloadedTab.css({
					position: 'absolute',
					top : '-99999px'
				});
				starbarElem.append(preloadedTab);

				// initialize on first mouseover - this prevents wonky layout when bar is initially hidden
				$SQ('#starbar-type', starbarElem).unbind().bind('mouseover', Switcher.create);

			},

			// create, position, handle, add to dom
			create : function(){

				// only create shell once
				if(Switcher.isCreated){
					return;
				};

				// remove preloaded tab graphic
				$SQ('.starbar-switcher-tab').remove();

				// remove existing if lingering from previous bar load operation
				$SQ('.starbar-switcher', starbarElem).remove();

				// indicate we exist
				Switcher.isCreated = true;

				// assign hover behavior to profile area, to show/hide tab
				$SQ('#starbar-type', starbarElem).unbind().hover(Switcher.showTab, Switcher.hideTab);

				// get ancestor handles
				Switcher.panel = $SQ('#starbar-type', starbarElem);
				Switcher.main = $SQ('#starbar-main', starbarElem);

				// create and position container
				Switcher.container = $SQ('<div>');
				Switcher.container.hide();
				Switcher.container.addClass('starbar-switcher');
				Switcher.container.css({
					'width' : (Switcher.panel.width() - Switcher.pngOffset) + 'px',
					'left' : Switcher.panel.css('left')
				});

				// create tab
				Switcher.tab = $SQ('<div>');
				Switcher.tab.addClass('starbar-switcher-tab');
				Switcher.container.append(Switcher.tab);

				// create "drawer" (element beneath tab, with background and corners)
				Switcher.drawer = $SQ('<div>');
				Switcher.drawer.addClass('starbar-switcher-drawer');
				Switcher.container.append(Switcher.drawer);

				// create overflow: hidden element that we'll control height through
				Switcher.slider = $SQ('<div>');
				Switcher.slider.css('overflow', 'hidden');
				Switcher.drawer.append(Switcher.slider);

				// create loader initially - leave it until view is loaded
				Switcher.loader = $SQ('<div>');
				Switcher.loader.addClass('sb_img_loading');
				Switcher.loader.addClass('starbar-switcher-loader');
				Switcher.slider.append(Switcher.loader);

				// insert the entire component into #starbar-main
				Switcher.main.append(Switcher.container);

				// if mouseout of tab, but component not fully open, hide tab
				Switcher.tab.on('mouseout', Switcher.hideTab);

				// on tab click, show/hide component
				Switcher.tab.on('click', Switcher.toggle);

				// hide the tab until moused over
				Switcher.slider.css({
					height: (Switcher.getPanelHeight() - Switcher.tabHeight - Switcher.paddingOffset - Switcher.pngOffset) + 'px'
				});

				// everything should be rendered properly now, go ahead and reveal
				Switcher.container.show();

				// and animate tab in first time
				Switcher.showTab();

			},

			// ajax in view phtml
			load : function(){
				if(Switcher.isLoaded){
					return;
				};
				Switcher.isShowing = true;
				Switcher.tab.addClass('starbar-switcher-active');
				Switcher.slider.animate({
					'height' : Switcher.getPanelHeight() + Switcher.loaderOffset
				});
				sayso.fn.ajaxWithAuth({
					url : '//' + sayso.baseDomain + '/starbar/content/starbar-list',
					success : Switcher.onLoad
				});
			},

			// show the entire component
			show : function(){
				if(!Switcher.isLoaded){
					Switcher.load();
					return;
				}
				if(Switcher.isShowing){
					return;
				}
				Switcher.isShowing = true;
				Switcher.tab.addClass('starbar-switcher-active');
				Switcher.slider.stop(true, false).animate({
					height : Switcher.getContentHeight() + Switcher.getPanelHeight()
				});
			},
			// hide the entire component
			hide : function(){
				if(!Switcher.isShowing){
					return;
				}
				Switcher.isShowing = false;
				Switcher.tab.removeClass('starbar-switcher-active');
				Switcher.slideOut();
			},
			// hide if showig, show if hidden
			toggle : function(){
				if(Switcher.isShowing){
					Switcher.hide();
				} else {
					Switcher.show();
				}
			},

			// after view is loaded, assign event handlders
			assignBehavior : function(){

				// switch bar when active community is clicked
				$SQ('.starbar-switcher-my-communities .sb_starbar-switch', starbarElem).on('click', Switcher.changeBar);

				// when meta box is rolled over, show tooltip
				$SQ('.starbar-switcher-available-communities .starbar-switcher-meta', starbarElem).on('mouseenter', Switcher.showInfo);
			},


			/* DOM handlers pre-load */

			// on hover of profile area, show tab
			showTab : function(){
				// if the component is showing, ignore mouseouts
				if(Switcher.isShowing){
					return true;
				};
				Switcher.slider.stop(true, false).animate({
					height : Switcher.getPanelHeight() - Switcher.paddingOffset - Switcher.pngOffset
				});
			},
			// on mouseout of profile area, hide tab
			hideTab : function(e){
				// if the component is showing, ignore mouseouts
				if(Switcher.isShowing){
					return true;
				};

				// who's mousing out and who's moused onto?
				var firer = $SQ(e.currentTarget);
				var onto = $SQ(e.relatedTarget);

				// if mousing-out of profile area...
				if(firer.is('#starbar-type')){
					// if mousing onto the switcher, don't hide tab
					if(onto.is('.starbar-switcher')){
						return true;
					};
					if(onto.parents('.starbar-switcher').length > 0){
						return true;
					};
				// if mousing-out of the tab...
				} else if(firer.is('.starbar-switcher-tab')){
					// if mousing onto the profile area, don't hide tab
					if(onto.is('#starbar-type')){
						return true;
					};
					if(onto.parents('#starbar-type').length > 0){
						return true;
					};
				}

				Switcher.slideOut();
			},
			// calculate and animate container to just beneath profile top
			slideOut : function(){
				Switcher.slider.stop(true, false).animate({
					height: Switcher.getPanelHeight() - Switcher.tabHeight - Switcher.paddingOffset - Switcher.pngOffset
				});
			},

			/* DOM handlers post-load */

			// show the bar into tooltip
			showInfo : function(){

				// general references
				var handle = $SQ(this);
				var row = handle.parents('.starbar-switcher-row');
				var id = row.attr('rel');

				// remove all existing tooltips not assigned to this bar
				$SQ('.starbar-switcher-tooltip').not('[rel=' + id + ']').remove();

				// if the appropriate tooltip is already open, skip it
				var existing = $SQ('.starbar-switcher-tooltip[rel=' + id + ']');
				if(existing.length > 0){
					existing.fadeIn();
					return true;
				}

				// create the container to insert into the tooltop
				var container = $SQ('<div>');

				// get the contextual element
				var context = handle.find('.starbar-switcher-info-content');

				// get info text and stick it in a new div
				var info = context.text();
				var content = $SQ('<div>');
				content.addClass('starbar-switcher-detail');
				content.text(info);
				container.append(content);

				// create the accept mini-form
				var signup = $SQ('<div>');
				signup.addClass('starbar-switcher-accept');

				// checkbox - button's enabled state is determined by this element's checked state
				var checkbox = $SQ('<input type="checkbox" />');
				signup.append(checkbox);

				// link target
				var termsURL = '//'+sayso.baseDomain+'/docs/' + context.attr('rel') + '/Say.So_App_EULA.pdf';
				signup.append(' I agree to the <a target="_blank" href="' + termsURL + '">Terms &amp; Conditions</a> ');

				// add button - start of disabled; enable if box is checked
				var button = $SQ('<button>');
				button.addClass('starbar-switcher-add-button');
				button.text('ADD');
				signup.append(button);

				// when checkbox is toggled, enable/disable appearance of button
				checkbox.on('click', function(){
					if(checkbox.prop('checked')){
						button.addClass('starbar-switcher-active').unbind().bind('click', function(){
							forge.message.broadcastBackground('starbar-switch', id)
						});
					} else {
						button.removeClass('starbar-switcher-active').unbind().bind('click', function(){
							window.alert('You must agree to our Terms & Condiditions by checking the box at left before adding this community.');
						});
					}
				});

				// add it to main container
				container.append(signup);

				// create the tooltip and populate it
				var tooltip = Switcher.Tooltip.create(handle, container);

				// assign rel to differentiate from other info tooltips
				tooltip.attr('rel', id);


			},

			// change bar without prompt - this is taken directly from existing code
			changeBar : function() {
				Switcher.hide();
				forge.message.broadcastBackground( 'starbar-switch', parseInt($SQ(this).attr('rel')) );
			},

			// tooltips
			Tooltip : {
				// how long to wait before fading out
				delay : 1500,
				// distance to perform transition
				offset : 4,
				// reference to the activating element
				handle : null,
				// reference to the active tootlip
				element : null,
				// schedule to close
				timer : null,
				// fade out and remove from dom
				close : function(){
					Switcher.Tooltip.handle.unbind('mouseenter', Switcher.Tooltip.enterHandler);
					Switcher.Tooltip.handle.unbind('mouseleave', Switcher.Tooltip.leaveHandler);
					Switcher.Tooltip.element.unbind();
					Switcher.Tooltip.element.fadeOut(function(){
						Switcher.Tooltip.element.remove();
					});
				},
				// clear timer when either handle or tooltip is entered
				enterHandler : function(){
					window.clearTimeout(Switcher.Tooltip.timer);
				},
				// resume timer when handle or tooltip is exited
				leaveHandler : function(){
					window.clearTimeout(Switcher.Tooltip.timer);
					Switcher.Tooltip.timer = window.setTimeout(Switcher.Tooltip.close, Switcher.Tooltip.delay);
				},

				create : function(handle, content){

					// remove any existing tooltips
					$SQ('.starbars-switcher-tooltip').remove();

					// references to parents used throughout
					var drawer = handle.parents('.starbar-switcher-drawer');
					var cell = handle.is('.starbar-switcher-cell') ? handle : handle.parents('.starbar-switcher-cell');

					// create and populate tooltip element
					var tooltip = $SQ('<div>');
					tooltip.addClass('starbar-switcher-tooltip');
					var label = $SQ('<div>');
					label.addClass('starbar-switcher-tooltip-label');
					label.append(content);
					tooltip.append(label);
					var arrow = $SQ('<div>');
					arrow.addClass('starbar-switcher-tooltip-arrow');
					tooltip.append(arrow);

					// insert into DOM
					drawer.append(tooltip);

					// position and fade it in
					var y = cell.position().top + (cell.outerHeight() / 2) - (tooltip.outerHeight() / 2);
					tooltip.css({
						'opacity' : 0,
						'top' : (y - Switcher.Tooltip.offset) + 'px'
					});
					tooltip.animate({
						'opacity' : 1,
						'top' : y + 'px'
					});

					// close in on document mousedown, unless event.target is tooltip or child of tooltip
					$SQ(document).bind('mousedown', function(e){
						var firer = $SQ(e.target);
						if(firer.is('.starbar-switcher-tooltip')){
							return true;
						}
						if(firer.parents('.starbar-switcher-tooltip').length > 0){
							return true;
						}
						Switcher.Tooltip.close();
						$(this).unbind('mousedown', arguments.callee);
					});

					// schedule close
					handle.bind('mouseenter', Switcher.Tooltip.enterHandler);
					handle.bind('mouseleave', Switcher.Tooltip.leaveHandler);
					tooltip.bind('mouseenter', Switcher.Tooltip.enterHandler);
					tooltip.bind('mouseleave', Switcher.Tooltip.leaveHandler);

					// cache references
					Switcher.Tooltip.handle = handle;
					Switcher.Tooltip.element = tooltip;

					// return it
					return tooltip;

				}
			}

		};

		Switcher.prepare();

	};

});
