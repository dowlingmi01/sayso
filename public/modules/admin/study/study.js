/**
 * Functions for creating and editing a study
 *
 * @author alecksmart
 */

function submitMain()
{
    //Select Product
    if(!parseInt($('input[name=radioProduct]').val()))
    {
        dialogAlert('Please choose product!');
        return false;
    }
    //Study Name*
    if(!$('#txtStudyName').val())
    {
        dialogAlert('Please fill in Study Name!');
        return false;
    }
    //Sample Size
    if(false === parseInt($('#txtSampleSize').val()) > 0)
    {
        dialogAlert('Please fill in Sample Size (must be > 0)!');
        return false;
    }
    //Min. Threshold
    if(false === parseInt($('#txtMinThreshold').val()) > 0)
    {
        dialogAlert('Please fill in Min. Threshold (must be > 0)!');
        return false;
    }
    //Begin
    if(!$('#txtBegin').val())
    {
        dialogAlert('Please fill in Begin Date!');
        return false;
    }
    //End
    if(!$('#txtEnd').val())
    {
        dialogAlert('Please fill in End Date!');
        return false;
    }

    // Check for sum in quotas not > 100%
    var dataCellTotal = 0;
    $('.data-cell-percentile').each(function(){
        dataCellTotal += parseInt($(this).text());
    });
    if(dataCellTotal > 100)
    {
        dialogAlert('Quotas percentile cannot be more than 100%!');
        return false;
    }

    // Check for both cell types
    var hasTest = false, hasControl = false;
    $('#existing-cells tbody tr td:nth-child(3)').each(function(){
        if($(this).text() == 'Test')
        {
            hasTest = true;
        }
        if($(this).text() == 'Control')
        {
            hasControl = true;
        }
    });
    if(!hasTest || !hasControl)
    {
        dialogAlert('A study must contail at least one Control cell and at least one Test cell!');
        return false;
    }

    // rebind form submit and submit
    $("#mainForm").unbind().bind('submit', function(){
        return true;
    });
    $("#mainForm").submit();
    return true;
}

/**
 * Show tabs and other products logics
 */
function switchProduct()
{
    switch(parseInt($('input[name=radioProduct]:checked').val()))
    {
        case 1:
            $("#tabContainer .ui-tabs-nav li:nth-child(2)").hide();
            $("#tabContainer .ui-tabs-nav li:nth-child(3)").hide();
            break;
        case 2:
            $("#tabContainer .ui-tabs-nav li:nth-child(2)").show();
            $("#tabContainer .ui-tabs-nav li:nth-child(3)").hide();
            break;
        case 3:
            $("#tabContainer .ui-tabs-nav li:nth-child(2)").hide();
            $("#tabContainer .ui-tabs-nav li:nth-child(3)").show();
            break;
    }
}

function setSurveyVisibility()
{
    $('input[name=radioSurveyCreate]').unbind('change').bind('change', function()
    {
        if(parseInt($(this).val()) == 2)
        {
            $('#txtPasteIframeUrl').parent().show('slow');
        }
        else
        {
            $('#txtPasteIframeUrl').parent().hide('slow');
        }
        if(parseInt($(this).val()) == 0)
        {
            $('#fieldset-groupsurveydelivery').hide('slow');
        }
        else
        {
            $('#fieldset-groupsurveydelivery').show('slow');
        }
    });
}

