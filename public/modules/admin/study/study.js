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

    // rebind form submit and submit
    $("#mainForm").unbind().bind('submit', function(){
        return true;
    });
    $("#mainForm").submit();
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
    var hiddens = [];
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
    hiddens[hiddens.length] = {'name': 'cType', 'value': cType};
    hiddens[hiddens.length] = {'name': 'iframeCustomUrl', 'value': ifrCustomUrl};
    hiddens[hiddens.length] = {'name': 'visitUrl', 'value': visitUrl};
    hiddens[hiddens.length] = {'name': 'timeframe', 'value': timeframe};

    // append data
    $.each(hiddens, function(i, v)
    {
        var html = '<input type="hidden" name="criteria['
                + criteria + ']['+v.name+']" value="'
                + v.value + '" class="hidden-criteria-'+criteria+'" />';
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
    })

}