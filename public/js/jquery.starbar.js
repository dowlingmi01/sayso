if (!window.sayso) window.sayso = {};
window.sayso.starbar = {};
                
setTimeout(function(){
													 
	// global var
    var themeColor = '#de40b2';
	
	//easy trigger var for whether an element is open or closed.
	var starBarStatusHeight = 'starbar-closed';
	var starBarStatusWidth = 'starbar-visOpen';
	
	//once we've got the cookie 
	initStarBar();
	
	//resize the frame if we've just loaded the page
	initFrame();
	
	//set up all links inside popBox to have hoverColor
	jQuery('#sayso-starbar .starbar-popBox a').addClass('starbar-setLinkHover');
													 
	setThemeColors(themeColor);
	
	function fixPlayerClassForBackwardsCompatibility (playerClass) {
	    // since we have changed the name of the player classes (e.g. starbar-visOpen),
	    // the current installed Starbar plugins will not work because a cookie 
	    // is set on every domain recording the visible state of the Starbar 
	    // and the value of this cookie is the class name that has changed.
	    // so instead of requiring everyone to delete all their cookies, we
	    // just correct the problem here (if it exists)
	    if (playerClass.substring(0,7) !== 'starbar') {
            var oldClass = playerClass;
            playerClass = 'starbar-' + playerClass;
            $('#sayso-starbar #starbar-player-console').addClass(playerClass);
            $('#sayso-starbar #starbar-player-console').removeClass(oldClass);
        }
	    return playerClass;
	}
	
	// set up the full / shrunk / closed states for the bar
	jQuery('#sayso-starbar #starbar-toggleVis').click(function(event){
		event.preventDefault();
		var playerClass = jQuery('#sayso-starbar #starbar-player-console').attr('class');
		playerClass = fixPlayerClassForBackwardsCompatibility(playerClass);
		animateBar(playerClass, 'button');
		popBoxClose();
		initFrame();
	});
	jQuery('#sayso-starbar #starbar-logo').click(function(event){
	    var playerClass = jQuery('#sayso-starbar #starbar-player-console').attr('class');
	    playerClass = fixPlayerClassForBackwardsCompatibility(playerClass);
		if (playerClass != 'starbar-visOpen'){
			// only run if we're not at full visibility
			animateBar(playerClass, 'logo');
			popBoxClose();
			initFrame();
		}else{
			event.preventDefault();
			popBox(jQuery(this));
			refreshScroll();
			refreshDialog();
		}
		return false;
	});
	
	//set up click behavior for menu + popBox reveals
	jQuery('#sayso-starbar .starbar-navLink').click(function(event){																
		event.preventDefault();
		popBox(jQuery(this));
		refreshScroll();
		refreshDialog();
		toggleNav();
		
		
	});	// end navLink click

	
	// close popBox if "escape" pressed
	jQuery(document).keyup(function(e) {
		if (e.keyCode == 27) {
			var currentNavActive = jQuery('#sayso-starbar #starbar-nav li#starbar-nav_active');
			currentNavActive.children().children('span.starbar-navBorder').css('backgroundColor','');
			popBoxClose();
			initFrame();
		}  // esc
	});
	
	
	// close if you click outside the starbar while in the iframe
	jQuery(document).click(function(e) {
		var navActive = jQuery('#sayso-starbar #starbar-nav_active');
		var navActiveLink = navActive.children('a');
		var navActiveSpan = navActiveLink.children('span.starbar-navBorder');
		navActiveSpan.css('backgroundColor','');
		popBoxClose();
		initFrame();
	});
	
	// close if you click outside the starbar on the main wondow
//	jQuery(parent.document).click(function(e) {
//		var navActive = jQuery('#starbar-nav_active');
//		var navActiveLink = navActive.children('a');
//		var navActiveSpan = navActiveLink.children('span.starbar-navBorder');
//		navActiveSpan.css('backgroundColor','');
//  	popBoxClose();
//		initFrame();
//	});
	
	jQuery('#sayso-starbar #starbar-player-console').click(function(e) {
	    e.stopPropagation();
	});
	
    
    function initStarBar(){	
    	if (jQuery.cookies.get('starBarStatus') == null){
    		jQuery.cookies.set('starBarStatus','starbar-visOpen');
    	}	
    	
    	// set the open / close state of the bar
    	var playerClass = jQuery.cookies.get('starBarStatus');
    	playerClass = fixPlayerClassForBackwardsCompatibility(playerClass);
    	animateBar(playerClass,'init');
    	
    	return false;
    }
    
    /* WHENEVER CALLED, FIGURES OUT IF THE STARBAR HAS VISIBLE POPUPS AND RESIZES THE PARENT FRAME TO SUIT */
    function initFrame(){	
    	var height = 60;
    	var width = '100%';
    	
    	if (starBarStatusHeight == 'starbar-closed'){
    		height = 60;
    	}else{
    		height = 530;
    	}	
    		
    	switch (starBarStatusWidth){
    		case 'starbar-visOpen':
    			width = '100%';
    		break;
    		case 'starbar-visClosed':
    			width = 110;
    		break;
    		case 'starbar-visStowed':
    			width = 65;
    		break;
    	}
    	
    	//alert(jQuery('#starbar-player-console').css('width'));
    		
    	//jQuery(parent.document.getElementById('saysoStarBarFrame')).attr('height', height);
    	//jQuery(parent.document.getElementById('saysoStarBarFrame')).delay(500).attr('width', width);	
    	return false;
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
    		
    	var currentPopID = jQuery('#sayso-starbar .starbar-popBox').attr('id');
    	var currentNavActive = jQuery('#sayso-starbar #starbar-nav_active');
    			
    	//if the link's parent <li> clicked already had an ID, it was the active one we should just close a box and quit
    	if (itemList.attr('id').length != 0){
    		popBoxClose();
    		initFrame();
    		return false;
    	}else{
    		// weird hack to manually delete the background "on" color if a different nav item was clicked
    		currentNavActive.children().children('span.starbar-navBorder').css('backgroundColor','');
    	}
    	
    	// first, close any open popbox
    	popBoxClose();
    		
    	// open the next popBox using the data from the clicked item
    	popBoxOpen(itemList);
    	
    	
    	initFrame();
    	
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
    	var popBox = jQuery('#sayso-starbar .starbar-popBox');
    	var popBoxInner = jQuery('#sayso-starbar .starbar-popBox .starbar-popInner');
    			
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
    	
    	var popBoxOpened = jQuery('#sayso-starbar .starbar-popBox');
    	var navActive = jQuery('#sayso-starbar #starbar-nav_active');
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
		jQuery('#sayso-starbar .starbar-setColor').css('backgroundColor', newColor);
		jQuery('#sayso-starbar .starbar-setColorActive').css('backgroundColor',newColor);
		jQuery('#sayso-starbar .starbar-setTextColor').css('color', newColor);
		
		// now we're going to set up specialized cases for hovers
		jQuery('#sayso-starbar .starbar-setColorHover').parent().hover(
			function(){
				jQuery(this).children('.starbar-setColorHover').css('backgroundColor', newColor);			
			},
			function(){
				if (jQuery(this).children().hasClass('starbar-setColorActive')){
					return false;
				}else{
					jQuery(this).children('.starbar-setColorHover').css('backgroundColor','');
				}
			});
		
		// set up hover colors for regular links
		jQuery('#sayso-starbar a.starbar-setLinkHover').hover(
			function(){
				jQuery(this).css('color', newColor);			
				jQuery(this).css('backgroundColor','none');
			},
			function(){				
				jQuery(this).css('color', '');		
				jQuery(this).css('backgroundColor','none');
			});
		
		//set up the hover shadows
		jQuery('#sayso-starbar .starbar-setColorShadow').parent().hover(
			function(){
				jQuery(this).children('.starbar-setColorShadow').css({boxShadow: '0 0 5px'+newColor});
				jQuery(this).children('.starbar-setColorShadow').css({'-moz-boxShadow': '0 0 5px'+newColor});
				jQuery(this).children('.starbar-setColorShadow').css({'-webkit-boxShadow': '0 0 5px'+newColor});
			},
			function(){
				jQuery(this).children('.starbar-setColorShadow').css({boxShadow: '0px 0px 0px'+newColor});
				jQuery(this).children('.starbar-setColorShadow').css({'-moz-boxShadow': '0 0 0px'+newColor});
				jQuery(this).children('.starbar-setColorShadow').css({'-webkit-boxShadow': '0 0 0px'+newColor});
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
    	
    	jQuery('#sayso-starbar .starbar .starbar-content img').attr('src',newImg.attr('src'));
    	jQuery('#sayso-starbar .starbar .starbar-content h3').html(newTitle);
    	jQuery('#sayso-starbar .starbar .starbar-content h5').html(newCount);
    	jQuery('#sayso-starbar .starbar .starbar-content h5 span').html('');
    	setThemeColors(newColor);
    	return false;
    }
    
    // animates the starbar-player-console bar based on current state
	function animateBar(playerClass, clickPoint){
		// if we're clicking from a button, determine what state we're in and how to shrink
		if (clickPoint == 'button'){
			switch (playerClass){
				case 'starbar-visOpen':
					jQuery('#sayso-starbar #starbar-mainContent').fadeOut('fast');
					jQuery('#sayso-starbar #starbar-toggleVis').attr('class','');
					jQuery('#sayso-starbar #starbar-toggleVis').addClass('close');
					jQuery('#sayso-starbar #starbar-player-console').animate({
							width: '90'
						}, 500, function() {
							// Animation complete.
							jQuery(this).attr('class','').addClass('starbar-visClosed');
							jQuery('#sayso-starbar #starbar-logoBorder').show();
							cookieUpdate();
						});
					starBarStatusHeight = 'starbar-closed';
					starBarStatusWidth = 'starbar-visClosed';
				break;
				case 'starbar-visClosed':
					jQuery('#sayso-starbar #starbar-toggleVis').attr('class','');
					jQuery('#sayso-starbar #starbar-toggleVis').addClass('starbar-closed');
					//jQuery('#starbar-player-console').addClass('starbar-visStowed');
					jQuery('#sayso-starbar #starbar-logoBorder').hide();
					jQuery('#sayso-starbar #starbar-player-console').animate({
							width: '45'
						}, 500, function() {
							// Animation complete.
							jQuery(this).attr('class','').addClass('starbar-visStowed');
							cookieUpdate();
					});
					starBarStatusHeight = 'starbar-closed';
					starBarStatusWidth = 'starbar-visStowed';
				break;
				case 'starbar-visStowed':
					jQuery('#sayso-starbar #starbar-toggleVis').attr('class','');
					jQuery('#sayso-starbar #starbar-toggleVis').addClass('starbar-hide');
					jQuery('#sayso-starbar #starbar-logoBorder').hide();
					jQuery('#sayso-starbar #starbar-player-console').addClass('starbar-visOpen');
					jQuery('#sayso-starbar #starbar-player-console').animate({
							width: '100%'
						}, 500, function() {
							// Animation complete.
							jQuery(this).attr('class','').addClass('starbar-visOpen');
							jQuery('#starbar-mainContent').fadeIn('fast');			
							cookieUpdate();
					});
					starBarStatusHeight = 'starbar-open';
					starBarStatusWidth = 'starbar-visOpen';
				break;
			}	// END SWITCH
						
		}// end if clickPoint = button
		else if (clickPoint == 'init'){
			switch (playerClass){ 
				case 'starbar-visOpen':				
					jQuery('#sayso-starbar #starbar-player-console').hide();
					jQuery('#sayso-starbar #starbar-toggleVis').attr('class','');
					jQuery('#sayso-starbar #starbar-toggleVis').addClass('starbar-hide');
					jQuery('#sayso-starbar #starbar-logoBorder').hide();
					jQuery('#sayso-starbar #starbar-player-console').attr('class','').addClass('starbar-visOpen').show();
					jQuery('#sayso-starbar #starbar-mainContent').fadeIn('fast');
					starBarStatusWidth = 'starbar-visOpen';
				break;
				case 'starbar-visClosed': 					
					jQuery('#sayso-starbar #starbar-player-console').hide();
					jQuery('#sayso-starbar #starbar-mainContent').fadeOut('fast');
					jQuery('#sayso-starbar #starbar-toggleVis').attr('class','');
					jQuery('#sayso-starbar #starbar-toggleVis').addClass('close');
					jQuery('#sayso-starbar #starbar-player-console').attr('class','').addClass('starbar-visClosed').show();
					jQuery('#sayso-starbar #starbar-logoBorder').show();
					starBarStatusHeight = 'starbar-closed';
					starBarStatusWidth = 'starbar-visClosed';
				break;
				case 'starbar-visStowed':					
					jQuery('#sayso-starbar #starbar-player-console').hide();
					jQuery('#sayso-starbar #starbar-toggleVis').attr('class','');
					jQuery('#sayso-starbar #starbar-toggleVis').addClass('starbar-closed');
					jQuery('#sayso-starbar #starbar-logoBorder').hide();
					jQuery('#sayso-starbar #starbar-player-console').attr('class','').addClass('starbar-visStowed').show();
					starBarStatusHeight = 'starbar-closed';
					starBarStatusWidth = 'starbar-visStowed';
				break;
			}	// END SWITCH
			
		}
		else{
			// if we clicked the logo, always go into full view if we aren't already there
			jQuery('#sayso-starbar #starbar-toggleVis').attr('class','');
			jQuery('#sayso-starbar #starbar-toggleVis').addClass('starbar-hide');
			jQuery('#sayso-starbar #starbar-logoBorder').hide();
			jQuery('#sayso-starbar #starbar-player-console').attr('class','');
			jQuery('#sayso-starbar #starbar-player-console').addClass('starbar-visOpen');
			jQuery('#sayso-starbar #starbar-player-console').animate({
					width: '100%'
				}, 500, function() {
					// Animation complete.
					jQuery(this).attr('class','');
					jQuery(this).addClass('starbar-visOpen');
					jQuery('#starbar-mainContent').fadeIn('fast');
					cookieUpdate();
				});
			starBarStatusHeight = 'starbar-closed';
			starBarStatusWidth = 'starbar-visOpen';
		} // end else
		
		return false;
	} // end animateBar
	
	/* refresh the scrollbars */
	
	function refreshScroll(){
		// set up scrollbars for the popBoxes
		if (jQuery('#sayso-starbar .starbar-popBox .starbar-column_left .starbar-scrollbar').length != 0){
			var ScrollbarL = jQuery('.starbar-popBox .starbar-column_left');
			ScrollbarL.tinyscrollbar();	
			ScrollbarL.tinyscrollbar_update();
		}
		if (jQuery('#sayso-starbar .starbar-popBox .starbar-column_right .starbar-scrollbar').length != 0){
			var ScrollbarR = jQuery('.starbar-popBox .starbar-column_right');
			ScrollbarR.tinyscrollbar();	
			ScrollbarR.tinyscrollbar_update();
		}		
		if (jQuery('#sayso-starbar .starbar-popBox .starbar-column_single .starbar-scrollbar').length != 0){
			var ScrollbarS = jQuery('.starbar-popBox .starbar-column_single');
			ScrollbarS.tinyscrollbar();	
			ScrollbarS.tinyscrollbar_update();
		}		
		return false;
	}
	
	// reset the dialog behaviors / set them up
	function refreshDialog(){
		// set up popDialogs
		jQuery('#sayso-starbar a.starbar-popDialog').unbind('click').click(function(event){
			event.preventDefault();
			var popDialogSrc = jQuery(this).attr('href'),
			    popDialog = jQuery('#saysoPopBoxDialog');
			popDialog.lightbox_me({
			    onLoad : function () {},
			    onClose : function () {},
			    modalCSS : { top: '170px' },
			    zIndex : 10000
			});							
			popDialog.find('iframe').attr('src',popDialogSrc);
		});
			
		jQuery('#sayso-starbar a.starbar-closePop').click(function(event){
			event.preventDefault();
			popBoxClose();				
		});
		
		jQuery('#sayso-starbar a.starbar-changeTheme').click(function(event){
			event.preventDefault();
			changeTheme(jQuery(this));				
		});
		return false;
	}
	
	// set up toggleNav behavior
	function toggleNav(){
		// initialize the first item
		jQuery('#sayso-starbar .starbar-toggleElement').hide();
		jQuery('#sayso-starbar .starbar-toggleNav li:nth-child(2)').children('a.starbar-toggleLink').addClass('active');
		jQuery('#sayso-starbar .starbar-toggleElement').first().show();
		
		
		jQuery('#sayso-starbar .starbar-toggleNav a.starbar-toggleLink').click(function(){
			var thisToggleID = jQuery(this).parent().attr('class');
				jQuery('#sayso-starbar .starbar-toggleNav a.starbar-toggleLink').each(function(){
					jQuery(this).removeClass('active');
				});
				jQuery(this).addClass('active');
				jQuery('#sayso-starbar .starbar-toggleElement').hide();
				jQuery('#sayso-starbar #'+thisToggleID).show();																				 
		});
	}
	
	function cookieUpdate(){		
		jQuery.cookies.set('starBarStatus', jQuery('#sayso-starbar #starbar-player-console').attr('class'));
	}
	
	function log (message) {
	    if (console && console.log) {
	        console.log('message');
	    }
	}
	
}, 200); // slight delay to ensure other libraries are loaded
