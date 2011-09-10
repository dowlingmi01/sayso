/**
 * Starbar
 */

// load after slight delay
setTimeout(function(){


    var kynetxAppId = 'a239x18';

	// global var
    var themeColor = '#de40b2';

	// LETS USE VARS!

	// clickable elements that ppl will interact with
	var btnToggleVis = $S('#sayso-starbar #starbar-visControls #starbar-toggleVis');
	var btnSaySoLogo = $S('#sayso-starbar #starbar-visControls #sb_starbar-logo');

	// container elements
	var elemSaySoLogoBorder = $S('#sayso-starbar #starbar-player-console #sb_starbar-logoBorder');
	var elemSaySoBarBG = $S('#sayso-starbar #starbar-player-console').css('background-image');
	var elemPlayerConsole = $S('#sayso-starbar #starbar-player-console');
	var elemStarbarMain = $S('#sayso-starbar #starbar-player-console #starbar-main');
	var elemVisControls = $S('#sayso-starbar #starbar-player-console #starbar-visControls');
	var elemStarbarClickable = $S('#sayso-starbar #starbar-player-console .sb_nav_element');
	var elemPopBox = $S('#sayso-starbar #starbar-player-console .sb_popBox');
	var elemAlerts = $S('#sayso-starbar #starbar-player-console .sb_starbar-alert');
	var elemPopBoxVisControl = $S('#sayso-starbar #starbar-player-console #starbar-visControls .sb_popBox');

	/*
	Set up some extra bits to handle closing windows if the user clicks outside the starbar or hits ESC key
	*/
	$S(document).keyup(function(e) {
		if (e.keyCode == 27) {
			closePopBox();
		}  // esc
	});

	// close if you click outside the starbar while in the iframe
	$S(document).click(function(e) {
		// don't close if they just right-clicked
		if (e.button != 2){
			closePopBox();
		}
	});

	elemPlayerConsole.click(function(e) {
	    e.stopPropagation();
	});


	/*
	set some properties for each of the popboxes
	- prevent from closing when clicked
	*/
	elemStarbarClickable.each(function(){
		$S(this).bind({
			click: function(e){
				 e.stopPropagation();
			}
		});
	});

	/* prevent default for any link with # as the href */
	$S('a').each(function(){
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

	btnToggleVis.click(function(event){
		event.preventDefault();
		var playerClass = elemPlayerConsole.attr('class');
		animateBar(playerClass, 'button');
		//popBoxClose();
	});

	/*
	Set up logo hover + click action behaviors
	*/

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
					openPopBox(thisPopBox, thisPopBoxSrc);
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
		$S(this).bind({
			click: function(event){
				event.preventDefault();

				// SPECIAL HANDLING FOR STARBAR LOGO
				if ($S(this).attr('id') == 'sb_starbar-logo'){
					return;
				}
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

					// set a delay before closing the alert element
					if ($S(this).hasClass('sb_alert')){
						hideAlerts($S(this).closest('.sb_starbar-alert'));
					}
				}

				// if it was already open, close it and remove the class. otherwise, open the popbox
				if (thisPopBox.hasClass('sb_popBoxActive')){
					closePopBox(thisPopBox);
				}else{
					// this menu item's popBox is active

					// check if the clickable area had an href. If so, load it into the pop box, then open it. Otherwise, just open it.
		  			var thisPopBoxSrc = $S(this).attr('href');
			  		openPopBox(thisPopBox, thisPopBoxSrc);

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

	// initialize the starbar
	initStarBar();


	/* FUNCTIONS */

	// initialize the starbar
	function initStarBar(){
		closePopBox();
		showAlerts();
		activateProgressBar();

		// initializes development-only jquery
		devInit();
	}

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

	function openPopBox(elem, src){
		closePopBox();
		var popBox = elem;

		// if the src string is specified, load via ajax (jsonp), then call this function again without the src
		if (src) {
			$S.ajax({
				dataType: 'jsonp',
				url : src,
				success : function (response, status) {
					popBox.html(response.data.html);
					openPopBox(elem, null);
    			}
			});
		// show the popBox
		} else {
			popBox.show();
			popBox.addClass('sb_popBoxActive');
			activateAccordion(popBox);
			activateScroll(popBox);
			activateTabs(popBox);
			activateProgressBar(popBox);

			// if we're a regular nav item, turn on the highlight
			var parentClick = elem.parent();
			if (parentClick.children('span.sb_nav_border')){
				$S('span.sb_nav_border',parentClick).addClass('sb_theme_navOnGradient');
			}
		}

		return;
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
				show: function(){
						// re-call the scrollbar to re-initialize to avoid the "flash" of narrow content.
						activateScroll(target);
					}
				//fx: { opacity: 'toggle' } 
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
		
		// set an absolute width for the scroll container to prevent "flash" while it resizes
		var contentWidth = $S('.sb_popContent',target).width();
		var contentPadL = $S('.sb_popContent',target).css('padding-left');
		var contentPadR = $S('.sb_popContent',target).css('padding-right');
		var contentPad = eval(contentWidth - (contentPadL.replace('px','') + contentPadR.replace('px','')))+'px';
		
		$S('.sb_scrollPane',target).css('height',contentHeight-headerHeight);
		$S('.sb_scrollPane',target).jScrollPane({
			autoReinitialise: true,
			autoReinitialiseDelay: 1000,
			contentWidth: contentPad
		});
	}

	function activateAccordion(target){
		if ($S('.sb_tabs',target).length > 0){
			$S('.sb_tabs .sb_tabPane',target).each(function(){
				var isCollapsible = true;
				if ($S('.sb_accordion',this).hasClass('sb_pollQuestion') || $S('.sb_accordion',this).hasClass('sb_pollResult')){
					isCollapsible = false;
				}
				$S('.sb_accordion',this).accordion({
					collapsible: isCollapsible,
                    // find the link that caused the accordian to open, take the href, and set the src of the inner iframe to it
                    changestart: function(event, ui){
                        var activeLink = ui.newHeader.find('a');
                        var activeIframe = ui.newContent.find('iframe');
                        if (activeIframe && activeLink && activeIframe.attr('src') != activeLink.attr('href')) {
                            activeIframe.attr('src', activeLink.attr('href'));
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
        var app = KOBJ.get_application(kynetxAppId);
        app.raise_event('update_state', { 'visibility' : visibility /* other state changes here */ });
    }
    
    function refreshState () {
        window.sayso.starbar.callback = function () { log('callback firing'); animateBar(null, 'refresh'); /* other state reload logic here */ };
        var app = KOBJ.get_application(kynetxAppId);
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
        if (sayso.debug && typeof console !== 'undefined') {
            console.log.apply(console, arguments);
        }
    };
    
    function warn () {
        if (sayso.debug && typeof console !== 'undefined') {
            console.warn.apply(console, arguments);
        }
    };

}, 200); // slight delay to ensure other libraries are loaded
