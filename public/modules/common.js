/**
 * @author alecksmart
 */
// Common stuff

(function($) {
    var cache = [];
    $.preLoadImages = function()
    {
        for (var i = 0; i < arguments.length; i++) {
            var cacheImage = document.createElement('img');
            cacheImage.src = arguments[i];
            cache.push(cacheImage);
        }
    };

    $.rand = function(x)
    {
        var str = '';
        if(!x) {
            return str;
        }
        while(x){
            str += Math.floor(Math.random()*10);
            x--;
        }
        return str;
    };

    $.srand = function(x)
    {
        var str = '';
        var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz";
        for (var i = 0; i < x; i++) {
            var y = Math.floor(Math.random() * chars.length);
            str += chars.substring(y,y+1);
        }
        return str;
    };

    $.preLoadImages
    (
        '/images/icons-actions.gif'
    );

})(jQuery);

// Login section begin

function bindLoginForm()
{
    $('#user-login').unbind('submit').bind('submit', function()
    {
        window._lastLoginSuccess_ = false;
        var data = $('#user-login').serialize();

        $.ajax({
            url         : '/admin/user/login',
            data        : data,
            type        : 'POST',
            success     : function(data)
            {
                $('#login-dialog').html(data);
                if(loginResult != undefined
                    && loginResult.ok)
                {
                    $('#login-dialog').dialog("close");
                    self.location.reload();
                    return;
                }
                if(loginResult.message != undefined
                    && loginResult.message.length > 0)
                {
                    alert(loginResult.message.join('\n'));
                }
                bindLoginForm();
            }
        });
    });
}

function loadLoginDialog()
{
    $.ajax({
        url         : '/admin/user/login',
        success     : function(data)
        {
            $('#login-dialog').html(data);
            bindLoginForm();
        }
    });
}
// Login section end


function bindDefaults()
{

    // handle login

    $('#login').unbind().bind('click', function()
    {
        if($(this).attr('rel') > '0')
        {
            $.ajax({
                url         : '/admin/user/logout',
                success     : function(data)
                {
                    self.location.reload();
                }
            });
        }
        else
        {
            $('#login-dialog').dialog({
                modal: true,
                open: function(){loadLoginDialog();}
            });
        }
    });

    // handle unified messaging

    if(window._saysoMessages != undefined && window._saysoMessages.length > 0)
    {
        $.fx.speeds._default = 500;
        $('#system-message').html(window._saysoMessages.join("<br />"));
        window._saysoMessages = [];
        $('#system-message').dialog({
            modal   : true,
            hide    : "explode",
            buttons : {
                'Ok': function(){$(this).dialog( "close" );}
            }
        });
    }
}

$(function(){ bindDefaults();});