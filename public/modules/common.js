/**
 * @author alecksmart
 */

function loadLoginDialog()
{

}

function bindDefaults()
{
    $('#login').unbind().bind('click', function()
    {
        if($(this).attr('rel') > '0')
        {
            alert('Logging out not implemented yet!')
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

$(function()
{
    bindDefaults();
});