function buildCriteria()
{
    var cType = parseInt($('input[name=radioSurveyCreate]:checked').val());
    var ifrCustomUrl    = $('#txtPasteIframeUrl').val();
    var timeframe       = $('#selectSurveyTimeframe').val();
    var cText = '', visitUrl = '';
    switch(cType)
    {
        case 1:
            cText = 'Standard';
            break;
        case 2:
            cText = 'Custom';
            if(ifrCustomUrl == '')
            {
                dialogAlert('Please supply iFrame URL!');
                return;
            }
            break;
        default:
            dialogAlert('Bad survey type!');
            return;
    }
    if($('#txtDeliverSurvey').val() > '')
    {
        visitUrl = $('#txtDeliverSurvey').val();
    }
    else if($('#selectSurveySite').val() > '')
    {
        visitUrl = $('#selectSurveySite').val();
    }
    if(visitUrl == '')
    {
        dialogAlert('Please supply visiting URL!');
        return;
    }

    var criteria = $.srand(8);
    var hiddens = [];
    hiddens[hiddens.length] = {'name': 'cType', 'value': cType};
    hiddens[hiddens.length] = {'name': 'iframeCustomUrl', 'value': ifrCustomUrl};
    hiddens[hiddens.length] = {'name': 'visitUrl', 'value': visitUrl};
    hiddens[hiddens.length] = {'name': 'timeframe', 'value': timeframe};

    // append data
    $.each(hiddens, function(i, v)
    {
        var html = '<input type="hidden" name="criteria['
                + criteria + ']['+v.name+']" value="'
                + v.value + '" class="hidden-criteria-'+criteria+' data-'+v.name+'" />';
        $('#tabContainer-frag-5 div.subForm').append(html);
    });

    // append row
    var cellType    ='<td style="width:50px">'+cText+'</td>';
    var cellIframe  ='<td class="align-left break-word">'+ifrCustomUrl+'</td>';
    var cellSite    ='<td class="align-left break-word">'+visitUrl+'</td>';
    var cellTime    ='<td>'+( $('#selectSurveyTimeframe option[value='+timeframe+']').text() )+'</td>';
    var cellDelete  ='<td style="width:20px"><a title="Delete" class="button-delete delete-criteria" '
                        +'href="javascript:void(null)" rel="'+criteria+'"></a></td>';
    var row = $('<tr id="row-criteria-'+criteria+'">'+cellType+cellIframe+cellSite+cellTime+cellDelete+'</tr>');
    $('#existing-criteria').append(row);

    // recolor rows
    var c = 0;
    $('#existing-criteria tbody tr').each(function(){
        ++c & 1 ? $(this).removeClass('alt') : $(this).addClass('alt');
    });

    // rebind delete actions
    bindDeleteCriteria();
}

function bindDeleteCriteria()
{
    $('.delete-criteria').unbind().bind('click', function(){
        var criteria = $(this).attr('rel');
        $('#row-criteria-'+criteria).remove();
        $('.hidden-criteria-'+criteria).remove();
    });
}

function buildQuota()
{
    var gender = $('#selectQuotaGender').val() || 0;
    var age = $('#selectQuotaAge').val() || 0;
    var eth = $('#selectQuotaEthnicity').val() || 0;
    var cell = $('#selectQuotaCellPerc').val() || 0;

    if((!gender && !age && !eth) || !cell)
    {
        dialogAlert('Please choose at least one criterion and / or cell percentile!');
        return;
    }

    var uniqKey = $.srand(8);
    var hiddens =[];
    hiddens[hiddens.length] = {'name': 'gender', 'value': gender};
    hiddens[hiddens.length] = {'name': 'age', 'value': age};
    hiddens[hiddens.length] = {'name': 'eth', 'value': eth};
    hiddens[hiddens.length] = {'name': 'cell', 'value': cell};


    // append data
    $.each(hiddens, function(i, v)
    {
        var html = '<input type="hidden" name="quotas['
                + uniqKey + ']['+v.name+']" value="'
                + v.value + '" class="hidden-quota-'+uniqKey+' data-'+v.name+'" />';
        $('#tabContainer-frag-6 div.subForm').append(html);
    });

    // append row
    var cellOne     ='<td style="align-center">'+(gender ? $('#selectQuotaGender option[value='+gender+']').text() : '-')+'</td>';
    var cellTwo     ='<td>'+(age ? $('#selectQuotaAge option[value='+age+']').text() : '-' )+'</td>';
    var cellThree   ='<td>'+(eth  ? $('#selectQuotaEthnicity option[value='+eth+']').text() : '-' )+'</td>';
    var cellFour    ='<td class="data-cell-percentile">'+(cell ? $('#selectQuotaCellPerc option[value='+cell+']').text() : '-' )+'</td>';
    var cellDelete  ='<td style="width:20px"><a title="Delete" class="button-delete delete-quota" '
                        +'href="javascript:void(null)" rel="'+uniqKey+'"></a></td>';
    var row = $('<tr id="row-quota-'+uniqKey+'">'+cellOne+cellTwo+cellThree+cellFour+cellDelete+'</tr>');
    $('#existing-quotas').append(row);

    // recolor rows
    var c = 0;
    $('#existing-quotas tbody tr').each(function(){
        ++c & 1 ? $(this).removeClass('alt') : $(this).addClass('alt');
    });

    // rebind delete actions
    bindDeleteQuota();
}

