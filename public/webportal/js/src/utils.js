//noinspection ThisExpressionReferencesGlobalObjectJS
sayso.module.webutils = (function(global, $, state, Handlebars) {
    'use strict';

    var shared = {},
        app = 'webportal',
        contentContainer = $('#content_container');

    function requestMarkup(key, starbarId, callback) {
        state.apiAddRequest('markup', {
            action_class : 'markup',
            action : 'getMarkup',
            starbar_id : starbarId,
            app: app,
            key : key
        });

        state.apiSendRequests(function(response){
            contentContainer.html(response.responses.markup.variables.markup);
            if (callback && typeof(callback) === 'function') {
                callback();
            }
        });
    }

    shared.requestMarkup = requestMarkup;

    return shared;

})(this, jQuery, sayso.module.state, sayso.module.Handlebars);