/**
 * Starbar
 */

$S.ajaxWithAuth = function (options) {
    var starbar_id = null;
    var auth_key = null;
    var user_id = null;
    var user_key = null;

    // Authenticated?
    try
    {
        starbar_id = window.sayso.starbar.id;
        user_id = window.sayso.starbar.user.id;
        user_key = window.sayso.starbar.user.key;
        auth_key = window.sayso.starbar.authKey;
    }
    catch (e) {}

    options.data = $S.extend(options.data || {}, {
        starbar_id : starbar_id,
        user_id : user_id,
        user_key : user_key,
        auth_key : auth_key
    });
    if (!options.dataType) options.dataType = 'jsonp';
		options.beforeSend = function(x) {
			if(x && x.overrideMimeType) {
			 x.overrideMimeType("application/j-son;charset=UTF-8");
			}
	 };
    return $S.ajax(options);
};

$S(function(){

	// global var
    var themeColor = '#de40b2';

	/*
	Set up some extra bits to handle closing windows if the user clicks outside the starbar or hits ESC key
	*/
	$S(document).keyup(function(e) {
		if (e.keyCode == 27) {
			closePopBox();
		}  // esc
		
		if (e.keyCode == 16) {
		    return; // don't fire this. It's annoying. Onboard popup will happen based on conditional logic (which I'm working on next) - David
			var thisPopBox = $S('#sb_popBox_onboard');
			var thisPopBoxSrc = $S('#sb_popBox_onboard').attr('href');
			
			if (thisPopBox.hasClass('sb_popBoxActive')){
				closePopBox(thisPopBox);
			}else{
				closePopBox();
				openPopBox(thisPopBox, thisPopBoxSrc, false);
			}
			
		}  // shift
		
	});

	// close if you click outside the starbar while in the iframe
	$S(document).click(function(e) {
		// don't close if they just right-clicked
		if (e.button != 2){
			closePopBox();
		}
	});
	

	// LETS USE VARS!
	// NOTE: The variables below are initialized in initElements()

	// clickable elements that ppl will interact with
	var btnToggleVis; //  = $S('#sayso-starbar #starbar-visControls #starbar-toggleVis');
	var btnSaySoLogo; // = $S('#sayso-starbar #starbar-visControls #sb_starbar-logo');

	// container elements
	var elemSaySoLogoBorder; // = $S('#sayso-starbar #starbar-player-console #sb_starbar-logoBorder');
	var elemSaySoBarBG; // = $S('#sayso-starbar #starbar-player-console').css('background-image');
	var elemPlayerConsole; // = $S('#sayso-starbar #starbar-player-console');
	var elemStarbarMain; // = $S('#sayso-starbar #starbar-player-console #starbar-main');
	var elemVisControls; // = $S('#sayso-starbar #starbar-player-console #starbar-visControls');
	var elemStarbarClickable; // = $S('#sayso-starbar #starbar-player-console .sb_nav_element');
	var elemPopBox; // = $S('#sayso-starbar #starbar-player-console .sb_popBox');
	var elemAlerts; // = $S('#sayso-starbar #starbar-player-console .sb_starbar-alert');
	var elemPopBoxVisControl; // = $S('#sayso-starbar #starbar-player-console #starbar-visControls .sb_popBox');

	// initialize the starbar
	initStarBar();

	/* FUNCTIONS */

	// initialize the starbar
	function initStarBar(){
		initElements();
		closePopBox();
		showAlerts();
		activateProgressBar();
		// initializes development-only jquery
		devInit();
		log('Loaded and Ready');
	}

	// initialize the elements
	function initElements(){
		// clickable elements that ppl will interact with
		btnToggleVis = $S('#sayso-starbar #starbar-visControls #starbar-toggleVis');
		btnSaySoLogo = $S('#sayso-starbar #starbar-visControls #sb_starbar-logo');

		// container elements
		elemSaySoLogoBorder = $S('#sayso-starbar #starbar-player-console #sb_starbar-logoBorder');
		elemSaySoBarBG = $S('#sayso-starbar #starbar-player-console').css('background-image');
		elemPlayerConsole = $S('#sayso-starbar #starbar-player-console');
		elemStarbarMain = $S('#sayso-starbar #starbar-player-console #starbar-main');
		elemVisControls = $S('#sayso-starbar #starbar-player-console #starbar-visControls');
		elemStarbarClickable = $S('#sayso-starbar #starbar-player-console .sb_nav_element');
		elemPopBox = $S('#sayso-starbar #starbar-player-console .sb_popBox');
		elemAlerts = $S('#sayso-starbar #starbar-player-console .sb_starbar-alert');
		elemPopBoxVisControl = $S('#sayso-starbar #starbar-player-console #starbar-visControls .sb_popBox');
		elemTabClick = $S('#sayso-starbar #starbar-player-console .sb_nav_tabs');
		
		// jquery edit in place
		elemJEIP = $S('#sayso-starbar .sb_jeip');

		elemPlayerConsole.unbind();
		elemPlayerConsole.click(function(e) {
		    e.stopPropagation();
		});


		/* prevent default for any link with # as the href */
		$S('a').each(function(){
			$S(this).unbind();
			if ($S(this).attr('href')=='#'){
				$S(this).bind({
					click: function(e){
						e.preventDefault();
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
			var playerClass = elemPlayerConsole.attr('class');
			animateBar(playerClass, 'button');
			//popBoxClose();
		});

		/*
		Set up logo hover + click action behaviors
		*/

		btnSaySoLogo.unbind();
		btnSaySoLogo.bind({
			click: function(e) {
				e.preventDefault();
				var playerClass = elemPlayerConsole.attr('class');
				if (playerClass != 'sb_starbar-visOpen'){
					// manual override to have any click re-open starbar to original state
					animateBar('sb_starbar-visStowed', 'button');
				}else{
					var thisPopBox = btnSaySoLogo.next('.sb_popBox');

					// if it was already open, close it and remove the class. otherwise, open the popbox
					if (thisPopBox.hasClass('sb_popBoxActive')){
						closePopBox(thisPopBox);
					}else{
						// check if the clickable area had an href. If so, load it into the pop box, then open it. Otherwise, just open it.
            			var thisPopBoxSrc = $S(this).attr('href');
						openPopBox(thisPopBox, thisPopBoxSrc, false);
					}
				}
			},
			mouseenter: function() {
				if (elemPlayerConsole.hasClass('sb_starbar-visClosed')){
					// if it's closed
					elemSaySoLogoBorder.addClass('sb_theme_bgGradient sb_theme_bgGlow').show();
				}
				else{
				}
			},
			mouseleave: function(){
				if (elemPlayerConsole.hasClass('sb_starbar-visClosed')){
					// if it's closed
					elemSaySoLogoBorder.removeClass('sb_theme_bgGradient sb_theme_bgGlow');
				}
				else{

				}
			}
		}); // end logo hover + click actions

		/*
		Set up nav items (click properties to show/hide their popboxes, hover / active sates
		*/
		elemStarbarClickable.each(function(){
			// SPECIAL HANDLING FOR STARBAR LOGO
			if ($S(this).attr('id') == 'sb_starbar-logo'){
				return;
			}

			$S(this).unbind();
			$S(this).bind({
				click: function(event){
					event.stopPropagation();
					event.preventDefault();
					// the popbox is AFTER the clickable area
					var thisPopBox = $S(this).next('.sb_popBox');

					/*
					set up a handler in case we click an element that isn't directly next to its target popbox.
					a linked element outside of the nav will have rel="#ID OF TARGET" set.
					*/
					var targetPopBox = '';
					if ($S(this).attr('rel') !== undefined){
						var targetPopBox = $S(this).attr('rel');

						// reset the popbox it should open to this ID
						thisPopBox = $S('#'+targetPopBox);
					}

					// set a delay before closing the alert element
					if ($S(this).hasClass('sb_alert')){
						hideAlerts($S(this).closest('.sb_starbar-alert'));
					}

					// if it was already open, close it and remove the class. otherwise, open the popbox
					if (thisPopBox.hasClass('sb_popBoxActive')){
						closePopBox(thisPopBox);
					}else{
						// this menu item's popBox is active

						// check if the clickable area had an href. If so, load it into the pop box, then open it. Otherwise, just open it.
		  				var thisPopBoxSrc = $S(this).attr('href');
			  			openPopBox(thisPopBox, thisPopBoxSrc, true);

						// try to turn on the nav highlight if it opened a "large" sub popbox
						if (targetPopBox != ''){
							if (thisPopBox.parents('.sb_theme_bgGradient').hasClass('sb_theme_bgGradient')){
								var listItem = thisPopBox.parents('.sb_theme_bgGradient');
								$S('span.sb_nav_border',listItem).addClass('sb_theme_navOnGradient');
							} // travel op the dom tree to find if the large subpopbox is open
						}// end if targetPopBox != ''

					}
				},
				mouseenter: function(event){
				event.preventDefault();
					if ($S(this).parent().hasClass('sb_theme_bgGradient')){
						$S('span.sb_nav_border', this).addClass('sb_theme_navOnGradient');
					}
				},
				mouseleave: function(event){
				event.preventDefault();
					var thisPopBox = $S(this).next('.sb_popBox');
					// only remove the "hover" class for the nav item if it's box isn't active
					if (($S(this).parent().hasClass('sb_theme_bgGradient')) && (!thisPopBox.hasClass('sb_popBoxActive'))){
						$S('span.sb_nav_border', this).removeClass('sb_theme_navOnGradient');
					}
				}
			}); // end bind
		}); // end each loop for starbarNav

		// to open a specific tab
		elemTabClick.each(function(){
			$S(this).unbind();
			$S(this).bind({
				click: function(event){
					var $tabs = $S('.sb_popBoxActive .sb_tabs').tabs();
					$tabs.tabs('select', $S(this).attr('rel')-1); // switch to third tab
    			return false;
				}
			});
		});
		
		// set up the EIP elements
		elemJEIP.each(function(){
			$S(this).eip( "/api/user/save-in-place", {
				savebutton_text		: "save",
				savebutton_class	: "sb_theme_button",
				cancelbutton_text	: "cancel",
				cancelbutton_class	: "sb_theme_button sb_theme_button_grey",
				
			});									 
		
		});
		

	} // end initElements()

	// animates the starbar-player-console bar based on current state
	function animateBar(playerClass, clickPoint){

	    if (!playerClass) playerClass = window.sayso.starbar.state.visibility;

	    switch(clickPoint){
            // if we're clicking from a button, determine what state we're in and how to shrink / grow
            case 'button':
                switch (playerClass){
                    case 'sb_starbar-visOpen':
                        _closeBar();
                        break;
                    case 'sb_starbar-visClosed':
                        _stowBar();
                        break;
                    case 'sb_starbar-visStowed':
                        _openBar();
                        break;
                }
                break;
            // if refreshing based on current state, then update bar to match
            case 'refresh' :
                switch (playerClass) {
                    case 'sb_starbar-visOpen':
                        if (!btnToggleVis.hasClass('sb_btnStarbar-open')) _openBar();
                        break;
                    case 'sb_starbar-visClosed':
                        if (!btnToggleVis.hasClass('sb_btnStarbar-closed')) _closeBar();
                        break;
                    case 'sb_starbar-visStowed':
                        if (!btnToggleVis.hasClass('sb_btnStarbar-stowed')) _stowBar();
                        break;
                }
                break;

	    } // end switch clickpoint

	    function _closeBar () {
	        elemStarbarMain.fadeOut('fast');
            elemPopBoxVisControl.fadeOut('fast');
            btnToggleVis.attr('class','');
            btnToggleVis.addClass('sb_btnStarbar-closed');
            btnSaySoLogo.css('backgroundPosition','3px 0px');
            elemPlayerConsole.animate({
                    width: '100'
                }, 500, function() {
                    // Animation complete.
                    $S(this).attr('class','').addClass('sb_starbar-visClosed');
                    elemSaySoLogoBorder.show();
                    hideAlerts();
                    updateState('sb_starbar-visClosed');
                });
	    }
	    function _stowBar () {
	        btnToggleVis.attr('class','');
            btnToggleVis.addClass('sb_btnStarbar-stowed');
            btnSaySoLogo.css('backgroundPosition','');
            btnSaySoLogo.css('width','28px');
            hideAlerts();
            elemPlayerConsole.animate({
                    width: '45'
                }, 300, function() {
                    // Animation complete.
                    $S(this).attr('class','').addClass('sb_starbar-visStowed');
                    btnSaySoLogo.css('width','');
            				elemSaySoLogoBorder.hide();
										elemPlayerConsole.css('background-image','none');
                    updateState('sb_starbar-visStowed');
            });
	    }
	    function _openBar () {
	        btnToggleVis.attr('class','');
            elemSaySoLogoBorder.hide();
            elemVisControls.hide();
            btnSaySoLogo.css('backgroundPosition','');
						elemPlayerConsole.css('background-image',elemSaySoBarBG);
            hideAlerts();
            elemPlayerConsole.animate({
                    width: '100%'
                }, 500, function() {
                    // Animation complete.
                    $S(this).attr('class','').addClass('sb_starbar-visOpen');
                    elemStarbarMain.fadeIn('fast');
                    elemVisControls.fadeIn('fast');
                    btnToggleVis.addClass('sb_btnStarbar-open');
                    showAlerts();
                    updateState('sb_starbar-visOpen');
            });
	    }
		return false;

	} // end FUNCTION ANIMATEBAR

	function closePopBox(exception){
		elemPopBox.each(function(){
			$S(this).removeClass('sb_popBoxActive');
			$S(this).hide();
		});
		elemStarbarClickable.each(function(){
			// remove hover class from all nav items
			$S('span.sb_nav_border').removeClass('sb_theme_navOnGradient');
		});
		return;
	}

	/* open (i.e. show) a popBox, optionally loading a source via AJAX in the process
	* popBox: the element to write into (emptied first!)
	* src: the URL to load into the popBox (set to false to not load via AJAX)
	* withLoadingElement: true to insert loading elements before loading via AJAX (ignored if src is false)
	*/
	function openPopBox(popBox, src, withLoadingElement){
		var ajaxContentContainer = null;
		var loadingElement = null;
		
		closePopBox();

		if (src && withLoadingElement) {  // insert loading elements into popBox, then load content into inner container
			// fill in the container with loading div and container divs
			popBox.html('<div class="sb_popBoxInner"><div class="sb_popContent"></div></div>');
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
			popBox.fadeTo(500, 1); // fade in the loading element
		}
		
		popBox.show();
		popBox.addClass('sb_popBoxActive');

		if (src) {
			$S.ajaxWithAuth({
				url : src,
				success : function (response, status) {
					ajaxContentContainer.html(response.data.html);
					initElements();
					showPopBoxContents(popBox, loadingElement, ajaxContentContainer);
    			}
			});
		} else {
			showPopBoxContents(popBox, false, ajaxContentContainer);
		}
	}
	
	function showPopBoxContents(popBox, loadingElement, ajaxContentContainer) {
		activateAccordion(popBox);
		activateScroll(popBox);
		activateTabs(popBox);
		activateProgressBar(popBox);

		// if we're a regular nav item, turn on the highlight
		var parentClick = popBox.parent();
		if (parentClick.children('span.sb_nav_border')){
			$S('span.sb_nav_border',parentClick).addClass('sb_theme_navOnGradient');
		}

		if (loadingElement) {
			// Hide the container (even though it's already hidden with 
			ajaxContentContainer.fadeTo(0, 0);
			// Fade out loading element
			loadingElement.fadeTo(500, 0);
			// Set display to none to avoid mouse click issues
			setTimeout(function() {loadingElement.css('display', 'none');}, 500);
		} else {
			// Hide the container
			ajaxContentContainer.fadeTo(0, 0);
			ajaxContentContainer.css('display', 'block');
		}
		// Fade in the content (container)
		ajaxContentContainer.fadeTo(500, 1);
	}

	function showAlerts(target){
		if (target){
			target.delay(200).slideDown('fast');
		}else{
			elemAlerts.each(function(){
				// show alerts that aren't empty.
				if ($S('a',this).html().length != 0){
					$S(this).delay(200).slideDown('fast');
				}
			});
		}
		return;
	}

	function hideAlerts(target){
		if (target){
			target.delay(300).slideUp('fast');
		}else{
			elemAlerts.each(function(){
				$S(this).hide();
			});
		}
	}

	function activateTabs(target){
		// only set up the tabs if they're there
		if ($S('.sb_tabs',target).length > 0){
			$S('.sb_tabs',target).tabs({
				show: function(event, ui){
						// re-call the scrollbar to re-initialize to avoid the "flash" of narrow content.
						activateScroll(target);							
						window.location.hash = '';
						
						// adding ID to determine which tab is selected
						$S('ul.sb_ui-tabs-nav',this).attr('id','');
						$S('ul.sb_ui-tabs-nav',this).attr('id','sb_ui-tabs-nav_'+eval(ui.index+1));
					}
			});
		}
	}

	function activateScroll(target){
		// first, resize the scrollpane dynamically to fit whatever height it lives in (.content.height() - .header.height())
		var contentHeight = $S('.sb_popContent',target).height();

		// add height of the header + any margins / paddings
		if ($S('.sb_popContent .sb_header',target).length > 0){
			var headerHeight =  eval($S('.sb_header',target).css('margin-bottom').replace('px',''))+$S('.sb_popContent .sb_header',target).height();
		}else{
			var headerHeight = 0;
		}

		var panes = $S('.sb_scrollPane',target);
		panes.each(function(i) {
			var paragraph = $S('.sb_tabHeader',$S(this).parent());
			var paragraphHeight = 0;
			paragraph.each(function(i) {paragraphHeight += paragraph.height()+eval(paragraph.css('margin-top').replace('px',''))+eval(paragraph.css('margin-bottom').replace('px',''));});
			$S(this).css('height',contentHeight-(headerHeight+paragraphHeight));
			$S(this).jScrollPane();
		});
	}

	function activateAccordion(target){
		if ($S('.sb_tabs',target).length > 0){
			$S('.sb_tabs .sb_tabPane',target).each(function(){
				var isCollapsible = true;
				/* Is this necessary still? -- Hamza
				if ($S('.sb_accordion',this).hasClass('sb_pollQuestion') || $S('.sb_accordion',this).hasClass('sb_pollResult')){
					isCollapsible = false;
				}
				*/
				$S('.sb_accordion',this).accordion({
					collapsible: isCollapsible, // Accordion can have all its divs be closed simultaneously
					active: false, // All accordion divs are closed by default
                    // find the link that caused the accordian to open, take the href, and set the src of the inner iframe to it
                    changestart: function(event, ui){
                        var activeLink = ui.newHeader.find('a');
                        var activeIframe = ui.newContent.find('iframe');
                        var activeFooter = ui.newContent.find('.sb_pollAccordionFooter');

                        // Hide the footer (share links, next survey links)
                        if (activeFooter){
							activeFooter.fadeTo(0, 0);
						}

						// Load the iframe if not already loaded
                        if (activeIframe && activeLink && activeIframe.attr('src') != activeLink.attr('href')) {
                            activeIframe.attr('src', activeLink.attr('href'));
                        }

                        // Fade in the footer
                        if (activeFooter){
	                        setTimeout(function(){
								activeFooter.fadeTo(500, 1);
	                        }, 2000);
						}
                    },
                    change: function (event, ui){
                    	var scrollPane = $S(this).parents('.sb_scrollPane')
                    	scrollPane.jScrollPane(); // re-initialize the scroll pane now that the content size may be different
                    	if (ui.newHeader.position()) {  // if the accordion is open
							var paneHandle = scrollPane.data('jsp');
                    		paneHandle.scrollToY(ui.newHeader.position().top-10); // scroll to the new header (-10 to keep some visibility of stuff above)
						}
					}
				});
			});
		}else{
			$S('.sb_accordion',target).accordion({
				collapsible: true
			});
		}

		return;
	}

	function activateProgressBar(target){
		$S('.sb_progressBar').each(function(){
			var percentValue = eval($S('.sb_progressBarPercent',this).html());
			$S(this).progressbar({
				value : percentValue
			});
			if (percentValue >= 55){
				$S('.sb_progressBarValue',this).addClass('sb_progressBarValue_revert');
			}
		});
	}

	function devInit(){


	}

	// keep the state of the Starbar consistent across sites (currently handles visibility only)

	function updateState (visibility){
        if (!visibility) visibility = elemPlayerConsole.attr('class');
        window.sayso.starbar.state.visibility = visibility;
        var app = KOBJ.get_application(window.sayso.starbar.kynetxAppId);
        app.raise_event('update_state', { 'visibility' : visibility /* other state changes here */ });
    }

    function refreshState () {
        window.sayso.starbar.callback = function () { animateBar(null, 'refresh'); /* other state reload logic here */ };
        var app = KOBJ.get_application(window.sayso.starbar.kynetxAppId);
        app.raise_event('refresh_state');
    }

    // http://www.thefutureoftheweb.com/blog/detect-browser-window-focus
    // I augmented this to include honoring existing focus events
    if (/*@cc_on!@*/false) { // check for Internet Explorer
        var oldOnFocus = document.onfocusin && typeof document.onfocusin === 'function' ? document.onfocusin : function () {};
        document.onfocusin = function () { oldOnFocus(); refreshState(); };
    } else {
        var oldOnFocus = window.onfocus && typeof window.onfocus === 'function' ? window.onfocus : function () {};
        window.onfocus = function () { oldOnFocus(); refreshState(); };
    }

    // "safe" logging functions

    function log () {
        if (window.sayso.debug && typeof console !== 'undefined' && typeof console.log !== 'undefined' && typeof console.log.apply === 'function') {
            console.log.apply(console, arguments);
        }
    };

    function warn () {
        if (window.sayso.debug && typeof console !== 'undefined' && typeof console.log !== 'undefined' && typeof console.log.apply === 'function') {
            console.warn.apply(console, arguments);
        }
    };

});

