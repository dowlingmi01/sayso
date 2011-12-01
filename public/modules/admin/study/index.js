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

    $('.button-show-progress').each(function(){
        var v = parseInt($(this).attr('data-rel'));
        $(this).progressbar({
			value: v
		});
    });

    $('.change-status').unbind('click').bind('click', function(){

    });

}

$(function(){ bindLocal();});