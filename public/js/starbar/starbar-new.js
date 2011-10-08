/**
 * Starbar
 */

$SQ(function(){

    sayso = window.sayso; // should work for starbar itself
    
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
	var elemSaySoBarBG; // = $SQ('#sayso-starbar #starbar-player-console').css('background-image');
	var elemPlayerConsole; // = $SQ('#sayso-starbar #starbar-player-console');
	var elemStarbarMain; // = $SQ('#sayso-starbar #starbar-player-console #starbar-main');
	var elemVisControls; // = $SQ('#sayso-starbar #starbar-player-console #starbar-visControls');
	var elemStarbarClickable; // = $SQ('#sayso-starbar #starbar-player-console .sb_nav_element');
	var elemPopBox; // = $SQ('#sayso-starbar #starbar-player-console .sb_popBox');
	var elemAlerts; // = $SQ('#sayso-starbar #starbar-player-console .sb_starbar-alert');
	var elemPopBoxVisControl; // = $SQ('#sayso-starbar #starbar-player-console #starbar-visControls .sb_popBox');
	var elemExternalConnect; // = $SQ('#sayso-starbar #starbar-player-console #sb_popBox_user-profile .sb_unconnected');
	var elemExternalShare; // = $SQ('#sayso-starbar #starbar-player-console .sb_externalShare, #sayso-starbar-embed .sb_externalShare');
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
		elemSaySoBarBG = $SQ('#sayso-starbar #starbar-player-console').css('background-image');
		elemPlayerConsole = $SQ('#sayso-starbar #starbar-player-console');
		elemStarbarMain = $SQ('#sayso-starbar #starbar-player-console #starbar-main');
		elemVisControls = $SQ('#sayso-starbar #starbar-player-console #starbar-visControls');
		elemStarbarClickable = $SQ('#sayso-starbar #starbar-player-console .sb_nav_element');
		elemPopBox = $SQ('#sayso-starbar #starbar-player-console .sb_popBox');
		elemPopBoxVisControl = $SQ('#sayso-starbar #starbar-player-console #starbar-visControls .sb_popBox');
		elemTabClick = $SQ('#sayso-starbar #starbar-player-console .sb_nav_tabs');
		elemExternalConnect = $SQ('#sayso-starbar #starbar-player-console #sb_popBox_user-profile .sb_unconnected');
		elemExternalShare = $SQ('#sayso-starbar #starbar-player-console .sb_externalShare, #sayso-starbar-embed .sb_externalShare');
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
						var notification_id = notification.attr('id').match(/([0-9]+)/);
						$SQ.ajaxWithAuth({
							url : 'http://'+sayso.baseDomain+'/api/user/notification-close?message_id='+notification_id,
							success : function (response, status) {}
						});
						hideAlerts(notification);
					}

					// if it was already open, close it and remove the class. otherwise, open the popbox
					if (thisPopBox.hasClass('sb_popBoxActive')){
						closePopBox(thisPopBox);
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
		
		// set up the EIP elements
		elemJEIP.each(function(){
			$SQ(this).eip( "http://"+sayso.baseDomain+"/api/user/save-in-place?renderer=jsonp", {
				savebutton_text		: "save",
				savebutton_class	: "sb_theme_button",
				cancelbutton_text	: "cancel",
				cancelbutton_class	: "sb_theme_button sb_theme_button_grey",
				
			});									 
		
		});
		
		// connect with facebook or twitter
		elemExternalConnect.each(function(){
			$SQ(this).unbind();
			$SQ(this).bind({
				click: function(event){
					closePopBox();
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
		
		// share via facebook or twitter
		elemExternalShare.each(function(){
			$SQ(this).unbind();
			$SQ(this).bind({
				click: function(event){
			  		var windowParameters = 'location=1,status=1,scrollbars=0,width=981,height=450';
					var link = $SQ(this).attr('href');

			  		window.open(link, 'sb_window_open', windowParameters);
			  		return false;
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
                    $SQ(this).attr('class','').addClass('sb_starbar-visClosed');
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
                	$SQ(this).attr('class','').addClass('sb_starbar-visStowed');
                  btnSaySoLogo.css('width','');
            			elemSaySoLogoBorder.hide();
                  updateState('sb_starbar-visStowed');
            });
	    }
	    function _openBar () {
	        btnToggleVis.attr('class','');
            elemSaySoLogoBorder.hide();
            elemVisControls.hide();
            btnSaySoLogo.css('backgroundPosition','');
						elemPlayerConsole.addClass('sb_starbar-visBG');
            hideAlerts();
            elemPlayerConsole.animate({
                    width: '100%'
                }, 500, function() {
                    // Animation complete.
                    $SQ(this).attr('class','').addClass('sb_starbar-visOpen');
										$SQ(this).removeClass('sb_starbar-visBG');
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
	function openPopBox(popBox, src, withLoadingElement){
		var ajaxContentContainer = null;
		var loadingElement = null;
		
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
			elemAlerts.each(function(){
				// show alerts that aren't empty.
				if ($SQ('a',this).html().length != 0){
					$SQ(this).delay(200).slideDown('fast');
				}
			});
		}
		return;
	}

	function hideAlerts(target){
		if (target){
			target.delay(300).slideUp('fast');
			// setTimeout is called in the global scope, so it needs to find the target again
			setTimeout("$SQ('#"+target.attr('id')+"').empty()", 300);
		}else{
			elemAlerts.each(function(){
				$SQ(this).hide();
			});
		}
	}

	function updateAlerts(reverseOrder) {
		$SQ.ajaxWithAuth({
			url : 'http://'+sayso.baseDomain+'/api/user/notification-update?renderer=jsonp&starbar_id='+sayso.starbar.id,
			success : function (response, status, jqXHR) {
				var newAlerts = false;
				if (response.data.messages.length > 0) {
					$SQ.each(response.data.messages, function (index, info) {
						// Check if an alert with that message already exists, if so, do nothing
						var id = info[0];
						if ($SQ('#starbar-alert-'+id).length == 0) {
							var notification_area = info[1];
							var message = info[2];
							var popbox_to_open = info[3];
							var color = info[4];

							var elemAlertContainer = $SQ('#starbar-alert-container-'+notification_area);

							var newAlertHtml = '<div class="sb_starbar-alert sb_starbar-alert-'+notification_area+'" id="starbar-alert-'+id+'"><div class="sb_inner"><div class="sb_content sb_theme_bgAlert'+color+'">';
							if (popbox_to_open) {
								newAlertHtml += '<a href="http://'+sayso.baseDomain+'/starbar/'+sayso.starbar.short_name+'/'+popbox_to_open+'" class="sb_nav_element sb_alert" rel="sb_popBox_'+popbox_to_open+'">'+message+'</a>'
							} else {
								newAlertHtml += '<a href="#" class="sb_nav_element sb_alert" rel="">'+message+'</a>';
							}

							newAlertHtml += '</div><!-- .sb_content --></div><!-- .sb_inner --></div><!-- #sb_alert-new -->';
							if (reverseOrder) {
								elemAlertContainer.prepend(newAlertHtml);
							} else {
								elemAlertContainer.append(newAlertHtml);
							}

							newAlerts = true;
						}
					});

					if (newAlerts) {
						initElements();
						showAlerts();
					}
				}
    		}
		});
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

							// This function inserts the iframe (with x-domain communication enabled!)
							// The id of the container is placed inside the 'ref' attribute at the top of the accordion
							new easyXDM.Rpc({
								local: "http://<?= BASE_DOMAIN ?>/html/communicator.html",
								swf: "http://"+sayso.baseDomain+"/swf/easyxdm.swf",
								remote: link,
								remoteHelper: "http://<?= BASE_DOMAIN ?>/html/communicator.html",
                				container: activeLink.attr('rel'),
                				props: {
				                    scrolling: "no",
				                    style: {
				                        height: parseInt(activeLink.attr('iframeHeight'))+"px",
				                        width: "470px",
				                        margin: 0,
				                        border: 0
				                    }
				                },
							}, {
								// Local functions (i.e. remote procedure calls arrive here)
								local: {
									resizeFrame: function (height) {
										frame = $SQ('iframe', ui.newContent);
										frame.height(height);
										ui.newContent.css('height', height+5);

                    					var scrollPane = ui.newHeader.parents('.sb_scrollPane');
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
									
									},
									alertMessage: function (msg) {
										sayso.log(msg);
									}
								}
							});
							
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
    
    // flag so we know this file has loaded
    window.sayso.starbar.loaded = true;
});

