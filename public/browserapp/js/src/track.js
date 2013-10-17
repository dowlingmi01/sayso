sayso.module.track = (function(config, util, $, comm, dommsg, forge, global) { return function(inIframe, frameId) {
	var parentFrameId, topFrameId;

	function handleParentReq( data ) {
		forge.message.broadcast('parent-location-' + data.frameId,
			{ location: config.location, frameId: frameId, topReq: data.topReq });
	}
	function requestParentLocation() {
		if( !parentFrameId )
//			util.evalInPageContext( "parent.postMessage( '[\"sayso-parent-req\", " + frameId + "]', '*' );");
			parent.postMessage( JSON.stringify(["sayso-parent-req", {frameId: frameId}]), '*' );
		if( !topFrameId )
			top.postMessage( JSON.stringify(["sayso-parent-req", {frameId: frameId, topReq: true}]), '*' );
		if( !parentFrameId || !topFrameId )
			setTimeout(requestParentLocation, 200);
	}
	comm.request( 'metrics-event', { type: 'page_view', frameId: frameId, url: config.location.href,
		topFrame: !inIframe } );
	dommsg.addHandler('parent-req', handleParentReq);
	if( inIframe ) {
		forge.message.listen('parent-location-' + frameId, function( m ) {
			if( !parentFrameId && !m.topReq) {
				parentFrameId = m.frameId;
				comm.request( 'metrics-event', { type: 'frame_link', frameId: frameId, parentFrameId: parentFrameId } );
			} else if( !topFrameId && m.topReq ) {
				topFrameId = m.frameId;
				comm.request( 'metrics-event', { type: 'top_link', frameId: frameId, topFrameId: topFrameId } );
			}
		});
		requestParentLocation();
	} else
		util.addEventListener(global, 'unload', function() {
			comm.request( 'metrics-event', { type: 'page_unload', frameId: frameId } );
		});

	if( config.location.host === 'vex.wildtangent.com') {
		var brandBoostStage = config.location.pathname.match(/\/(?:Vex\/)?(\w+)(?:.aspx)?/);
		if( brandBoostStage ) {
			var par = util.urlParams(config.location.search.substring(1));
			comm.request( 'brandboost-event', { stage: brandBoostStage[1], urlParams: par, frameId: frameId } );
		}
	}

	if( config.location.href.match(/:\/\/simssoc.game.playfish.com\/g\/fb\/simssoc\//) ) {
		var elementFound = false;
		function monitorElement() {
			if( $('div#overlay div#bank').length ) {
				if( !elementFound ) {
					elementFound = true;
					comm.request('submit-event', { event_name: 'add_cash', event_data: { event_source: 'monitorElement', game_name: 'simssocial', game_source: 'facebook', add_cash_stage: 'open'} } );
				}
			} else if( elementFound )
				elementFound = false;
			setTimeout( monitorElement, 1000 );
		}
		monitorElement();
	}

	/**
	 * Helper function for recording behaviors on the server
	 */
	var behaviorTracker = {

		videoView: function (type, id) {
			this.videoId = id;
			comm.request('metrics-event', {
				type: 'asset', frameId: frameId,
				provider: type,
				asset_type: 'video',
				action: 'load',
				asset_id: id
			});
		},

		search: function (data) {
			data.type = 'search';
			data.frameId = frameId;
			comm.request('metrics-event', data);
		},

		// social activity

		socialActivity: function (url, content, social_network) {
			comm.request('metrics-event', {
				type: 'social_action', frameId: frameId,
				social_network: social_network,
				target_url: url,
				message: content
			} );
		}
	};

	// Behavioral tracking

	// ================================================================
	// Video View
	var m = config.location.href.match(/youtube\..*\/watch.*[?&]v=([\w\-]{11})/);
	if( m )
		behaviorTracker.videoView('youtube', m[1] );
	else if( m = config.location.href.match(/youtube\..*\/embed\/([\w\-]{11})/) )
		behaviorTracker.videoView('youtube', m[1] );
	else if( config.location.href.match(/youtube\./) )
		$.doTimeout(3000, function checkForVideoPlayer() {
			var vid;
			if( behaviorTracker.videoId )
				return false;
			else if( (vid = $('div.player-root[data-video-id]')).length ) {
				vid = vid.attr('data-video-id');
				if( vid.length === 11 ) {
					behaviorTracker.videoView('youtube', vid);
					return false;
				}
			}
			return true;
		});

	// ================================================================
	// Search

	if (!inIframe) {

		var searchType = 0,
			searchRegex = '';

		var googleEngineRegexp = /google(\..{2,3})+(\..{2,3})?\//;

		if (config.location.href.match('bing.com/search'))
		{
			searchType = 1; // bing (these ids match lookup_search_engines table)
			searchRegex = /q=([^&]+)&/g;
		}
		else if (googleEngineRegexp.test(config.location.href))
		{
			searchType = 2; // google
			searchRegex = /(?:\?|&)q=([^&]+)/;
		}
		else if (config.location.href.match('search.yahoo.com'))
		{
			searchType = 3; // yahoo
			searchRegex = /[\?&]?p=([^&]+)&/g;
		}

		if (searchType)
		{
			var data = {
				type_id : searchType
			};

			var searchQueryArray = searchRegex.exec(config.location.href);
			if (searchQueryArray && searchQueryArray.length > 1)
			{
				var searchQuery = searchQueryArray[1];
				if (searchQuery) {
					data.query = searchQuery;
					behaviorTracker.search(data);
				}
			}
			else
			{
				util.log('On search page, but no query found');
			}

			// We are in Google, let's monitor the query field
			if(searchType === 2)
			{
				// remeber initial value in search field
				var lastQueryValue  = $('input[name=q]').val();
				// the above is changed and remains so validInterval ms
				var validInterval   = 2000;
				// start counting time at 0
				var startInterval   = 0;
				// poll each checkInterval ms
				var checkInterval   = 100;
				// did we send stats before?
				// yes, we sent them at the page load already...
				var statsSent	   = true;
				// do poll
				$.doTimeout(checkInterval, function()
				{
					// any changes
					var currentQueryValue = $('input[name=q]').val();

					// yes, reset all
					if(currentQueryValue !== lastQueryValue)
					{
						lastQueryValue  = currentQueryValue;
						startInterval   = 0;
						statsSent	   = false;
					}
					else
					{
						// increment the start and check
						startInterval += checkInterval;
						if(startInterval >= validInterval)
						{
							// no stats sent? send it!
							if(!statsSent && currentQueryValue)
							{
								// send now asynchronously...
								data.query = currentQueryValue;
								behaviorTracker.search(data);
								// but set the check synchronously to avoid repeating...
								statsSent = true;
							}
							// reset
							startInterval = 0;
						}
					}
					return true;
				});
			}
		}
	}

	// ================================================================
	// Tweets
	var tweet;

	// popup/x-domain Tweet tracking
	if (config.location.href.match('twitter.com/intent')) {

		var tweetUrl = decodeURIComponent(/(?:\?|&)url=([^&]+)/.exec(config.location.search)[1]);
		tweet = $('#status').val();
		$('#status').keyup(function () {
			// on every key event we capture the full contents. ensures that
			// the tweet isn't removed by Twitter before we grab it
			tweet = $(this).val();
		});
		// use mousedown, not click. click is not reliable because the window
		// is closed very quickly after submitting the tweet which kills the ajax call
		$('#update-form input.submit').mousedown(function () {
			behaviorTracker.socialActivity(tweetUrl, tweet, 'Twitter');
			$(this).unbind('mousedown');
		});
	// Tweet tracking on Twitter.com
	} else if (config.location.hostname.match('twitter.com') && $('div.tweet-box textarea').length) {

		tweet = '';

		// append to what is already bound...
		$('div.tweet-box textarea').bind('keyup', function()
		{
			// since there is a race condition between
			// when our click event is fired and Twitter removes
			// the content of the tweet box, then we just
			// continuously capture the contents here
			tweet = $(this).val();
		});

		// append to what is already bound...
		$('div.tweet-box div.tweet-button-sub-container').bind('click', function(e)
		{
			try
			{
				behaviorTracker.socialActivity(config.location.href, tweet, 2);
				e.preventDefault();
				tweet = '';
			}
			catch(ex)
			{
				util.log('Exception: '+ ex.getMessage());
			}
		});
	}

	// ================================================================
	// Facebook Like

	if (inIframe) {
		var comment;
		if (config.location.href.match('facebook.com/sharer/sharer')) {

			var textArea = $('textarea').first(),
				url = decodeURIComponent(/(?:\?|&)u=([^&]+)/.exec(config.location.search)[1]);
			comment = textArea.val();

			textArea.keyup(function () {
				comment = $(this).val();
			});

			$('input[name=share]').click(function () {
				if (comment.length && comment !== 'Write Something...') {
					behaviorTracker.socialActivity(url, comment, 'Facebook');
				}
			});
		} else if (config.location.href.match('facebook.com/plugins/comment_widget_shell')) {
			util.log('In comment widget!!'); // can't get this working, iframe loads into a hidden div ?
			comment = $('textarea.connect_comment_widget_full_input_textarea').val();
			util.log(comment);
		} else if (config.location.href.match('facebook.com/plugins/like')) {
			$('.pluginConnectButton button').click(function () {
				var likedUrl = decodeURIComponent(/href=([^&]*)/g.exec(config.location.search)[1]);
				behaviorTracker.socialActivity(likedUrl, '', 'Facebook');
			});
		}
	}
};})(sayso.module.config, sayso.module.util, jQuery, sayso.module.comm, sayso.module.dommsg, forge, this)
;
