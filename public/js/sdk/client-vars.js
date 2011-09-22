// Before providing this to client, fill in the custom fields:
// - client name
// - UUID cookie name
// - logged-in cookie name (may be same as UUID)
// - uuid type

/**
 * Client post-login variables
 * - include this snippet on any post-login page
 * 
 * @author davidbjames 
 */
(function () {

    if (!window.sayso) window.sayso = {};
    window.sayso.client = {
        name : '',
        uuid : getCookie(''),
        uuidType : '', // email, username, hash, integer
        userLoggedIn : (getCookie('').length ? true : false)
    };
    
    String.prototype.trim = function() {
        return this.replace(/^\s+|\s+$/g,'');
    };

    function getCookie (find) {
        var cookies = document.cookie.split(';');
        for(var i = 0; i < cookies.length; i++) {
            var nameValue = cookies[i].split('=');
            var name = nameValue[0].trim();
            if (name === find) {
                return nameValue[1].trim();
            }
        }
        return '';
    }
    
})();