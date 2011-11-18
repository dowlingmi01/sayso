/**
 * @author alecksmart
 */

function bindLocal()
{

    $('#list').addClass('admin-table');

    // delete action needs confirmation...
    $('.button-delete').click(function()
    {
        return confirm('Delete this entry?') ? true : false;
    });
}

$(function(){ bindLocal();});