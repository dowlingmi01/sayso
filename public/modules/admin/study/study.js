/**
 * Functions for creating and editing a study
 *
 * @author alecksmart
 */

function submitMain()
{    
    //alert('Not implemented yet!');return false;
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
    
    $('#submitBtn').button().unbind().click(function(){
        submitMain();
    });

    // show tabs and add effects
    $("#tabContainer").tabs();
    $("#mainForm").show('slow');
    
    //fix datepicker bug in absolution theme...
    $('.ui-datepicker').css({width : '0px'});
}