function bindDeleteQuota()
{
    $('.delete-quota').unbind().bind('click', function(){
        var uniqKey = $(this).attr('rel');
        $('#row-quota-'+uniqKey).remove();
        $('.hidden-quota-'+uniqKey).remove();

        var c = 0;
        $('#existing-quotas tbody tr').each(function(){
            ++c & 1 ? $(this).removeClass('alt') : $(this).addClass('alt');
        });

    });
}

function buildOnlineBrowsing()
{
    // init
    var action      = $('#selectOnlineBrowsing').val();
    var url         = $('#txtWhoVisited').val();
    var timeframe   = $('#selectTimeframe').val();

    if(!action || !url || !timeframe)
    {
        dialogAlert('Please set/choose all criteria for online browsing!');
        return;
    }

    // create metadata
    var uniqKey = $.srand(8);
    var hiddens =[];
    hiddens[hiddens.length] = {'name': 'qftype', 'value': 'online-browsing'};
    hiddens[hiddens.length] = {'name': 'action', 'value': action};
    hiddens[hiddens.length] = {'name': 'url', 'value': url};
    hiddens[hiddens.length] = {'name': 'timeframe', 'value': timeframe};

    // append data
    $.each(hiddens, function(i, v)
    {
        var html = '<input type="hidden" name="cell['+ window._cell + ']['
                + uniqKey + '][' + v.name + ']" value="'
                + v.value + '" class="cell-' + window._cell + ' cell-row-' + uniqKey + ' cell-data-ob-'+ v.name + '" />';
        $('#tabContainer-frag-7 div.subForm').append(html);
    });

    // append row
    var cellOne     ='<td style="align-center">'+(action ? $('#selectOnlineBrowsing option[value='+action+']').text() : '-')+'</td>';
    var cellTwo     ='<td>'+url+'</td>';
    var cellThree   ='<td>'+(timeframe ? $('#selectTimeframe option[value='+timeframe+']').text() : '-')+'</td>';
    var cellDelete  ='<td style="width:20px"><a title="Delete" class="button-delete delete-ob-row" '
                        +'href="javascript:void(null)" rel="' + uniqKey + '"></a></td>';
    var row = $('<tr id="cell-tb-row-'+uniqKey+'">'+cellOne+cellTwo+cellThree+cellDelete+'</tr>');
    $('#cell-qf-online').append(row);

    // recolor rows
    var c = 0;
    $('#cell-qf-online tbody tr').each(function(){
        ++c & 1 ? $(this).removeClass('alt') : $(this).addClass('alt');
    });

    // rebind delete actions
    bindDeleteOnlineBrowsing();

}

function bindDeleteOnlineBrowsing()
{
    $('.delete-ob-row').unbind().bind('click', function(){
        var uniqKey = $(this).attr('rel');
        $('#cell-tb-row-'+uniqKey).remove();
        $('.cell-row-'+uniqKey).remove();

        var c = 0;
        $('#cell-qf-online tbody tr').each(function(){
            ++c & 1 ? $(this).removeClass('alt') : $(this).addClass('alt');
        });
    });
}

