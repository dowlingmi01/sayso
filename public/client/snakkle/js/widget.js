$(function(){
	
	// namespace
	var SaySo = window.SaySo || {};
	
	// significant events
	SaySo.on = {};
	
	// no need to crawl the DOM over and over - cache element references here
	SaySo.elements = {};
	
	// convenience ns for heights needed during animation sequences
	SaySo.dimensions = {};
	
	// manage quizes
	SaySo.Quiz = {};
	SaySo.Quiz.isComplete = false;
	SaySo.Quiz.points = 0;
	SaySo.Quiz.quizzes = [];
	SaySo.Quiz.index = -1;
	SaySo.Quiz.getQuiz = function(i){
		return this.quizzes[i];
	};
	SaySo.Quiz.getActiveQuiz = function(){
		return this.getQuiz(this.index);
	};
	SaySo.Quiz.revealPoints = function(correctAnswerSelected){
		SaySo.elements.quizTitle.hide();
		SaySo.elements.report.show();
		if(correctAnswerSelected){
			this.points += 11;
			SaySo.elements.correct.show();
			SaySo.elements.incorrect.hide();
		} else {
			this.points += 5;
			SaySo.elements.correct.hide();
			SaySo.elements.incorrect.show();
		}
		SaySo.elements.points.text(this.points);
		SaySo.elements.hint.hide();
		SaySo.elements.options.show();
	};
	SaySo.Quiz.render = function(){
		
		// get the current quiz iteration
		var quiz = this.getActiveQuiz();
		
		// clear previous
		SaySo.elements.portrait.empty();
		SaySo.elements.userInput.empty();
		
		// show title
		SaySo.elements.quizTitle.show();
		SaySo.elements.report.hide();
		
		// show photo
		var photo = SaySo.elements.portrait.createElement('img');
		photo.attr('src', quiz.image);
	
		// hide options
		SaySo.elements.options.hide();
		
		// show hint
		if( quiz.hint ) {
			SaySo.elements.hint.show();
			SaySo.elements.hint.html('<span>Hint - </span>' + quiz.hint);
		}
		
		// add answers
		for(var i = 0; i < quiz.answers.length; i++){
			var answer = quiz.answers[i];
			new SaySo.Answer(answer);
		};
		
		// reposition logo
		SaySo.measure('quiz', 'actionBar');
		SaySo.elements.logo.css({
			'top' : (SaySo.dimensions.quiz + Math.round(SaySo.dimensions.actionBar / 2 - 12)) + 'px'
		});
		
	};
	SaySo.Quiz.next = function(){
		this.index = this.index + 1;
		if(this.index < this.quizzes.length){
			this.render();
		} else {
			this.isComplete = true;
			SaySo.on.complete();
		}
	};
	
	// class to bind abstract answers with DOM representations
	SaySo.Answer = function(data){
		this.data = data;
		this.container = SaySo.elements.userInput.createElement('div.sayso-answer');
		this.label = this.container.createElement('label');
		this.radio = this.label.createElement('<input type="radio" name="sayso-answer-radio" />');
		this.label.append(data.name);
		this.track = this.container.createElement('div.sayso-progress-track');
		this.progress = this.track.createElement('div.sayso-progress-meter');
		this.progress.createElement('div');
		this.label.click($.proxy(this.clickHandler, this));
		SaySo.Answer.instances.push(this);
	};
	SaySo.Answer.instances = [];
	SaySo.Answer.reset = function(){
		for(var i = 0; i < this.instances.length; i++){
			var instance = this.instances[i];
			instance.container.remove();
		}
		SaySo.Answer.instances = [];
	};
	SaySo.Answer.revealScores = function(correctAnswerSelected){
		for(var i = 0; i < this.instances.length; i++){
			var instance = this.instances[i];
			instance.showScore();
		}
		SaySo.Quiz.revealPoints(correctAnswerSelected);
	};
	SaySo.Answer.prototype.showScore = function(){
		this.radio.remove();
		this.track.css('visibility', 'visible');
		if(this.isCorrect()){
			this.container.addClass('sayso-correct-answer');
		}
		var percent = this.getPercentile();
		this.label.append(' - ' + percent);
		this.progress.animate({
			'width' : percent
		}, 800);
	};
	SaySo.Answer.prototype.getPercentile = function(){
		var total = 0;
		var quiz = SaySo.Quiz.getActiveQuiz();
		for(var i = 0; i < quiz.answers.length; i++){
			var iteration = quiz.answers[i];
			total = total + iteration.count;
		}
		var decimal = this.data.count / total;
		var percentile = Math.round(100 * decimal);
		return percentile + '%';
	}
	SaySo.Answer.prototype.isCorrect = function(){
		return this.data.id == SaySo.Quiz.getActiveQuiz().correct;
	};
	SaySo.Answer.prototype.clickHandler = function(e){
		var correctAnswerSelected = this.isCorrect();
		if(!correctAnswerSelected){
			this.container.addClass('sayso-wrong-answer');
		}
		SaySo.Answer.revealScores(correctAnswerSelected);
		$(e.target).unbind('click', arguments.callee);
	};
	
	// update dimensions on each animation pass
	SaySo.measure = function(){
		this.elements.logo.hide();
		for(var i = 0; i < arguments.length; i++){
			var element = arguments[i];
			this.dimensions[element] = this.elements[element].prop('scrollHeight');  // use scrollHeight to measure hidden or overflow:hidden elements
		}
		this.elements.logo.show();
	};
	
	// flag to skip animation if requested state is already visible
	SaySo.formIsShowing = false;
	SaySo.showForm = function(){
		
		// skip it if we're already showing
		if(this.formIsShowing){
			return;
		}
		
		// set flag
		this.formIsShowing = true;
		
		// set toggle handler to action bar
		this.elements.actionBar.unbind('click').click(hideFormHandler);
		
		// animate
		this.measure('quiz', 'signup', 'actionBar');		
		this.elements.actionBar.animate({
			'border-top-width' : 0
		});
		this.elements.quiz.css({
			'height' : this.dimensions.quiz + 'px'
		});
		this.elements.quiz.animate({
			'height' : 0
		});
		this.elements.content.animate({
			'height' : (this.dimensions.signup + this.dimensions.actionBar) + 'px'
		});
		this.elements.signup.animate({
			'height' : this.dimensions.signup + 'px'
		});
		this.elements.upTitle.animate({
			'opacity' : 1
		});
		this.elements.logo.animate({
			'height' : '100px',
			'top' : '-25px',
			'left' : '-13px'  // -25 for half the difference in size, offset by 12 (left border width of container)
		});
	};
	SaySo.hideForm = function(){
		
		// skip it if we're already showing
		if(!this.formIsShowing){
			return;
		}
		
		// set flag
		this.formIsShowing = false;
		
		// set toggle handler to action bar
		this.elements.actionBar.unbind('click').click(showFormHandler);
		
		// animate
		this.measure('quiz', 'actionBar');
		this.elements.actionBar.animate({
			'border-top-width' : '7px'
		});
		this.elements.quiz.animate({
			'height' : this.dimensions.quiz + 'px'
		});
		this.elements.content.animate({
			'height' : (this.dimensions.quiz + this.dimensions.actionBar) + 'px'
		});
		this.elements.signup.animate({
			'height' : 0
		});
		this.elements.upTitle.animate({
			'opacity' : 0
		});
		// logo should drop to height of quiz, then center to action bar, less 12 pixels offset
		this.elements.logo.animate({
			'height' : '50px',
			'top' : (this.dimensions.quiz + Math.round(this.dimensions.actionBar / 2 - 12)) + 'px',
			'left' : '12px' // width of .sayso-container left border
		});
	};
	
	/* HANDLERS AND GLOBAL UTILITY FUNCTIONS */
		
	// handlers to maintain scope without binding
	var showFormHandler = function(){
		SaySo.showForm();
	};
	var hideFormHandler = function(){
		SaySo.hideForm();
	};
		
	// show next quiz if available
	var playAgainHandler = function(){
		if(!SaySo.Quiz.isComplete){
			SaySo.hideForm();
			SaySo.Quiz.next();	
		} else {
			if(!SaySo.formIsShowing){
				SaySo.showForm();
			}
		}
	};
	
	// jiggle every 5 seconds
	var attentionStep = 3000;
	
	// reference to attention interval
	var attentionTimer;
	
	// perform the jiggle effect
	var performJiggle = function(){
		var jiggle = new Jiggle(SaySo.elements.openIcon);
		jiggle.execute();
	};
	
	// hide the entire widget, either via 'Hide' tooltip or 'Not Now' link
	var hideWidget = function(duration){
		window.clearInterval(attentionTimer);
		attentionTimer = window.setInterval(performJiggle, attentionStep);
		SaySo.measure('container', 'logo');
		SaySo.elements.open.animate({
			'bottom' : 0
		}, duration);
		// slide to negative container height + 25 pixels (amount logo peeks out of top)
		SaySo.elements.container.animate({
			'bottom' : -(SaySo.dimensions.container + 25) + 'px'
		}, duration);
		SaySo.setCookie('sayso-snakkle-widget-hidden', true);
	};
	
	// restore a previously closed widget instance
	var showWidget = function(){
		window.clearInterval(attentionTimer);
		SaySo.elements.open.animate({
			'bottom' : '-100px'
		});
		SaySo.elements.container.animate({
			'bottom' : '0px'
		});
		SaySo.setCookie('sayso-snakkle-widget-hidden', null, -10);
	};
	
	// class to perform jiggle
	var Jiggle = function(element){
		this.element = element;
		this.execute = $.proxy(this.execute, this);
		this.stop = $.proxy(this.stop, this);
		this.element.bind('mouseover', this.stop);
	};
	Jiggle.prototype.element = null;
	Jiggle.prototype.distance = 4;
	Jiggle.prototype.interval = 50;
	Jiggle.prototype.count = 8;
	Jiggle.prototype.timer = null;
	Jiggle.prototype.stop = function(){
		window.clearTimeout(this.timer);
		this.element.css({
			'left' : '',
			'top' : ''
		});
		this.element.unbind('mouseover', this.stop);
	};
	Jiggle.prototype.start = function(count){
		this.count = count;
		this.execute();
	};
	Jiggle.prototype.execute = function(){
		// use try/catch since :hover is not supported by jQuery naturally and will throw an error in LTE IE7
		try {
			if(this.element.is(':hover')){
				return;
			}
		} catch(e){};
		if(this.count > 0){
			this.count = this.count - 1;
			var direction = (this.count & 1 * 2 - 1);
			var x = Math.floor((direction * this.count));
			var y = -Math.floor(Math.random() * this.count);
			this.element.css({
				'left' : x + 'px',
				'top' : y + 'px'
			});
			this.timer = window.setTimeout(this.execute, this.interval);
		} else {
			this.stop();
		}
	};
	
	
	
	// show the tooltip with timer to dismiss
	var showTooltip = function(){
		SaySo.elements.tooltip.fadeIn();
	};
	var hideTooltip = function(){
		SaySo.elements.tooltip.fadeOut();
	};
	
	// animate the opener-logo on hover
	var pushUpLogo = function(){
		SaySo.elements.openIcon.animate({
			top : '-8px'
		}, 150);
	};
	var pushDownLogo = function(){
		SaySo.elements.openIcon.animate({
			top : 0
		}, 250);
	};
	
	// show signup form
	var showSignup = function(){
		$('.sayso-signup-layer').hide();
		$('.sayso-signup-input').show();			
	};
	
	var onGrabIt = function() {
		var errMsg;
		if(!(errMsg = validateFields())) {
			var ajaxData = {
					client_name : 'snakkle',
					install_origination : 'p-1',
					user_agent_supported : true,
					install_url : document.location.href,
					location_token : SaySo.locationCookie,
					referrer : document.referrer,
					user_email: SaySo.elements.email.val(),
					user_password: SaySo.elements.password.val(),
					renderer: 'jsonp'
				};
			$.ajax( {
				url: '//' + SaySo.baseDomain + '/starbar/install/user-password',
				dataType : 'jsonp',
				data : ajaxData,
				success: onPasswordResponse
			} )
		} else
			showWarning(errMsg);
	}
	
	var onPasswordResponse = function( response ) {
		if (response.status && response.status == "error") {
			showWarning(response.data.message);
		} else {
			if( !document.location.href.match('sayso-installing') )
				document.location.hash = 'sayso-installing';
			location.href = '//' + SaySo.baseDomain + '/starbar/install/extension?install_token=' + response.data.install_token;
			showSuccess();
		}
	}
	
	// on succesful login creation
	var showSuccess = function(){
		$('.sayso-signup-layer').hide();
		$('.sayso-signup-success').show();
	};
	
	// is password verified?
	var comparePasswords = function(){
		var original = SaySo.elements.password.val();
		var verification = SaySo.elements.verification.val();
		return (original && verification && (original == verification));
	};
	
	// if password matches verification, activate button
	var activateSubmission = function(){
		if( !validateFields() )
			SaySo.elements.grabit.addClass('sayso-active');
		else
			SaySo.elements.grabit.removeClass('sayso-active');
		hideWarning();
	};


	function validateFields() {
		var emailadd = SaySo.elements.email.val();
		if( emailadd.length < 1 ) {
			return "Woops - Please enter your email address";
			var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
			if (!emailPattern.test(emailadd))
				return "Woops - Please enter a valid email address";
		}

		var pwd1 = SaySo.elements.password.val();
		if( pwd1.length < 1 )
			return "Woops - Please enter your password";

		var pwd2 = SaySo.elements.verification.val();
		if( pwd1 != pwd2 )
			return "Woops - Your passwords do not match.<br>Please reenter your password";
		if( pwd1.length < 6 || pwd1.length > 12 )
			return "Woops - Your password needs to have between 6 and 12 characters.<br>Please reenter your password";
			
		return false;
	}

	// if password doesn't match verification and they leave the input, show the warning
	var showWarning = function( msg ){
		SaySo.elements.warning.html(msg).fadeIn('slow');
	};	
	var hideWarning = function(){
		SaySo.elements.warning.fadeOut('slow');
	};
	
	// faux-synchronous preload
	var preload = function(){
		for(var i = 0; i < arguments.length; i++){
			var url = arguments[i];
			var image = new Image();
			image.src = url;
		}
	};
	
	
	// cache progress bar graphics
	preload(
		SaySo.baseImgUrl + 'progress-meter-sprite-blue.png',
		SaySo.baseImgUrl + 'progress-meter-sprite-red.png',
		SaySo.baseImgUrl + 'progress-meter-sprite-green.png',
		SaySo.baseImgUrl + 'grabit-sprite.png',
		SaySo.baseImgUrl + 'save-your-points.png'
	);
	
	/* SUGAR */

	// convenience method for creating new elements, accepts selector e.g., div#id.class1.class2
	$.createElement = function(selector){
		if(/^</.test(selector)){  // if passing an html string, just wrap that and return
			return $(selector);
		}
		var tag = selector.match(/^\w+/)[0];
		var dom = document.createElement(tag);
		var element = $(dom);
		var pattern = /\.([\w|-]+)/g;
		var classes = pattern.exec(selector);
		while(classes != null){
		   element.addClass(classes[1]);
		   classes = pattern.exec(selector);
		}
		pattern = /#([\w|-]+)/;
		var id = pattern.exec(selector);
		if(id != null){
			element.attr('id', id[1]);
		}
		return element;
	};
	
	// sugar for direct append to instances
	$.fn.createElement = function(selector){
		var element = $.createElement(selector);
		this.append(element);
		return element;
	};
	
	// fires after the quiz image has been loaded, and will reveal the UI
	SaySo.on.image = function(){
		
		// on page load, top layer is hidden because we can't count on opacity
		SaySo.elements.upTitle.css({
			'visibility' : 'visible',
			'opacity' : 0
		});
		
		// once we have a top position, use that for animation and devalue bottom
		SaySo.measure('quiz', 'actionBar');
		SaySo.elements.logo.css({
			'bottom' : '',
			'top' : (SaySo.dimensions.quiz + Math.round(SaySo.dimensions.actionBar / 2 - 12)) + 'px'
		});		
		
		// now that we have dimensions, swap visiblity with opacty and fade in
		SaySo.elements.widget.css({
			'visibility' : 'visible',
			'opacity' : 0
		});
		SaySo.elements.widget.animate({
			'opacity' : 1
		});
		
	};
	
	// fires after quiz json is received and parsed
	SaySo.on.json = function(json){
				
		// assign quiz data to ns
		SaySo.Quiz.quizzes = json;
		
		// preload and listen for the first image so we have accurate dimensions for animation
		var url = json[0].image;
		var image = new Image();
		image.src = url;
		var listener = $(image);
		listener.bind('load', SaySo.on.image);
		
		// preload the rest of the images now
		for(var i = 1; i < json.length; i++){
			preload(json[i].image);
		};
		
		// write quiz elements to DOM immediately
		SaySo.Quiz.next();
		
	};
	
	// fires after markup is loaded
	SaySo.on.html = function(html){
		
		// write to DOM
		var dom = $(html);
		$(document.body).append(dom);		
		
		// cache element references
		var widget = SaySo.elements.widget = $('#sayso-widget');
		SaySo.elements.container = widget.find('.sayso-container');
		SaySo.elements.content = widget.find('.sayso-content');
		SaySo.elements.actionBar = widget.find('.sayso-action-bar');
		SaySo.elements.signup = widget.find('.sayso-signup');
		SaySo.elements.quiz = widget.find('.sayso-quiz');
		SaySo.elements.logo = widget.find('.sayso-logo');
		SaySo.elements.upTitle = widget.find('.sayso-action-up');
		SaySo.elements.downTitle = widget.find('.sayso-action-down');
		SaySo.elements.portrait = widget.find('.sayso-portrait');
		SaySo.elements.userInput = widget.find('.sayso-user-input');
		SaySo.elements.feedback = widget.find('.sayso-feedback');
		SaySo.elements.quizTitle = widget.find('.sayso-feedback-quiz-title');
		SaySo.elements.report = widget.find('.sayso-feedback-report');
		SaySo.elements.correct = widget.find(".sayso-feedback-report-correct");
		SaySo.elements.incorrect = widget.find(".sayso-feedback-report-incorrect");
		SaySo.elements.points = widget.find('.sayso-save-points');
		SaySo.elements.options = widget.find('.sayso-quiz-options');
		SaySo.elements.hint = widget.find('.sayso-hint');
		SaySo.elements.grabit = widget.find('.sayso-grabit');
		SaySo.elements.tooltip = widget.find('.sayso-hide-tooltip');
		SaySo.elements.warning = widget.find('.sayso-error');
		SaySo.elements.open = widget.find('.sayso-open-widget');
		SaySo.elements.openIcon = widget.find('.sayso-open-widget > img');
		SaySo.elements.email = widget.find('input[name="sayso-input-email"]');
		SaySo.elements.password = widget.find('input[name="sayso-input-password"]');
		SaySo.elements.verification = widget.find('input[name="sayso-input-confirmation"]');
		
		// assign event handlers
		SaySo.elements.actionBar.click(showFormHandler);
		SaySo.elements.email.bind('keyup change', activateSubmission);
		SaySo.elements.password.bind('keyup change', activateSubmission);
		SaySo.elements.verification.bind('keyup change', activateSubmission);
		SaySo.elements.open.click(showWidget);
		SaySo.elements.openIcon.hover(pushUpLogo, pushDownLogo);
		SaySo.elements.grabit.click(onGrabIt);
		
		// one-off element references
		$('.sayso-hide-reveal').hover(showTooltip, hideTooltip).click(hideWidget);
		$('.sayso-close').click(hideWidget);
		$('.sayso-save-points').click(showFormHandler);
		$('.sayso-play-again').click(playAgainHandler);
		$('input[name="sayso-agree"]').click(showSignup);
		
		$('.sayso-text-input').each( function() {
			var pwd = $(this);
			var txt = widget.find('input[name="'+pwd.attr('name')+'-txt"]');
			if( txt.length ) {
				pwd.hide()
				txt.focus( function() {
					txt.hide();
					pwd.show();
					pwd.focus();
				});
				pwd.blur( function() {
					if( pwd.val() == '' ) {
						pwd.hide();
						txt.show();
					}
				});
			}
		});

		if( SaySo.getCookie('sayso-snakkle-widget-hidden') )
			hideWidget(0);
	};

	// fires after user has taken all available quizzes
	SaySo.on.complete = showFormHandler;	
	
	$.ajax(SaySo.baseUrl + 'js/widget-data.js', { cache: true, dataType: 'script', success: function() {
		SaySo.on.html(SaySo.data.html);
		SaySo.on.json(SaySo.data.json);
	}});
			
});