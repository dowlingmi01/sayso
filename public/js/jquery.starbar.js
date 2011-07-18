// JavaScript Document

$(document).ready(function(){
													 
	// global var
	themeColor = '#de40b2';
	
	//easy trigger var for whether an element is open or closed.
	starBarStatusHeight = 'closed';
	starBarStatusWidth = 'visOpen';
	
	//once we've got the cookie 
	initStarBar();
	
	//resize the frame if we've just loaded the page
	initFrame();
	
	//set up all links inside popBox to have hoverColor
	$('.popBox a').addClass('setLinkHover');
													 
	setThemeColors(themeColor);
	
	// set up the full / shrunk / closed states for the bar
	$('#toggleVis').click(function(event){
		event.preventDefault();
		var playerClass = $('#player-console').attr('class');
		animateBar(playerClass, 'button');
		popBoxClose();
		initFrame();
	});
	$('#logo').click(function(event){
		var playerClass = $('#player-console').attr('class');
		if (playerClass != 'visOpen'){
			// only run if we're not at full visibility
			animateBar(playerClass, 'logo');
			popBoxClose();
			initFrame();
		}else{
			event.preventDefault();
			popBox($(this));
			refreshScroll();
			refreshDialog();
		}
		return false;
	});
	
	//set up click behavior for menu + popBox reveals
	$('.navLink').click(function(event){																
		event.preventDefault();
		popBox($(this));
		refreshScroll();
		refreshDialog();
		toggleNav();
		
		// set up the accordion
		$('.accordion').accordion({
			active: false,
			collapsible: true,
			fillspace: true,
			header: 'h4'
			//icons: { 'header': 'ui-icon-triangle-1-e', 'headerSelected': 'ui-icon-triangle-1-s' }
		});
		
		
		
	});	// end navLink click

	
	// close popBox if "escape" pressed
	$(document).keyup(function(e) {
		if (e.keyCode == 27) {
			var currentNavActive = $('#nav li#nav_active');
			currentNavActive.children().children('span.navBorder').css('backgroundColor','');
			popBoxClose();
			initFrame();
		}  // esc
	});
	
	// set up the dialog
	$('#popBoxDialog').dialog({
		autoOpen: false,
		modal: true,
		closeOnEscape: true,
		maxHeight: 400,
		width: 610
	});
	
	// close if you click outside the starbar while in the iframe
	$(document).click(function(e) {
		var navActive = $('#nav_active');
		var navActiveLink = navActive.children('a');
		var navActiveSpan = navActiveLink.children('span.navBorder');
		navActiveSpan.css('backgroundColor','');
  	popBoxClose();
		initFrame();
	});
	
	// close if you click outside the starbar on the main wondow
	$(parent.document).click(function(e) {
		var navActive = $('#nav_active');
		var navActiveLink = navActive.children('a');
		var navActiveSpan = navActiveLink.children('span.navBorder');
		navActiveSpan.css('backgroundColor','');
  	popBoxClose();
		initFrame();
	});
	
	$('#player-console').click(function(e) {
  	e.stopPropagation();
	});
	
});