function buildSearchActions()
{
    // init
    var action      = $('#selectSearchActions').val();
    var qs          = $('#txtWhoSearchedFor').val();
    var timeframe   = $('#selectTimeframeSearch').val();
    var engines     = $('.cb-search-on-engines:checked');

    if(!action || !qs || !timeframe || !engines.length)
    {
        dialogAlert('Please set/choose all criteria for search actions!');
        return;
    }

    // create metadata
    var uniqKey = $.srand(8);
    var hiddens =[];
    hiddens[hiddens.length] = {'name': 'qftype', 'value': 'search-action'};
    hiddens[hiddens.length] = {'name': 'action', 'value': action};
    hiddens[hiddens.length] = {'name': 'qs', 'value': qs};
    hiddens[hiddens.length] = {'name': 'timeframe', 'value': timeframe};

    // append metadata
    $.each(hiddens, function(i, v)
    {
        var html = '<input type="hidden" name="cell['+ window._cell + ']['
                + uniqKey + '][' + v.name + ']" value="'
                + v.value + '" class="cell-' + window._cell + ' cell-row-se-' + uniqKey + ' cell-data-se-'+ v.name + '" />';
        $('#tabContainer-frag-7 div.subForm').append(html);
    });
    // add engines array
    var seNames = [];    
    $(engines).each(function()
    {
        seNames[seNames.length] = $('label[for=cbSearchOnEngines-'+$(this).val()+']').text();
        var html = '<input type="hidden" name="cell['+ window._cell + ']['
                + uniqKey + '][engines][]" value="'
                + $(this).val() + '" class="cell-' + window._cell + ' cell-row-se-' + uniqKey + ' cell-data-se-engines" />';
        $('#tabContainer-frag-7 div.subForm').append(html);
    });

    // append row
    var cellOne     ='<td style="align-center">'+(action ? $('#selectSearchActions option[value='+action+']').text() : '-')+'</td>';
    var cellTwo     ='<td>'+qs+'</td>';
    var cellThree   ='<td>'+(timeframe ? $('#selectTimeframeSearch option[value='+timeframe+']').text() : '-')+'</td>';
    var cellFour    ='<td>'+(seNames.join(', '))+'</td>';
    var cellDelete  ='<td style="width:20px"><a title="Delete" class="button-delete delete-se-row" '
                        +'href="javascript:void(null)" rel="' + uniqKey + '"></a></td>';
    var row = $('<tr id="cell-tb-row-se-'+uniqKey+'">'+cellOne+cellTwo+cellThree+cellFour+cellDelete+'</tr>');
    $('#cell-qf-search').append(row);

    // recolor rows
    var c = 0;
    $('#cell-qf-search tbody tr').each(function(){
        ++c & 1 ? $(this).removeClass('alt') : $(this).addClass('alt');
    });

    // rebind delete actions
    bindDeleteSearchActions();
}

function bindDeleteSearchActions()
{
    $('.delete-se-row').unbind().bind('click', function(){
        var uniqKey = $(this).attr('rel');
        $('#cell-tb-row-se-'+uniqKey).remove();
        $('.cell-row-se-'+uniqKey).remove();

        var c = 0;
        $('#cell-qf-search tbody tr').each(function(){
            ++c & 1 ? $(this).removeClass('alt') : $(this).addClass('alt');
        });
    });
}

