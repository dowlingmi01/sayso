(function () {
	var baseDomain = 'app-dev.saysollc.com';
	var environment = 'DEV';

	var starbarId = appAPI.db.get('starbarId') || 0;
	var userId = appAPI.db.get('userId') || 0;
	var userKey = appAPI.db.get('userKey') || '';
	var authKey = appAPI.db.get('authKey') || '';
	var visibleState = appAPI.db.get('visibleState') || "open";
	var notificationsState = appAPI.db.get('notificationsState') || 'ready';
	var profileState = appAPI.db.get('profileState') || 'ready';
	var gameState = appAPI.db.get('gameState') || 'ready';
	var windowWidth = appAPI.db.get('windowWidth') || 1000;
	var windowHeight = appAPI.db.get('windowHeight') || 1000;

	var flags = appAPI.db.get('flags') || 'none';
	var studies = appAPI.db.get('studies') || '';
	var studiesTimestamp = appAPI.db.get('studiesTimestamp') || '';
	var adTargets = appAPI.db.get('adTargets') || '{}';


	if (!window.sayso) window.sayso = {};
	window.sayso.debug = true;
	window.sayso.baseDomain = baseDomain;
	window.sayso.environment = environment;
	window.sayso.flags = flags;
	window.sayso.starbar = {
		id : starbarId,
		authKey : authKey,
		user : {
			id : userId,
			key : userKey
		},
		state : {
			visibility : visibleState,
			notifications : notificationsState,
			profile : profileState,
			game : gameState
		},
		context : {
			windowWidth : windowWidth,
			windowHeight : windowHeight
		},
		loaded : false
	};

	window.sayso.study = {
		studies : studies,
		studiesTimestamp : studiesTimestamp,
		adTargets : adTargets
	};

	window.sayso.installed = true;
})();
