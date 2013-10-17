sayso.module.metrics = (function(comm, util) {
	var frames = {};
	var topFrames = {};
	var sendInterval = 5000, saveInterval = 1000, updateInterval = 500;
	var getSessionApi;

	function processEvent( data ) {
		data.ts = util.getTime();
		if( data.type === 'page_view' ) {
			frames[data.frameId] = data;
			data.events = [];
			if( data.topFrame ) {
				topFrames[data.frameId] = true;
				data.descendants = {};
				data.lastUpdate = data.ts;
			}
		} else if( data.type === 'frame_link' ) {
			frames[data.parentFrameId].events.push(frames[data.frameId]);
			frames[data.frameId].parentFrameId = data.parentFrameId;
			if(frames[data.frameId].topFrameId) {
				delete frames[frames[data.frameId].topFrameId][data.frameId];
			}
		} else if( data.type === 'top_link' ) {
			if( !frames[data.frameId].parentFrameId)
				frames[data.topFrameId].descendants[data.frameId] = true;
			frames[data.frameId].topFrameId = data.topFrameId;
		} else if( data.type === 'page_unload' )
			frames[data.frameId].unloaded = true;
		else {
			frames[data.frameId].events.push(data);
			delete data.frameId;
		}
	}
	function sendEvents() {
		var events = [];
		for( var frameId in topFrames )
			if( frames[frameId].unloaded ) {
				events.push(frames[frameId]);
				processFrame(frames[frameId]);
			}
		if( events.length )
			getSessionApi().sendRequest( {action_class: 'Metrics', action: 'insertEvents',
				events: {events: events, base_ts: util.getTime()}} );
		setTimeout(sendEvents, sendInterval);
	}
	function saveEvents() {
		comm.set('track', {frames: frames, topFrames: topFrames});
	}
	function processFrame(frame) {
		if( frame.topFrame ) {
			delete topFrames[frame.frameId];
			for( var descendantId in frame.descendants )
				frame.events.push(frames[descendantId]);
			delete frame.descendants;
			delete frame.unloaded;
			delete frame.lastUpdate;
		}
		delete frames[frame.frameId];
		delete frame.topFrameId;
		delete frame.topFrame;
		delete frame.parentFrameId;
		delete frame.frameId;
		if( frame.events.length > 0 ) {
			for( var i = 0; i < frame.events.length; i++ )
				if( frame.events[i].frameId )
					processFrame(frame.events[i]);
		} else
			delete frame.events;
	}
	function startup( apiFun ) {
		getSessionApi = apiFun;
		sendEvents();
	}
	return { processEvent: processEvent, frames: frames, topFrames: topFrames, sendEvents: sendEvents,
		startup: startup };
})(sayso.module.comm, sayso.module.util)
;
