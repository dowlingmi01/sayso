if (!window.sayso) window.sayso = {};
window.sayso.starbar = {};
                
setTimeout(function(){
													 
	// global var
    var themeColor = '#de40b2';
	
	//easy trigger var for whether an element is open or closed.
	var starBarStatusHeight = 'closed';
	var starBarStatusWidth = 'visOpen';
	
	//once we've got the cookie 
	initStarBar();
	
	//resize the frame if we've just loaded the page
	initFrame();
	
	//set up all links inside popBox to have hoverColor
	jQuery('#sayso-starbar .popBox a').addClass('setLinkHover');
													 
	setThemeColors(themeColor);
	
	// set up the full / shrunk / closed states for the bar
	jQuery('#sayso-starbar #toggleVis').click(function(event){
		event.preventDefault();
		var playerClass = jQuery('#sayso-starbar #player-console').attr('class');
		animateBar(playerClass, 'button');
		popBoxClose();
		initFrame();
	});
	jQuery('#sayso-starbar #logo').click(function(event){
		var playerClass = jQuery('#sayso-starbar #player-console').attr('class');
		if (playerClass != 'visOpen'){
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
	jQuery('#sayso-starbar .navLink').click(function(event){																
		event.preventDefault();
		popBox(jQuery(this));
		refreshScroll();
		refreshDialog();
		toggleNav();
		
		
	});	// end navLink click

	
	// close popBox if "escape" pressed
	jQuery(document).keyup(function(e) {
		if (e.keyCode == 27) {
			var currentNavActive = jQuery('#sayso-starbar #nav li#nav_active');
			currentNavActive.children().children('span.navBorder').css('backgroundColor','');
			popBoxClose();
			initFrame();
		}  // esc
	});
	
	
	// close if you click outside the starbar while in the iframe
	jQuery(document).click(function(e) {
		var navActive = jQuery('#sayso-starbar #nav_active');
		var navActiveLink = navActive.children('a');
		var navActiveSpan = navActiveLink.children('span.navBorder');
		navActiveSpan.css('backgroundColor','');
		popBoxClose();
		initFrame();
	});
	
	// close if you click outside the starbar on the main wondow
//	jQuery(parent.document).click(function(e) {
//		var navActive = jQuery('#nav_active');
//		var navActiveLink = navActive.children('a');
//		var navActiveSpan = navActiveLink.children('span.navBorder');
//		navActiveSpan.css('backgroundColor','');
//  	popBoxClose();
//		initFrame();
//	});
	
	jQuery('#sayso-starbar #player-console').click(function(e) {
	    e.stopPropagation();
	});
	
    
    function initStarBar(){	
    	if (jQuery.cookies.get('starBarStatus') == null){
    		jQuery.cookies.set('starBarStatus','visOpen');
    	}	
    	
    	// set the open / close state of the bar
    	animateBar(jQuery.cookies.get('starBarStatus'),'init');
    	
    	return false;
    }
    
    /* WHENEVER CALLED, FIGURES OUT IF THE STARBAR HAS VISIBLE POPUPS AND RESIZES THE PARENT FRAME TO SUIT */
    function initFrame(){	
    	var height = 60;
    	var width = '100%';
    	
    	if (starBarStatusHeight == 'closed'){
    		height = 60;
    	}else{
    		height = 530;
    	}	
    		
    	switch (starBarStatusWidth){
    		case 'visOpen':
    			width = '100%';
    		break;
    		case 'visClosed':
    			width = 110;
    		break;
    		case 'visStowed':
    			width = 65;
    		break;
    	}
    	
    	//alert(jQuery('#player-console').css('width'));
    		
    	//jQuery(parent.document.getElementById('saysoStarBarFrame')).attr('height', height);
    	//jQuery(parent.document.getElementById('saysoStarBarFrame')).delay(500).attr('width', width);	
    	return false;
    }

    /*
    POPBOX
    - see if there's an active box, if it is, close it, remove the active class
    - if activeBox and popBox are the same, break out of the function
    - if no activeBox, fadeIn popbox
    - make sure the nav span gets the class 'setColorActive'
    */
    
    function popBox(clickedLink){	
    	var itemLink = clickedLink;
    	var itemList = clickedLink.parent();
    	var itemID = itemList.children('.popBoxContent').attr('id');
    		
    	var currentPopID = jQuery('#sayso-starbar .popBox').attr('id');
    	var currentNavActive = jQuery('#sayso-starbar #nav_active');
    			
    	//if the link's parent <li> clicked already had an ID, it was the active one we should just close a box and quit
    	if (itemList.attr('id').length != 0){
    		popBoxClose();
    		initFrame();
    		return false;
    	}else{
    		// weird hack to manually delete the background "on" color if a different nav item was clicked
    		currentNavActive.children().children('span.navBorder').css('backgroundColor','');
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
    	var itemID = itemList.children('.popBoxContent').attr('id');
    	var itemInner = itemList.children('.popBoxContent').html();
    	var itemLink = itemList.children('a.navLink');
    	var itemLinkSpan = itemLink.children('span.navBorder');
    	var popBox = jQuery('#sayso-starbar .popBox');
    	var popBoxInner = jQuery('#sayso-starbar .popBox .popInner');
    			
    	popBoxInner.html(itemInner);
    	itemList.attr('id','nav_active');
    	itemLinkSpan.addClass('setColorActive');
    	//itemLinkSpan.css('backgroundColor',themeColor);
    	
    	//itemLinkSpan.css('backgroundColor',themeColor);
    	popBox.attr('id','popBox'+itemID).show();
    	
    	starBarStatusHeight = 'open';
    	
    	return false;
    }
    
    function popBoxClose(){
    	/*
    	1. clears content from active popbox
    	2. clears "active" state on nav item
    	3. closes popbox
    	*/
    	
    	var popBoxOpened = jQuery('#sayso-starbar .popBox');
    	var navActive = jQuery('#sayso-starbar #nav_active');
    	var navActiveLink = navActive.children('a');
    	var navActiveSpan = navActiveLink.children('span.navBorder');
    		
    	// check to make sure the is opened
    	if (popBoxOpened.attr('id').length != 0){
    		popBoxOpened.children('.popInner').html('');
    		popBoxOpened.attr('id','').hide();
    		navActiveSpan.removeClass('setColorActive');
    		navActive.attr('id','');
    	}	
    	
    	starBarStatusH = 'closed';
    		
    	return false;
    }


    // receives a single hex color and sets the button rollover color and starbox colors
    function setThemeColors(newColor){
		if (newColor == ''){
			newColor = "#666666";
		}
		
		// if it's just a flat color that needs changing...
		jQuery('#sayso-starbar .setColor').css('backgroundColor', newColor);
		jQuery('#sayso-starbar .setColorActive').css('backgroundColor',newColor);
		jQuery('#sayso-starbar .setTextColor').css('color', newColor);
		
		// now we're going to set up specialized cases for hovers
		jQuery('#sayso-starbar .setColorHover').parent().hover(
			function(){
				jQuery(this).children('.setColorHover').css('backgroundColor', newColor);			
			},
			function(){
				if (jQuery(this).children().hasClass('setColorActive')){
					return false;
				}else{
					jQuery(this).children('.setColorHover').css('backgroundColor','');
				}
			});
		
		// set up hover colors for regular links
		jQuery('#sayso-starbar a.setLinkHover').hover(
			function(){
				jQuery(this).css('color', newColor);			
				jQuery(this).css('backgroundColor','none');
			},
			function(){				
				jQuery(this).css('color', '');		
				jQuery(this).css('backgroundColor','none');
			});
		
		//set up the hover shadows
		jQuery('#sayso-starbar .setColorShadow').parent().hover(
			function(){
				jQuery(this).children('.setColorShadow').css({boxShadow: '0 0 5px'+newColor});
				jQuery(this).children('.setColorShadow').css({'-moz-boxShadow': '0 0 5px'+newColor});
				jQuery(this).children('.setColorShadow').css({'-webkit-boxShadow': '0 0 5px'+newColor});
			},
			function(){
				jQuery(this).children('.setColorShadow').css({boxShadow: '0px 0px 0px'+newColor});
				jQuery(this).children('.setColorShadow').css({'-moz-boxShadow': '0 0 0px'+newColor});
				jQuery(this).children('.setColorShadow').css({'-webkit-boxShadow': '0 0 0px'+newColor});
			});
		
		
		return false;
	}

    // changes the theme of the starbar 
    function changeTheme(newTheme){
    	var themeParent = newTheme.closest('.columnContent');
    	var newImg = themeParent.children('img');
    	var newTitle = themeParent.children().children('h4').html();
    	var newCount = themeParent.children().children('h5').html();
    	var newColor = newTheme.attr('href');
    	
    	jQuery('#sayso-starbar .starbar .content img').attr('src',newImg.attr('src'));
    	jQuery('#sayso-starbar .starbar .content h3').html(newTitle);
    	jQuery('#sayso-starbar .starbar .content h5').html(newCount);
    	jQuery('#sayso-starbar .starbar .content h5 span').html('');
    	setThemeColors(newColor);
    	return false;
    }
    
    // animates the player-console bar based on current state
	function animateBar(playerClass, clickPoint){
		// if we're clicking from a button, determine what state we're in and how to shrink
		if (clickPoint == 'button'){
			switch (playerClass){
				case 'visOpen':
					jQuery('#sayso-starbar #mainContent').fadeOut('fast');
					jQuery('#sayso-starbar #toggleVis').attr('class','');
					jQuery('#sayso-starbar #toggleVis').addClass('close');
					jQuery('#sayso-starbar #player-console').animate({
							width: '90'
						}, 500, function() {
							// Animation complete.
							jQuery(this).attr('class','').addClass('visClosed');
							jQuery('#sayso-starbar #logoBorder').show();
							cookieUpdate();
						});
					starBarStatusHeight = 'closed';
					starBarStatusWidth = 'visClosed';
				break;
				case 'visClosed':
					jQuery('#sayso-starbar #toggleVis').attr('class','');
					jQuery('#sayso-starbar #toggleVis').addClass('closed');
					//jQuery('#player-console').addClass('visStowed');
					jQuery('#sayso-starbar #logoBorder').hide();
					jQuery('#sayso-starbar #player-console').animate({
							width: '45'
						}, 500, function() {
							// Animation complete.
							jQuery(this).attr('class','').addClass('visStowed');
							cookieUpdate();
					});
					starBarStatusHeight = 'closed';
					starBarStatusWidth = 'visStowed';
				break;
				case 'visStowed':
					jQuery('#sayso-starbar #toggleVis').attr('class','');
					jQuery('#sayso-starbar #toggleVis').addClass('hide');
					jQuery('#sayso-starbar #logoBorder').hide();
					jQuery('#sayso-starbar #player-console').addClass('visOpen');
					jQuery('#sayso-starbar #player-console').animate({
							width: '100%'
						}, 500, function() {
							// Animation complete.
							jQuery(this).attr('class','').addClass('visOpen');
							jQuery('#mainContent').fadeIn('fast');			
							cookieUpdate();
					});
					starBarStatusHeight = 'open';
					starBarStatusWidth = 'visOpen';
				break;
			}	// END SWITCH
						
		}// end if clickPoint = button
		else if (clickPoint == 'init'){
			switch (playerClass){
				case 'visOpen':				
					jQuery('#sayso-starbar #player-console').hide();
					jQuery('#sayso-starbar #toggleVis').attr('class','');
					jQuery('#sayso-starbar #toggleVis').addClass('hide');
					jQuery('#sayso-starbar #logoBorder').hide();
					jQuery('#sayso-starbar #player-console').attr('class','').addClass('visOpen').show();
					jQuery('#sayso-starbar #mainContent').fadeIn('fast');
					starBarStatusWidth = 'visOpen';
				break;
				case 'visClosed':					
					jQuery('#sayso-starbar #player-console').hide();
					jQuery('#sayso-starbar #mainContent').fadeOut('fast');
					jQuery('#sayso-starbar #toggleVis').attr('class','');
					jQuery('#sayso-starbar #toggleVis').addClass('close');
					jQuery('#sayso-starbar #player-console').attr('class','').addClass('visClosed').show();
					jQuery('#sayso-starbar #logoBorder').show();
					starBarStatusHeight = 'closed';
					starBarStatusWidth = 'visClosed';
				break;
				case 'visStowed':					
					jQuery('#sayso-starbar #player-console').hide();
					jQuery('#sayso-starbar #toggleVis').attr('class','');
					jQuery('#sayso-starbar #toggleVis').addClass('closed');
					jQuery('#sayso-starbar #logoBorder').hide();
					jQuery('#sayso-starbar #player-console').attr('class','').addClass('visStowed').show();
					starBarStatusHeight = 'closed';
					starBarStatusWidth = 'visStowed';
				break;
			}	// END SWITCH
			
		}
		else{
			// if we clicked the logo, always go into full view if we aren't already there
			jQuery('#sayso-starbar #toggleVis').attr('class','');
			jQuery('#sayso-starbar #toggleVis').addClass('hide');
			jQuery('#sayso-starbar #logoBorder').hide();
			jQuery('#sayso-starbar #player-console').attr('class','');
			jQuery('#sayso-starbar #player-console').addClass('visOpen');
			jQuery('#sayso-starbar #player-console').animate({
					width: '100%'
				}, 500, function() {
					// Animation complete.
					jQuery(this).attr('class','');
					jQuery(this).addClass('visOpen');
					jQuery('#mainContent').fadeIn('fast');
					cookieUpdate();
				});
			starBarStatusHeight = 'closed';
			starBarStatusWidth = 'visOpen';
		} // end else
		
		return false;
	} // end animateBar
	
	/* refresh the scrollbars */
	
	function refreshScroll(){
		// set up scrollbars for the popBoxes
		if (jQuery('#sayso-starbar .popBox .column_left .scrollbar').length != 0){
			var ScrollbarL = jQuery('.popBox .column_left');
			ScrollbarL.tinyscrollbar();	
			ScrollbarL.tinyscrollbar_update();
		}
		if (jQuery('#sayso-starbar .popBox .column_right .scrollbar').length != 0){
			var ScrollbarR = jQuery('.popBox .column_right');
			ScrollbarR.tinyscrollbar();	
			ScrollbarR.tinyscrollbar_update();
		}		
		if (jQuery('#sayso-starbar .popBox .column_single .scrollbar').length != 0){
			var ScrollbarS = jQuery('.popBox .column_single');
			ScrollbarS.tinyscrollbar();	
			ScrollbarS.tinyscrollbar_update();
		}		
		return false;
	}
	
	// reset the dialog behaviors / set them up
	function refreshDialog(){
		// set up popDialogs
		jQuery('#sayso-starbar a.popDialog').unbind('click').click(function(event){
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
			
		jQuery('#sayso-starbar a.closePop').click(function(event){
			event.preventDefault();
			popBoxClose();				
		});
		
		jQuery('#sayso-starbar a.changeTheme').click(function(event){
			event.preventDefault();
			changeTheme(jQuery(this));				
		});
		return false;
	}
	
	// set up toggleNav behavior
	function toggleNav(){
		// initialize the first item
		jQuery('#sayso-starbar .toggleElement').hide();
		jQuery('#sayso-starbar .toggleNav li:nth-child(2)').children('a.toggleLink').addClass('active');
		jQuery('#sayso-starbar .toggleElement').first().show();
		
		
		jQuery('#sayso-starbar .toggleNav a.toggleLink').click(function(){
			var thisToggleID = jQuery(this).parent().attr('class');
				jQuery('#sayso-starbar .toggleNav a.toggleLink').each(function(){
					jQuery(this).removeClass('active');
				});
				jQuery(this).addClass('active');
				jQuery('#sayso-starbar .toggleElement').hide();
				jQuery('#sayso-starbar #'+thisToggleID).show();																				 
		});
	}
	
	function cookieUpdate(){		
		jQuery.cookies.set('starBarStatus', jQuery('#sayso-starbar #player-console').attr('class'));
	}
	
	function log (message) {
	    if (console && console.log) {
	        console.log('message');
	    }
	}
	
}, 500); // slight delay to ensure other libraries are loaded
