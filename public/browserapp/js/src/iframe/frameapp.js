sayso.module.frameApp = (function(global, $, api, comm, dommsg) {
	function runAction(data) {
		if ('action' in data && data['action'] in actions) {
			actions[data['action']](data);
		}
	}

	var actions = {
		'display-get-satisfaction': function(data) {
            (function(){var uv=document.createElement('script');uv.type='text/javascript';uv.async=true;uv.src='//widget.uservoice.com/ZWJLw5Ynjj8uEXSqi9RHBA.js';var s=document.getElementsByTagName('script')[0];s.parentNode.insertBefore(uv,s)})();

			var $container = $('<div data-uv-inline="classic_widget" data-uv-mode="full" data-uv-primary-color="#00ccc9" data-uv-link-color="#8ebd00" data-uv-default-mode="feedback" data-uv-forum-id="218239" data-uv-support-tab_name="Ping Recon" data-uv-feedback-tab_name="Give Feedback" data-uv-width="560px" data-uv-height="494px"></div>');
			$('body').append($container);
		},
		'display-survey': function(data) {
			var $SGQ = global.$SGQ;

			var $body = $('body');
			var cssSGQ = document.createElement('link');
			cssSGQ.rel = 'stylesheet';
			cssSGQ.href = '//' + $SGQ.base_domain + '/css/surveygizmo/surveys-' + $SGQ.starbar_short_name + '.css';
			document.body.appendChild(cssSGQ);

			if ($SGQ.size == "large" || $SGQ.size == "huge") {
				var cssLargeSGQ = document.createElement('link');
				cssLargeSGQ.rel = 'stylesheet';
				cssLargeSGQ.href = '//' + $SGQ.base_domain + '/css/surveygizmo/surveys-' + $SGQ.size + '-' + $SGQ.starbar_short_name + '.css';
				document.body.appendChild(cssLargeSGQ);
			}

			var maximumTimeToWait = 8000; // 8 seconds
			var timeBetweenChecks = 150;

			function everythingIsLoaded() {
				return ( $('.sg-footer-hook-2').css('text-align') == "right" ); // indicates that css is done loading
			}

			afterSQloads();

			function afterSQloads () {
				var totalTimeWaitedSoFar = 0;
				$.doTimeout('waitForEverything', timeBetweenChecks, function() {
					if (everythingIsLoaded()) {
						if ($('.sg-disqualify').length == 1) {
							comm.fireEvent('survey-done', {survey_status: 'disqualified'});
							return false;
						} else if ($('.sg-progress-bar-full').length == 1) {
							comm.fireEvent('survey-done', {survey_status: 'completed'});
							return false;
						}

						// Loading complete!
						$('.sg-wrapper').show();
						$('.sayso_loading_container').hide();
						return false; // exit doTimeout loop
					}

					totalTimeWaitedSoFar += timeBetweenChecks;

					if (totalTimeWaitedSoFar > maximumTimeToWait) {
						alert("An error has occured while loading this survey. Please try again later.");
						return false; // exit doTimeout loop
					}

					return true; // loop doTimeout
				});
			}

		},
		'display-poll': function(data) {
			var oldDocumentWrite = document.write;

			// SurveyGizmo's poll JS uses document.write
			document.write = function(s) {
				$('body').append(s);
			}

			// sadly, inserting SG's JS doesn't work with $('head').append() because their script uses document.write(). Fail.
			$('head').append('<scr'+'ipt type="text/javascript" src="//www.surveygizmo.com/s3/polljs/'+data['poll']['external_id']+'-'+data['poll']['external_key']+'/"></scr'+'ipt>');
			var afterCssLoadMaxChecks = 15; // after the CSS loads, check up to 15 times (3000 ms) for changes in height

			var previousHeight;

			function afterPollLoaded() {
				var height = 27+$('.sg-survey-form').outerHeight();
				if (afterCssLoadMaxChecks > 0) {
					if (previousHeight != height) {
						comm.fireEvent('poll-loaded', {height: height});
						previousHeight = height;
					}

					afterCssLoadMaxChecks--;
					setTimeout(afterPollLoaded, 200); // repeat
				}
			}

			function enableClickableRadios() {
				if ((typeof global.SG_init_page) != 'undefined' && (typeof global.SGSurvey) != 'undefined') {
					var externalContentElem = $('.sg-wrapper');
					var domain = $('#sg_SubmitButton').data("domain");
					var survey = null;
					var starbarId = data['starbarId'];
					
					dommsg.resetHandleMessage();

					// SG_init_page still fails sometimes, if poll.js is partially loaded
					try
					{
						global.SG_init_page($('#survey-wrapper-' + data['poll']['external_id']));
						survey = new global.SGSurvey(global.SGAPI.surveyData[data['poll']['external_id'] + ""], false);
						survey.InitPage(3);

						// If we've made it this far, everything has loaded properly.
						clearInterval(repeatUntilEmbedLoads);

						var cssTag = document.createElement('link');
						cssTag.rel = 'stylesheet';
						cssTag.href = '/css/surveygizmo/polls-' + data['starbar_short_name'] + '.css';
						document.body.appendChild(cssTag);

						var i = 15; // Check if CSS has loaded a maximum of 15 times, i.e. for 3000 ms, or 3 seconds
						// After that assume it has (or give up regardless) and show the poll
						var cssCheckInterval = setInterval(function(){
							if (externalContentElem.css('display') == 'inline-table') { // Our css is loaded!
								clearInterval(cssCheckInterval);
								// document.write = oldDocumentWrite; // not sure it's necessary
								externalContentElem.css('display', 'block');
								afterPollLoaded();
							}
							i--;
							if (i < 1) externalContentElem.css('display', 'inline-table');
						}, 200);

						// Make the radio buttons clickable
						var elemRadios = $('input:radio');
						elemRadios.on('click', function(event){
							elemRadios.off('click');
							afterCssLoadMaxChecks = 0; // stop updating the poll size, otherwise it will resize after completion

							var $radio = $(this);
							$radio.attr('checked', 'checked');

							// Simulate what survey.Vote(domain) does... which is submit the vote via ajax
							var vote=$("#sg_FormFor"+data['poll']['external_id']).serialize();
							var link=["//",domain,"/s3/polljs/"+data['poll']['external_id']+"-"+data['poll']['external_key']+"?_vote=",encodeURIComponent(vote)].join("");

							var questionId = "" + data['poll']['questions'][0]['id'];
							var choice = $.grep(data['poll']['questions'][0]['choices'], function (c){ return parseInt(c['external_choice_id']) === parseInt($radio.attr('value')); })[0];

							var request = {
								action_class : "survey",
								action : "updateSurveyResponse",
								starbar_id : starbarId,
								survey_id : data['poll']['id'],
								survey_response_id : data['poll']['survey_response_id'],
								survey_data: {
									answers: {}
								}
							};
							request.survey_data.answers[questionId] = choice['id'];

							api.doRequest(request, function (response) {
								// @todo handle failure
								$.ajax({
									url:link,
									dataType:"jsonp",
									complete: function() {
										comm.fireEvent('poll-completed');
										survey.ShowResults(domain);
									}
								});

							});
						});
					}
					catch (err) {}
				}
			}

			var repeatUntilEmbedLoads = setInterval(enableClickableRadios, 100);
		},
		"display-video": function(data) {
			var player;
			var $videoContainer = $('<div id="sayso-video-container"></div>');
			$('body').append($videoContainer);

			switch (data['video_provider']) {
				case "youtube":
					function onPlayerError(errorCode) {
						// do nothing
					}

					function onPlayerReady(event) {
						player.addEventListener('onStateChange', onPlayerStateChange);
						player.addEventListener('onError', onPlayerError);
						// event.target.playVideo(); // to autoplay
					}

					function onPlayerStateChange(event) {
						if (event.data == global.YT.PlayerState.PLAYING)
							comm.fireEvent('video-start');
						else if(event.data == global.YT.PlayerState.ENDED)
							comm.fireEvent('video-done');
					}

					global.onYouTubeIframeAPIReady = function() {

						player = new global.YT.Player('sayso-video-container', {
							height: '390',
							width: '642',
							videoId: data['video_key'],
							events: {
								'onReady': onPlayerReady
							},
							playerVars : {
								'iv_load_policy' : 3,
								'controls' : 0,
								'disablekb' : 1,
								'rel' : 0,
								'showinfo' : 0
							}

						});
					}

					//Load player api asynchronously.
					var newTag = document.createElement('script');
					newTag.src = "//www.youtube.com/iframe_api";
					var firstScriptTag = document.getElementsByTagName('script')[0];
					firstScriptTag.parentNode.insertBefore(newTag, firstScriptTag);

					break;

				// case "vimeo":
					//break;

				default:
					return;
			}
		}
	}

	comm.listen('init-action', runAction);
	comm.fireEvent('ready');
})(this, jQuery, sayso.module.api, sayso.module.frameComm, sayso.module.dommsg)
;
