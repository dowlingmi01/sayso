/**
 * Starbar
 */
		  
// load after slight delay
setTimeout(function(){
		
	var kynetxAppId = 'a239x14';
	
	// global var
	var themeColor = '#de40b2';
	
	//easy trigger var for whether an element is open or closed.
	var starBarStatusHeight = 'starbar-closed';
	var starBarStatusWidth = 'starbar-visOpen';
	
	//once we've got the cookie 
	initStarBar();
	
	//set up all links inside popBox to have hoverColor
	$SQ('#sayso-starbar .starbar-popBox a').addClass('starbar-setLinkHover');
													 
	setThemeColors(themeColor);
	
	// set up the full / shrunk / closed states for the bar
	$SQ('#sayso-starbar #starbar-toggleVis').click(function(event){
		event.preventDefault();
		var playerClass = $SQ('#sayso-starbar #starbar-player-console').attr('class');
		animateBar(playerClass, 'button');
		popBoxClose();
	});
	$SQ('#sayso-starbar #starbar-logo').click(function(event){
		var playerClass = $SQ('#sayso-starbar #starbar-player-console').attr('class');
		if (playerClass != 'starbar-visOpen'){
			// only run if we're not at full visibility
			animateBar(playerClass, 'logo');
			popBoxClose();
		}else{
			event.preventDefault();
			popBox($SQ(this));
			refreshScroll();
			refreshDialog();
		}
		return false;
	});
	
	//set up click behavior for menu + popBox reveals
	$SQ('#sayso-starbar .starbar-navLink').click(function(event){																
		event.preventDefault();
		popBox($SQ(this));
		refreshScroll();
		refreshDialog();
		toggleNav();
		
		
	});	// end navLink click

	
	// close popBox if "escape" pressed
	$SQ(document).keyup(function(e) {
		if (e.keyCode == 27) {
			var currentNavActive = $SQ('#sayso-starbar #starbar-nav li#starbar-nav_active');
			currentNavActive.children().children('span.starbar-navBorder').css('backgroundColor','');
			popBoxClose();
		}  // esc
	});
	
	
	// close if you click outside the starbar while in the iframe
	$SQ(document).click(function(e) {
		var navActive = $SQ('#sayso-starbar #starbar-nav_active');
		var navActiveLink = navActive.children('a');
		var navActiveSpan = navActiveLink.children('span.starbar-navBorder');
		navActiveSpan.css('backgroundColor','');
		popBoxClose();
	});
	
	// close if you click outside the starbar on the main window
	// @todo now that we are not using an iframe, re-enable this using 'body'
//	$SQ(parent.document).click(function(e) {
//		var navActive = $SQ('#starbar-nav_active');
//		var navActiveLink = navActive.children('a');
//		var navActiveSpan = navActiveLink.children('span.starbar-navBorder');
//		navActiveSpan.css('backgroundColor','');
//  	popBoxClose();
//	});
	
	$SQ('#sayso-starbar #starbar-player-console').click(function(e) {
		e.stopPropagation();
	});
	
	function initStarBar(method){
		if (!method) method = 'init';
		if (!window.sayso.starbar.state.visibility){
			updateState('starbar-visOpen');
		}	
		// set the open / close state of the bar
		var playerClass = window.sayso.starbar.state.visibility;
		animateBar(playerClass, method);
	}
	
	/*
	POPBOX
	- see if there's an active box, if it is, close it, remove the active class
	- if activeBox and popBox are the same, break out of the function
	- if no activeBox, fadeIn popbox
	- make sure the nav span gets the class 'starbar-setColorActive'
	*/
	
	function popBox(clickedLink){	
		var itemLink = clickedLink;
		var itemList = clickedLink.parent();
		var itemID = itemList.children('.starbar-popBoxContent').attr('id');
			
		var currentPopID = $SQ('#sayso-starbar .starbar-popBox').attr('id');
		var currentNavActive = $SQ('#sayso-starbar #starbar-nav_active');
				
		//if the link's parent <li> clicked already had an ID, it was the active one we should just close a box and quit
		if (itemList.attr('id').length != 0){
			popBoxClose();
			return false;
		}else{
			// weird hack to manually delete the background "on" color if a different nav item was clicked
			currentNavActive.children().children('span.starbar-navBorder').css('backgroundColor','');
		}
		
		// first, close any open popbox
		popBoxClose();
			
		// open the next popBox using the data from the clicked item
		popBoxOpen(itemList);
		
		
		return false;	
	}
	
	function popBoxOpen(clickedItem){
		/*
		1. populate the popBox inner with the popInner from the clicked item
		2. add necessary classes to define menu item as "on"
		3. fadeIn the populated popBox and assign it an ID
		*/
		var itemList = clickedItem;
		var itemID = itemList.children('.starbar-popBoxContent').attr('id');
		var itemInner = itemList.children('.starbar-popBoxContent').html();
		var itemLink = itemList.children('a.starbar-navLink');
		var itemLinkSpan = itemLink.children('span.starbar-navBorder');
		var popBox = $SQ('#sayso-starbar .starbar-popBox');
		var popBoxInner = $SQ('#sayso-starbar .starbar-popBox .starbar-popInner');
				
		popBoxInner.html(itemInner);
		itemList.attr('id','starbar-nav_active');
		itemLinkSpan.addClass('starbar-setColorActive');
		//itemLinkSpan.css('backgroundColor',themeColor);
		
		//itemLinkSpan.css('backgroundColor',themeColor);
		popBox.attr('id','popBox'+itemID).show();
		
		starBarStatusHeight = 'starbar-open';
		
		return false;
	}
	
	function popBoxClose(){
		/*
		1. clears content from active popbox
		2. clears "active" state on nav item
		3. closes popbox
		*/
		
		var popBoxOpened = $SQ('#sayso-starbar .starbar-popBox');
		var navActive = $SQ('#sayso-starbar #starbar-nav_active');
		var navActiveLink = navActive.children('a');
		var navActiveSpan = navActiveLink.children('span.starbar-navBorder');
			
		// check to make sure the is opened
		if (popBoxOpened.attr('id').length != 0){
			popBoxOpened.children('.starbar-popInner').html('');
			popBoxOpened.attr('id','').hide();
			navActiveSpan.removeClass('starbar-setColorActive');
			navActive.attr('id','');
		}	
		
		starBarStatusH = 'starbar-closed';
			
		return false;
	}


	// receives a single hex color and sets the button rollover color and starbox colors
	function setThemeColors(newColor){
		if (newColor == ''){
			newColor = "#666666";
		}
		
		// if it's just a flat color that needs changing...
		$SQ('#sayso-starbar .starbar-setColor').css('backgroundColor', newColor);
		$SQ('#sayso-starbar .starbar-setColorActive').css('backgroundColor',newColor);
		$SQ('#sayso-starbar .starbar-setTextColor').css('color', newColor);
		
		// now we're going to set up specialized cases for hovers
		$SQ('#sayso-starbar .starbar-setColorHover').parent().hover(
			function(){
				$SQ(this).children('.starbar-setColorHover').css('backgroundColor', newColor);			
			},
			function(){
				if ($SQ(this).children().hasClass('starbar-setColorActive')){
					return false;
				}else{
					$SQ(this).children('.starbar-setColorHover').css('backgroundColor','');
				}
			});
		
		// set up hover colors for regular links
		$SQ('#sayso-starbar a.starbar-setLinkHover').hover(
			function(){
				$SQ(this).css('color', newColor);			
				$SQ(this).css('backgroundColor','none');
			},
			function(){				
				$SQ(this).css('color', '');		
				$SQ(this).css('backgroundColor','none');
			});
		
		//set up the hover shadows
		$SQ('#sayso-starbar .starbar-setColorShadow').parent().hover(
			function(){
				$SQ(this).children('.starbar-setColorShadow').css({boxShadow: '0 0 5px'+newColor});
				$SQ(this).children('.starbar-setColorShadow').css({'-moz-boxShadow': '0 0 5px'+newColor});
				$SQ(this).children('.starbar-setColorShadow').css({'-webkit-boxShadow': '0 0 5px'+newColor});
			},
			function(){
				$SQ(this).children('.starbar-setColorShadow').css({boxShadow: '0px 0px 0px'+newColor});
				$SQ(this).children('.starbar-setColorShadow').css({'-moz-boxShadow': '0 0 0px'+newColor});
				$SQ(this).children('.starbar-setColorShadow').css({'-webkit-boxShadow': '0 0 0px'+newColor});
			});
		
		
		return false;
	}

	// changes the theme of the starbar 
	function changeTheme(newTheme){
		var themeParent = newTheme.closest('.starbar-columnContent');
		var newImg = themeParent.children('img');
		var newTitle = themeParent.children().children('h4').html();
		var newCount = themeParent.children().children('h5').html();
		var newColor = newTheme.attr('href');
		
		$SQ('#sayso-starbar .starbar .starbar-content img').attr('src',newImg.attr('src'));
		$SQ('#sayso-starbar .starbar .starbar-content h3').html(newTitle);
		$SQ('#sayso-starbar .starbar .starbar-content h5').html(newCount);
		$SQ('#sayso-starbar .starbar .starbar-content h5 span').html('');
		setThemeColors(newColor);
		return false;
	}
	
	// animates the starbar-player-console bar based on current state
	function animateBar(playerClass, clickPoint){
		// if we're clicking from a button, determine what state we're in and how to shrink
		if (clickPoint == 'button'){
			switch (playerClass){
				case 'starbar-visOpen':
					$SQ('#sayso-starbar #starbar-mainContent').fadeTo('fast', 0);
					$SQ('#sayso-starbar #starbar-toggleVis').attr('class','');
					$SQ('#sayso-starbar #starbar-toggleVis').addClass('close');
					$SQ('#sayso-starbar #starbar-player-console').animate({
							width: '90'
						}, 500, function() {
							// Animation complete.
							$SQ(this).attr('class','').addClass('starbar-visClosed');
							$SQ('#sayso-starbar #starbar-logoBorder').show();
							updateState();
						});
					starBarStatusHeight = 'starbar-closed';
					starBarStatusWidth = 'starbar-visClosed';
				break;
				case 'starbar-visClosed':
					$SQ('#sayso-starbar #starbar-toggleVis').attr('class','');
					$SQ('#sayso-starbar #starbar-toggleVis').addClass('starbar-closed');
					//$SQ('#starbar-player-console').addClass('starbar-visStowed');
					$SQ('#sayso-starbar #starbar-logoBorder').hide();
					$SQ('#sayso-starbar #starbar-player-console').animate({
							width: '45'
						}, 500, function() {
							// Animation complete.
							$SQ(this).attr('class','').addClass('starbar-visStowed');
							updateState();
					});
					starBarStatusHeight = 'starbar-closed';
					starBarStatusWidth = 'starbar-visStowed';
				break;
				case 'starbar-visStowed':
					$SQ('#sayso-starbar #starbar-toggleVis').attr('class','');
					$SQ('#sayso-starbar #starbar-toggleVis').addClass('starbar-hide');
					$SQ('#sayso-starbar #starbar-logoBorder').hide();
					$SQ('#sayso-starbar #starbar-player-console').addClass('starbar-visOpen');
					$SQ('#sayso-starbar #starbar-player-console').animate({
							width: '100%'
						}, 500, function() {
							// Animation complete.
							$SQ(this).attr('class','').addClass('starbar-visOpen');
							$SQ('#starbar-mainContent').fadeTo('fast', 1);
							updateState();
					});
					starBarStatusHeight = 'starbar-open';
					starBarStatusWidth = 'starbar-visOpen';
				break;
			}	// END SWITCH
						
		}// end if clickPoint = button
		else if (clickPoint == 'init'){
			switch (playerClass){ 
				case 'starbar-visOpen':				
					$SQ('#sayso-starbar #starbar-player-console').hide();
					$SQ('#sayso-starbar #starbar-toggleVis').attr('class','');
					$SQ('#sayso-starbar #starbar-toggleVis').addClass('starbar-hide');
					$SQ('#sayso-starbar #starbar-logoBorder').hide();
					$SQ('#sayso-starbar #starbar-player-console').attr('class','').addClass('starbar-visOpen').show();
					$SQ('#sayso-starbar #starbar-mainContent').fadeTo('fast', 1);
					starBarStatusWidth = 'starbar-visOpen';
					break;
				case 'starbar-visClosed': 					
					$SQ('#sayso-starbar #starbar-player-console').hide();
					$SQ('#sayso-starbar #starbar-mainContent').fadeTo('fast', 0);
					$SQ('#sayso-starbar #starbar-toggleVis').attr('class','');
					$SQ('#sayso-starbar #starbar-toggleVis').addClass('close');
					$SQ('#sayso-starbar #starbar-player-console').attr('class','').addClass('starbar-visClosed').show();
					$SQ('#sayso-starbar #starbar-logoBorder').show();
					starBarStatusHeight = 'starbar-closed';
					starBarStatusWidth = 'starbar-visClosed';
					break;
				case 'starbar-visStowed':					
					$SQ('#sayso-starbar #starbar-player-console').hide();
					$SQ('#sayso-starbar #starbar-toggleVis').attr('class','');
					$SQ('#sayso-starbar #starbar-toggleVis').addClass('starbar-closed');
					$SQ('#sayso-starbar #starbar-logoBorder').hide();
					$SQ('#sayso-starbar #starbar-player-console').attr('class','').addClass('starbar-visStowed').show();
					starBarStatusHeight = 'starbar-closed';
					starBarStatusWidth = 'starbar-visStowed';
					break;
			}	// END SWITCH
			
		}
		else if (clickPoint === 'refresh') {
			switch (playerClass){
				case 'starbar-visOpen':
					$SQ('#sayso-starbar #starbar-toggleVis').attr('class','');
					$SQ('#sayso-starbar #starbar-toggleVis').addClass('starbar-hide');
					$SQ('#sayso-starbar #starbar-logoBorder').hide();
					$SQ('#sayso-starbar #starbar-player-console').addClass('starbar-visOpen');
					$SQ('#sayso-starbar #starbar-player-console').animate({
							width: '100%'
						}, 500, function() {
							// Animation complete.
							$SQ(this).attr('class','').addClass('starbar-visOpen');
							$SQ('#starbar-mainContent').fadeTo('fast', 1);		  
					});
					starBarStatusHeight = 'starbar-open';
					starBarStatusWidth = 'starbar-visOpen';
					break;
				case 'starbar-visClosed':
					$SQ('#sayso-starbar #starbar-mainContent').fadeTo('fast', 0);
					$SQ('#sayso-starbar #starbar-toggleVis').attr('class','');
					$SQ('#sayso-starbar #starbar-toggleVis').addClass('close');
					$SQ('#sayso-starbar #starbar-player-console').animate({
							width: '90'
						}, 500, function() {
							// Animation complete.
							$SQ(this).attr('class','').addClass('starbar-visClosed');
							$SQ('#sayso-starbar #starbar-logoBorder').show();
						});
					starBarStatusHeight = 'starbar-closed';
					starBarStatusWidth = 'starbar-visClosed';
					break;
				case 'starbar-visStowed':
					if (!$SQ('#starbar-mainContent').is(':hidden')) {
						$SQ('#sayso-starbar #starbar-mainContent').fadeTo('fast', 0);
					}
					$SQ('#sayso-starbar #starbar-toggleVis').attr('class','');
					$SQ('#sayso-starbar #starbar-toggleVis').addClass('starbar-closed');
					$SQ('#sayso-starbar #starbar-logoBorder').hide();
					$SQ('#sayso-starbar #starbar-player-console').animate({
							width: '45'
						}, 500, function() {
							// Animation complete.
							$SQ(this).attr('class','').addClass('starbar-visStowed');
					});
					
					starBarStatusHeight = 'starbar-closed';
					starBarStatusWidth = 'starbar-visStowed';
					break;
			}   // END SWITCH
		}
		else{
			// if we clicked the logo, always go into full view if we aren't already there
			$SQ('#sayso-starbar #starbar-toggleVis').attr('class','');
			$SQ('#sayso-starbar #starbar-toggleVis').addClass('starbar-hide');
			$SQ('#sayso-starbar #starbar-logoBorder').hide();
			$SQ('#sayso-starbar #starbar-player-console').attr('class','');
			$SQ('#sayso-starbar #starbar-player-console').addClass('starbar-visOpen');
			$SQ('#sayso-starbar #starbar-player-console').animate({
					width: '100%'
				}, 500, function() {
					// Animation complete.
					$SQ(this).attr('class','');
					$SQ(this).addClass('starbar-visOpen');
					$SQ('#starbar-mainContent').fadeTo('fast', 1);
					updateState();
				});
			starBarStatusHeight = 'starbar-closed';
			starBarStatusWidth = 'starbar-visOpen';
		} // end else
		
		return false;
	} // end animateBar
	
	/* refresh the scrollbars */
	
	function refreshScroll(){
		// set up scrollbars for the popBoxes
		if ($SQ('#sayso-starbar .starbar-popBox .starbar-column_left .starbar-scrollbar').length != 0){
			var ScrollbarL = $SQ('.starbar-popBox .starbar-column_left');
			ScrollbarL.tinyscrollbar();	
			ScrollbarL.tinyscrollbar_update();
		}
		if ($SQ('#sayso-starbar .starbar-popBox .starbar-column_right .starbar-scrollbar').length != 0){
			var ScrollbarR = $SQ('.starbar-popBox .starbar-column_right');
			ScrollbarR.tinyscrollbar();	
			ScrollbarR.tinyscrollbar_update();
		}		
		if ($SQ('#sayso-starbar .starbar-popBox .starbar-column_single .starbar-scrollbar').length != 0){
			var ScrollbarS = $SQ('.starbar-popBox .starbar-column_single');
			ScrollbarS.tinyscrollbar();	
			ScrollbarS.tinyscrollbar_update();
		}		
		return false;
	}
	
	// reset the dialog behaviors / set them up
	function refreshDialog(){
		// set up popDialogs
		$SQ('#sayso-starbar a.starbar-popDialog').unbind('click').click(function(event){
			event.preventDefault();
			var popDialogSrc = $SQ(this).attr('href'),
				popDialog = $SQ('#saysoPopBoxDialog');
			popDialog.lightbox_me({
				onLoad : function () {},
				onClose : function () {},
				modalCSS : { top: '170px' },
				zIndex : 10000
			});							
			popDialog.find('iframe').attr('src',popDialogSrc);
		});
			
		$SQ('#sayso-starbar a.starbar-closePop').click(function(event){
			event.preventDefault();
			popBoxClose();				
		});
		
		$SQ('#sayso-starbar a.starbar-changeTheme').click(function(event){
			event.preventDefault();
			changeTheme($SQ(this));				
		});
		return false;
	}
	
	// set up toggleNav behavior
	function toggleNav(){
		// initialize the first item
		$SQ('#sayso-starbar .starbar-toggleElement').hide();
		$SQ('#sayso-starbar .starbar-toggleNav li:nth-child(2)').children('a.starbar-toggleLink').addClass('active');
		$SQ('#sayso-starbar .starbar-toggleElement').first().show();
		
		
		$SQ('#sayso-starbar .starbar-toggleNav a.starbar-toggleLink').click(function(){
			var thisToggleID = $SQ(this).parent().attr('class');
				$SQ('#sayso-starbar .starbar-toggleNav a.starbar-toggleLink').each(function(){
					$SQ(this).removeClass('active');
				});
				$SQ(this).addClass('active');
				$SQ('#sayso-starbar .starbar-toggleElement').hide();
				$SQ('#sayso-starbar #'+thisToggleID).show();																				 
		});
	}
	
	function updateState (visibility){	
		if (!visibility) visibility = $SQ('#sayso-starbar #starbar-player-console').attr('class');
		window.sayso.starbar.state.visibility = visibility;
		var app = KOBJ.get_application(kynetxAppId);
		app.raise_event('update_state', { 'visibility' : visibility /* other state changes here */ });
	}
	
	function refreshState () {
		window.sayso.starbar.callback = function () { initStarBar('refresh'); };
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
	
	function log (message) {
		if (console && console.log) {
			console.log('message');
		}
	}
	
}, 200); // slight delay to ensure other libraries are loaded
