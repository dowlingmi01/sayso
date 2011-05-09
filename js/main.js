if(typeof console === "undefined") {
  console = { log: function(){} };
}

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
    
    sayso : {}
    
  },
  
  init : function( config ){
    
    if ( config && typeof( config ) === 'object' ) {
      $.extend( this.config, config );
    }
    
    // on page load, let's go ahead and take our local storage object, parse it out, and
    // store it in the empty studyInfo object to make sure we have the latest
    var localData = (localStorage.sayso) ? JSON.parse(localStorage.sayso) : null;
    
    $.extend( this.studyInfo.sayso, localData );
    
    this.bindTheDom();    
    
  },
  
  bindTheDom : function() {
    
    // alias this so we can refer to it in our upcoming function
    var that = this;        
    
    $('form').delegate( this.config.fieldsToWatch, 'change', function(){
      
      // get the value we've updated to and the selector we're going to use as a key
      var dataKey = $(this).attr(that.config.dataSelector),
          dataValue = $(this).val();
      
      that.updateData( dataKey, dataValue );
      
    });
    
  },
  
  // updateData and deleteData could probably be improved, including an abstraction of
  // the accessor portions of the methods. just trying to get a proof of concept working.
  
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

    // check if a value was passed to this method, and if so, use it as a setter, else
    // act as a getter for a key's value
    if( value ){
    
      // set our object container's last item to the value that was passed, ie allKeys
      // could be foo-bar-baz-name, then we would set our current container's `name`
      // property to our value
      container[allKeys[ keyLength - 1 ]] = value;
  
      // stringify the JSON, then insert it into local storage
      stringifiedJson = JSON.stringify(this.studyInfo[allKeys[0]]);
      localStorage[allKeys[0]] = stringifiedJson;            
    
    } else {
      
      return container[allKeys[ keyLength - 1 ]];
      
    }
    
  },
    
  deleteData : function( key ){
    
    // go ahead and split up our key we're receiving,
    // then store our length
    var allKeys = key.split("-"),
        keyLength = allKeys.length,
        stringifiedJson,
        container = this.studyInfo,
        isSuccessful;

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
    
    isSuccessful = delete container[allKeys[ keyLength - 1 ]];
    
    // stringify the JSON, then insert it into local storage
    stringifiedJson = JSON.stringify(this.studyInfo[allKeys[0]]);
    localStorage[allKeys[0]] = stringifiedJson;
    
    return isSuccessful;        
    
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
  
  // set up our basic DOM bindings for interacting with our data
  bindInteractions : function(){
    
    // alias our current context so we can refer to it within function scopes
    var that = this;
    
    $('button.add-fieldset-data').click(function( e ){
      
      e.preventDefault();
      
      // when this button is clicked, there's going to be a related list of items written
      // to and a form that's refreshed. we are going to go ahead and cache jquery 
      // objects for both of those.
      var $this = $(this),
          relatedFieldsetSelector = $this.attr( 'data-for-fieldset' ),
          relatedListSelector = $this.attr( 'data-for-list' ),
          cellKey = $this.attr( 'data-store-key' ),
          $fieldsetFriend = $('#' + relatedFieldsetSelector),
          $listFriend = $('#' + relatedListSelector),
          cellData = saySo.storeLocalData.updateData( cellKey ),
          items = [];
            
      // we need to push each piece of cellData to an array in order to iterate over it
      // and get the length so we can make sure to just get the last item and stick it
      // in the DOM. whew.      
      for(n in cellData){
        items.push(cellData[n]);        
      }
      
      that.addDomDataPoint( $listFriend, $fieldsetFriend, items );
      that.refreshFieldset( $fieldsetFriend );
            
    });
    
    // when delete is clicked, an indicator should be deleted from the DOM and the data 
    // should be removed from the data object
    $('ul.cell-lists').delegate('.delete', 'click', function( e ){
      
      e.preventDefault();
      
      that.removeDomDataPoint( $(this) );      
    });
    
  },
  
  // this is a pure-dom function. just clear out the fieldset, and load up a new fieldset
  refreshFieldset : function( $fieldset ){    
    
    // handle our templates    
    var templateName = $fieldset.attr( 'data-template' ),
        parsedTemplateName = this.parseTemplateName( templateName ),
        
    // handle the counter.
        counter = parseInt( $fieldset.attr( 'data-counter' ), 10 ),
        counterObj = { nextCounter : counter += 1 },
        emptyFieldset = saySo.templates.goBuildMeATemplate( saySo.templates[parsedTemplateName], counterObj ),
        $emptyFieldset = $(emptyFieldset),
        $prevEl = $fieldset.prev();
    
    // just eye candy here. animate the current fieldset out, and animate the new
    // fieldset in.
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
  
  // convert a CSS hyphen-style naming convention into a javascript camelCase convention
  // so neither of the two seems out of place
  parseTemplateName : function( templateName ){

    var templateWords = templateName.split("-");
    
    for ( w in templateWords ) {
      if ( parseInt(w, 10) !== 0 ) {
        templateWords[w] = templateWords[w].charAt(0).toUpperCase() + templateWords[w].slice(1);
      }            
      
    }
    
    return templateWords.join("");
    
  },
  
  // because the data addition is already handled in saySo.storeLocalData.updateData on a
  // field's change event, all this does is reflect that change in the list of qualifiers
  // in the DOM
  addDomDataPoint : function( $list, $fieldset, items ){    
    
    var templateName = $list.attr('data-template'),
        parsedTemplateName = this.parseTemplateName( templateName ),
        counter = parseInt( $fieldset.attr( 'data-counter' ), 10 ),
        cellsLength = items.length,
        newHtml;
    
    // extend our item with a counter property that we can use to build our deletion
    items[cellsLength - 1].thisCounter = counter;
        
    newHtml = saySo.templates.goBuildMeATemplate( saySo.templates[parsedTemplateName], items[cellsLength - 1] );
    
    $(newHtml).appendTo($list);
    
  },
  
  // this needs to update the list of qualifiers in the DOM as well as modify the data
  // model to remove the unwanted item
  removeDomDataPoint : function( $clickedEl ){        
    
    // actually remove the data
    // THIS DOES NOT WORK RIGHT NOW
    var cellKey = $clickedEl.attr('data-store-key'),
        response = saySo.storeLocalData.deleteData( cellKey );        
    
    // if our deletion is successful, then we'll go ahead and fade out the list indicator
    if( response === true ){
      
      $clickedEl.closest('li').fadeOut(function(){ 
        $(this).remove(); 
      });
      
    } else {
      
      console.log( "Something went wrong. The item wasn't deleted." );
      
    }
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