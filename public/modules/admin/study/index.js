/**
 * @author alecksmart
 */

function bindLocal()
{
    // delete action needs confirmation...
    $('.button-delete').click(function()
    {
        return confirm('Delete this entry?') ? true : false;
    });
}

$(function(){ bindLocal();});