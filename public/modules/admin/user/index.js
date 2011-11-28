/**
 * @author alecksmart
 */

function bindLocal()
{
    $('#list').addClass('admin-table');

    $('#list a').click(function(){
        alert('Not implemented yet!');
        return false;
    });

    /*$('.button-delete').click(function()
    {
        return confirm('Delete this entry?') ? true : false;
    });*/
}

$(function(){ bindLocal();});