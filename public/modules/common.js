/**
 * @author alecksmart
 */


/**
 * Login section
 */
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
                if(loginResult != undefined && loginResult.ok)
                {
                    // suport for other functions
                    window._lastLoginSuccess_ = true;
                    //$('#login').html('Logout').attr('rel', '1');
                    //bindLogout();
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

function bindLogout()
{
    $('#login').unbind().bind('click', function()
    {
        alert('Logging out not implemented yet!');
    });
}

function bindDefaults()
{
    $('#login').unbind().bind('click', function()
    {
        if($(this).attr('rel') > '0')
        {
            bindLogout();
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

/**
 * Login section end
 */

$(function()
{
    /**
     * Login section
     */
    window._lastLoginSuccess_ = false;
    bindDefaults();
});