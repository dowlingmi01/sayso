// set up a basic namespace for everything we're going to do
var saySo = {};

saySo.templates = {
  
  goBuildMeATemplate : function( tpl, obj ) {
    
    return Mustache.to_html( tpl, obj );
    
  },
  
  quotas : "{{#quota}}<li class='completed-parameter'>TEST {{percent}}% {{ethnicity}} {{gender}}s, {{age}} <a href='#' class='delete'>Delete</a></li>{{/quota}}",
  
  browsingQualifiers : "{{#browse}}<li class='completed-parameter'>{{include}} {{site}} in the last {{timeframe}} <a href='#' class='delete''>Delete</a></li>{{/browse}}",
  
  searchQualifiers : "{{#search}}<li class='completed-parameter'>{{include}} {{term}} on {{which}} in the last {{timeframe}} <a href='#' class='delete'>Delete</a></li>{{/search}}",
  
  tagDomainPairs : "<li class='completed-parameter'>Facebook.com <a href='#' class='delete''>Delete</a></li>",
  
  deliveryCriteria : "{{#criteria}}<li class='completed-parameter'>{{domain}} within {{timeframe}} <a href='#'>Delete</a></li>{{/criteria}}",
  
  quotaFieldset : "<fieldset id='fieldset-cell-quota'\
            data-template='quota-fieldset'>\
    <legend class='hide-visual'>Enter a description of a response cell</legend>\
    <ul class='cell-parameters'>\
      <li class='padded-column-8 first'>\
        <label for='cell-description'>CELL Description</label>\
        <input type='text' name='cell-description' id='cell-description' \
               data-store-key='sayso-cells-n1-quota-description'>\
      </li><li class='padded-column-2 last'>\
        <fieldset class='cell-type'>\
          <legend>Type of Cell</legend>\
          <div class='radios-labeled'>\
            <input type='radio' name='cell-type' id='cell-type-1' value='Control' \
                   data-store-key='sayso-cells-n1-quota-type'>\
            <label for='cell-type-1'>Control</label>\
          </div>\
          <div>\
            <input type='radio' name='cell-type' id='cell-type-2' value='Test'\
                   data-store-key='sayso-cells-n1-quota-type'>\
            <label for='cell-type-2'>Test</label>\
          </div>\
        </fieldset>\
      </li>\
      <li class='quota-select first'>\
        <label for='cell-size'>Quota Size</label>\
        <select name='cell-size' id='cell-size'\
                data-store-key='sayso-cells-n1-quota-size'>\
          <option value='1000'></option>\
        </select>\
      </li>\
      <li class='quota-select'>\
        <label for='cell-gender'>M/F</label>\
        <select name='cell-gender' id='cell-gender' \
                data-store-key='sayso-cells-n1-quota-gender'>\
          <option value='Choose a gender' disabled>Choose</option>\
          <option value='Male'>Male</option>\
          <option value='Female'>Female</option>\
        </select>\
      </li>\
      <li class='quota-select'>\
        <label for='cell-age'>Age</label>\
        <select name='cell-age' id='cell-age' \
                data-store-key='sayso-cells-n1-quota-age'>\
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
                data-store-key='sayso-cells-n1-quota-percent'>\
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
                data-store-key='sayso-cells-n1-quota-ethnicity'>\
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
  
  searchFieldset : "<fieldset id='fieldset-search-qualifier'>\
    <legend class='hide-visual'>Online search qualifiers</legend>\
    <ul>\
      <li>\
        <label for='browswing-engine-include-exclude'></label>\
        <select name='browsing-engine-include-exclude' id='browsing-engine-include-exclude' \
                data-store-key='sayso-cells-n1-qualifier-search-include'>\
          <option value='choose' disabled>Include?</option>\
          <option value='Include'>Include users</option>\
          <option value='Exclude'>Exclude users</option>\
        </select>\
      </li>\
      <li>\
        <label for='browswing-engine-domain-name'>who have searched for</label>\
        <input type='text' name='browswing-engine-domain-name' id='browswing-engine-domain-name' \
               data-store-key='sayso-cells-n1-qualifier-search-term'>\
      </li>\
      <li>\
        <label for='browsing-engine-which'>On</label>\
        <select name='browsing-engine-which' id='browsing-engine-which' \
                data-store-key='sayso-cells-n1-qualifier-search-which'>\
          <option value='choose'>Search Engine</option>\
          <option value='All'>All</option>\
          <option value='Google'>Google</option>\
          <option value='Bing'>Bing</option>\
          <option value='Yahoo'>Yahoo</option>\
        </select>\
      </li>\
      <li>\
        <label for='browsing-engine-timeframe'>in the</label>\
        <select name='browsing-engine-timeframe' id='browsing-engine-timeframe' \
                data-store-key='sayso-cells-n1-qualifier-search-timeframe'>\
          <option value='choose'>Timeframe</option>\
          <option value='Last 1 day'>Last 1 day</option>\
          <option value='Last 1 week'>Last 1 week</option>\
          <option value='Last 1 month'>Last 1 month</option>\
        </select>\
      </li>\
    </ul>\
  </fieldset>",
  
  browseFieldset : "<fieldset id='fieldset-browsing-qualifier'\
            data-template='browse-fieldset'>\
    <legend class='hide-visual'>Online browsing qualifiers</legend>\
    <ul>\
      <li>\
        <label for='browswing-include-exclude'></label>\
        <select name='include-exclude' id='browsing-include-exclude' \
                data-store-key='sayso-cells-n1-qualifier-browse-include'>\
          <option value='Choose' disabled>Include?</option>\
          <option value='Include'>Include users</option>\
          <option value='Exclude'>Exclude users</option>\
        </select>\
      </li>\
      <li>\
        <label for='browswing-domain-name'>who have visited</label>\
        <input type='text' name='browswing-domain-name' id='browswing-domain-name' \
               data-store-key='sayso-cells-n1-qualifier-browse-site'>\
      </li>\
      <li>\
        <label for='browsing-timeframe'>in the</label>\
        <select name='browsing-timeframe' id='browsing-timeframe' \
                data-store-key='sayso-cells-n1-qualifier-browse-timeframe'>\
          <option value='Choose'>Timeframe</option>\
          <option value='Last 1 day'>Last 1 day</option>\
          <option value='Last 1 week'>Last 1 week</option>\
          <option value='Last 1 month'>Last 1 month</option>\
        </select>\
      </li>\
    </ul>\
  </fieldset>",
  
  criteriaFieldset : "<fieldset class='under-full' id='fieldset-survey-delivery-criteria'\
            data-template='criteria-fieldset'>\
    <legend class='hide-visual'>When and where to deliver surveys</legend>\
    <label for='delivery-domain'>Deliver if a user visits</label>\
    <select name='delivery-domain' id='delivery-domain' \
            data-store-key='sayso-surveyinfo-criteria-domain'>\
      <option value='choose'>Choose</option>\
      <option value='Facebook.com'>Facebook.com</option>\
      <option value='CNN.com'>CNN.com</option>\
      <option value='ESPN.com'>ESPN.com</option>\
    </select>\
    <label for='delivery-timeframe'>within</label>\
    <select name='delivery-timeframe' id='delivery-timeframe' \
            data-store-key='sayso-surveyinfo-criteria-timeframe'>\
      <option value='timeframe''>Timeframe</option>\
      <option value='1 hour'>1 hour</option>\
      <option value='1 day'>1 day</option>\
      <option value='1 week'>1 week</option>\
      <option value='1 month'>1 month</option>\
    </select>\
    <p>of seeing targeted ad(s).</p>\
  </fieldset>"
  
};