function initStarBar(){	
	if ($.cookies.get('starBarStatus') == null){
		$.cookies.set('starBarStatus','visOpen');
	}	
	
	// set the open / close state of the bar
	animateBar($.cookies.get('starBarStatus'),'init');
	
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
	
	//alert($('#player-console').css('width'));
		
	$(parent.document.getElementById('saysoStarBarFrame')).attr('height', height);
	$(parent.document.getElementById('saysoStarBarFrame')).delay(500).attr('width', width);	
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
		
	var currentPopID = $('.popBox').attr('id');
	var currentNavActive = $('#nav_active');
			
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
	var popBox = $('.popBox');
	var popBoxInner = $('.popBox .popInner');
			
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
	
	var popBoxOpened = $('.popBox');
	var navActive = $('#nav_active');
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
		$('.setColor').css('backgroundColor', newColor);
		$('.setColorActive').css('backgroundColor',newColor);
		$('.setTextColor').css('color', newColor);
		
		// now we're going to set up specialized cases for hovers
		$('.setColorHover').parent().hover(
			function(){
				$(this).children('.setColorHover').css('backgroundColor', newColor);			
			},
			function(){
				if ($(this).children().hasClass('setColorActive')){
					return false;
				}else{
					$(this).children('.setColorHover').css('backgroundColor','');
				}
			});
		
		// set up hover colors for regular links
		$('a.setLinkHover').hover(
			function(){
				$(this).css('color', newColor);			
				$(this).css('backgroundColor','none');
			},
			function(){				
				$(this).css('color', '');		
				$(this).css('backgroundColor','none');
			});
		
		//set up the hover shadows
		$('.setColorShadow').parent().hover(
			function(){
				$(this).children('.setColorShadow').css({boxShadow: '0 0 5px'+newColor});
				$(this).children('.setColorShadow').css({'-moz-boxShadow': '0 0 5px'+newColor});
				$(this).children('.setColorShadow').css({'-webkit-boxShadow': '0 0 5px'+newColor});
			},
			function(){
				$(this).children('.setColorShadow').css({boxShadow: '0px 0px 0px'+newColor});
				$(this).children('.setColorShadow').css({'-moz-boxShadow': '0 0 0px'+newColor});
				$(this).children('.setColorShadow').css({'-webkit-boxShadow': '0 0 0px'+newColor});
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
	
	$('.starbar .content img').attr('src',newImg.attr('src'));
	$('.starbar .content h3').html(newTitle);
	$('.starbar .content h5').html(newCount);
	$('.starbar .content h5 span').html('');
	setThemeColors(newColor);
	return false;
}

// animates the player-console bar based on current state
	function animateBar(playerClass, clickPoint){
		// if we're clicking from a button, determine what state we're in and how to shrink
		if (clickPoint == 'button'){
			switch (playerClass){
				case 'visOpen':
					$('#mainContent').fadeOut('fast');
					$('#toggleVis').attr('class','');
					$('#toggleVis').addClass('close');
					$('#player-console').animate({
							width: '90'
						}, 500, function() {
							// Animation complete.
							$(this).attr('class','').addClass('visClosed');
							$('#logoBorder').show();
							cookieUpdate();
						});
					starBarStatusHeight = 'closed';
					starBarStatusWidth = 'visClosed';
				break;
				case 'visClosed':
					$('#toggleVis').attr('class','');
					$('#toggleVis').addClass('closed');
					//$('#player-console').addClass('visStowed');
					$('#logoBorder').hide();
					$('#player-console').animate({
							width: '45'
						}, 500, function() {
							// Animation complete.
							$(this).attr('class','').addClass('visStowed');
							cookieUpdate();
					});
					starBarStatusHeight = 'closed';
					starBarStatusWidth = 'visStowed';
				break;
				case 'visStowed':
					$('#toggleVis').attr('class','');
					$('#toggleVis').addClass('hide');
					$('#logoBorder').hide();
					$('#player-console').addClass('visOpen');
					$('#player-console').animate({
							width: '100%'
						}, 500, function() {
							// Animation complete.
							$(this).attr('class','').addClass('visOpen');
							$('#mainContent').fadeIn('fast');			
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
					$('#player-console').hide();
					$('#toggleVis').attr('class','');
					$('#toggleVis').addClass('hide');
					$('#logoBorder').hide();
					$('#player-console').attr('class','').addClass('visOpen').show();
					$('#mainContent').fadeIn('fast');
					starBarStatusWidth = 'visOpen';
				break;
				case 'visClosed':					
					$('#player-console').hide();
					$('#mainContent').fadeOut('fast');
					$('#toggleVis').attr('class','');
					$('#toggleVis').addClass('close');
					$('#player-console').attr('class','').addClass('visClosed').show();
					$('#logoBorder').show();
					starBarStatusHeight = 'closed';
					starBarStatusWidth = 'visClosed';
				break;
				case 'visStowed':					
					$('#player-console').hide();
					$('#toggleVis').attr('class','');
					$('#toggleVis').addClass('closed');
					$('#logoBorder').hide();
					$('#player-console').attr('class','').addClass('visStowed').show();
					starBarStatusHeight = 'closed';
					starBarStatusWidth = 'visStowed';
				break;
			}	// END SWITCH
			
		}
		else{
			// if we clicked the logo, always go into full view if we aren't already there
			$('#toggleVis').attr('class','');
			$('#toggleVis').addClass('hide');
			$('#logoBorder').hide();
			$('#player-console').attr('class','');
			$('#player-console').addClass('visOpen');
			$('#player-console').animate({
					width: '100%'
				}, 500, function() {
					// Animation complete.
					$(this).attr('class','');
					$(this).addClass('visOpen');
					$('#mainContent').fadeIn('fast');
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
		if ($('.popBox .column_left .scrollbar').length != 0){
			var ScrollbarL = $('.popBox .column_left');
			ScrollbarL.tinyscrollbar();	
			ScrollbarL.tinyscrollbar_update();
		}
		if ($('.popBox .column_right .scrollbar').length != 0){
			var ScrollbarR = $('.popBox .column_right');
			ScrollbarR.tinyscrollbar();	
			ScrollbarR.tinyscrollbar_update();
		}		
		if ($('.popBox .column_single .scrollbar').length != 0){
			var ScrollbarS = $('.popBox .column_single');
			ScrollbarS.tinyscrollbar();	
			ScrollbarS.tinyscrollbar_update();
		}		
		return false;
	}
	
	// reset the dialog behaviors / set them up
	function refreshDialog(){
		// set up popDialogs
		$('a.popDialog').click(function(event){
			event.preventDefault();
			//var popDialogInner = $(this).closest('.overview').children('.popDialogInner').html();
			var popDialogSrc = $(this).attr('href');
			$('#popBoxDialog iframe').attr('src',popDialogSrc);
			$('#popBoxDialog').dialog('open');							
		});
			
		$('a.closePop').click(function(event){
			event.preventDefault();
			popBoxClose();				
		});
		
		$('a.changeTheme').click(function(event){
			event.preventDefault();
			changeTheme($(this));				
		});
		return false;
	}
	
	// set up toggleNav behavior
	function toggleNav(){
		// initialize the first item
		$('.toggleElement').hide();
		$('.toggleNav li:nth-child(2)').children('a.toggleLink').addClass('active');
		$('.toggleElement').first().show();
		
		
		$('.toggleNav a.toggleLink').click(function(){
			var thisToggleID = $(this).parent().attr('class');
				$('.toggleNav a.toggleLink').each(function(){
					$(this).removeClass('active');
				});
				$(this).addClass('active');
				$('.toggleElement').hide();
				$('#'+thisToggleID).show();																				 
		});
	}
	
	function cookieUpdate(){		
		$.cookies.set('starBarStatus', $('#player-console').attr('class'));
	}
	
