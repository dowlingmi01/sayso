$SQ.ajaxWithAuth = function (options) {
    var starbar_id = null;
    var auth_key = null;
    var user_id = null;
    var user_key = null;

    sayso = window.sayso;
    
    // Authenticated?
    try
    {
        starbar_id = sayso.starbar.id;
        user_id = sayso.starbar.user.id;
        user_key = sayso.starbar.user.key;
        auth_key = sayso.starbar.authKey;
    }
    catch (e) {}
    
    if (typeof sayso == "undefined") {
    // setup global "safe" logging functions
    window.sayso.log = _log('log'); 
    window.sayso.warn = _log('warn');
    function _log (type) { // <-- closure here allows re-use for log() and warn()
        return function () {
            if (window.sayso.debug && typeof window.console !== 'undefined' && typeof window.console.log !== 'undefined') {
                var args = Array.prototype.slice.call(arguments);
                if (typeof console.log.apply === 'function') {
                    args.unshift('SaySo:');
                    window.console[type].apply(window.console, args);
                } else {
                    // must be IE
                    if (typeof args[0] !== 'object') {
                        window.console.log(args[0]);
                    }
                }
            }
        }
    };
    
    var sayso = window.sayso;
	}
    
    options.data = $SQ.extend(options.data || {}, {
        starbar_id : starbar_id,
        user_id : user_id,
        user_key : user_key,
        auth_key : auth_key
    });

    if (!options.dataType)
    	options.dataType = 'jsonp';

	options.beforeSend = function(x) {
		if (x && x.overrideMimeType) {
			x.overrideMimeType("application/j-son;charset=UTF-8");
		}
	};
    return $SQ.ajax(options);
};

(function($$SQ){
	$$SQ.fn.extend({
		totalHeight: function() {
			return (
				this.height() +
				eval(this.css('margin-top').replace('px','')) +
				eval(this.css('margin-bottom').replace('px','')) +
				eval(this.css('padding-top').replace('px','')) +
				eval(this.css('padding-bottom').replace('px',''))
			);
		}
	});

	$$SQ.fn.extend({
		percentWidth: function() {
			return Math.round(this.width() * 100 / this.parent().width());
		}
	});

	$$SQ.fn.extend({
		annihilate: function() {
			this.attr('id', 'sb_oldElement_'+$SQ.randomString(10));
			this.detach();
			this.empty();
		}
	});
})($SQ);

$SQ.frameCommunicationFunctions = {
	loadComplete: function (hideLoadingElem, newFrameHeight) {
		var openFrame = sayso.starbar.openFrame;
		var openFrameContainer = sayso.starbar.openFrameContainer;
		var openFrameContainerParent = sayso.starbar.openFrameContainer.parent();

		if (hideLoadingElem) {
			var loadingElement = openFrameContainer.children('.sayso-starbar-loading-external');
			loadingElement.fadeTo(200, 0);
			// Set display to none to avoid mouse click issues
			setTimeout(function() {
				// Note that setTimeout works in global scope
				sayso.starbar.openFrameContainer.children('.sayso-starbar-loading-external').css('display', 'none');
			}, 200);
		}
		
		if (newFrameHeight) {
			openFrame.height(newFrameHeight);
			openFrameContainerParent.css('height', newFrameHeight+5);

			// if the frame (and container and its parent) are in a scrollpane, re-initialize it and scroll if necessary
		    var scrollPane = openFrameContainerParent.parents('.sb_scrollPane');
		    if (scrollPane.length > 0) {
			    scrollPane.jScrollPane(); // re-initialize the scroll pane now that the content size may be different
			    if (openFrameContainerParent.position()) {  // if the accordion is open
					var paneHandle = scrollPane.data('jsp');

					var accordionHeader = openFrameContainerParent.prev('h3');
			        var currentScroll = paneHandle.getContentPositionY();
			        var topOfOpenAccordion = accordionHeader.position().top;
			        var bottomOfOpenAccordion = topOfOpenAccordion+accordionHeader.totalHeight()+openFrameContainerParent.totalHeight();
			        var sizeOfPane = scrollPane.height();

			        if ((bottomOfOpenAccordion - currentScroll) > (sizeOfPane - 10)) { // - 24 for the extra padding
			            paneHandle.scrollByY((bottomOfOpenAccordion - currentScroll) - (sizeOfPane - 10)); // scroll by the difference
					}
				}
			}
		}
	},
	updateGame: function (newProfile) {
		if (newProfile) $SQ.updateGame(newProfile, true, true);
		else $SQ.updateGame('ajax', true, true);
	},
	alertMessage: function (msg) {
		sayso.log(msg);
	}
};

