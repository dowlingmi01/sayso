/**
 * STORE ALL LOCAL DATA
 *
 * This object is the star of our show, handling all of our data storage internally while
 * writing to our browser's local storage.
 *
 */

saySo.storeLocalData = {
  
  config : {
    dataSelector : null,
    fieldsToWatch: null
  },
  
  // an empty object that we're going to temporarily write to with each DOM change so we
  // can stringify the whole thing and stick it in local storage
  studyInfo : {
    
  },
  
  init : function( config ){
    
    if ( config && typeof( config ) === 'object' ) {
      $.extend(this.config, config);
    }
    
    this.bindTheDom();    
    
  },
  
  bindTheDom : function() {
    
    // alias this so we can refer to it in our upcoming function
    var that = this;        
    
    $(this.config.fieldsToWatch).change(function(){
      
      // get the value we've updated to and the selector we're going to use as a key
      var dataKey = $(this).attr(that.config.dataSelector),
          dataValue = $(this).val();
      
      that.updateData( dataKey, dataValue );
      
    });
    
  },
  
  updateData : function( key, value ){
    
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
      container = Object.hasOwnProperty.call( container, currentKey ) ? 
                  container[currentKey] :
                  container[currentKey] = {};
      
    }    
    
    if( value ){
    
      // set our object container's last item to the value that was passed
      container[allKeys[ keyLength - 1 ]] = value;    
  
      // stringify the JSON, then insert it into local storage
      stringifiedJson = JSON.stringify(this.studyInfo[allKeys[0]]);
      localStorage[allKeys[0]] = stringifiedJson;            
    
    } else {
      
      return container;
      
    }
        
  }
  
};

/**
 * HANDLE DATA INTERACTIONS
 *
 * I'm counting each section.main-criteria as a sort of widget with duplicated
 * functionality. This object handles the interaction with said widgets, mainly covering
 * the building of templates and binding publishers and subscriptions within the DOM.
 *
 */

saySo.dataInteractions = {
  
  bindInteractions : function(){
    
    // ugh, this is all heavily dom-dependent, but i can't think of a way to do it better
    // handle interactions where a criteria is being added
    var that = this;
    
    $('button.add-fieldset-data').click(function( e ){
      
      e.preventDefault();
      
      var $this = $(this),
          relatedFieldsetSelector = $this.attr( 'data-for-fieldset' ),
          relatedListSelector = $this.attr( 'data-for-list' ),
          cellKey = $this.attr( 'data-store-key' ),
          $fieldsetFriend = $('#' + relatedFieldsetSelector),
          $listFriend = $('#' + relatedListSelector),
          cellData = saySo.storeLocalData.updateData( cellKey );
      
      that.refreshFieldset( $fieldsetFriend );
      that.addDomDataPoint( $listFriend, cellData );
      
    });
    
    $('a.delete').click(function( e ){
      
      e.preventDefault();
      
      that.removeDomDataPoint( $(this) );
      
    });
    
  },
  
  refreshFieldset : function( $fieldset ){    
        
    var templateName = $fieldset.attr( 'data-template' ),
        parsedTemplateName = this.parseTemplateName( templateName ),
        emptyFieldset = saySo.templates.goBuildMeATemplate( saySo.templates[parsedTemplateName] ),
        $emptyFieldset = $(emptyFieldset),
        $prevEl = $fieldset.prev();
    
    $fieldset.animate({
      opacity : 0
    }, function(){
      $fieldset.remove();
      
      $emptyFieldset.css({
        opacity : 0
      });
      
      $emptyFieldset.insertAfter($prevEl);            
      
      $emptyFieldset.animate({
        opacity : 1
      });
    });    
    
  },
  
  parseTemplateName : function( templateName ){
    
    var templateWords = templateName.split("-");
    
    for ( w in templateWords ) {
      if ( parseInt(w, 10) !== 0 ) {
        templateWords[w] = templateWords[w].charAt(0).toUpperCase() + templateWords[w].slice(1);
      }            
      
    }
    
    return templateWords.join("");
    
  },
  
  addDomDataPoint : function( $list, cellData ){
    
    var templateName = $list.attr('data-template'),
        parsedTemplateName = this.parseTemplateName( templateName ),
        newHtml = saySo.templates.goBuildMeATemplate( saySo.templates[parsedTemplateName], cellData );
        
    $(newHtml).appendTo($list);
    
  },
  
  removeDomDataPoint : function( $clickedEl ){
    
    $clickedEl.closest('li').fadeOut(function(){ 
      $(this).remove(); 
    });
    
    // actually remove the data
    
  }
  
};

// does this browser support input type of date? if not, load up a jquery UI solution
yepnope({
  test : Modernizr.inputtypes && Modernizr.inputtypes.date,
  nope : [
    'js/jquery-ui-1.8.12.custom.min.js',
    'css/jquery-ui-css/jquery-ui-1.8.12.custom.css',
    'js/datepicker.js'
  ]
});

/**
 * IMPROVE THIS
 *
 * This is just some naked DOM interaction that ought to be improved.
 */

$('nav.lock').delegate( 'a.minimize, a.maximize', 'click', function(e) {

  e.preventDefault();
  
  var $this = $(this),
      $section = $this.closest('section.main-criteria'),
      $header = $section.find('header'),
      $container = $section.find('.section-container'),
      type = ( $this.hasClass( 'minimize' )) ? "minimize" : "maximize";        
       
  $header.animate({    
    marginBottom : ( type === "minimize" ) ? "0" : "15px"
  });
  
  if( type === "minimize" ) {
    
    $this.text('+')
         .removeClass('minimize')
         .addClass('maximize');
  
    $container.slideUp();
  
  } else {
    
    $this.text('-')
         .removeClass('maximize')
         .addClass('minimize');
  
    $container.slideDown();
    
  }
         
});

$(document).ready(function(){
  
  // is local storage available? if so, initialize it.
  if ( Modernizr.localstorage ) {

    saySo.storeLocalData.init({
      dataSelector: 'data-store-key',
      fieldsToWatch: 'input, textarea, select'
    });

  } else {

    alert('Hey! Update your browser.');

  }
  
  saySo.dataInteractions.bindInteractions();
  
  /** DEVELOPMENT ONLY 
   *
   * Bind our submit function to instead display our JSON on the console, and bind the key
   * sequence of "clear" to empty out our local storage object.
   */

  $('#do-ze-build').click(function(e) {

    e.preventDefault();

    console.log(JSON.parse(localStorage.sayso));

  });
  
  var keys = [],
      clear = '67,76,69,65,82';
  
	$(document).keydown(function(e) {
	  keys.push( e.keyCode );
	  
	  if( keys.toString().indexOf( clear ) >= 0 ){
	    	    
	    localStorage.removeItem('sayso');
	    keys = [];
	    
	    console.log("Console cleared.");
	    
	  }
	});
});