function buildCell()
{
    // add common features
    var desc = $('#txtCellDescription').val();
    var size = parseInt($('#txtCellSize').val());
    var type = parseInt($('input[name=radioCellType]:checked').val());

    if(!desc || !size || !(type == 1 || type == 2))
    {
        dialogAlert('Please fill in all necessary general cell information!');
        return;
    }
    if($('#cell-qf-online tbody tr').length <= 1 && $('#cell-qf-search tbody tr').length <= 1)
    {
        dialogAlert('At least one qualifier is required!');
        return;
    }

    var hiddens = [];

    hiddens[hiddens.length] = {'name': 'cell['+window._cell+'][description]', 'value': desc};
    hiddens[hiddens.length] = {'name': 'cell['+window._cell+'][size]', 'value': size};
    hiddens[hiddens.length] = {'name': 'cell['+window._cell+'][type]', 'value': type};

    // add and delete visible rows in qualifiers but leave the metadata
    // create metadata for the cell
    var c = 0;
    $('#cell-qf-online tbody tr').each(function(){
        if(c++ > 0)
        {
            hiddens[hiddens.length] = {'name': 'cell['+window._cell+'][qualifiers][]',
                'value': $(this).find('a.delete-ob-row').attr('rel')};
            $(this).remove();
        }
    });
    c =0;
    $('#cell-qf-search tbody tr').each(function(){
        if(c++ > 0)
        {
            hiddens[hiddens.length] = {'name': 'cell['+window._cell+'][qualifiers][]',
                'value': $(this).find('a.delete-se-row').attr('rel')};
            $(this).remove();
        }
    });

    // append metadata
    $.each(hiddens, function(i, v)
    {
        var html = '<input type="hidden" name="'+ v.name + '" class="cell-' + window._cell + '" value="'+v.value+'" />';
        $('#tabContainer-frag-7 div.subForm').append(html);
    });

    // create a table row for the cell
    var cellOne     ='<td style="align-center">'+desc+'</td>';
    var cellTwo     ='<td>'+size+'</td>';
    var cellThree   ='<td>'+($('#radioCellType-'+type).parent().text())+'</td>';

    var cellDelete  ='<td style="width:20px"><a title="Delete" class="button-delete delete-cell" '
                        +'href="javascript:void(null)" rel="' + window._cell + '"></a></td>';
    var row = $('<tr id="cell-row-'+window._cell+'">'+cellOne+cellTwo+cellThree+cellDelete+'</tr>');
    $('#existing-cells tbody').append(row);

    // recolor rows
    c = 0;
    $('#existing-cells tbody tr').each(function(){
        ++c & 1 ? $(this).removeClass('alt') : $(this).addClass('alt');
    });

    //re-assing and rebind all
    window._cell = $.srand(8);
    bindDeleteCell();
}

function bindDeleteCell()
{
    $('.delete-cell').unbind().bind('click', function(){
        var uniqKey = $(this).attr('rel');
        $('#cell-row-'+uniqKey).remove();
        $('.cell-'+uniqKey).remove();

        var c = 0;
        $('#existing-cells tbody tr').each(function(){
            ++c & 1 ? $(this).removeClass('alt') : $(this).addClass('alt');
        });
    });
}

/**
 * (Re)bind all actions at startup
 */
function bindStudyFromActions()
{
    // datepickers
    $('#txtBegin, #txtEnd').each(function()
    {
        $(this).unbind().attr('readonly', 'readonly')
            .datepicker();
    });

    // products selector
    $('#radioProduct-1,#radioProduct-2,#radioProduct-3').each(function()
    {
        $(this).unbind().bind('click', function()
        {
            switchProduct();
        });
    });

    switchProduct();

    $("#mainForm").unbind().bind('submit', function(){
        return false;
    });

    $('#submitBtn').button().unbind().click(function(){
        submitMain();
    });

    // show tabs and add effects
    $("#tabContainer").tabs();
    $("#mainForm").show('slow');

    //fix datepicker bug in absolution theme...
    $('.ui-datepicker').css({width : '0px'});

    $("#mainForm").unbind().bind('submit', function(){
        return false;
    });

    // survey radio switch
    setSurveyVisibility();

    $('#btnAddCriteria').unbind('click').bind('click', function()
    {
        buildCriteria();
    });
    bindDeleteCriteria()

    $('#btnAddQuota').unbind('click').bind('click', function()
    {
        buildQuota();
    });
    bindDeleteQuota();

    // cells

    window._cell = $.srand(8);

    $('#btnBuildCell').unbind('click').bind('click', function()
    {
        buildCell();
    });
    bindDeleteCell();

    // online browsing

    $('#btnAddQualifierOnlineBrowsing').unbind('click').bind('click', function()
    {
        buildOnlineBrowsing();
    });
    bindDeleteOnlineBrowsing();

    // search action

    $('#btnAddQualifierSearchEngines').unbind('click').bind('click', function()
    {
        buildSearchActions();
    });
    bindDeleteSearchActions();
}
