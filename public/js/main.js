/**
 * Main Javascript functions/objects for Sayso
 * 
 * Dependencies: 
 * - jQuery
 * - jquery.form.js @see http://jquery.malsup.com/form/
 * - pubsub.js @see http://higginsforpresident.net/js/static/jq.pubsub.js
 */
a = (function () {
    
    // ensure indexOf works on all browsers
    if (!Array.prototype.indexOf)
    {
        Array.prototype.indexOf = function(obj, start) {
            for (var i = (start || 0), j = this.length; i < j; i++) {
                if (this[i] == obj) { return i; }
            }
            return -1;
       };
    }
    
    var _this = {
        
        api : {
            authKey : null,
            imageKey : null
        },
        
        user : 
        {
            loggedIn : false,
            id : 0,
            timezone : null, 
            object : null
        },
        
        location : 
        {
            baseDomain : '',
            url : '',
            setUrl : function (pathInfo, subDomain) {
                if (!pathInfo) pathInfo = '';
                subDomain = subDomain || 'www';
                this.url = 'http://' + subDomain + '.' + this.baseDomain + pathInfo;
            }
        },
        
        /**
         * Simple Ajax wrapper used solely for augmenting
         * jQuery.ajax (and not replacing it)
         * 
         * @param object options
         * @todo MAKE SURE THIS FUNCTION FITS IN TO SAYSO ARCHITECTURE
         * @returns
         */
        ajax : function (options)
        {
            // if URL has 3 segments then it is a cross-module
            // URL, therefore prepend with /ajax in order for 
            // routing to work correctly. see ModuleManager.php
            if (options.url.split(/\//).length > 3 && !options.url.match(/http/)) {
                options.url = '/ajax' + options.url;
            }
            // for jsonp, ensure PHP session ID is passed along
            if (options.dataType === 'jsonp') {
                var sessionId = _this.utils.cookie.get('PHPSESSID');
                if (!options.data) options.data = {};
                if (sessionId) options.data.PHPSESSID = sessionId;
            }
            // where i got to: change Addictionary ajax to remove all auth keys
            // and ensure all calls use a.ajax.. then test.. then remove Auth logic.. then test again!
            // add the auth_key to the call
            if (a.api && a.api.authKey) {
                options.data = $.extend(options.data || {}, { auth_key : a.api.authKey });
            }
            $.ajax(options);
        },
        /**
         * Functions specific to form-handling
         */
        forms : 
        {
            /**
             * Wrapper for Ajax forms using jQuery ajaxForm
             * 
             * @param jQuery form - jQuery handle to <form>
             * @param string event - event to fire on success
             * @param object options - optional jQuery Ajax options (overrides)
             * @author davidbjames
             */
            ajax : function (form, event, options)
            {
                if (!options) options = {};
                if (a.api && a.api.authKey) {
                    options.data = $.extend(options.data || {}, { auth_key : a.api.authKey });
                }
                // setup handling of the ajax form
                var _options = $.extend({
                    data : {},
                    // override the form action url to use the ajax route
                    url : form.attr('action'),
                    dataType : 'json',
                    beforeSubmit : function (data, form, options) {
                        if (form.hasClass('disabled') || form.find('a.button').hasClass('disabled')) return false;
                        else return true;
                    },
                    success : function (response, status, xhr) {
                        
                        // clear any previous errors
                        $('span.field-error').hide('slow').remove();
                        $('.custom-error').hide('slow').text('');
                        
                        if (response.status === 'success') {
                            // check if we are in a modal
                            if (form.parents('#modal-window').length) {
                                // publish success callback *after* 
                                // the modal is completely closed
                                var _handle = $.subscribe('/modal/closed', function() {
                                    $.publish(event, [response.data]);
                                    $.unsubscribe(_handle);
                                });
                                // close the modal
                                $.publish('/modal/close');
                            } else {
                                $.publish(event, [response.data]);
                            }
                        } else {
                            // API errors (
                            _this.log(response.data.type);
                            switch (response.data.type)
                            {
                                case 'Error' :
                                    // publish an error event
                                    $.publish(event + '/error', [response.data]);
                                    break;
                                case 'ValidationError' :
                                default :
                                    // append new errors
                                    for (key in response.data) {
                                        // allow using custom error containers
                                        var customError = form.find('.custom-error[name=' + key + ']');
                                        if (customError.length) {
                                            customError.text(response.data[key][0])
                                        } else {
                                            form.find('input[name=' + key + '],textarea[name=' + key + '],select[name=' + key + ']')
                                                .parent()
                                                .append(
                                                    '<span style="display: none;"' +
                                                        'class="field-error">' + 
                                                        response.data[key][0] + 
                                                    '</span>'
                                                );
                                        }
                                        form.find('span.field-error, .custom-error').fadeIn('slow');
                                    }
                            }
                        }
                        _this.log(response);
                    },
                    error : function (xhr, status, error) {
                        _this.log('Uncaught exception');
                        _this.log(error);
                    }
                }, options);
                
                form.ajaxForm(_options);
                
                // allow use of non-standard form buttons
                var _customButton = form.find('a.button');
                if (_customButton.length) {
                    // wire up the custom button to submit on click
                    _customButton.unbind('click').click(function () { 
                        if (!$(this).hasClass('disabled')) form.submit(); 
                    });
                    // since using a custom button disables
                    // the use of return/enter for form submission
                    // we need to re-wire it here to submit the form
                    form.find('input[type=text],input[type=password]').keyup(function (e) {
                        if (e.keyCode === 13 && !_customButton.hasClass('disabled')) {
                            form.submit();
                        }
                    });
                }
                
                // catch authentication errors
                $.subscribe(event + '/error', function (error) {
                    // check for authentication problems
                    if (error.code >= 230 && error.code <= 239)
                    {
                        _this.log(error);
                    } 
                });
            }
        },
        log : function (data) {
            if (typeof window.console !== 'undefined' && typeof window.console.log !== 'undefined')
            {
                console.log(data);
            }
        },
        workingState : {
            clear : function () {
                $.cookie(this._cookieName, '', { expires: -2, path: '/' });
                $.cookie('workingStateClear', '', { expires: -2, path: '/' });
            },
            load : function () {
                return JSON.parse($.cookie(this._cookieName));
            },
            save : function (context) {
                $.cookie(this._cookieName, JSON.stringify(context), { path: '/' });
            },
            _cookieName : 'workingState'
        },
        utils : {
            /**
             * Simple timer class enabling setting a time length,
             * starting, stopping and checking if running
             * - optional callback can be passed to start()
             *   which will be fired when timer is finished
             * 
             * @param integer milliseconds
             * @author davidbjames
             */
            timer : function (milliseconds) {
                /**
                 * Length of time in milliseconds
                 */
                var _milliseconds = milliseconds;
                /**
                 * Timer ID
                 */
                var _timerId = null;
                /**
                 * Instance of this object
                 */
                var instance = this;
                /**
                 * Current running status
                 * @var boolean
                 */
                this.running = false;
                
                /**
                 * Start the timer
                 * - optionally provide callback to be run when finished
                 * 
                 * @param function callback
                 */
                this.start = function (callback) {
                    if (instance.running) {
                        instance.stop();
                    }
                    callback = callback || instance.stop;
                    _timerId = window.setTimeout(callback, _milliseconds);
                    instance.running = true;
                };
                
                /**
                 * Stop the timer
                 */
                this.stop = function () {
                    window.clearTimeout(_timerId);
                    instance.running = false;
                };
            },
            cookie : {
                get : function (find) {
                    var cookies = document.cookie.split(';');
                    for(var i = 0; i < cookies.length; i++) {
                        // split cookies into key (offset 0) and value (offset 1)
                        var nameValue = cookies[i].split('=');
                        
                        // grab the cookie name and remove any whitespace (newlines)
                        var name = nameValue[0].trim();
                        
                        // if the name is the same then return the value
                        if (name === find) {
                            return nameValue[1].trim();
                        }
                    }
                    return null;
                }
            }
        }
    };
    
    // Clear working state if requested
    
//    if (_this.utils.cookie.get('workingStateClear')) {
//        _this.workingState.clear();
//    }
    
    return _this;
})();
