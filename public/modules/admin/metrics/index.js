/**
 * @author alecksmart
 * @see Timeouts: http://benalman.com/projects/jquery-dotimeout-plugin/
 */

function doPoll(o)
{    
    var data = {lastRowId : window._adminPoller.lastRowId};

    $.ajax({
        url:      '/admin/metrics/poll',
        dataType: 'json',
        data:     data,
        success:  function(data)
        {
            //console.debug(data);
            $('#last-updated').html('Last updated: ' + (data.lastUpdated != undefined ? data.lastUpdated : ''));
        }
    });

}

function bindPoll()
{
    // do it immediately once
    doPoll();

    // poll with timeout
    $('#last-updated').doTimeout('main-poll', parseInt($('#dummy select option:selected').text())*1000, function()
    {
        doPoll();
        return true;
    });
}

function bindAll()
{
    window._adminPoller = {lastRowId : 0};

    // disallow dummy submits
    $('#dummy').submit(function(){return false;});

    // poll with timeout
    bindPoll();

    // rebind when changed
    $('#dummy select').change(function(){
        bindPoll();
    });
}

$(function(){bindAll();});