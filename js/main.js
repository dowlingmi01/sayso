if(typeof console === "undefined") {
  console = { log: function(){} };
}

// set up a basic namespace for everything we're going to do
var saySo = {};

saySo.templates = {

  // just a specific place to turn to run the mustache
  goBuildMeATemplate : function( tpl, obj ) {

    return Mustache.to_html( tpl, obj );

  },

  // list of quotas
  quotas : "<li class='completed-parameter'>{{description}} ({{type}} {{percent}}% {{ethnicity}} {{gender}}s, {{age}}) <a href='#' class='delete' data-store-key='sayso-cells-n1-quota-{{thisCounter}}'>Delete</a></li>",

  // list of browsing qualifiers
  browsingQualifiers : "<li class='completed-parameter'>{{include}} visits to {{site}} in the last {{timeframe}} <a href='#' class='delete' data-store-key='sayso-cells-n1-qualifier-browse-{{thisCounter}}'>Delete</a></li>",

  // list of search qualifiers
  searchQualifiers : "<li class='completed-parameter'>{{include}} searches for \"{{term}}\" on {{which}} in the last {{timeframe}} <a href='#' class='delete' data-store-key='sayso-cells-n1-qualifier-search-{{thisCounter}}'>Delete</a></li>",

  // list of tag-domain pairs
  tagDomainPairs : "<li class='completed-parameter'>Facebook.com <a href='#' class='delete''>Delete</a></li>",

  // list of delivery criteria
  deliveryCriteria : "<li class='completed-parameter'>{{domain}} within {{timeframe}} <a href='#' class='delete' data-store-key='sayso-surveyinfo-deliverIf-{{thisCounter}}'>Delete</a></li>",

  // list of domains for tag-domain-pairs
  domains : "<li class='completed-parameter'>{{name}} <a href='#' data-store-key='sayso-tagdomain-1-domain-{{thisCounter}}' class='delete' class='delete'>Delete</a></li>",

  // fieldset for adding a quota
  quotaFieldset : "<fieldset id='fieldset-cell-quota'\
            data-template='quota-fieldset'\
            data-counter='{{nextCounter}}'>\
    <legend class='hide-visual'>Enter a description of a response cell</legend>\
    <ul class='cell-parameters'>\
      <li class='padded-column-8 first'>\
        <label for='cell-description'>CELL Description</label>\
        <input type='text' name='cell-description' id='cell-description' \
               data-store-key='sayso-cells-n1-quota-{{nextCounter}}-description'>\
      </li><li class='padded-column-2 last'>\
        <fieldset class='cell-type'>\
          <legend>Type of Cell</legend>\
          <div class='radios-labeled'>\
            <input type='radio' name='cell-type' id='cell-type-1' value='Control' \
                   data-store-key='sayso-cells-n1-quota-{{nextCounter}}-type'>\
            <label for='cell-type-1'>Control</label>\
          </div>\
          <div>\
            <input type='radio' name='cell-type' id='cell-type-2' value='Test'\
                   data-store-key='sayso-cells-n1-quota-{{nextCounter}}-type'>\
            <label for='cell-type-2'>Test</label>\
          </div>\
        </fieldset>\
      </li>\
      <li class='quota-select first'>\
        <label for='cell-size'>Quota Size</label>\
        <select name='cell-size' id='cell-size'\
                data-store-key='sayso-cells-n1-quota-{{nextCounter}}-size'>\
          <option value='1000'>1000</option>\
        </select>\
      </li>\
      <li class='quota-select'>\
        <label for='cell-gender'>M/F</label>\
        <select name='cell-gender' id='cell-gender' \
                data-store-key='sayso-cells-n1-quota-{{nextCounter}}-gender'>\
          <option value='Choose a gender' disabled>Choose</option>\
          <option value='Male'>Male</option>\
          <option value='Female'>Female</option>\
        </select>\
      </li>\
      <li class='quota-select'>\
        <label for='cell-age'>Age</label>\
        <select name='cell-age' id='cell-age' \
                data-store-key='sayso-cells-n1-quota-{{nextCounter}}-age'>\
          <option value='Choose an age' disabled>Choose</option>\
          <option value='0-18'>0-18</option>\
          <option value='19-25'>19-25</option>\
          <option value='26-35'>26-35</option>\
          <option value='36+'>36+</option>\
        </select>\
      </li>\
      <li class='quota-select'>\
        <label for='cell-size-percent'>Cell %</label>\
        <select name='cell-size-percent' id='cell-size-percent' \
                data-store-key='sayso-cells-n1-quota-{{nextCounter}}-percent'>\
          <option value='Choose a percentage' disabled>Choose</option>\
          <option value='25'>25%</option>\
          <option value='50'>50%</option>\
          <option value='75'>75%</option>\
          <option value='100'>100%</option>\
        </select>\
      </li>\
      <li class='quota-select'>\
        <label for='cell-ethnicity'>Ethnicity</label>\
        <select name='cell-ethnicity' id='cell-ethnicity' \
                data-store-key='sayso-cells-n1-quota-{{nextCounter}}-ethnicity'>\
          <option value='Choose an ethnicity' disabled>Choose</option>\
          <option value='All'>All</option>\
          <option value='White'>White</option>\
          <option value='African American'>African American</option>\
          <option value='Asian'>Asian</option>\
          <option value='Latino'>Latino</option>\
          <option value='Native American'>Native American</option>\
          <option value='Hawaiian or Pacific Islander'>Hawaiian or Pacific Islander</option>\
        </select>\
      </li>\
    </ul>\
  </fieldset>",

  // fieldset for adding search qualifiers
  searchFieldset : "<fieldset id='fieldset-search-qualifier' \
      data-template='search-fieldset' \
      data-counter='{{nextCounter}}'>\
    <legend class='hide-visual'>Online search qualifiers</legend>\
    <ul>\
      <li>\
        <label for='engine-include-exclude'></label>\
        <select name='engine-include-exclude' id='engine-include-exclude' \
                data-store-key='sayso-cells-n1-qualifier-search-{{nextCounter}}-include'>\
          <option value='choose' disabled>Include?</option>\
          <option value='Include'>Include users</option>\
          <option value='Exclude'>Exclude users</option>\
        </select>\
      </li>\
      <li>\
        <label for='engine-domain-name'>who have searched for</label>\
        <input type='text' name='engine-domain-name' id='engine-domain-name' \
               data-store-key='sayso-cells-n1-qualifier-search-{{nextCounter}}-term'>\
      </li>\
      <li>\
        <label for='engine-which'>on</label>\
        <select name='engine-which' id='engine-which' \
                data-store-key='sayso-cells-n1-qualifier-search-{{nextCounter}}-which'>\
          <option value='choose' disabled>Search Engine</option>\
          <option value='All'>All</option>\
          <option value='Google'>Google</option>\
          <option value='Bing'>Bing</option>\
          <option value='Yahoo'>Yahoo</option>\
        </select>\
      </li>\
      <li>\
        <label for='engine-timeframe'>in the last</label>\
        <select name='engine-timeframe' id='engine-timeframe' \
                data-store-key='sayso-cells-n1-qualifier-search-{{nextCounter}}-timeframe'>\
          <option value='choose' disabled>Timeframe</option>\
          <option value='1 day'>1 day</option>\
          <option value='1 week'>1 week</option>\
          <option value='1 month'>1 month</option>\
        </select>\
      </li>\
    </ul>\
  </fieldset>",

  // fieldset for adding online browsing qualifiers
  browseFieldset : "<fieldset id='fieldset-browsing-qualifier'\
            data-template='browse-fieldset'\
            data-counter='{{nextCounter}}'>\
    <legend class='hide-visual'>Online browsing qualifiers</legend>\
    <ul>\
      <li>\
        <label for='browsing-include-exclude'></label>\
        <select name='include-exclude' id='browsing-include-exclude' \
                data-store-key='sayso-cells-n1-qualifier-browse-{{nextCounter}}-include'>\
          <option value='Choose' disabled>Include?</option>\
          <option value='Include'>Include users</option>\
          <option value='Exclude'>Exclude users</option>\
        </select>\
      </li>\
      <li>\
        <label for='browsing-domain-name'>who have visited</label>\
        <input type='text' name='browsing-domain-name' id='browsing-domain-name' \
               data-store-key='sayso-cells-n1-qualifier-browse-{{nextCounter}}-site'>\
      </li>\
      <li>\
        <label for='browsing-timeframe'>in the</label>\
        <select name='browsing-timeframe' id='browsing-timeframe' \
                data-store-key='sayso-cells-n1-qualifier-browse-{{nextCounter}}-timeframe'>\
          <option value='Choose' disabled>Timeframe</option>\
          <option value='1 day'>1 day</option>\
          <option value='1 week'>1 week</option>\
          <option value='1 month'>1 month</option>\
        </select>\
      </li>\
    </ul>\
  </fieldset>",

  // fieldset for adding delivery criteria
  criteriaFieldset : "<fieldset class='under-full' id='fieldset-survey-delivery-criteria'\
            data-template='criteria-fieldset'\
            data-counter='{{nextCounter}}'>\
    <legend class='hide-visual'>When and where to deliver surveys</legend>\
    <label for='delivery-domain'>Deliver if a user visits</label>\
    <select name='delivery-domain' id='delivery-domain' \
            data-store-key='sayso-surveyinfo-deliverIf-{{nextCounter}}-domain'>\
      <option value='choose' disabled>Choose</option>\
      <option value='Facebook.com'>Facebook.com</option>\
      <option value='CNN.com'>CNN.com</option>\
      <option value='ESPN.com'>ESPN.com</option>\
    </select>\
    <label for='delivery-timeframe'>within</label>\
    <select name='delivery-timeframe' id='delivery-timeframe' \
            data-store-key='sayso-surveyinfo-deliverIf-{{nextCounter}}-timeframe'>\
      <option value='timeframe' disabled>Timeframe</option>\
      <option value='1 hour'>1 hour</option>\
      <option value='1 day'>1 day</option>\
      <option value='1 week'>1 week</option>\
      <option value='1 month'>1 month</option>\
    </select>\
    <p>of seeing targeted ad(s).</p>\
  </fieldset>",

  domainFieldset : "<fieldset id='fieldset-domains'\
            data-template='domain-fieldset'\
            data-counter='{{nextCounter}}'>\
    <input type='text' name='pairs-domains' id='pairs-domains' class='pairs-domains' \
           data-store-key='sayso-tagdomain-1-domain-{{nextCounter}}-name'>\
  </fieldset>"

};


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

    this
      .initFieldValues()
      .bindFieldChanges();
    
  },
  
  bindFieldChanges : function() {
    
    // Alias this so we can refer to it in our upcoming function
    var that = this;        
    
    $('form').delegate(this.config.fieldsToWatch, 'change', function() {
      
      // Get key-value pair
      var dataKey = $(this).attr(that.config.dataSelector),
          dataValue = $(this).val();

      // Checkbox data should be updated if checked, deleted if not checked
      if ($(this).attr('type') === 'checkbox') {
        if ($(this).attr('checked')) {
          that.updateData(dataKey, dataValue);
        } else {
          that.deleteData(dataKey);
        }
      }
      // Other input types may simply be updated
      else {
        that.updateData(dataKey, dataValue);
      }
      
    });

    // Provide fluent interface
    return this;
    
  },

  // Initializes local data with values from form fields
  initFieldValues : function() {

    // Alias this so we can refer to it in our upcoming function
    var that = this;

    // For each of the form fields from which we get values
    $('form').find(this.config.fieldsToWatch).each(function(){

      // Get key-value pair
      var dataKey = $(this).attr(that.config.dataSelector),
          dataValue = $(this).val();

      // If the data key is unavailable, return
      if (dataKey === undefined) {
        return this;
      }

      // Checkbox data should be updated if checked, deleted if not checked
      if ($(this).attr('type') === 'checkbox') {
        if ($(this).attr('checked')) {
          that.updateData(dataKey, dataValue);
        } else {
          that.deleteData(dataKey);
        }
      }
      // Other input types may simply be updated
      else {
        that.updateData(dataKey, dataValue);
      }

    });

    return this;

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
  
  // Replaces old field set with new one
  refreshFieldset : function( $fieldset ){    
    
    // handle our templates    
    var templateName = $fieldset.attr( 'data-template' ),
        parsedTemplateName = this.parseTemplateName( templateName ),
        
    // handle the counter.
        counter = parseInt( $fieldset.attr( 'data-counter' ) ),
        counterObj = { nextCounter : counter += 1 },
        emptyFieldset = saySo.templates.goBuildMeATemplate( saySo.templates[parsedTemplateName], counterObj ),
        $emptyFieldset = $(emptyFieldset),
        $prevEl = $fieldset.prev();
    
    // just eye candy here. animate the current fieldset out, and animate the new
    // fieldset in.
    $fieldset.animate({ opacity : 0 }, function() {
      
      $fieldset.remove();
      
      $emptyFieldset.css({ opacity : 0 });
      
      $emptyFieldset.insertAfter($prevEl);

      $emptyFieldset.animate({ opacity : 1 }, function() {
        saySo.storeLocalData.initFieldValues();
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
    
    $list.removeClass('empty');
    
  },
  
  // this needs to update the list of qualifiers in the DOM as well as modify the data
  // model to remove the unwanted item
  removeDomDataPoint : function( $clickedEl ){        
    
    // actually remove the data
    // THIS DOES NOT WORK RIGHT NOW
    var cellKey = $clickedEl.attr('data-store-key'),
        response = saySo.storeLocalData.deleteData( cellKey ),
        $parent = $clickedEl.closest('ul');        
    
    // if our deletion is successful, then we'll go ahead and fade out the list indicator
    if( response === true ){
      
      $clickedEl.closest('li').fadeOut(function(){ 
        $(this).remove(); 
      });
      
      if ( $parent.children().length === 0 ) {
        $parent.addClass('empty');
      }
      
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

$(document).ready(function(){
  
  // Minimize/maximize sections
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

  // When "Build My Survey" button is clicked, JSON-decode and dump current localStorage object to console
  $('#do-ze-build').click(function(e) {

    e.preventDefault();

    console.log(JSON.parse(localStorage.sayso));

  });

  // When "Build Cell" button is clicked...
  $('.build-cell').click(function(e) {
    e.preventDefault();
    /** @todo Implementation */
  });

  // When "Reset Input" button is clicked...
  $('.reset-input').click(function(e) {
    e.preventDefault();
    /** @todo Implementation */
  });
  
});