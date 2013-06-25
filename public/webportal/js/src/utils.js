//noinspection ThisExpressionReferencesGlobalObjectJS
sayso.module.webutils = (function(global, $, api, Handlebars) {
    'use strict';

    var shared = {},
        app = 'webportal',
        contentContainer = $('#content_container');

    function requestMarkup(key, starbarId, callback) {
        api.doRequest({
            action_class : 'markup',
            action : 'getMarkup',
            starbar_id : starbarId,
            app: app,
            key : key
        }, function(response){
            contentContainer.html(response.responses['default'].variables.markup);
            if (callback && typeof(callback) === 'function') {
                callback();
            }
        });
    }

    shared.requestMarkup = requestMarkup;

    return shared;

})(this, jQuery, sayso.module.api, sayso.module.Handlebars);