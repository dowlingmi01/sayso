/**
 * Starbar
 */

$SQ(function(){

    var sayso = window.sayso,
        starbar = sayso.starbar;
    
    easyXDM.DomHelper.requiresJSON("http://"+sayso.baseDomain+"/js/starbar/json2.min.js");

	// global var
    var themeColor = '#de40b2';

	/*
	Set up some extra bits to handle closing windows if the user clicks outside the starbar or hits ESC key
	*/
	$SQ(document).keyup(function(e) {
		if (e.keyCode == 27) {
			closePopBox();
		}  // esc
	});
	
	// setup event binding to allow starbar-loader.js to 
	// display onboarding if the user has not already seen it
	// and the user is on the Starbar's base domain (e.g. hellomusic.com)
	$SQ(document).bind('onboarding-display', function () {
	    var onboarding = $SQ('#sb_popBox_onboard');
	    openPopBox(onboarding, onboarding.attr('href'), false);
	    setTimeout(function () {
	        // once the onboarding is displayed, bind click on the last step 
	        // to trigger completion of the onboarding 
	        $SQ('#sb_popBox_onboard a.sb_surveyLaunch').bind('click', function () {
	            $SQ(document).trigger('onboarding-complete');
	        });
	    }, 500);
	});

	// close if you click outside the starbar while in the iframe
	$SQ(document).click(function(e) {
		// don't close if they just right-clicked OR were clicking inside of a colorbox
		if ((e.button != 2) && ($SQ('#sb_cboxOverlay').css('display') != 'block')){
			closePopBox();
		}
	});
	

	// LETS USE VARS!
	// NOTE: The variables below are initialized in initElements()

	// clickable elements that ppl will interact with
	var btnToggleVis; //  = $SQ('#sayso-starbar #starbar-visControls #starbar-toggleVis');
	var btnSaySoLogo; // = $SQ('#sayso-starbar #starbar-visControls #sb_starbar-logo');
	var btnCloseColorbox; // = $SQ('#sayso-starbar .sb_closeColorbox');

	// container elements
	var elemSaySoLogoBorder; // = $SQ('#sayso-starbar #starbar-player-console #sb_starbar-logoBorder');
	var elemSaySoLogoSemiStowed; // = $SQ('#sayso-starbar #sb_starbar-logoSemiStowed');
	var elemStarbarWrapper; // = $SQ('#sayso-starbar #starbar-player-console #starbar-wrapper');
	var elemSaySoBarBG; // = $SQ('#sayso-starbar #starbar-player-console').css('background-image');
	var elemPlayerConsole; // = $SQ('#sayso-starbar #starbar-player-console');
	var elemStarbarMain; // = $SQ('#sayso-starbar #starbar-player-console #starbar-main');
	var elemVisControls; // = $SQ('#sayso-starbar #starbar-player-console #starbar-visControls');
	var elemStarbarClickable; // = $SQ('#sayso-starbar #starbar-player-console .sb_nav_element');
	var elemPopBox; // = $SQ('#sayso-starbar #starbar-player-console .sb_popBox');
	var elemAlerts; // = $SQ('#sayso-starbar #starbar-player-console .sb_starbar-alert');
	var elemPopBoxVisControl; // = $SQ('#sayso-starbar #starbar-player-console #starbar-visControls .sb_popBox');
	var elemExternalConnect; // = $SQ('#sayso-starbar #starbar-player-console #sb_popBox_user-profile .sb_unconnected');
	var elemRewardItem; // = $SQ('#sayso-starbar #starbar-player-console #sb_popBox_rewards .sb_rewardItem');
	var elemTooltip; // = $SQ('#sayso-starbar .sb_Tooltip');

	// initialize the starbar
	initStarBar();

	/* FUNCTIONS */

	// initialize the starbar
	function initStarBar(){
		initElements();
		updateAlerts(true);
		activateProgressBar();
		// initializes development-only jquery
		devInit();
		sayso.log('Loaded and Ready');
	}

	// initialize the elements
	function initElements(){
		// clickable elements that ppl will interact with
		btnToggleVis = $SQ('#sayso-starbar #starbar-visControls #starbar-toggleVis');
		btnSaySoLogo = $SQ('#sayso-starbar #starbar-visControls #sb_starbar-logo');

		// container elements
		elemSaySoLogoBorder = $SQ('#sayso-starbar #starbar-player-console #sb_starbar-logoBorder');
		elemSaySoLogoSemiStowed = $SQ('#sayso-starbar #sb_starbar-logoSemiStowed');
		elemStarbarWrapper = $SQ('#sayso-starbar #starbar-player-console #starbar-wrapper');
		elemSaySoBarBG = $SQ('#sayso-starbar #starbar-player-console').css('background-image');
		elemPlayerConsole = $SQ('#sayso-starbar #starbar-player-console');
		elemStarbarMain = $SQ('#sayso-starbar #starbar-player-console #starbar-main');
		elemVisControls = $SQ('#sayso-starbar #starbar-player-console #starbar-visControls');
		elemStarbarClickable = $SQ('#sayso-starbar #starbar-player-console .sb_nav_element');
		elemPopBox = $SQ('#sayso-starbar #starbar-player-console .sb_popBox');
		elemPopBoxVisControl = $SQ('#sayso-starbar #starbar-player-console #starbar-visControls .sb_popBox');
		elemTabClick = $SQ('#sayso-starbar #starbar-player-console .sb_nav_tabs');
		elemExternalConnect = $SQ('#sayso-starbar #starbar-player-console #sb_popBox_user-profile .sb_unconnected');
		elemRewardItem = $SQ('#sayso-starbar #starbar-player-console #sb_popBox_rewards .sb_rewardItem');
		btnCloseColorbox = $SQ('#sayso-starbar .sb_closeColorbox');
		elemTooltip = $SQ('#sayso-starbar .sb_tooltip');


		// jquery edit in place
		elemJEIP = $SQ('#sayso-starbar .sb_jeip');

		elemPlayerConsole.unbind();
		elemPlayerConsole.click(function(e) {
		    e.stopPropagation();
		});
		
		// tooltip binding
		elemTooltip.each(function(){
		 	$SQ(this).easyTooltip();
		});


		/* prevent default for any link with # as the href */
		$SQ('a').each(function(){
			$SQ(this).unbind();
			if ($SQ(this).attr('href')=='#'){
				$SQ(this).bind({
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
            			var thisPopBoxSrc = $SQ(this).attr('href');
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
			if ($SQ(this).attr('id') == 'sb_starbar-logo'){
				return;
			}

			$SQ(this).unbind();
			$SQ(this).bind({
				click: function(event){
					event.stopPropagation();
					event.preventDefault();
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

					// set a delay before closing the alert element
					if ($SQ(this).hasClass('sb_alert')){
						var notification = $SQ(this).closest('.sb_starbar-alert');
						hideAlerts(notification, true, true);
					}

					// if it was already open, close it and remove the class. otherwise, open the popbox
					if (thisPopBox.hasClass('sb_popBoxActive')){
						closePopBox(thisPopBox);
					}else{
						// this menu item's popBox is active

						// check if the clickable area had an href. If so, load it into the pop box, then open it. Otherwise, just open it.
		  				var thisPopBoxSrc = $SQ(this).attr('href');
			  			openPopBox(thisPopBox, thisPopBoxSrc, true, $SQ(this).hasClass('sb_alert'));

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
		
		// set up the EIP elements
		elemJEIP.each(function(){
			$SQ(this).eip( "http://"+sayso.baseDomain+"/api/user/save-in-place?renderer=jsonp", {
				savebutton_text		: "save",
				savebutton_class	: "sb_theme_button",
				cancelbutton_text	: "cancel",
				cancelbutton_class	: "sb_theme_button sb_theme_button_grey",
				after_save			: function() { updateProfile(true); }
			});									 
		
		});
		
		// connect with facebook or twitter
		elemExternalConnect.each(function(){
			$SQ(this).unbind();
			$SQ(this).bind({
				click: function(event){
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
					link += "user_id="+sayso.starbar.user.id+"&user_key="+sayso.starbar.user.key+"&auth_key="+sayso.starbar.authKey;
			  		window.open(link, 'sb_window_open', windowParameters);
				}
			});
		});
		
		
		// rewards center items overlay
		elemRewardItem.each(function(){
			$SQ(this).unbind();
			$SQ(this).bind({
				click: function(event){
					if ($SQ(this).hasClass('sb_rewardItem_disabled')){
						return;
					}else{
						$SQ.sb_colorbox({
							width:"220px", 
							inline: true, 
							initialWidth: 50,
							initialHeight: 50,
							speed: 500,
							transition: 'fade',
							href:"#sb_rewardsOverlay"
						});
					}
				}			
			});
		
		});
		
		btnCloseColorbox.each(function(){
			$SQ(this).unbind();
			$SQ(this).bind({
				click: function(event){
					$SQ.sb_colorbox.close();
					return false;
				}			
			});									 
		});

	} // end initElements()

	function closePopBox(exception){
		elemPopBox.each(function(){
			$SQ(this).removeClass('sb_popBoxActive');
			$SQ(this).hide();
		});
		elemStarbarClickable.each(function(){
			// remove hover class from all nav items
			$SQ('span.sb_nav_border').removeClass('sb_theme_navOnGradient');
		});
		updateAlerts(false);
		return;
	}

	/* open (i.e. show) a popBox, optionally loading a source via AJAX in the process
	* popBox: the element to write into (emptied first!)
	* src: the URL to load into the popBox (set to false to not load via AJAX)
	* withLoadingElement: true to insert loading elements before loading via AJAX (ignored if src is false)
	*/
	function openPopBox(popBox, src, withLoadingElement, fromNotification){
		var ajaxContentContainer = null;
		var loadingElement = null;
		var alertContainers = $SQ('.sb_alerts_container');
		
		// Close the last (i.e. the visible) alert from each alert (notification) container if the user opens a popBox
		// Unless the popBox is opening because the user clicked an alert
		if (!fromNotification && alertContainers.length > 0) {
			alertContainers.each(function(){
		 		var alertsInContainer = $SQ('.sb_starbar-alert', $SQ(this));
		 		if (alertsInContainer.length > 0) hideAlerts(alertsInContainer.last(), true, true);
			});
		}
		closePopBox();
		
		
		// if there's a colorbox open, close it
		$SQ.sb_colorbox.close();

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
			popBox.fadeTo(200, 1); // fade in the loading element
		}
		
		popBox.show();
		popBox.addClass('sb_popBoxActive');

		if (src) {
			$SQ.ajaxWithAuth({
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
	}

	function showAlerts(target){
		if (target){
			target.delay(200).slideDown('fast');
		}else{
			elemAlerts = $SQ('#sayso-starbar #starbar-player-console .sb_starbar-alert');
			if (elemAlerts.length > 0) {
				elemAlerts.each(function(){
					$SQ(this).delay(200).slideDown('fast');
				});
			}
		}
		return;
	}

	function hideAlerts(target, performAjaxCall, animate){
		if (target){
			if (performAjaxCall) {
				var notification_id = target.attr('id').match(/(?:[0-9]+)/);
				$SQ.ajaxWithAuth({
					url : 'http://'+sayso.baseDomain+'/api/notification/close?renderer=jsonp&message_id='+notification_id,
					success : function (response, status) {}
				});
			}

			if (animate) {
				target.delay(300).slideUp('fast');
				// setTimeout is called in the global scope, so it needs to find the target again
				setTimeout("$SQ('#"+target.attr('id')+"').annihilate()", 600);
			} else {
				target.annihilate();
			}
		} else {
			elemAlerts = $SQ('#sayso-starbar #starbar-player-console .sb_starbar-alert');
			elemAlerts.each(function(){
				hideAlerts($SQ(this), performAjaxCall, animate);
			});
		}
	}

	function updateAlerts(reverseOrder) {
		$SQ.ajaxWithAuth({
			url : 'http://'+sayso.baseDomain+'/api/notification/get-all?renderer=jsonp&starbar_id='+sayso.starbar.id,
			success : function (response, status, jqXHR) {
				var randomString = $SQ.randomString(10);
				var newAlerts = false;
				
				elemAlerts = $SQ('#sayso-starbar #starbar-player-console .sb_starbar-alert');
				if (elemAlerts.length > 0) {
					elemAlerts.each(function(){
						$SQ(this).addClass('sb_starbar-alert_'+randomString);
					});
				}
				
				if (response.data.collection.length > 0) {
					$SQ.each(response.data.collection, function (index, message) {
						// Check if an alert with that message already exists, if so, do nothing
						var currentAlert = $SQ('#starbar-alert-'+message['id']);
						if (currentAlert.length == 0) { // New Alert
							var elemAlertContainer = $SQ('#starbar-alert-container-'+message['notification_area']);

							var newAlertHtml = '<div class="sb_starbar-alert sb_starbar-alert-'+message['notification_area']+'" id="starbar-alert-'+message['id']+'"><div class="sb_inner"><div class="sb_content sb_theme_bgAlert'+message['color']+'">';
							if (message['popbox_to_open']) {
								newAlertHtml += '<a href="http://'+sayso.baseDomain+'/starbar/'+sayso.starbar.shortName+'/'+message['popbox_to_open']+'" class="sb_nav_element sb_alert" rel="sb_popBox_'+message['popbox_to_open']+'">'+message['message']+'</a>'
							} else {
								newAlertHtml += '<a href="#" class="sb_nav_element sb_alert" rel="">'+message['message']+'</a>';
							}

							newAlertHtml += '</div><!-- .sb_content --></div><!-- .sb_inner --></div><!-- #sb_alert-new -->';
							if (reverseOrder) {
								elemAlertContainer.prepend(newAlertHtml);
							} else {
								elemAlertContainer.append(newAlertHtml);
							}

							// Update profile if we've just received a notification regarding FB or TW getting connected.
							if (message['short_name'] == 'FB Account Connected' || message['short_name'] == 'TW Account Connected') {
								updateProfile(true);
							}
							
							newAlerts = true;
						} else { // Alert already exist, remove the class with the random string that we just added
							currentAlert.removeClass('sb_starbar-alert_'+randomString);
						}
					});

					if (elemAlerts.length > 0) {
						elemAlerts.each(function(){
							if ($SQ(this).hasClass('sb_starbar-alert_'+randomString)) { // Alert has been closed elsewhere, or should no longer be shown!
								hideAlerts($SQ(this), false, true); // Animate but don't submit anything via ajax, since alert should already be closed for the code to get here
							}
						});
					}
				
					if (newAlerts) {
						initElements();
						showAlerts();
					}
				}
    		}
		});
	}

	function updateProfile(setGlobalUpdate) {
		$SQ.ajaxWithAuth({
			url : 'http://'+sayso.baseDomain+'/api/user/get?renderer=jsonp',
			success : function (response, status, jqXHR) {
				var user = response.data;
				var userSocials;

				if (user && user['_user_socials'] && user['_user_socials']['collection'])
					userSocials = user['_user_socials']['collection'];

				// Update Profile Image and connect icons
				if (userSocials) {
					$SQ.each(userSocials, function (index, userSocial) {
						var connectIcon = $SQ('#sb_profile_'+userSocial['provider']);
						if (connectIcon && connectIcon.hasClass('sb_unconnected')) {
							connectIcon.unbind();
							connectIcon.attr('href', '');
							connectIcon.removeClass('sb_unconnected');
							connectIcon.removeClass('sb_connected');
						}
						if (userSocial['provider'] == 'facebook') {
							var profileImages = $SQ('img.sb_userImg');
							if (profileImages.length > 0) {
								profileImages.each(function(){
		 							$SQ(this).attr('src', 'http://graph.facebook.com/'+userSocial['identifier']+'/picture?type=square');
								});
							}
						}
					});
				}

				// Update Username
				if (user['username']) {
					$SQ('.sb_userTitle').each(function(){
						$SQ(this).html(user['username']);
					});
				}
    		}
		});

		if (setGlobalUpdate) {
			starbar.state.profile = Math.round(new Date().getTime() / 1000);
			starbar.state.update();
		}
	}

	function activateTabs(target){
		// only set up the tabs if they're there
		if ($SQ('.sb_tabs',target).length > 0){
			$SQ('.sb_tabs',target).tabs({
				show: function(event, ui){
						// re-call the scrollbar to re-initialize to avoid the "flash" of narrow content.
						activateScroll(target);							
						window.location.hash = '';
						
						// adding ID to determine which tab is selected
						$SQ('ul.sb_ui-tabs-nav',this).attr('id','');
						$SQ('ul.sb_ui-tabs-nav',this).attr('id','sb_ui-tabs-nav_'+eval(ui.index+1));
					}
			});
		}
	}

	function activateScroll(target){
		// first, resize the scrollpane dynamically to fit whatever height it lives in (.content.height() - .header.height())
		var contentHeight = $SQ('.sb_popContent',target).height();

		// add height of the header + any margins / paddings
		if ($SQ('.sb_popContent .sb_header',target).length > 0){
			var headerHeight = $SQ('.sb_popContent .sb_header',target).totalHeight();
		}else{
			var headerHeight = 0;
		}
		
		// recalculate if we're using 2 column layout. 
		if ($SQ('.sb_popContent .sb_column60', target).length > 0){
			var headerHeight = $SQ('.sb_popContent  .sb_column60 .sb_header',target).totalHeight();
		}
		
		if ($SQ('.sb_popContent .sb_column40', target).length > 0){
			var headerHeight = $SQ('.sb_popContent  .sb_column60 .sb_header',target).totalHeight();
		}
		
		var panes = $SQ('.sb_scrollPane',target);
		panes.each(function(i) {
			// Add height of all the paragraphs (or anything with the class "sb_tabHeader" really)
			var paragraphs = $SQ('.sb_tabHeader',$SQ(this).parent());
			var paragraphHeight = 0;
			paragraphs.each(function(i) {paragraphHeight += $SQ(this).totalHeight();});		
						
			// special rule to handle if there are 2 columns in a popbox, check to see if any doesn't have a header, if it doesn't, change the height of the scroll.
			var parent = $SQ(this).parent();
			if (parent.children(':first').hasClass('sb_scrollPane')){
				$SQ(this).css('height',contentHeight);
			}else{	
				$SQ(this).css('height',contentHeight-(headerHeight+paragraphHeight));
			}
			$SQ(this).jScrollPane();
		});
	}

	function activateAccordion(target){
		if ($SQ('.sb_tabs',target).length > 0){
			$SQ('.sb_tabs .sb_tabPane',target).each(function(){
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
							if (link.indexOf("?") == -1)
								link += "?";
							else
								link += "&";
							link += "user_id="+window.sayso.starbar.user.id+"&user_key="+window.sayso.starbar.user.key+"&auth_key="+window.sayso.starbar.authKey;

							// The container for the new iFrame is in the link's 'ref' attribute
							var iFrameContainerId = activeLink.attr('rel');
							$SQ.insertCommunicationIframe(link, iFrameContainerId, 470, parseInt(activeLink.attr('iframeHeight')), "no");
							
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
                    		var bottomOfOpenAccordion = topOfOpenAccordion+ui.newHeader.totalHeight()+ui.newContent.totalHeight();
                    		var sizeOfPane = scrollPane.height();

                    		if ((bottomOfOpenAccordion - currentScroll) > (sizeOfPane - 10)) { // - 24 for the extra padding
                    			paneHandle.scrollByY((bottomOfOpenAccordion - currentScroll) - (sizeOfPane - 10)); // scroll by the difference
							}
						}
					}
				});
			});
		}else{
			$SQ('.sb_accordion',target).accordion({
				collapsible: true
			});
		}

		return;
	}

	function activateProgressBar(target){
		$SQ('.sb_progressBar').each(function(){
			var percentValue = eval($SQ('.sb_progressBarPercent',this).html());
			$SQ(this).progressbar({
				value : percentValue
			});
			if (percentValue >= 55){
				$SQ('.sb_progressBarValue',this).addClass('sb_progressBarValue_revert');
			}
		});
	}

	function devInit(){


	}

	// animates the starbar-player-console bar based on current state
	function animateBar(playerClass, clickPoint){

	    if (!playerClass) playerClass = window.sayso.starbar.state.visibility;

	    switch(clickPoint){
            // if we're clicking from a button, determine what state we're in and how to shrink / grow
            case 'button':
                switch (playerClass){
                    case 'sb_starbar-visOpen':
                        _stowBar(true);
                        break;
                    case 'sb_starbar-visStowed':
                        _openBar(true);
                        break;
                }
                break;
            // if refreshing based on current state, then update bar to match
            case 'refresh' :
                switch (playerClass) {
                    case 'sb_starbar-visOpen':
                        if (!btnToggleVis.hasClass('sb_btnStarbar-open')) _openBar(false);
                        break;
                    case 'sb_starbar-visStowed':
                        if (!btnToggleVis.hasClass('sb_btnStarbar-stowed')) _stowBar(false);
                        break;
                }
                break;

	    } // end switch clickpoint

	    function _stowBar (needToUpdateState) {
			if (needToUpdateState) {
			    starbar.state.visibility = 'sb_starbar-visStowed';
			    starbar.state.update();
			}

	        elemStarbarMain.fadeOut('fast');
            elemPopBoxVisControl.fadeOut('fast');
            btnToggleVis.attr('class','').addClass('sb_btnStarbar-closed');
            btnSaySoLogo.css('backgroundPosition','3px 0px');
            elemPlayerConsole.animate(
            	{ width: '100' },
                500,
                function() {
                    // Animation complete.
                    $SQ(this).attr('class','').addClass('sb_starbar-visClosed');
                    elemSaySoLogoSemiStowed.fadeTo(0, 1);
                    $SQ(this).fadeTo(500, 0);
                    hideAlerts();
                    setTimeout(function () {
                    	elemPlayerConsole.hide();
					}, 500);
                    setTimeout(function () {
			            btnToggleVis.attr('class','').addClass('sb_btnStarbar-stowed');
            			btnSaySoLogo.css('backgroundPosition','');
                    	elemPlayerConsole.css('width','');
                    	elemPlayerConsole.attr('class','').addClass('sb_starbar-visStowed');
                    	elemPlayerConsole.show();
                    	elemPlayerConsole.fadeTo(157, 1); // 157 found to work best for some bizarre reason
                    	elemSaySoLogoSemiStowed.fadeTo(500, 0);
					}, 1000);
            	}
            );
	    }
	    function _openBar (needToUpdateState) {
			if (needToUpdateState) {
			    starbar.state.visibility = 'sb_starbar-visOpen';
                starbar.state.update();
			}

	        btnToggleVis.attr('class','');
            elemSaySoLogoBorder.hide();
            elemVisControls.hide();
			elemPlayerConsole.addClass('sb_starbar-visBG');
            hideAlerts();
            elemPlayerConsole.animate(
            	{ width: '100%' },
            	500,
            	function() {
                    // Animation complete.
                    $SQ(this).attr('class','').addClass('sb_starbar-visOpen');
                    elemStarbarMain.fadeIn('fast');
                    elemVisControls.fadeIn('fast');
                    btnToggleVis.attr('class','').addClass('sb_btnStarbar-open');
                    showAlerts();
            	}
            );
	    }
		return false;

	} // end FUNCTION ANIMATEBAR

	
	// Starbar state
	starbar.state.local = {
        'profile' : Math.round(new Date().getTime() / 1000),
        'game' : Math.round(new Date().getTime() / 1000)
	}

	// Update the cross-domain state variables
	starbar.state.update = function (){
        var app = KOBJ.get_application(starbar.kynetxAppId);
        app.raise_event('update_state', { 
            'visibility' : starbar.state.visibility,
            'notifications' : starbar.state.notifications,
            'profile' : starbar.state.profile,
            'game' : starbar.state.game
        });
    }

	// Refresh the Starbar to respond to state changes, if any
	starbar.state.refresh = function () {
        starbar.state.callback = function () { 
            // logic here to determine if/what should be fired to "refresh"
            animateBar(null, 'refresh'); 
            updateAlerts(false);
            
            if (starbar.state.profile > starbar.state.local.profile) {
            	updateProfile(false);
			}

            /* (starbar.state.game > starbar.state.local.game) {
            	updateGame();
			}*/
            // example:
            // if (starbar.state.notifications === 'update') updateAlerts();
            // also, in updateAlerts() or wherever, don't forget to reset the
            // value back to 'ready' and call starbar.state.update() again
        };
        var app = KOBJ.get_application(starbar.kynetxAppId);
        app.raise_event('refresh_state');
    }

    if (/*@cc_on!@*/false) { // check for Internet Explorer
        var oldOnFocus = document.onfocusin && typeof document.onfocusin === 'function' ? document.onfocusin : function () {};
        document.onfocusin = function () { oldOnFocus(); starbar.state.refresh(); };
    } else {
        var oldOnFocus = window.onfocus && typeof window.onfocus === 'function' ? window.onfocus : function () {};
        window.onfocus = function () { oldOnFocus(); starbar.state.refresh(); };
    }
    
    // flag so we know this file has loaded
    window.sayso.starbar.loaded = true;
});

