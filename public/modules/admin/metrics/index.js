/**
 * @author alecksmart
 * @see Timeouts: http://benalman.com/projects/jquery-dotimeout-plugin/
 */

function drawSingleRow(v)
{
    var html        = '';
    var rowStyle    = '';
    var rowTypeName = '';

    /**
     * 1 metrics_search
     * 2 metrics_page_view
     * 3 metrics_social_activity
     * 4 metrics_tag_view
     * 5 metrics_tag_click_thru
     * 6 metrics_creative_view
     * 7 metrics_creative_click_thru
     */

    switch(parseInt(v.metrics_type))
    {
        case 1:
            rowTypeName = 'Search';
            rowStyle    = 'updates-row-search';
            break;
        case 2:
            rowTypeName = 'Page View';
            rowStyle    = 'updates-row-page-view';
            break;
        case 3:
            rowTypeName = 'Social Activity';
            rowStyle    = 'updates-row-social-activity';
            break;
        case 4:
            rowTypeName = 'Campaign Impression';
            rowStyle    = 'updates-row-campaign-impression';
            break;
        case 5:
            rowTypeName = 'Campaign Click-Thru';
            rowStyle    = 'updates-row-campaign-click-thru';
            break;
        case 6:
            rowTypeName = 'Creative Impression';
            rowStyle    = 'updates-row-creative-impression';
            break;
        case 7:
            rowTypeName = 'Creative Click-Thru ';
            rowStyle    = 'updates-row-creative-click-thru';
            break;
    }

    html += '<div class="updates-entry ' + rowStyle + '" data-rowid="'+v.id+'">';
        html += '<div class="updates-entry-user">';
            html += '<a href="javascript:void(null)" rel="'+ v.user_id +'" class="filterUserOnly"'
                    +' title="Filter this user only">User Id '+ v.user_id +'</a>';
        html += '</div>';
        html += '<div class="updates-entry-starbar">';
            html += v.starbar_name ;
        html += '</div>';
        html += '<div class="updates-entry-metricsType">';
            html += rowTypeName ;
        html += '</div>';
        html += '<div class="updates-entry-dateTime">';
            html += v.created ;
        html += '</div>';
        html += '<div class="clear"></div>';
        html += '<div class="updates-entry-data">';
            html += v.content ;
        html += '</div>';
    html += '</div>';

    return html;
}

function prependRows(rows)
{
    $.each(rows, function(i, v)
    {
        var html = drawSingleRow(v);
        $('#updates').prepend(html);
    });
    /*for(var i=rows.length-1; i >=0; i--)
    {
        //console.debug(rows[i])
        var html = drawSingleRow(rows[i]);
        $('#updates').prepend(html);
    }*/
}

function appendRows(rows)
{
    $.each(rows, function(i, v)
    {
        //var html = drawSingleRow(v);
        //$('#updates').prepend(html);
    });
}

function doPoll()
{
    var data            = {};

    var pollSocial      = $('#control-social').attr('checked') ? 1 : 0;
    var pollPageView    = $('#control-page-view').attr('checked') ? 1 : 0;
    var pollMetrics     = $('#control-metrics').attr('checked') ? 1 : 0;
    var pollTags        = $('#control-tags').attr('checked') ? 1 : 0;
    var pollCreatives   = $('#control-creatives').attr('checked') ? 1 : 0;

    // nothing to poll for...
    if(!pollSocial && !pollPageView && !pollMetrics && !pollTags && !pollCreatives)
    {
        return;
    }

    var dir = 'up';

    // expect arguments as hash
    if(arguments.length > 0)
    {
        if(arguments[0].dir != undefined)
        {
            dir = arguments[0].dir;
        }
    }

    var rowId = window.poll.rowId ;

    if(dir == 'down')
    {
        rowId = $('#updates .updates-entry:last').attr('data-rowid') || 0;
    }

    // crate poll request
    data['rowId']           = rowId;
    data['dir']             = dir;
    data['pollForTypes']    =
    {
        'social'        : pollSocial,
        'pageView'      : pollPageView,
        'metrics'       : pollMetrics,
        'tags'          : pollTags,
        'creatives'     : pollCreatives
    };

    //console.debug(data);

    $.ajax({
        url         : '/admin/metrics/poll',
        dataType    : 'json',
        data        : data,
        success     : function(data)
        {
            //console.debug(data);

            $('#last-updated').html('Last updated: ' + (data.lastUpdated != undefined ? data.lastUpdated : ''));

            if(dir == 'up' && rowId == 0)
            {                
                // redefine id for next poll                
                prependRows(data.rows);
                window.poll.rowId = $('#updates .updates-entry:first').attr('data-rowid');                
                //console.debug(window.poll.rowId);
                
            }
            else
            {
                if(dir == 'up')
                {
                    if(data.rows.length > 0)
                    {
                        // pull to cache
                        window.poll.cache = $.merge(window.poll.cache, data.rows);
                        // redefine id for next poll
                        $.each(data.rows, function(i, v)
                        {
                            if(v.id > window.poll.rowId)
                            {
                                window.poll.rowId = v.id;
                            }
                        });
                        //console.debug(window.poll.rowId);
                        // twitter-style updater: add a clickable div if not exists
                        // or update the count otherwise
                        if($('#update-marker').length == 0)
                        {
                            $('#updates').prepend('<div id="update-marker">'
                                +'<a href="javascript:void(null)" class="new-data-available">'
                                    +'New updates are available (<span id="new-data-available-count">'
                                        + window.poll.cache.length + '</span>)</a></div>');
                        }
                        else
                        {
                            $('#new-data-available-count').html(window.poll.cache.length);
                        }
                    }
                }
                else
                {
                    appendRows(data.rows);
                }
            }

            // re-bind cache
            $('.new-data-available').unbind('click').bind('click', function()
            {
                var cache = window.poll.cache
                window.poll.cache = [];
                $('#update-marker').remove();
                prependRows(cache);
            });

            // re-bind filter for only user...
            $('.filterUserOnly').unbind('click').bind('click', function()
            {
                $.cookie('control-metrics-user-only', $(this).attr('rel'));
                self.location.reload();
            });
        }
    });
}