$SQ.insertCommunicationIframe = function(link, container, width, height, scrolling) {
	// This function inserts the iframe (with x-domain communication enabled!)
	// The id of the container is placed inside the 'ref' attribute at the top of the accordion
	new easyXDM.Rpc({
		local: "http://"+sayso.baseDomain+"/html/communicator.html",
		swf: "http://"+sayso.baseDomain+"/swf/easyxdm.swf",
		remote: link,
		remoteHelper: "http://"+sayso.baseDomain+"/html/communicator.html",
        container: container,
        props: {
			scrolling: scrolling,
			style: {
				height: parseInt(height)+"px",
				width: parseInt(width)+"px",
				margin: 0,
				border: 0
			}
		}
	}, {
		// Local functions (i.e. remote procedure calls arrive here)
		local: $SQ.frameCommunicationFunctions
	});

	sayso.starbar.openFrameContainer = $SQ('#'+container);
	sayso.starbar.openFrame = sayso.starbar.openFrameContainer.children('iframe');
}

/*
* This function takes two parameters: integer value for string length and optional 
* boolean value true if you want to include special characters in your generated string.
* From: http://jquery-howto.blogspot.com/2009/10/javascript-jquery-password-generator.html
*/
$SQ.randomString = function (length, special) {
	var iteration = 0;
	var randomString = "";
	var randomNumber;
	if(special == undefined){
		var special = false;
	}
	while(iteration < length){
		randomNumber = (Math.floor((Math.random() * 100)) % 94) + 33;
		if(!special){
			if ((randomNumber >=33) && (randomNumber <=47)) { continue; }
			if ((randomNumber >=58) && (randomNumber <=64)) { continue; }
			if ((randomNumber >=91) && (randomNumber <=96)) { continue; }
			if ((randomNumber >=123) && (randomNumber <=126)) { continue; }
		}
		iteration++;
		randomString += String.fromCharCode(randomNumber);
	}
	return randomString;
}

$SQ.updateGame = function(loadSource, setGlobalUpdate, animate) {
	if (loadSource === "ajax") {
		$SQ.ajaxWithAuth({
			url : 'http://'+sayso.baseDomain+'/api/gaming/user-profile?renderer=jsonp',
			success : function (response, status, jqXHR) {
				window.sayso.starbar.user.gaming = response.data;
				$SQ.activateGameElements(null, animate);
    		}
		});
	} else if (loadSource === "cache") {
		$SQ.activateGameElements(null, animate);
	} else { // loadSource object is a gamer profile, load from there
		window.sayso.starbar.user.gaming = loadSource;
		$SQ.activateGameElements(null, animate);
	}


	if (setGlobalUpdate) {
		sayso.starbar.state.game = Math.round(new Date().getTime() / 1000);
		sayso.starbar.state.update();
	}
}

