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

}