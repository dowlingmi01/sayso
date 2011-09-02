/**
 * Starbar
 */
          
// load after slight delay
setTimeout(function(){
		
		
    var kynetxAppId = 'a239x14';
    
	// global var
    var themeColor = '#de40b2';
	
	// LETS USE VARS! 
	
	// clickable elements that ppl will interact with
	var btnToggleVis = $S('#sayso-starbar #starbar-visControls #starbar-toggleVis');
	var btnSaySoLogo = $S('#sayso-starbar #starbar-visControls #starbar-logo');
	
	// container elements
	var elemSaySoLogoBorder = $S('#sayso-starbar #starbar-player-console #starbar-logoBorder');
	var elemPlayerConsole = $S('#sayso-starbar #starbar-player-console');
	var elemStarbarMain = $S('#sayso-starbar #starbar-player-console #starbar-main');
	var elemVisControls = $S('#sayso-starbar #starbar-player-console #starbar-visControls');
	var elemStarbarClickable = $S('#sayso-starbar #starbar-player-console .nav_element');
	var elemPopBox = $S('#sayso-starbar #starbar-player-console .popBox');
	var elemAlerts = $S('#sayso-starbar #starbar-player-console .starbar-alert');
	
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
		click: function(event) {
			event.preventDefault();
			var playerClass = elemPlayerConsole.attr('class');
			if (playerClass != 'starbar-visOpen'){
				// manual override to have any click re-open starbar to original state
				animateBar('starbar-visStowed', 'button');
			}
		},
		mouseenter: function() {
			if (elemPlayerConsole.hasClass('starbar-visOpen')){
				// if it's open
			}
			else{
				elemSaySoLogoBorder.addClass('theme_bgGradient theme_bgGlow').show();
			}
		},
		mouseleave: function(){
			if (elemPlayerConsole.hasClass('starbar-visOpen')){
				// if it's open
			}
			else{
				elemSaySoLogoBorder.removeClass('theme_bgGradient theme_bgGlow');
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
				// the popbox is AFTER the clickable area
				var thisPopBox = $S(this).next('.popBox');
			
				// set up a handler in case we click an element that isn't directly next to its target popbox. it will have 'target_popBoxID' as a class
				var targetPopBox = $S(this).attr('class');
				if (targetPopBox.indexOf('target_') > 0){
					targetPopBox = targetPopBox.replace('nav_element','');
					targetPopBox = targetPopBox.replace('target_','');
					targetPopBox = targetPopBox.replace(' ','');
					// reset the popbox it should open to this ID
					thisPopBox = $S('#'+targetPopBox);
					// set a delay before closing the alert element
					hideAlerts($S(this).closest('.starbar-alert'));
				}
				
				// if it was already open, close it and remove the class. otherwise, open the popbox
				if (thisPopBox.hasClass('popBoxActive')){
					closePopBox(thisPopBox);
				}else{
					// this menu item's popBox is active
					closePopBox(thisPopBox);
					openPopBox(thisPopBox);
					// if we're a regular nav item
					if ($S(this).parent().hasClass('theme_bgGradient')){
						$S('span', this).addClass('theme_navOnGradient');
					}
				}
			},
			mouseenter: function(event){
			event.preventDefault();
				if ($S(this).parent().hasClass('theme_bgGradient')){
					$S('span', this).addClass('theme_navOnGradient');
				}
			},
			mouseleave: function(event){
			event.preventDefault();				
			
				var thisPopBox = $S(this).next('.popBox');
				// only remove the "hover" class for the nav item if it's box isn't active
				if (($S(this).parent().hasClass('theme_bgGradient')) && (!thisPopBox.hasClass('popBoxActive'))){
					$S('span', this).removeClass('theme_navOnGradient');
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
	}
	
	// animates the starbar-player-console bar based on current state
	function animateBar(playerClass, clickPoint){		
		switch(clickPoint){
			// if we're clicking from a button, determine what state we're in and how to shrink / grow
			case 'button':
				switch (playerClass){
					case 'starbar-visOpen':
						elemStarbarMain.fadeOut('fast');
						btnToggleVis.attr('class','');
						btnToggleVis.addClass('btnStarbar-closed');
						btnSaySoLogo.css('backgroundPosition','3px 0px');
						elemPlayerConsole.animate({
								width: '100'
							}, 500, function() {
								// Animation complete.
								$S(this).attr('class','').addClass('starbar-visClosed');
								elemSaySoLogoBorder.show();
								hideAlerts();
							});
					break;
					case 'starbar-visClosed':
						btnToggleVis.attr('class','');
						btnToggleVis.addClass('btnStarbar-stowed');
						btnSaySoLogo.css('backgroundPosition','');
						btnSaySoLogo.css('width','28px');
						hideAlerts();
						elemPlayerConsole.animate({
								width: '45'
							}, 500, function() {
								// Animation complete.
								$S(this).attr('class','').addClass('starbar-visStowed');
								btnSaySoLogo.css('width','');
						});
					break;
					case 'starbar-visStowed':
						btnToggleVis.attr('class','');
						elemSaySoLogoBorder.hide();
						elemVisControls.hide();
						btnSaySoLogo.css('backgroundPosition','');
						hideAlerts();
						elemPlayerConsole.animate({
								width: '100%'
							}, 500, function() {
								// Animation complete.
								$S(this).attr('class','').addClass('starbar-visOpen');
								elemStarbarMain.fadeIn('fast');
								elemVisControls.fadeIn('fast');
								btnToggleVis.addClass('btnStarbar-open');		
								showAlerts();
						});
					break;
				}	// END SWITCH				
			break; // end if clickPoint = button	
			
		} // end switch clickpoint
		
		return false;
	
	} // end FUNCTION ANIMATEBAR
	
	function closePopBox(exception){
		elemPopBox.each(function(){
			$S(this).removeClass('popBoxActive');
			$S(this).hide();
		});
		elemStarbarClickable.each(function(){ 
			// remove hover class from all nav items
			$S('span.nav_border', this).removeClass('theme_navOnGradient');
		});
		return;
	}
	
	function openPopBox(elem){
		closePopBox();
		var popBox = elem;
		popBox.show();
		popBox.addClass('popBoxActive');
		activateTabs(popBox);
		activateAccordion(popBox);
		activateScroll(popBox);
		return;
	}
	
	function showAlerts(target){
		if (target){
			target.delay(200).slideDown('fast');				
		}else{
			elemAlerts.each(function(){
				// show alerts that aren't empty.
				if ($S('span',this).html().length != 0){
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
		if ($S('.tabs',target).length > 0){
			$S('.tabs',target).tabs();
		}		
	}
	
	function activateScroll(target){
		// first, resize the scrollpane dynamically to fit whatever height it lives in (.content.height() - .header.height())
		var contentHeight = $S('.content',target).height();
		// add height of the header + any margins / paddings	
		if ($S('.content .header',target).length > 0){
			var headerHeight =  eval($S('.header',target).css('margin-bottom').replace('px',''))+$S('.content .header',target).height();		
		}else{
			var headerHeight = 0;		}
		
		$S('.scrollPane',target).css('height',contentHeight-headerHeight);		
		$S('.scrollPane',target).jScrollPane({
			autoReinitialise: true,
			autoReinitialiseDelay: 100
		});
	}
	
	function activateAccordion(target){
		$S(".accordion", target).accordion({
			collapsible: true																	 
		});
	}
		
}, 200); // slight delay to ensure other libraries are loaded
