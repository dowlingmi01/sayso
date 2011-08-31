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
	var elemStarbarNav = $S('#sayso-starbar #starbar-player-console #starbar-nav ul li');
	var elemPopBox = $S('#sayso-starbar #starbar-player-console .popBox');
	
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
	elemStarbarNav.each(function(){
		$S(this).bind({
			click: function(event){
			event.preventDefault();
				// if it was already open, close it and remove the class. otherwise, open the popbox
				if ($S('.popBox',this).hasClass('popBoxActive')){
					closePopBox($S('.popBox',this));
				}else{
					// this menu item's popBox is active
					closePopBox($S('.popBox',this));
					openPopBox($S('.popBox',this));
					$S('span', this).addClass('theme_navOnGradient');
				}
			},
			mouseenter: function(event){
			event.preventDefault();
				$S('span', this).addClass('theme_navOnGradient');
			},
			mouseleave: function(event){
			event.preventDefault();				
				// only remove the "hover" class for the nav item if it's box isn't active
				if (!$S('.popBox',this).hasClass('popBoxActive')){
					$S('span.nav_border', this).removeClass('theme_navOnGradient');
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
								//updateState();
							});
						//starBarStatusHeight = 'starbar-closed';
						//starBarStatusWidth = 'starbar-visClosed';
					break;
					case 'starbar-visClosed':
						btnToggleVis.attr('class','');
						btnToggleVis.addClass('btnStarbar-stowed');
						btnSaySoLogo.css('backgroundPosition','');
						btnSaySoLogo.css('width','28px');
						elemPlayerConsole.animate({
								width: '45'
							}, 500, function() {
								// Animation complete.
								$S(this).attr('class','').addClass('starbar-visStowed');
								btnSaySoLogo.css('width','');
								//updateState();
						});
						//starBarStatusHeight = 'btnStarbar-closed';
						//starBarStatusWidth = 'starbar-visStowed';
					break;
					case 'starbar-visStowed':
						btnToggleVis.attr('class','');
						elemSaySoLogoBorder.hide();
						elemVisControls.hide();
						btnSaySoLogo.css('backgroundPosition','');
						elemPlayerConsole.animate({
								width: '100%'
							}, 500, function() {
								// Animation complete.
								$S(this).attr('class','').addClass('starbar-visOpen');
								elemStarbarMain.fadeIn('fast');
								elemVisControls.fadeIn('fast');
								btnToggleVis.addClass('btnStarbar-open');
								//updateState();
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
		elemStarbarNav.each(function(){ 
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
		return;
	}
	
}, 200); // slight delay to ensure other libraries are loaded
