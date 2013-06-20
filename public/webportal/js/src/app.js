//noinspection ThisExpressionReferencesGlobalObjectJS
sayso.module.webportal = (function(global, $, state, Handlebars, utils) {
    'use strict';

    var shared = {},
        initialized = false,
        app = 'webportal',
        version = '1.0',
        starbarId = 4,
        $loginButton = $('#login'),
        $signOutButton = $('#sign_out'),
        $emailField = $('#email_field'),
        $passwordField = $('#password_field'),
        $forgotPassword = $('#forgot_password');

    function initialize() {
        if (!initialized) {
            //For now, throw away the hash they navigated here with.
            if (window.location.hash) {
                window.location.hash = '';
            }
        }
        //Bind our hashchange event.
        window.onhashchange = hashChanged;
        $loginButton.click(function() {
            state.login($emailField.val(), $passwordField.val());
        });
        $signOutButton.click(function() {
            if (state.state.loggedIn) {
                state.logout();
            }
        });
        if (state.state.loggedIn) {
            utils.requestMarkup('profile', starbarId);
        }
        initialized = true;
    }

    function login() {
        if(state.state.loggedIn)
        {
            utils.requestMarkup('profile', starbarId);
            $emailField.val('');
            $passwordField.val('');
            $loginButton.hide();
            $forgotPassword.hide();
        }
        else
        {
            utils.requestMarkup('landing', starbarId);
            $loginButton.show();
            $forgotPassword.show();
        }
    }

    function logout() {
        utils.requestMarkup('log-out', starbarId);
        $loginButton.show();
        $forgotPassword.show();
    }

    function hashChanged() {
        if (initialized) {
            var hash = document.location.hash;
            if (hash === '') {
                //We have navigated to the home page or to /#
                if (state.state.loggedIn) {
                    utils.requestMarkup('profile', starbarId);
                }
                else {
                    utils.requestMarkup('landing', starbarId);
                }
            }
            else {
                hash = hash.substring(1);
            }
            var values = hash.split('/');
            if (values && values[0] === 'content') {
                utils.requestMarkup(values[1], starbarId);
            }
            //TODO: Cs - Handle failure elegantly.
            //TODO: Cs - implement handlers for 'action' and 'lightbox'
            //TODO: Cs - should we split this out into hash_manager if it gets large enough?
        }
    }

    shared.app = app;
    shared.version = version;

    $(document).on('sayso:state-ready', initialize);
    $(document).on('sayso:state-login sayso:state-ready', login);
    $(document).on('sayso:state-logout', logout);

    return shared;

})(this, jQuery, sayso.module.state, sayso.module.Handlebars, sayso.module.webutils);