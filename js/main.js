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
  quotas : "<li class='completed-parameter'>{{type}} {{percent}}% {{ethnicity}} {{gender}}s, {{age}} <a href='#' class='delete' data-store-key='sayso-cells-n{{cellNumber}}-quota-{{thisCounter}}'>Delete</a></li>",

  // list of browsing qualifiers
  browsingQualifiers : "<li class='completed-parameter'>{{include}} visits to {{site}} in the last {{timeframe}} <a href='#' class='delete' data-store-key='sayso-cells-n{{cellNumber}}-qualifier-browse-{{thisCounter}}'>Delete</a></li>",

  // list of search qualifiers
  searchQualifiers : "<li class='completed-parameter'>{{include}} searches for \"{{term}}\" on {{#which}}{{#bing}}{{bing}}, {{/bing}}{{#google}}{{google}}, {{/google}}{{#yahoo}}{{yahoo}}, {{/yahoo}}{{/which}} in the last {{timeframe}} <a href='#' class='delete' data-store-key='sayso-cells-n{{cellNumber}}-qualifier-search-{{thisCounter}}'>Delete</a></li>",

  // list of delivery criteria
  deliveryCriteria : "<li class='completed-parameter'>{{domain}} within {{timeframe}} <a href='#' class='delete' data-store-key='sayso-surveyinfo-deliverIf-{{thisCounter}}'>Delete</a></li>",

  // list of domains for tag-domain-pairs
  domains : '<li class="completed-parameter">\
    {{name}}\
    <a href="#" data-store-key="sayso-tagdomain-{{tagDomainNumber}}-domain-{{thisCounter}}" class="delete">Delete</a>\
  </li>',

  // list of tag-domain pairs
  tagDomainPairs : '<li>\
    {{label}}\
    <nav>\
      <a href="#" class="edit">Edit</a>\
      <a href="#" class="delete">Delete</a>\
    </nav>\
  </li>',

  // fieldset for adding a quota
  quotaFieldset : '<fieldset id="fieldset-cell-quota"\
            data-template="quota-fieldset"\
            data-counter="{{nextCounter}}">\
    <ul class="cell-parameters">\
      <li class="padded-column-10 first">\
        <ul>\
          <li class="quota-select first">\
            <label for="cell-gender">M/F</label>\
            <select name="cell-gender" id="cell-gender"\
                    data-store-key="sayso-cells-n{{cellNumber}}-quota-{{nextCounter}}-gender">\
              <option value="" selected>Choose</option>\
              <option value="Male">Male</option>\
              <option value="Female">Female</option>\
            </select>\
          </li>\
          <li class="quota-select">\
            <label for="cell-age">Age</label>\
            <select name="cell-age" id="cell-age"\
                    data-store-key="sayso-cells-n{{cellNumber}}-quota-{{nextCounter}}-age">\
              <option value="" selected>Choose</option>\
              <option value="13-17">13-17</option>\
              <option value="18-24">18-24</option>\
              <option value="25-34">25-34</option>\
              <option value="35-44">35-44</option>\
              <option value="45-54">45-54</option>\
              <option value="55-64">55-64</option>\
              <option value="65+">65+</option>\
              <option value="18+">18+</option>\
              <option value="18-49">18-49</option>\
            </select>\
          </li>\
          <li class="quota-select">\
            <label for="cell-size-percent">Cell %</label>\
            <select name="cell-size-percent" id="cell-size-percent"\
                    data-store-key="sayso-cells-n{{cellNumber}}-quota-{{nextCounter}}-percent">\
              <option value="" selected>Choose</option>\
              <option value="25">25%</option>\
              <option value="50">50%</option>\
              <option value="75">75%</option>\
              <option value="100">100%</option>\
            </select>\
          </li>\
          <li class="quota-select">\
            <label for="cell-ethnicity">Ethnicity</label>\
            <select name="cell-ethnicity" id="cell-ethnicity"\
                    data-store-key="sayso-cells-n{{cellNumber}}-quota-{{nextCounter}}-ethnicity">\
              <option value="" selected>Choose</option>\
              <option value="All">All</option>\
              <option value="White">White</option>\
              <option value="African American">African American</option>\
              <option value="Asian">Asian</option>\
              <option value="Latino">Latino</option>\
              <option value="Native American">Native American</option>\
              <option value="Hawaiian or Pacific Islander">Hawaiian or Pacific Islander</option>\
            </select>\
          </li>\
        </ul>\
      </li>\
    </ul>\
  </fieldset>',

  // fieldset for adding search qualifiers
  searchFieldset : '<fieldset id="fieldset-search-qualifier"\
            data-template="search-fieldset"\
            data-counter="{{nextCounter}}">\
    <ul>\
      <li>\
        <label for="engine-include-exclude"></label>\
        <select name="engine-include-exclude" id="engine-include-exclude"\
                data-store-key="sayso-cells-n{{cellNumber}}-qualifier-search-{{nextCounter}}-include">\
          <option value="" selected>Include/Exclude</option>\
          <option value="Include">Include panelists</option>\
          <option value="Exclude">Exclude panelists</option>\
        </select>\
      </li>\
      <li>\
        <label for="engine-domain-name">who have searched for</label>\
        <input type="text" name="engine-domain-name" id="engine-domain-name"\
               data-store-key="sayso-cells-n{{cellNumber}}-qualifier-search-{{nextCounter}}-term">\
      </li>\
      <li>\
        <label>on</label>\
        <div>\
          <input type="checkbox" name="engine-bing" id="engine-bing"\
                 value="Bing" data-store-key="sayso-cells-n{{cellNumber}}-qualifier-search-{{nextCounter}}-which-bing">\
          <label for="engine-bing">Bing</label>\
        </div>\
        <div>\
          <input type="checkbox" name="engine-google" id="engine-google"\
                 value="Google" data-store-key="sayso-cells-n{{cellNumber}}-qualifier-search-{{nextCounter}}-which-google">\
          <label for="engine-google">Google</label>\
        </div>\
        <div>\
          <input type="checkbox" name="engine-yahoo" id="engine-yahoo"\
                 value="Yahoo!" data-store-key="sayso-cells-n{{cellNumber}}-qualifier-search-{{nextCounter}}-which-yahoo">\
          <label for="engine-yahoo">Yahoo!</label>\
        </div>\
      </li>\
      <li>\
        <label for="engine-timeframe">in the last</label>\
        <select name="engine-timeframe" id="engine-timeframe"\
                data-store-key="sayso-cells-n{{cellNumber}}-qualifier-search-{{nextCounter}}-timeframe">\
          <option value="" selected>Timeframe</option>\
          <option value="1 day">1 day</option>\
          <option value="1 week">1 week</option>\
          <option value="1 month">1 month</option>\
        </select>\
      </li>\
    </ul>\
  </fieldset>',

  // fieldset for adding online browsing qualifiers
  browseFieldset : '<fieldset id="fieldset-browsing-qualifier"\
            data-template="browse-fieldset"\
            data-counter="{{nextCounter}}">\
    <ul>\
      <li>\
        <select name="include-exclude" id="browsing-include-exclude"\
                data-store-key="sayso-cells-n{{cellNumber}}-qualifier-browse-{{nextCounter}}-include">\
          <option value="" selected>Include/Exclude</option>\
          <option value="Include">Include panelists</option>\
          <option value="Exclude">Exclude panelists</option>\
        </select>\
      </li>\
      <li>\
        <label for="browsing-domain-name">who have visited</label>\
        <input type="text" name="browsing-domain-name" id="browsing-domain-name"\
               data-store-key="sayso-cells-n{{cellNumber}}-qualifier-browse-{{nextCounter}}-site">\
      </li>\
      <li>\
        <label for="browsing-timeframe">in the last</label>\
        <select name="browsing-timeframe" id="browsing-timeframe"\
                data-store-key="sayso-cells-n{{cellNumber}}-qualifier-browse-{{nextCounter}}-timeframe">\
          <option value="" selected>Timeframe</option>\
          <option value="1 day">1 day</option>\
          <option value="1 week">1 week</option>\
          <option value="1 month">1 month</option>\
        </select>\
      </li>\
    </ul>\
  </fieldset>',

  // fieldset for adding delivery criteria
  criteriaFieldset : '<fieldset class="under-full" id="fieldset-survey-delivery-criteria"\
            data-template="criteria-fieldset"\
            data-counter="{{nextCounter}}">\
    <legend class="hide-visual">When and where to deliver surveys</legend>\
    <label for="delivery-domain">Deliver survey to those that visit</label>\
    <select name="delivery-domain" id="delivery-domain"\
            data-store-key="sayso-surveyinfo-deliverIf-{{nextCounter}}-domain">\
      <option value="" selected>Choose</option>\
      <option value="Facebook.com">Facebook.com</option>\
      <option value="CNN.com">CNN.com</option>\
      <option value="ESPN.com">ESPN.com</option>\
    </select>\
    <label for="delivery-timeframe">within</label>\
    <select name="delivery-timeframe" id="delivery-timeframe"\
            data-store-key="sayso-surveyinfo-deliverIf-{{nextCounter}}-timeframe">\
      <option value="" selected>Timeframe</option>\
      <option value="1 hour">1 hour</option>\
      <option value="1 day">1 day</option>\
      <option value="1 week">1 week</option>\
      <option value="1 month">1 month</option>\
    </select>\
    <p>of seeing targeted ad(s).</p>\
  </fieldset>',

  domainFieldset : '<fieldset id="fieldset-domains"\
            data-template="domain-fieldset"\
            data-counter="{{nextCounter}}">\
    <input type="text" name="pairs-domains" id="pairs-domains" class="pairs-domains"\
           data-store-key="sayso-tagdomain-{{tagDomainNumber}}-domain-{{nextCounter}}-name">\
  </fieldset>',

  tagDomainFieldset : '<fieldset class="under-full" id="fieldset-tag-domain-pair" data-template="tag-domain-fieldset"\
      data-counter="{{nextCounter}}">\
    <label for="pairs-label">Label It</label>\
    <input type="text" name="pairs-label" id="pairs-label"\
           data-store-key="sayso-tagdomain-{{nextCounter}}-label">\
    <label for="pairs-ad-tag">Paste Ad Tag Here</label>\
    <textarea name="pairs-ad-tag" id="pairs-ad-tag" cols="30" rows="10"\
              data-store-key="sayso-tagdomain-{{nextCounter}}-tag"></textarea>\
    <label for="pairs-domains">Domains:</label>\
    <fieldset id="fieldset-domains" \
              data-template="domain-fieldset"\
              data-counter="1">\
      <input type="text" name="pairs-domains" id="pairs-domains" class="pairs-domains"\
             data-store-key="sayso-tagdomain-{{nextCounter}}-domain-1-name">\
    </fieldset>\
    <button class="add-fieldset-data"\
            data-for-fieldset="fieldset-domains"\
            data-for-list="list-domains"\
            data-store-key="sayso-tagdomain-{{nextCounter}}-domain">Add Domain</button>\
    <ul class="cell-lists empty" id="list-domains"\
        data-template="domains">\
    </ul>\
  </fieldset>',

  cellTableRow : '<tr>\
    <td>n{{number}}</td>\
    <td>{{size}}</td>\
    <td>{{type}}</td>\
    <td class="description">{{description}}</td>\
    <td>\
      <a href="/cell/n{{number}}/view" class="view">View</a>\
      <a href="/cell/n{{number}}/edit" class="edit">Edit</a>\
      <a href="/cell/n{{number}}/delete" class="delete">Delete</a>\
    </td>\
  </tr>',

  dialogCellView : '\
  <div class="wrap">\
    <div class="entry">\
      <label>Cell Description</label>\
      <div class="value">{{description}}</div>\
    </div>\
    <div class="entry">\
      <label>Type of Cell</label>\
      <div class="value">{{type}}</div>\
    </div>\
    <div class="entry">\
      <label>Cell Size</label>\
      <div class="value">{{size}}</div>\
    </div>\
    <div class="entry">\
      <label>Ad Tags</label>\
      <div class="value">\
        <ul>\
          {{#adTag}}\
            <li>{{.}}</li>\
          {{/adTag}}\
        </ul>\
      </div>\
    </div>\
    <div class="entry">\
      <label>Cell Quotas</label>\
      <div class="value">\
        <ul>\
          {{#quota}}\
            <li>{{percent}}% {{ethnicity}} {{gender}}s, {{age}}</li>\
          {{/quota}}\
        </ul>\
      </div>\
    </div>\
    {{#qualifier}}\
    <div class="entry">\
      <label>Online Browsing</label>\
      <div class="value">\
        <ul>\
          {{#browse}}\
            <li>{{include}} visits to {{site}} in the last {{timeframe}}</li>\
          {{/browse}}\
        </ul>\
      </div>\
    </div>\
    <div class="entry">\
      <label>Search Actions</label>\
      <div class="value">\
        <ul>\
          {{#search}}\
            <li>{{include}} searches for "{{term}}" on {{#which}}{{.}}, {{/which}}in the last {{timeframe}}</li>\
          {{/search}}\
        </ul>\
      </div>\
    </div>\
    <div class="entry">\
      <label>Deliver survey to those that</label>\
      <div class="value">{{condition}}</div>\
    </div>\
    {{/qualifier}}\
  </div>\
  '

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

      // Skip template element
      if ($(this).parents('.template').length) {
        return;
      }

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
      container = container.hasOwnProperty(currentKey) ?
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
      container = container.hasOwnProperty(currentKey) ?
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
    
    $('button.add-fieldset-data').live('click', function(e) {
      
      e.preventDefault();
      
      // when this button is clicked, there's going to be a related list of items written
      // to and a form that's refreshed. we are going to go ahead and cache jquery 
      // objects for both of those.
      var $this = $(this),
          relatedFieldsetSelector = $this.attr( 'data-for-fieldset' ),
          relatedListSelector = $this.attr( 'data-for-list' ),
          cellKey = $this.attr(saySo.storeLocalData.config.dataSelector),
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
    $('.cell-lists .delete').live('click', function(e) {
      e.preventDefault();
      that.removeDomDataPoint($(this));
    });
    
  },
  
  // Replaces old field set with new one
  refreshFieldset : function( $fieldset ){    
    
    // handle our templates    
    var templateName = $fieldset.attr( 'data-template' ),
        parsedTemplateName = this.parseTemplateName( templateName ),
        
    // handle the counter.
        counter = parseInt( $fieldset.attr( 'data-counter' ) ),
        templateData = { nextCounter : counter += 1 };

    switch ($fieldset.attr('id')) {
      // For tag-domain pairs field set
      case 'fieldset-tag-domain-pair':
        // Add ad tag to selector list for cell
        $('#fieldset-cell-adtags .template').parent().append(
          saySo.templates.goBuildMeATemplate(
            $('#fieldset-cell-adtags .template').html(),
            {
              number : $fieldset.attr('data-counter'),
              label : $('#pairs-label').val(),
              cellId : $('#cell-description').attr('data-store-key').match(/-(n\d+)-/)[1]
            }
          )
        );
        break;
      // For domain field set, add tag-domain pair number
      case 'fieldset-domains':
        templateData.tagDomainNumber = $('#fieldset-tag-domain-pair').attr('data-counter');
        break;
    }

    switch (parsedTemplateName) {
      case 'quotas':
      case 'browsingQualifiers':
      case 'searchQualifiers':
      case 'quotaFieldset':
      case 'searchFieldset':
      case 'browseFieldset':
        // Add cell number to template data
        templateData.cellNumber = $('#cell-description').attr('data-store-key').match(/-n(\d+)-/)[1];
        break;
    }

    var emptyFieldset = saySo.templates.goBuildMeATemplate( saySo.templates[parsedTemplateName], templateData ),
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
      if ( parseInt(w) !== 0 ) {
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
        counter = parseInt( $fieldset.attr( 'data-counter' ) ),
        cellsLength = items.length,
        newHtml;
    
    // extend our item with a counter property that we can use to build our deletion
    items[cellsLength - 1].thisCounter = counter;

    switch ($fieldset.attr('id')) {
      // For domain field set, add tag-domain pair number
      case 'fieldset-domains':
        items[cellsLength - 1].tagDomainNumber = $('#fieldset-tag-domain-pair').attr('data-counter');
        break;
    }

    switch (parsedTemplateName) {
      case 'quotas':
      case 'browsingQualifiers':
      case 'searchQualifiers':
      case 'quotaFieldset':
      case 'searchFieldset':
      case 'browseFieldset':
        // Add cell number to template data
        items[cellsLength - 1].cellNumber = $('#cell-description').attr('data-store-key').match(/-n(\d+)-/)[1];
        break;
    }

    newHtml = saySo.templates.goBuildMeATemplate( saySo.templates[parsedTemplateName], items[cellsLength - 1] );
    
    $(newHtml).appendTo($list);
    
    $list.removeClass('empty');
    
  },
  
  // this needs to update the list of qualifiers in the DOM as well as modify the data
  // model to remove the unwanted item
  removeDomDataPoint : function( $clickedEl ){        
    
    // actually remove the data
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
  },

  // Empties the form of cell data and applies cellNumber to relevant fields
  emptyFormOfCellData : function(cellNumber) {
    // Empty quota and qualifier lists
    $('#list-cell-quota, #list-browsing-qualifier, #list-search-qualifier').empty();
    // For each form field relevant to a cell
    $('*[data-store-key^="sayso-cells-n"]').each(function() {
      var dataStoreKeyOld = $(this).attr('data-store-key');
      // Set cell number
      $(this).attr(
        'data-store-key',
        dataStoreKeyOld.replace(/-n\d+-/, '-n' + cellNumber + '-')
      );
      // Reset quota and qualifier indices to 1
      var matches = $(this).attr('data-store-key')
        .match(/^(sayso-cells-n\d+-)(quota|qualifier-(?:browse|search))(-\d+-)(.+)$/);
      if (matches) {
        $(this).attr('data-store-key', matches[1] + matches[2] + '-1-' + matches[4]);
      }
      // Reset field value to default
      switch ($(this).attr('type')) {
        case 'checkbox':
        case 'radio':
          $(this).removeAttr('checked');
          break;
        case 'text':
          $(this).val('');
          break;
        default:
          if ($(this).filter('select').length > 0) {
            $(this).children(':not(:first)').removeAttr('selected');
          }
      }
    });
  },

  // Loads form with identified cell data
  loadFormWithCellData : function(cellId) {
    var cellData = saySo.storeLocalData.studyInfo.sayso.cells[cellId];
    var cellNumber = parseInt(cellId.substr(1));
    this.emptyFormOfCellData(cellNumber);
    // Populate form fields with cell data
    $('#cell-description').val(cellData.description);
    $('input[name="cell-type"]').each(function() {
      if ($(this).attr('value') === cellData.type) {
        $(this).attr('checked', 'checked');
      } else {
        $(this).removeAttr('checked');
      }
    });
    $('#cell-size').val(cellData.size);
    $('input[name^="cell-adtag"]').each(function() {
      if (cellData.adtag && cellData.adtag[$(this).attr('value')]) {
        $(this).attr('checked', 'checked');
      } else {
        $(this).removeAttr('checked');
      }
    });
    // Populate quotas
    var quotaNumber = 1;
    for (var key in cellData.quota) {
      if (!cellData.quota[key].age) {
        continue;
      }
      $('#list-cell-quota').append(
        saySo.templates.goBuildMeATemplate(
          saySo.templates.quotas,
          cellData.quota[key]
        )
      );
      quotaNumber++;
    }
    // Adjust quota number for relevant fields
    $('*[data-store-key^="sayso-cells-n' + cellNumber + '-quota"]').each(function() {
      var dataStoreKeyOld = $(this).attr('data-store-key');
      $(this).attr(
        'data-store-key',
        dataStoreKeyOld.replace(
          /^sayso-cells-n\d+-quota-(\d+)(.+)$/,
          'sayso-cells-n' + cellNumber + '-quota-' + quotaNumber + "$2"
        )
      );
    });
    // Populate online browsing qualifiers
    var qualifierBrowseNumber = 1;
    for (var key in cellData.qualifier.browse) {
      if (!cellData.qualifier.browse[key].include) {
        continue;
      }
      $('#list-browsing-qualifier').append(
        saySo.templates.goBuildMeATemplate(
          saySo.templates.browsingQualifiers,
          cellData.qualifier.browse[key]
        )
      );
      qualifierBrowseNumber++;
    }
    // Adjust online browsing qualifier number for relevant fields
    $('*[data-store-key^="sayso-cells-n' + cellNumber + '-qualifier-browse"]').each(function() {
      var dataStoreKeyOld = $(this).attr('data-store-key');
      $(this).attr(
        'data-store-key',
        dataStoreKeyOld.replace(
          /^sayso-cells-n\d+-qualifier-browse-(\d+)(.+)$/,
          'sayso-cells-n' + cellNumber + '-qualifier-browse-' + qualifierBrowseNumber + "$2"
        )
      );
    });
    // Populate search action qualifiers
    var qualifierSearchNumber = 1;
    for (var key in cellData.qualifier.search) {
      if (!cellData.qualifier.search[key].include) {
        continue;
      }
      $('#list-search-qualifier').append(
        saySo.templates.goBuildMeATemplate(
          saySo.templates.searchQualifiers,
          cellData.qualifier.search[key]
        )
      );
      qualifierSearchNumber++;
    }
    // Adjust search action qualifier number for relevant fields
    $('*[data-store-key^="sayso-cells-n' + cellNumber + '-qualifier-search"]').each(function() {
      var dataStoreKeyOld = $(this).attr('data-store-key');
      $(this).attr(
        'data-store-key',
        dataStoreKeyOld.replace(
          /^sayso-cells-n\d+-qualifier-search-(\d+)(.+)$/,
          'sayso-cells-n' + cellNumber + '-qualifier-search-' + qualifierSearchNumber + "$2"
        )
      );
    });
    // Populate condition qualifier
    $('input[name="deliver-if"]').each(function() {
      if ($(this).attr('value') === cellData.qualifier.condition) {
        $(this).attr('checked', 'checked');
      } else {
        $(this).removeAttr('checked');
      }
    });
  }
  
};

// does this browser support input type of date? if not, load up a jquery UI solution
yepnope([{
  test : Modernizr.inputtypes && Modernizr.inputtypes.date,
  nope : [
    'js/datepicker.js'
  ]
}]);

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

  // When "Clear Local Data" button is clicked, confirm and delete local data, and refresh the page
  $('#clear-local-data').click(function(e) {
    e.preventDefault();
    if (confirm('Are you sure you want to clear local data?')) {
      localStorage.clear();
      location.reload();
    }
  });

  // When "Build Cell" button is clicked
  $('.build-cell').click(function(e) {
    e.preventDefault();
    var cellNumber = $('#cell-description').attr('data-store-key').match(/-n(\d+)-/)[1];
    // Add a row to the cells table
    $('.cell-lists tbody').append(
      saySo.templates.goBuildMeATemplate(
        saySo.templates.cellTableRow,
        {
          number : cellNumber,
          size : $('#cell-size').val(),
          type : $('input[name="cell-type"]:checked').val(),
          description : $('#cell-description').val()
        }
      )
    );
    saySo.dataInteractions.emptyFormOfCellData(parseInt(cellNumber) + 1);
    // Save form data for new fields so that data storage algorithm doesn't fail
    saySo.storeLocalData.initFieldValues();
  });

  // Display survey embed field only when custom is selected
  $('input[name="type-survey"]').change(function() {
    if ($('#type-survey-3').attr('checked')) {
      $('#type-iframe').show();
      $('label[for="type-iframe"]').show();
    } else {
      $('#type-iframe').hide();
      $('label[for="type-iframe"]').hide();
    }
  });

  // When "Reset Input" button is clicked...
  $('.reset-input').click(function(e) {
    e.preventDefault();
    /** @todo Implementation */
  });

  // When "Lock" buttons are clicked...
  $('nav.lock .lock').click(function(e) {
    e.preventDefault();
    /** @todo Implementation */
  });

  // Construct cell "View" dialog
  var $dialogCellView = $('<div id="dialogCellView"></div>')
    .dialog({
      autoOpen: false,
      modal: true,
      resizable: false,
      title: 'View Cell',
      width: 800
    });

  // When cell "View" is clicked
  $('table.cell-lists a.view').live('click', function(e) {
    e.preventDefault();
    var cellId = $(this).attr('href').match(/(n\d+)/)[1];
    var cellData = saySo.storeLocalData.studyInfo.sayso.cells[cellId];
    var templateData = {
      description: cellData.description,
      type: cellData.type,
      size: cellData.size,
      adTag: [],
      quota: [],
      qualifier: {
        browse: [],
        search: [],
        condition: cellData.qualifier.condition
      }
    };
    for (var i in cellData.adtag) {
      templateData.adTag.push(saySo.storeLocalData.studyInfo.sayso.tagdomain[i].label);
    }
    for (var i in cellData.quota) {
      if (!cellData.quota[i].percent) {
        continue;
      }
      templateData.quota.push(cellData.quota[i]);
    }
    for (var i in cellData.qualifier.browse) {
      if (!cellData.qualifier.browse[i].include) {
        continue;
      }
      templateData.qualifier.browse.push(cellData.qualifier.browse[i]);
    }
    for (var i in cellData.qualifier.search) {
      if (!cellData.qualifier.search[i].include) {
        continue;
      }
      var searchData = {
        include: cellData.qualifier.search[i].include,
        term: cellData.qualifier.search[i].term,
        timeframe: cellData.qualifier.search[i].timeframe,
        which: []
      };
      for (var j in cellData.qualifier.search[i].which) {
        searchData.which.push(cellData.qualifier.search[i].which[j]);
      }
      templateData.qualifier.search.push(searchData);
    }
    $dialogCellView
      .html(saySo.templates.goBuildMeATemplate(saySo.templates.dialogCellView, templateData))
      .dialog('open');
  });
  
  // When cell "Edit" is clicked
  $('table.cell-lists a.edit').live('click', function(e) {
    e.preventDefault();
    // Save current cell number
    localStorage.cellNumberToRestore = $('#cell-description').attr('data-store-key').match(/-n(\d+)-/)[1];
    // Load referenced cell data into form
    var cellId = $(this).attr('href').match(/(n\d+)/)[1];
    saySo.dataInteractions.loadFormWithCellData(cellId);
    // Hide "Build Cell" and show "Update Cell"
    $('.build-cell').hide();
    $('.update-cell').show();
  });

  // When "Update Cell" is clicked
  $('.update-cell').click(function(e) {
    e.preventDefault();
    // Get cell number to restore to form fields and remove it from local storage
    var cellNumberToRestore = localStorage.cellNumberToRestore;
    delete localStorage.cellNumberToRestore;
    // Reset form fields, restoring old cell number
    saySo.dataInteractions.emptyFormOfCellData(cellNumberToRestore);
    // Hide "Update Cell" and show "Build Cell"
    $('.update-cell').hide();
    $('.build-cell').show();
  });

});
