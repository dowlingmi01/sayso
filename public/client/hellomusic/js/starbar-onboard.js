/**

Onboarding

**/

// load after slight delay
setTimeout(function(){
	var elemPage = $S('#sayso-onboard');

	elemPage.height($S(window).height());
	elemPage.width($S(window).width());

}, 200); // slight delay to ensure other libraries are loaded

