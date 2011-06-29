if (typeof sayso === 'undefined') sayso = {};

sayso.templates = {

  // list of quotas
  quotas : '<li data-id="{{id}}" class="completed-parameter"><span>{{type}} {{percent}}% {{ethnicity}} {{gender}}s, {{age}}</span> <a href="#" class="delete" data-store-key="sayso-cells-n{{cellNumber}}-quota-{{thisCounter}}">Delete</a></li>',

  // list of browsing qualifiers
  browsingQualifiers : '<li data-id="{{id}}" class="completed-parameter"><span>{{include}} visits to {{site}} in the last {{timeframe}}</span> <a href="#" class="delete" data-store-key="sayso-cells-n{{cellNumber}}-qualifier-browse-{{thisCounter}}">Delete</a></li>',

  // list of search qualifiers
  searchQualifiers : '<li data-id="{{id}}" class="completed-parameter"><span>{{include}} searches for \"{{term}}\" on {{#which}}{{#bing}}{{bing}}, {{/bing}}{{#google}}{{google}}, {{/google}}{{#yahoo}}{{yahoo}}, {{/yahoo}}{{/which}} in the last {{timeframe}}</span> <a href="#" class="delete" data-store-key="sayso-cells-n{{cellNumber}}-qualifier-search-{{thisCounter}}">Delete</a></li>',

  // list of delivery criteria
  deliveryCriteria : '<li data-id="{{id}}" class="completed-parameter"><span>{{domain}} within {{timeframe}}</span> <a href="#" class="delete" data-store-key="sayso-surveyinfo-deliverIf-{{thisCounter}}">Delete</a></li>',

  // list of domains for tag-domain-pairs
  domains : '<li data-id="{{id}}" class="completed-parameter">\
    {{name}}\
    <a href="#" data-store-key="sayso-tagdomain-{{tagDomainNumber}}-domain-{{thisCounter}}" class="delete">Delete</a>\
  </li>',

  // list of tag-domain pairs
  tagDomainPairs : '<li data-id="{{id}}"><nav><a href="#" class="edit">Edit</a>&nbsp;<a href="#" class="delete">Delete</a></nav><span>{{label}}</span></li>',
    
  adTag : '<div style="display: inline-block;" data-id="{{id}}"><input type="checkbox" name="cell-adtag-{{id}}" id="cell-adtag-{{id}}" value="{{id}}" data-store-key="sayso-cells-{{cellId}}-adtag-{{id}}"> <label for="cell-adtag-{{id}}">{{label}}</label>&nbsp;&nbsp;</div>',

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

  cellTableRow : '<tr data-id="{{id}}">\
      <td class="description">{{description}}</td>\
    <td>{{size}}</td>\
    <td>{{type}}</td>\
    <td>{{id}}</td>\
    <td>\
      <a href="#" class="view">View</a>\
      <a href="#" class="edit">Edit</a>\
      <a href="#" class="delete">Delete</a>\
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
  ',

  dialogCellDeleteConfirmation : '\
  <div class="wrap">\
    Are you sure you want to delete "{{description}}"?\
  </div>\
  ',

  dialogValidationFailure : '\
  <div class="wrap">\
    <ul>\
      {{#messages}}\
        <li>{{.}}</li>\
      {{/messages}}\
    </ul>\
  </div>\
  '

};