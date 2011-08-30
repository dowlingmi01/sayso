/**
 * Starbar
 */
          
// load after slight delay
setTimeout(function(){
		
    var kynetxAppId = 'a239x14';
    
	// global var
    var themeColor = '#de40b2';
	
	
	/*
	 Set up handlers for expanding / minimizing the starbar when "hide" or logo is clicked
	*/
	
	$S('#sayso-starbar #starbar-visControls #starbar-toggleVis').click(function(event){
		event.preventDefault();																																
		var playerClass = $S('#starbar-player-console').attr('class');
		animateBar(playerClass, 'button');
		//popBoxClose();																														
	});
	
	
	
	
	// animates the starbar-player-console bar based on current state
	function animateBar(playerClass, clickPoint){		
		switch(clickPoint){
			// if we're clicking from a button, determine what state we're in and how to shrink / grow
			case 'button':
				switch (playerClass){
					case 'starbar-visOpen':
						$S('#sayso-starbar #starbar-main').fadeOut('fast');
						$S('#sayso-starbar #starbar-toggleVis').attr('class','');
						$S('#sayso-starbar #starbar-toggleVis').addClass('btnStarbar-closed');
						$S('#sayso-starbar #starbar-player-console').animate({
								width: '120'
							}, 500, function() {
								// Animation complete.
								$S(this).attr('class','').addClass('starbar-visClosed');
								$S('#sayso-starbar #starbar-logoBorder').show();
								//updateState();
							});
						//starBarStatusHeight = 'starbar-closed';
						//starBarStatusWidth = 'starbar-visClosed';
					break;
					case 'starbar-visClosed':
						$S('#sayso-starbar #starbar-toggleVis').attr('class','');
						$S('#sayso-starbar #starbar-toggleVis').addClass('btnStarbar-stowed');
						//$S('#starbar-player-console').addClass('starbar-visStowed');
						$S('#sayso-starbar #starbar-logoBorder').hide();
						$S('#sayso-starbar #starbar-player-console').animate({
								width: '45'
							}, 500, function() {
								// Animation complete.
								$S(this).attr('class','').addClass('starbar-visStowed');
								updateState();
						});
						starBarStatusHeight = 'btnStarbar-closed';
						starBarStatusWidth = 'starbar-visStowed';
					break;
					case 'starbar-visStowed':
						$S('#sayso-starbar #starbar-toggleVis').attr('class','');
						$S('#sayso-starbar #starbar-toggleVis').addClass('btnStarbar-hide');
						$S('#sayso-starbar #starbar-logoBorder').hide();
						$S('#sayso-starbar #starbar-player-console').addClass('starbar-visOpen');
						$S('#sayso-starbar #starbar-player-console').animate({
								width: '100%'
							}, 500, function() {
								// Animation complete.
								$S(this).attr('class','').addClass('starbar-visOpen');
								$S('#starbar-main').fadeIn('fast');			
								updateState();
						});
						starBarStatusHeight = 'btnStarbar-open';
						starBarStatusWidth = 'starbar-visOpen';
					break;
				}	// END SWITCH
				
			break; // end if clickPoint = button
						
		}
		
		return false;
	} // end animateBar
	
	
	
}, 200); // slight delay to ensure other libraries are loaded
