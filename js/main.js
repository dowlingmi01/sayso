var storeLocalData = {
  
  config : {
    dataSelector : null,
    fieldsToWatch: null
  },
  
  studyInfo : {
    
  },
  
  init : function( config ){
    
    if ( config && typeof( config ) === 'object' ) {
      $.extend(this.config, config);
    }
    
    this.bindTheDom();    
    
  },
  
  bindTheDom : function() {
    
    var that = this;
    
    $(this.config.fieldsToWatch).change(function(){
      
      var dataKey = $(this).attr(that.config.dataSelector),
          dataValue = $(this).val();
          
      that.writeData( dataKey, dataValue );
      
    });
    
  },
  
  writeData : function( key, value ){
    
    // go ahead and split up our key we're receiving,
    // then store our length
    var allKeys = key.split("-"),
        keyLength = allKeys.length,
        stringifiedJson,
        container = this.studyInfo;
    
    for ( var i = 0; i < keyLength - 1; i++ ) {
      
      // store the key we're currently working with      
      var currentKey = allKeys[i];
      
      // does our object container have the current key as a key? if so, then leave it
      // alone. if not, add it. and make sure to re-set our container variable so on
      // the next iteration, we're looking within the current container.
      container = container.hasOwnProperty( currentKey ) ? 
                  container[currentKey] :
                  container[currentKey] = {};
      
    }
    
    // set our object container's last item to the value that was passed
    container[allKeys[ keyLength - 1 ]] = value;    
    
    // stringify the JSON, then insert it into local storage
    stringifiedJson = JSON.stringify(this.studyInfo[allKeys[0]]);
    localStorage[allKeys[0]] = stringifiedJson;
        
  }
  
};

// is local storage available? if so, initialize it.
if ( Modernizr.localstorage ) {
  
  storeLocalData.init({
    dataSelector: 'data-store-key',
    fieldsToWatch: 'input, textarea, select'
  });
  
} else {
  
  alert('Hey! Update your browser.');
  
}

// does this browser support input type of date? if not, load up a jquery UI solution
yepnope({
  test : Modernizr.inputtypes && Modernizr.inputtypes.date,
  nope : [
    'js/jquery-ui-1.8.12.custom.min.js',
    'css/jquery-ui-css/jquery-ui-1.8.12.custom.css',
    'js/datepicker.js'
  ]
});

$('#do-ze-build').click(function(e) {
  
  e.preventDefault();
  
  console.log(JSON.parse(localStorage.sayso));
  
});

$(document).ready(function(){
  var keys = [],
      clear = '67,76,69,65,82';
  
	$(document).keydown(function(e) {
	  keys.push( e.keyCode );
	  
	  if( keys.toString().indexOf( clear ) >= 0 ){
	    
	    
	    localStorage.removeItem('sayso');
	    keys = [];
	    
	  }
	});
});