$SQ.activateGameElements = function(target, animate) {
	var levelIconsContainerElems = $SQ('.sb_user_level_icons_container', target);
	var currencyBalanceNextLevelElems = $SQ('.sb_currency_balance_next_level', target);
	var currencyBalanceElems = $SQ('.sb_currency_balance', target);
	var currencyPercentElems = $SQ('.sb_currency_percent', target);
	var progressBarElems = $SQ('.sb_progress_bar', target);
	var userLevelNumberElems = $SQ('.sb_user_level_number', target);
	var userLevelTitleElems = $SQ('.sb_user_level_title', target);

	var animationDuration = 1500; // milliseconds

	var allLevels = window.sayso.starbar._game._levels.collection;
	var userLevels = window.sayso.starbar.user.gaming._levels.collection;
	// The current level is the first level in the collection (it is sorted by the gaming API!)
	var userCurrentLevel = userLevels[0];
	var userNextLevel;
	var justLeveledUp = false;

	if (allLevels && userLevels) {
		$SQ.each(allLevels, function (index, level) {
			if (parseInt(userCurrentLevel.ordinal) < parseInt(level.ordinal) && (!userNextLevel || userNextLevel.ordinal > level.ordinal)) {
				userNextLevel = level;
			}
		});
	}
	
	if (!userNextLevel) { // There should always be a next level for the user, but just in case...
		userNextLevel = { ordinal : userCurrentLevel.ordinal + 50000 }
	}

	if (currencyBalanceNextLevelElems.length > 0) {
		currencyBalanceNextLevelElems.each(function() {
			$SQ(this).html(userNextLevel.ordinal);
		});
	}

	if (userLevelNumberElems.length > 0) {
		userLevelNumberElems.each(function() {
			var newLevel = ""+(userLevels.length - 1);
			if ($SQ(this).html() != newLevel) {
				$SQ(this).html(newLevel);
				justLeveledUp = true;
				if (animate) {
					$SQ(this).effect("pulsate", { times:3 }, parseInt(animationDuration/3));
				}
			}
		});
	}

	if (justLeveledUp) {
		if (userLevelTitleElems.length > 0) {
			userLevelTitleElems.each(function() {
				$SQ(this).html(userCurrentLevel.title);
				if (animate) {
					$SQ(this).effect("pulsate", { times:3 }, parseInt(animationDuration/3));
				}
			});
		}
	}

	if (levelIconsContainerElems.length > 0) {
		levelIconsContainerElems.each(function() {
			$SQ(this).html('');
			if (allLevels && userCurrentLevel) {
				$SQ.each(allLevels, function (index, level) {
					var smallImageUrl, bigImageUrl;
					$SQ.each(level.urls.collection, function (index, url) {
						if (url.url.indexOf('_b.png') != -1) bigImageUrl = url.url;
						if (url.url.indexOf('_sm.png') != -1) smallImageUrl = url.url;
					});

					var levelIcon = $SQ(document.createElement('div'));
					levelIcon.addClass('sb_userLevelIcons');
					if (level.ordinal == userCurrentLevel.ordinal) {
						levelIcon.addClass('sb_userLevel_current');
						levelIcon.html('<div class="sb_userLevelImg" style="background-image: url(\''+bigImageUrl+'\')"></div><p><strong class="sb_theme_textHighlight">'+level.title+'</strong><br /><small class="sb_chopsEarned">'+level.ordinal+' Chops</small></p>');
					} else {
						if (level.ordinal < userCurrentLevel.ordinal) {
							levelIcon.addClass('sb_userLevel_earned');
						} else { // level.ordinal > userCurrentLevel.ordinal
							levelIcon.addClass('sb_userLevel_next');
						}
						levelIcon.html('<div class="sb_userLevelImg" style="background-image: url(\''+smallImageUrl+'\')"></div><p>'+level.title+'<br /><small class="sb_chopsEarned">'+level.ordinal+' Chops</small></p>');
					}
					levelIconsContainerElems.append(levelIcon);
				});

				var emptyLevelsToAdd = 5 - allLevels.length;
				while (emptyLevelsToAdd > 0) {
					levelIconsContainerElems.append('<div class="sb_userLevelIcons sb_userLevel_next"><div class="sb_userLevelImg sb_userLevel_empty"></div><p><br /></p></div>');
					emptyLevelsToAdd--;
				}
			}
		});
	}

	if (progressBarElems.length > 0) {
		progressBarElems.each(function(){
			if (!$SQ(this).hasClass('sb_ui-progressbar')) {
				$SQ(this).addClass('sb_ui-progressbar sb_ui-widget sb_ui-widget-content sb_ui-corner-all');
			}
		});
	}

	$SQ.each(window.sayso.starbar.user.gaming._currencies.collection, function (index, currency) {
		var currencyTitle = currency.title.toLowerCase();
		var currencyBalance = parseInt(currency.current_balance);

		if (currencyBalanceElems.length > 0) {
			currencyBalanceElems.each(function() {
				if ($SQ(this).attr('data-currency') == currencyTitle) {
					if (animate && currencyBalance != parseInt($SQ(this).html())) { // New value, play animation
						var originalColor = $SQ(this).css('color');
						// total duration is doubled when leveling up
						var durationMultiplier = 4/5;
						if (justLeveledUp) durationMultiplier = 9/5;
						// Prepare the element for numeric 'animation' (i.e. tweening the number)
						$SQ(this).animate(
							{ animationCurrencyBalance: parseInt($SQ(this).html()) },
							{ duration : 0 }
						).animate(
							{
								color : 'red',
								animationCurrencyBalance : currencyBalance
							},
							{ 
								duration : parseInt(animationDuration*durationMultiplier),
								step : function (now, fx) {
									$SQ(this).html(parseInt(now));
								},
								complete : function () {
									$SQ(this).html(currencyBalance);
									$SQ(this).css('color', originalColor);
								}
							}
						).animate(
							{ color : originalColor },
							{ duration : parseInt(animationDuration/5) }
						);
					} else {
						$SQ(this).html(currencyBalance);
					}
				}
			});
		}

		if (currencyPercentElems.length > 0) {
			if (userNextLevel && userNextLevel.ordinal && currencyBalance > userCurrentLevel.ordinal) {
				currencyPercent = Math.round((currencyBalance - userCurrentLevel.ordinal)/(userNextLevel.ordinal - userCurrentLevel.ordinal)*100);
			} else {
				currencyPercent = 0;
			}
			
			if (currencyPercent > 100) currencyPercent = 100; // technically this should never happen
		
			currencyPercentElems.each(function() {
				if (!$SQ(this).hasClass('sb_ui-progressbar-value')) {
					$SQ(this).addClass('sb_ui-progressbar-value sb_ui-widget-header sb_ui-corner-left');
				}
				if ($SQ(this).attr('data-currency') == currencyTitle) {
					startingWidthPercent = $SQ(this).percentWidth();
					if (animate && !justLeveledUp) {
						var animatingBarElem = $SQ(document.createElement('div'));
						var fadingBarElem = $SQ(document.createElement('div'));
						var progressBarElem = $SQ(this); // so it can be accessed from setTimeout()
						var currencyPercentLocal = currencyPercent; // so it can be accessed from setTimeout()
						animatingBarElem.addClass('sb_ui-progressbar-value-animating sb_ui-widget-header sb_ui-corner-left');
						animatingBarElem.css('width', startingWidthPercent+'%');
						fadingBarElem.addClass('sb_ui-progressbar-value-fading sb_ui-widget-header sb_ui-corner-left');
						fadingBarElem.css('width', currencyPercent+'%');
						
						animatingBarElem.insertBefore($SQ(this));
						fadingBarElem.insertBefore($SQ(this));
						fadingBarElem.fadeTo(0, 0);
						
						animatingBarElem.animate(
							{ width : currencyPercent+'%' },
							{ duration : parseInt(animationDuration*2/5) }
						);
						setTimeout(function() {
							fadingBarElem.fadeTo(parseInt(animationDuration*3/5), 1);
						}, parseInt(animationDuration*2/5));
						
						setTimeout(function() {
							progressBarElem.css('width', currencyPercentLocal+'%');
							animatingBarElem.annihilate();
							fadingBarElem.annihilate();
						}, animationDuration);
					} else if (animate && justLeveledUp) { // Just leveled up
						var animatingBarElem = $SQ(document.createElement('div'));
						var progressBarElem = $SQ(this); // so it can be accessed from setTimeout()
						var currencyPercentLocal = currencyPercent; // so it can be accessed from setTimeout()
						animatingBarElem.addClass('sb_ui-progressbar-value-animating sb_ui-widget-header sb_ui-corner-left');
						animatingBarElem.css('width', startingWidthPercent+'%');
						
						animatingBarElem.insertBefore($SQ(this));
						
						animatingBarElem.animate(
							{ width : '100%' },
							{ duration : parseInt(animationDuration*2/5) }
						);
						setTimeout(function() {
							progressBarElem.fadeTo(parseInt(animationDuration), 0);
						}, parseInt(animationDuration*2/5));
						setTimeout(function() {
							progressBarElem.css('width', currencyPercentLocal+'%');
							progressBarElem.fadeTo(parseInt(animationDuration*3/5), 1);
							animatingBarElem.fadeTo(parseInt(animationDuration*3/5), 0);
						}, parseInt(animationDuration*7/5));
						setTimeout(function() {
							animatingBarElem.annihilate();
						}, animationDuration*2);
					} else {
						$SQ(this).css('width', currencyPercent+'%');
					}
				}
			});
		}
	}); // each currency
}
