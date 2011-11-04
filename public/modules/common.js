/**
 * @author alecksmart
 */


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
}

$(function(){ bindDefaults();});