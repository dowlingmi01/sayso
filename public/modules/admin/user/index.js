/**
 * @author alecksmart
 */

function bindLocal()
{
    $('#list').addClass('admin-table');

    $('.button-delete').click(function()
    {
        return confirm('Delete this entry?') ? true : false;
    });

    $('.button-roles').click(function()
    {
        alert('Not implemented yet...');
        return false;
    });
}

$(function(){ bindLocal();});