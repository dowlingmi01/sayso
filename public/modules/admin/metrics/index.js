/**
 * @author alecksmart
 * @see Timeouts: http://benalman.com/projects/jquery-dotimeout-plugin/
 */

function prependRows()
{
    if($('#update-marker').length > 0)
    {
        $('#update-marker').remove();
    }
    if(window._adminPoller.rows.length > 0)
    {
        // reset to an empty array
        var rows = window._adminPoller.rows;
        window._adminPoller.rows = [];
        // populate the rows
        $.each(rows, function(i, v){
            var html = '';
            html += '<div class="updates-entry alt_'+ (window._adminPoller.alt++ & 1 ? 2 : 1) +'">';
                html += '<div class="updates-entry-user">';
                    html += v.userName + ' (User Id '+ v.userId + ')';
                html += '</div>';
                html += '<div class="updates-entry-starbar">';
                    html += v.starbar ;
                html += '</div>';
                html += '<div class="updates-entry-metricsType">';
                    html += v.metricsType ;
                html += '</div>';
                html += '<div class="updates-entry-dateTime">';
                    html += v.dateTime ;
                html += '</div>';
                html += '<div class="clear"></div>';
                html += '<div class="updates-entry-data">';
                    html += v.data ;
                html += '</div>';
            html += '</div>';
            $('#updates').prepend(html);
        });
        // too long? truncate up to 100 rows...
        while($('.updates-entry').length > 100)
        {
            $('#updates .updates-entry:last').remove();
        }
    }
}

function doPoll()
{
    var data = {lastRowId : window._adminPoller.lastRowId};

    $.ajax({
        url         : '/admin/metrics/poll',
        dataType    : 'json',
        data        : data,
        success     : function(data)
        {
            //console.debug(data);
            window._adminPoller.lastRowId = data.lastRowId;            
            $('#last-updated').html('Last updated: ' + (data.lastUpdated != undefined ? data.lastUpdated : ''));
            if(data.rows.length > 0)
            {
                window._adminPoller.rows = $.merge(window._adminPoller.rows, data.rows);
            }
            if(window._adminPoller.rows.length > 0)
            {
                if(window._adminPoller.isInit)
                {
                    // we do it only once at page load
                    prependRows();
                    window._adminPoller.isInit = false;
                }
                else
                {
                    // twitter-style updater:
                    // add a clickable div if not exists
                    // or update the count otherwise
                    if($('#update-marker').length == 0)
                    {
                        $('#updates').prepend('<div id="update-marker">'
                            +'<a href="javascript:void(null)" class="new-data-available">'
                                +'New updates are available (<span id="new-data-available-count">'
                                    + window._adminPoller.rows.length + '</span>)</a></div>');
                    }
                    else
                    {
                        $('#new-data-available-count').html(window._adminPoller.rows.length);
                    }
                    $('.new-data-available').unbind('click').bind('click', function()
                    {
                        prependRows();
                    });
                }

            }
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
    window._adminPoller = {lastRowId : 0, isInit : true,  rows : [], alt : 0};

    // disallow dummy submits
    $('#dummy').submit(function(){return false;});

    // poll with timeout
    bindPoll();

    // rebind when changed
    $('#dummy select').change(function()
    {
        bindPoll();
    });
}

$(function(){bindAll();});