// closure for DOM ready
$(function(){
		
	// cache template files for reuse
	(function($){
		var cache = {};
		$.getCached = function(url, callback){
			if(url in cache){
				callback(cache[url]);
			} else {
				$.get(url, function(raw){
					cache[url] = raw;
					callback(raw);
				});
			}
		}
	})(jQuery);
	
	// fake console.log for IE
	console = window.console || { log : $.noop };
	
	// namespace
	var MissionSurvey = {};
	
	// constants
	MissionSurvey.Options = {
		STAGE_TRANSITION_DURATION : 200,
		SLIDE_TRANSITION_DURATION : 500,
		PROGRESS_TRANSITION_DURATION : 1000,
		LOADER_TRANSITION_DURATION : 200
	};
	
	// model
	MissionSurvey.Model = {};
	
	// placeholder for json
	MissionSurvey.Model.data = {};
	
	// handle errors
	MissionSurvey.Model.onError = function(jqXHR, textStatus, errorThrown){
		console.log(jqXHR, textStatus, errorThrown);
	};	
	
	// on startup json loaded
	MissionSurvey.Model.onLoad = function(json){
		// save data
		MissionSurvey.Model.data = json;
		// calculate dimensions for each step, based on total stages.  this'll work in theory, but if you go much past 5 it'll start to layout wierd
		MissionSurvey.Controller.step = 1 / json.stages.length;			
		// create the intro container
		var container = MissionSurvey.Views.content.empty().createElement('div#mission-introduction');		
		// write the intro to the DOM
		container.html(json.description);
		
		MissionSurvey.Views.body.css('background-image', 'url(' + json.background + ')');
		$('#mission-progress-token').css('background-image', 'url(' + json.progress_token + ')');
		// container to hold the titles of each stage
//		var titles = container.createElement('div.mission-titles');		
		// write the steps to the bottom of the intro and show steps
		var labels = $('#mission-progress-labels > ul');
		json.stages.forEach(function(element, index){
//			titles.createElement('span', 'Stage ' + ( index + 1 ) + ': ' + element.short_title);
			labels.createElement('li', 'Stage ' + ( index + 1 ) );
		});		
		// assign initial widths to stage labels
		var percent = Math.floor( MissionSurvey.Controller.step * 100 ) + '%';
		$('#mission-progress-labels > ul > li').css('width', percent).show();		
		// write the start button and label, assign 'next' behavior
		container.createElement('div.mission-next-button').on('click', MissionSurvey.Controller.getHandler('next'));
		container.createElement('p', 'ACCEPT MISSION');		
		window.postMessage(JSON.stringify(['sayso-mission-progress', { stage: -1, data: MissionSurvey.Model.data }]), '*');
	};
	
	// post data to server - passing the .stages property back with each question object populated with a .selectedAnswerId property
	MissionSurvey.Model.post = function(){
	};
	
	// load up the JSON and start the process
	MissionSurvey.Model.load = function(){
		$.ajax({
			url : 'models/survey.json',
			dataType : 'json',
			error : this.onError,
			success : this.onLoad
		});
	};
	
	// views
	MissionSurvey.Views = {};
	MissionSurvey.Views.body = $(document.body);
	MissionSurvey.Views.content = $('#mission-content');
	MissionSurvey.Views.indicator = $('#mission-progress-indicator');
	MissionSurvey.Views.seekbar = $('#mission-progress-bar-seek');
	
	// controller
	MissionSurvey.Controller = {};
	MissionSurvey.Controller.watcher = $({});
	MissionSurvey.Controller.index = -1;
	MissionSurvey.Controller.step = 0;
	MissionSurvey.Controller.satisfied = true;
	MissionSurvey.Controller.next = function(){
		if(!this.satisfied){
			return alert('Please provide your responses to this stage before continuing');
		}
		this.index++;
		this.update();
	};
	MissionSurvey.Controller.getHandler = function(method){
		var self = this;
		return function(e){
			e.preventDefault();
			self[method].call(self);
		};
	};
	MissionSurvey.Controller.update = function(){
		window.postMessage(JSON.stringify(['sayso-mission-progress', { stage: this.index, data: MissionSurvey.Model.data }]), '*');

		// if at the end, show the congratulatory screen and post data to server
		var complete = ( this.index == MissionSurvey.Model.data.stages.length );
		if(complete){
			MissionSurvey.Views.content.fadeTo(MissionSurvey.Options.STAGE_TRANSITION_DURATION, 0, function(){
				// write out-tro to DOM
				var container = MissionSurvey.Views.content.empty().createElement('div#mission-introduction');
				container.html(MissionSurvey.Model.data.validation);
				// fade it in
				MissionSurvey.Views.content.fadeTo(MissionSurvey.Options.STAGE_TRANSITION_DURATION, 1);
				// post data to server
				MissionSurvey.Model.post();
			});
			return;
		};
		// otherwise, get details about the next stage, and create local references for the closure...
		var stage = MissionSurvey.Model.data.stages[this.index];
		var type = stage.type;
		var index = this.index;
		// ...then load the template and update the UI
		$.getCached('views/' + type + '.tpl', function(raw){
			MissionSurvey.Views.content.fadeTo(MissionSurvey.Options.STAGE_TRANSITION_DURATION, 0, function(){
				// populate the template
				var html = Mustache.render(raw, stage.data);
				// write template to the DOM
				MissionSurvey.Views.content.html(html);
				// update the background
				MissionSurvey.Views.body.css('background-image', 'url(' + stage.background + ')');
				// update the progress bar
				MissionSurvey.Controller.showProgress();
				// enable next buttons
				$('.mission-next-button').on('click', MissionSurvey.Controller.getHandler('next'));
				// fire hooks for stage type
				MissionSurvey.Controller.fireHook(type, stage);
				// notify of change, and if at end or beginning
				MissionSurvey.Controller.watcher.trigger('change', [stage, index]);
				// animate in
				MissionSurvey.Views.content.fadeTo(MissionSurvey.Options.STAGE_TRANSITION_DURATION, 1);
			});			
		});
	};
	MissionSurvey.Controller.showProgress = function(){
		// translate float progress to css percentile value
		var percent = Math.floor( ( this.step * ( this.index + 1 ) ) * 100 ) + '%';
		// animate the draghead
		MissionSurvey.Views.indicator.animate({
			'left' : percent
		}, MissionSurvey.Options.PROGRESS_TRANSITION_DURATION);
		// animate the filled progress bar
		MissionSurvey.Views.seekbar.animate({
			'width' : percent
		}, MissionSurvey.Options.PROGRESS_TRANSITION_DURATION);
	};
	MissionSurvey.Controller.hooks = {};
	MissionSurvey.Controller.addHook = function(type, callback){
		if(!(this.hooks[type] instanceof Array)){
			this.hooks[type] = [];
		}
		this.hooks[type].push(callback);
	};
	MissionSurvey.Controller.fireHook = function(type, data){
		if(this.hooks[type] instanceof Array){
			this.hooks[type].forEach(function(callback){
				callback(data);
			});
		}
	};
	
	/* add event listeners */
	
	// on change
	MissionSurvey.Controller.watcher.on('change', function(event, stage, index){
		// reset flag for required input
		MissionSurvey.Controller.satisfied = false;
		// for site visits, don't scroll body
		MissionSurvey.Views.body.css('overflow-y', ( stage.type == 'site-survey' ) ? 'hidden' : 'scroll');
	});
	
	/* add hooks */
	
	// poll hooks
	function pollHook(stage) {
		MissionSurvey.Views.content.find('input[type="radio"]').on('change', function(e){
			stage.data.selectedAnswerId = $(this).val();
			MissionSurvey.Controller.satisfied = true;
		});
	}
	MissionSurvey.Controller.addHook('poll', pollHook);
	
	// video hooks
	function videoHook(stage) {
		// on video complete, hide it and show the poll
		MissionSurvey.VideoPlayer.render('mission-video-container', stage.data, function(){
			if( html5video ) {
				$('.mission-trailer').remove();
			} else {
				/*
				 * we need to hide the video but keep it accessible since it's register for state change callbacks with the YTJSAPI...
				 * IE8 throws errors when state change fires and there's no element in the DOM
				 * so stick the container in a hidden element
				*/ 			
				var player = $('.mission-trailer');
				$('#trash').append(player);
			}
			// and fade in the question/answer element
			$('.mission-video .mission-video-poll').fadeIn();
		});		
	}
	MissionSurvey.Controller.addHook('video', function(stage){
		videoHook(stage);
		pollHook(stage);
	});
	MissionSurvey.Controller.addHook('video-image', function(stage){
		videoHook(stage);
		imagePollHook(stage);
	});
	
	// visit hooks
	MissionSurvey.Controller.addHook('site-survey', function(stage){
		// only way to have iframe scale with window is by forcing it
		var iframe = $('.mission-visit iframe');
		var layout = function(){
			var height = $(window).height();
			height = height - 50 - 60;  // subtract heights of top and bottom iris TODO: measure these dynamically
			iframe.height(height);
		};
		// size it as soon as it's written to dom...
		layout();
		// and update it on window resize
		$(window).on('resize', layout);
		// slider has it's own API
		MissionSurvey.Slider.init();
		// update the Model - this one is per question in the stage, not just one answer ID for the stage
		$('.mission-slide-set input[type="radio"]').on('change', function(e){
			var handle = $(this);
			var answerId = handle.val();
			var container = handle.closest('.mission-slide-set');
			var questionId = container.data('id');
			var stage = MissionSurvey.Model.data.stages[MissionSurvey.Controller.index];
			var question = _.find(stage.data.questions, function(element){
				return element.question.id == questionId;
			});
			question.selectedAnswerId = answerId;
			MissionSurvey.Slider.next();
		});
	});
	
	// image poll hooks
	function imagePollHook(stage) {
		// figure out how many answers and size the columns accordingly
		var quantity = 1 / stage.data.answers.length;
		var percent = Math.floor( quantity * 100) + '%';
		$('.mission-image-poll li').css('width', percent);
		// update Model and advance
		MissionSurvey.Views.content.find('input[type="radio"]').on('change', function(e){
			stage.data.selectedAnswerId = $(this).val();
			MissionSurvey.Controller.satisfied = true;
			MissionSurvey.Controller.next();
		});
	}
	MissionSurvey.Controller.addHook('image-poll', imagePollHook);
	
	/* other API */
	
	// slider API
	MissionSurvey.Slider = {};
	MissionSurvey.Slider.index = -1;
	MissionSurvey.Slider.isOpen = false;
	MissionSurvey.Slider.offset = 120;  // height of background graphic
	MissionSurvey.Slider.close = function(){
		var header = $('#mission-slide').find('.mission-slide-set h4:visible:first');
		if(header.size() == 0){
			header = $('#mission-slide').find('.mission-slide-instructions h3:first');
		}
		var height = header.outerHeight(true);
		var margin = $('#mission-slide-content').css('margin-top');
		margin = parseInt(margin);
		var visible = height + margin;
		$('#mission-slide').animate({
			'height' : ( visible + this.offset )
		});
		$('#mission-slide-activator').removeClass('active');
		this.isOpen = false;
	};
	MissionSurvey.Slider.open = function(){
		$('#mission-slide-activator').show();
		var container = $('#mission-slide');
		var height = container.outerHeight();
		container.css('height', 'auto');
		var visible = container.prop('scrollHeight');
		container.css('height', height + 'px');
		container.animate({
			'height' : ( visible + this.offset )
		});
		$('#mission-slide-activator').addClass('active');
		this.isOpen = true;
	};
	MissionSurvey.Slider.toggle = function(){
		var method = this.isOpen ? 'close' : 'open';
		this[method]();
	};
	MissionSurvey.Slider.next = function(){
		this.index++;
		var slides = $('.mission-slide-set');
		// once a user answers a question, go to the next in this stage's series...  otherwise, if we're out of questions, go to next stage
		if(this.index < slides.length){
			// animate out
			$('.mission-slide-sets').fadeTo(MissionSurvey.Options.SLIDE_TRANSITION_DURATION, 0, function(){
				// update the status X/X in top left
				$('#mission-slide-status').text( ( MissionSurvey.Slider.index + 1) + '/' + slides.length);
				// show the active question (slide) and hide the others
				slides.eq(MissionSurvey.Slider.index).show().siblings().hide();
				// update the height
				MissionSurvey.Slider.open();
				// animate in
				$(this).fadeTo(MissionSurvey.Options.SLIDE_TRANSITION_DURATION, 1);
			});			
		} else {
			MissionSurvey.Controller.satisfied = true;
			MissionSurvey.Controller.next();
		}
	};
	MissionSurvey.Slider.init = function(){
		// start the survey when they click the prompt
		$('#mission-slide .mission-slide-start').on('click', function(){
			$(this).closest('.mission-slide-instructions').remove();
			MissionSurvey.Slider.next();
		});
		// enable the toggle button, but hide it initially
		$('#mission-slide-activator').hide().on('click', function(e){
			MissionSurvey.Slider.toggle();
		});
		// animate in
		this.open();
	};
	
	// video API
	MissionSurvey.VideoPlayer = {};
	MissionSurvey.VideoPlayer.width = 560;
	MissionSurvey.VideoPlayer.height = 315;
	MissionSurvey.VideoPlayer.options = {
		'enablejsapi' : 1,
		'playerapiid' : 'ytplayer',
		'version' : 3,
		'rel' : 0,
		'autoplay' : 1,
		'disablekb' : 1,
		'showinfo' : 0,
		'iv_load_policy' : 3		
	};	
	
	// can we use HTML5 video?  if so, prefer that to the JS-SWF embed...
	var html5video = !!document.createElement('video').canPlayType;
	
	// use IFrame API if HTML5 is supported, otherwise SWFObject
	if(html5video){
		
		MissionSurvey.VideoPlayer.elementId = null;
		MissionSurvey.VideoPlayer.values = {
			height: MissionSurvey.VideoPlayer.height,
			width: MissionSurvey.VideoPlayer.width,
			playerVars: MissionSurvey.VideoPlayer.options,
			events : {}
		};
		MissionSurvey.VideoPlayer.rendered = false;
		MissionSurvey.VideoPlayer.render = function(elementId, stageData, complete){
			// update values
			this.elementId = elementId;
			this.values.videoId = stageData.url;
			this.values.playerVars.controls = (location.host.match(/say\.so/) && !stageData.enableControls ? 0 : 1);
			this.values.events.onStateChange = function(event){
				if(event && event.preventDefault){
					event.preventDefault();
				}
				if(event.data == YT.PlayerState.ENDED){
					complete();
				}
				return false;
			};
			// only load the API once
			if(!this.rendered){			
				var script = document.createElement('script');
				script.src = "//www.youtube.com/iframe_api";
				var first = document.getElementsByTagName('script')[0];
				first.parentNode.insertBefore(script, first);
				this.rendered = true;
				window.onYouTubeIframeAPIReady = startPlayer;
			} else
				startPlayer();
			// load it
			function startPlayer() {
				new YT.Player(
					MissionSurvey.VideoPlayer.elementId,
					MissionSurvey.VideoPlayer.values
				);
			}
		};
		
	} else {
		
		// HTML5 video not supported, use the SWF object instead - this will prevent errors in IE8 as well (which should always fail the test anyway)
		
		MissionSurvey.VideoPlayer.id = 'sayso-player-object';		
		MissionSurvey.VideoPlayer.minimumVersion = '10.1';
		MissionSurvey.VideoPlayer.params = { 'allowScriptAccess' : 'always' };
		MissionSurvey.VideoPlayer.attributes = { 'id' : MissionSurvey.VideoPlayer.id };
		
		MissionSurvey.VideoPlayer.render = function(elementId, videoId, complete){
			window.onYouTubePlayerStateChange = function(state) {
				// if it's done (state is 0)
				if(state == 0) {
					// and registered callback is a function
					if(typeof complete == 'function'){
						complete();
					};
					// YouTube JS API does not support .removeEventListener so set it to noop instead
					window.onYouTubePlayerStateChange = $.noop;
				};
			};
			window.onYouTubePlayerReady = function(playerId) {
				var player = document.getElementById(MissionSurvey.VideoPlayer.id);
				player.addEventListener('onStateChange', 'onYouTubePlayerStateChange');
			};
			swfobject.embedSWF(
				'http://www.youtube.com/v/' + videoId + '?' + $.param(this.options),
				elementId,
				this.width,
				this.height,
				this.minimumVersion,
				null,
				null, 
				this.params,
				this.attributes
			);
		};
		
	};	

	// show/hide loader when making ajax calls
	$(document).ajaxSend(function(){
		$('#mission-ajax-overlay').fadeIn(MissionSurvey.Options.LOADER_TRANSITION_DURATION);
	});
	$(document).ajaxComplete(function(){
		$('#mission-ajax-overlay').fadeOut(MissionSurvey.Options.LOADER_TRANSITION_DURATION);
	});
	
	// startup
	MissionSurvey.Model.load();

});