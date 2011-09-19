// Place this Javascript on all post-login pages

// @todo - set client name
//       - set UUID cookie name
//       - set logged in cookie name (may be same as UUID)
//       - set uuid type

(function () {

    if (!window.sayso) window.sayso = {};
    window.sayso.client = {
        name : '',
        uuid : getCookie(''),
        uuid_type : '', // email, username, hash, integer
        user_logged_in : (getCookie('').length ? true : false)
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