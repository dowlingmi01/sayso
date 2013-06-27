sayso.module.frameApp = (function(global, $, api, comm) {
	function runAction(data) {
		if ('action' in data && data['action'] in actions) {
			actions[data['action']](data);
		}
	}

	var actions = {
		'display-poll': function(data) {
			// sadly, inserting SG's JS doesn't work with $('head').append() because their script uses document.write(). Fail.
			document.write('<scr'+'ipt type="text/javascript" src="//www.surveygizmo.com/s3/polljs/'+data['poll']['external_id']+'-'+data['poll']['external_key']+'/"></scr'+'ipt>');
			document.write('<link href="/css/surveygizmo/polls-'+data['starbar_short_name']+'.css" rel="stylesheet" media="all" type="text/css" />');

			function enableClickableRadios() {
				if ((typeof global.SG_init_page) != 'undefined' && (typeof global.SGSurvey) != 'undefined') {
					var externalContentElem = $('.sg-wrapper');
					var domain = $('#sg_SubmitButton').data("domain");
					var survey = null;
					var starbarId = data['starbarId'];

					// SG_init_page still fails sometimes, if poll.js is partially loaded
					try
					{
						global.SG_init_page($('#survey-wrapper-' + data['poll']['external_id']));
						survey = new global.SGSurvey(global.SGAPI.surveyData[data['poll']['external_id'] + ""], false);
						survey.InitPage(3);

						// If we've made it this far, everything has loaded properly.
						clearInterval(repeatUntilEmbedLoads);

						var i = 15; // Check if CSS has loaded a maximum of 15 times, i.e. for 3000 ms, or 3 seconds
						// After that assume it has (or give up regardless) and show the poll
						var cssCheckInterval = setInterval(function(){
							if (externalContentElem.css('display') == 'inline-table') { // Our css is loaded!
								clearInterval(cssCheckInterval);
								externalContentElem.css('display', 'block');
								comm.fireEvent('poll-loaded', {height: 27+$('.sg-survey-form').outerHeight()});
							}
							i--;
							if (i < 1) externalContentElem.css('display', 'inline-table');
						}, 200);

						// Make the radio buttons clickable
						var elemRadios = $('input:radio');
						elemRadios.each(function(){
							$(this).bind({
								click: function(event){
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
										// @todo Sergio -- sayso-iframe-api-response never fires, even though the transaction was successful

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

								}
							});
						});
					}
					catch (err) {}
				}
			}

			var repeatUntilEmbedLoads = setInterval(enableClickableRadios, 100);
		}
	}

	comm.listen('init-action', runAction);
	comm.fireEvent('ready');
})(this, jQuery, sayso.module.api, sayso.module.frameComm)
;
