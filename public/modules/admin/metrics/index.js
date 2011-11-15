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
            var html        = '';
            var rowStyle    = 'updates-row-social';
            var rowTypeName = v.metricsType;
            switch(v.metricsType)
            {
                case 'Search':
                    rowStyle = 'updates-row-search';
                    break;
                case 'Page View':
                    rowStyle = 'updates-row-page-view';
                    break;
                case 'Tag':
                    rowStyle = 'updates-row-tag';
                    rowTypeName = 'Ad Impression';
                    break;
                case 'Creative':
                    rowStyle = 'updates-row-creative';
                    rowTypeName = 'Creative Impression';
                    break;
            }
            html += '<div class="updates-entry ' + rowStyle + '">';
                html += '<div class="updates-entry-user">';
                    html += '<a href="javascript:void(null)" rel="'+ v.userId +'" class="filterUserOnly"'
                            +' title="Filter this user only">User Id '+ v.userId +'</a>';
                html += '</div>';
                html += '<div class="updates-entry-starbar">';
                    html += v.starbar ;
                html += '</div>';
                html += '<div class="updates-entry-metricsType">';
                    html += rowTypeName ;
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
        // bind filetr user only
        $('.filterUserOnly').unbind('click').bind('click', function()
        {
            $.cookie('control-metrics-user-only', $(this).attr('rel'));
            self.location.reload();
        });
    }
}

function doPoll()
{
    var data            = {lastRowId : window._adminPoller.lastRowId};
    
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

    /**
     * @todo
     * Recognize through coolkies
     */
    data['pollForTypes'] =
    {
        'social'        : pollSocial,
        'pageView'      : pollPageView,
        'metrics'       : pollMetrics,
        'tags'          : pollTags,
        'creatives'     : pollCreatives
    };

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
    window._adminPoller = {lastRowId : 0, isInit : true,  rows : [], alt : 0};

    // set checkboxes according to cookies
    bindControls();

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