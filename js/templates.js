// set up a basic namespace for everything we're going to do
var saySo = {};

saySo.templates = {
  

  // just a specific place to turn to run the mustache
  goBuildMeATemplate : function( tpl, obj ) {
    
    return Mustache.to_html( tpl, obj );
    
  },
  
  // list of quotas
  quotas : "<li class='completed-parameter'>TEST {{percent}}% {{ethnicity}} {{gender}}s, {{age}} <a href='#' class='delete' data-store-key='sayso-cells-n1-quota-{{thisCounter}}'>Delete</a></li>",
  
  // list of browsing qualifiers
  browsingQualifiers : "<li class='completed-parameter'>{{include}} {{site}} in the last {{timeframe}} <a href='#' class='delete'' data-store-key='sayso-cells-n1-qualifier-browse-{{thisCounter}}'>Delete</a></li>",
  
  // list of search qualifiers
  searchQualifiers : "<li class='completed-parameter'>{{include}} {{term}} on {{which}} in the last {{timeframe}} <a href='#' class='delete' data-store-key='sayso-cells-n1-qualifier-search-{{thisCounter}}'>Delete</a></li>",
  
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
          <option value='1000'></option>\
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
        <label for='engine-which'>On</label>\
        <select name='engine-which' id='engine-which' \
                data-store-key='sayso-cells-n1-qualifier-search-{{nextCounter}}-which'>\
          <option value='choose'>Search Engine</option>\
          <option value='All'>All</option>\
          <option value='Google'>Google</option>\
          <option value='Bing'>Bing</option>\
          <option value='Yahoo'>Yahoo</option>\
        </select>\
      </li>\
      <li>\
        <label for='engine-timeframe'>in the</label>\
        <select name='engine-timeframe' id='engine-timeframe' \
                data-store-key='sayso-cells-n1-qualifier-search-{{nextCounter}}-timeframe'>\
          <option value='choose'>Timeframe</option>\
          <option value='Last 1 day'>Last 1 day</option>\
          <option value='Last 1 week'>Last 1 week</option>\
          <option value='Last 1 month'>Last 1 month</option>\
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
          <option value='Last 1 day'>Last 1 day</option>\
          <option value='Last 1 week'>Last 1 week</option>\
          <option value='Last 1 month'>Last 1 month</option>\
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