function bindPoll()
{
    // do it immediately once
    doPoll();

    // poll with timeout
    $('#last-updated').doTimeout('main-poll', parseInt($('#poll-freq select option:selected').text())*1000, function()
    {
        doPoll();
        return true;
    });
}

function bindControls()
{
    $.cookie('controlSocial') == undefined || $.cookie('controlSocial') == 'on'
        ? $('#control-social').attr('checked', 'checked')
        : $('#control-social').removeAttr('checked');
    $.cookie('controlPageView') == undefined || $.cookie('controlPageView') == 'on'
        ? $('#control-page-view').attr('checked', 'checked')
        : $('#control-page-view').removeAttr('checked');
    $.cookie('controlMetrics') == undefined || $.cookie('controlMetrics') == 'on'
        ? $('#control-metrics').attr('checked', 'checked')
        : $('#control-metrics').removeAttr('checked');
    $.cookie('controlTags') == undefined || $.cookie('controlTags') == 'on'
        ? $('#control-tags').attr('checked', 'checked')
        : $('#control-tags').removeAttr('checked');
    $.cookie('controlCreatives') == undefined || $.cookie('controlCreatives') == 'on'
        ? $('#control-creatives').attr('checked', 'checked')
        : $('#control-creatives').removeAttr('checked');

    $('#control-social').unbind().bind('click', function(){
        $.cookie('controlSocial', $(this).attr('checked') ? 'on' : 'off');
        self.location.reload();
    });
    $('#control-page-view').unbind().bind('click', function(){
        $.cookie('controlPageView', $(this).attr('checked') ? 'on' : 'off');
        self.location.reload();
    });
    $('#control-metrics').unbind().bind('click', function(){
        $.cookie('controlMetrics', $(this).attr('checked') ? 'on' : 'off');
        self.location.reload();
    });
    $('#control-tags').unbind().bind('click', function(){
        $.cookie('controlTags', $(this).attr('checked') ? 'on' : 'off');
        self.location.reload();
    });
    $('#control-creatives').unbind().bind('click', function(){
        $.cookie('controlCreatives', $(this).attr('checked') ? 'on' : 'off');
        self.location.reload();
    });

    // handle setting a user filter
    if($.cookie('control-metrics-user-only') != undefined && $.cookie('control-metrics-user-only') > 0)
    {
        var html = '<label for="control-metrics-remove-user-filter"><input id="control-metrics-remove-user-filter" '
                    +'type="checkbox" value="'+$.cookie('control-metrics-user-only')
                    +'" checked="checked"> User Id '+$.cookie('control-metrics-user-only')+'</label>';
        $('#controls').append(html);
        $('#control-metrics-remove-user-filter').unbind().bind('click', function()
        {
            $.cookie('control-metrics-user-only', 0);
            // strange bug, the above does not always work...
            delete document.cookie['control-metrics-user-only'];
            self.location.reload();
        });
    }

}

function bindAll()
{
    window.poll = {cache:[], rowId : '0'};

    // set checkboxes according to cookies
    bindControls();

    // disallow dummy submits
    $('#dummy').submit(function(){return false;});

    // poll with timeout
    bindPoll();

    // rebind when changed
    $('#poll-freq select').change(function()
    {
        bindPoll();
    });

    // detect window scroll
    var allowPixels = 50;
    $(window).unbind('scroll').bind('scroll', function()
    {
        if($(window).scrollTop() > $(document).height() - $(window).height() - allowPixels)
        {
            doPoll({dir : 'down'});
        }
    });
}

$(function(){bindAll();});