if (typeof sayso === 'undefined') sayso = {};

sayso.templates = {

  // list of quotas
  quotas : '<li data-id="{{id}}" class="completed-parameter"><span>{{type}} {{percent}}% {{ethnicity}} {{gender}}s, {{age}}</span> <a href="#" class="delete">Delete</a></li>',

  // list of browsing qualifiers
  browsingQualifiers : '<li data-id="{{id}}" class="completed-parameter"><span>{{include}} visits to {{site}} in the last {{timeframe}}</span> <a href="#" class="delete">Delete</a></li>',

  // list of search qualifiers
  searchQualifiers : '<li data-id="{{id}}" class="completed-parameter"><span>{{include}} searches for \"{{term}}\" on {{#which}}{{#bing}}{{bing}}, {{/bing}}{{#google}}{{google}}, {{/google}}{{#yahoo}}{{yahoo}}, {{/yahoo}}{{/which}} in the last {{timeframe}}</span> <a href="#" class="delete">Delete</a></li>',

  // list of delivery criteria
  deliveryCriteria : '<li data-id="{{id}}" class="completed-parameter"><span>{{domain}} within {{timeframe}}</span> <a href="#" class="delete">Delete</a></li>',

  // list of domains for tag-domain-pairs
  domains : '<li data-id="{{id}}" class="completed-parameter">{{name}}<a href="#" class="delete">Delete</a></li>',

  // list of tag-domain pairs
  tagDomainPairs : '<li data-id="{{id}}"><nav><a href="#" class="edit">Edit</a>&nbsp;<a href="#" class="delete">Delete</a></nav><span>{{label}}</span></li>',
  
  creative : '<li data-id="{{id}}"><div><nav><a href="#" class="edit">Edit</a>&nbsp;<a href="#" class="delete">Delete</a></nav><span class="creative-name">{{name}}</span><span class="creative-url">{{creativeUrl}}</span></div><img src="{{creativeUrl}}" /></li>',
    
  adTag : '<div style="display: inline-block;" data-id="{{id}}"><input type="checkbox" name="cell-adtag-{{id}}" id="cell-adtag-{{id}}" value="{{id}}"> <label for="cell-adtag-{{id}}">{{label}}</label>&nbsp;&nbsp;</div>',

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
  '
};