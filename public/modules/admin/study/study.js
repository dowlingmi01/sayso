/**
 * Functions for creating and editing a study
 *
 * @author alecksmart
 * @todo refactor messy html generation to use functions tor table rows and hiddens
 */

function advanceMain()
{
	var product = parseInt($('input[name=radioProduct]:checked').val());
	var error;

	switch($.trim($('.ui-tabs-selected a span').text()))
	{
		case 'Basics':
			error = validateTabsBasics();
			if(error)
			{
				dialogAlert(error);
				return;
			}
			switch(product)
			{
				case 1:
					$("#tabContainer").tabs('select', 3);
					break;
				case 2:
					$("#tabContainer").tabs('select', 1);
					break;
				case 3:
					$("#tabContainer").tabs('select', 2);
					break;
			}
			break;
		case 'Behavioral Metrics':
			error = validateTabsMetrics();
			if(error)
			{
				dialogAlert(error);
				return;
			}
			$("#tabContainer").tabs('select', 4);
			break;
		case 'Survey':
			error = validateTabsSurvey()
			if(error)
			{
				dialogAlert(error);
				return;
			}
			$("#tabContainer").tabs('select', 5);
			break;
		case 'Quotas':
			error = validateTabsQuotas();
			if(error)
			{
				dialogAlert(error);
				return;
			}
			$("#tabContainer").tabs('select', 6);
			break;
		case 'Cells':
			error = validateTabsCells();
			if(error)
			{
				dialogAlert(error);
				return;
			}
			alert('Press Save Study button to submit the form!');
			break;
		case 'ADjuster Campaign':
			error = validateTabsADCampaign();
			if(error)
			{
				dialogAlert(error);
				return;
			}
			$("#tabContainer").tabs('select', 3);
			break;
		case 'ADjuster Creative':
			error = validateTabsADCreative();
			if(error)
			{
				dialogAlert(error);
				return;
			}
			$("#tabContainer").tabs('select', 3);
			break;
	}
}

function validateTabsBasics()
{
	var product = parseInt($('input[name=radioProduct]:checked').val());
	if(!product)
	{
		return 'Please choose product!';
	}
	//Study Name*
	if(!$('#txtStudyName').val())
	{
		return 'Please fill in Study Name!';
	}
	//Sample Size
	if(false === parseInt($('#txtSampleSize').val()) > 0)
	{
		return 'Please fill in Sample Size (must be > 0)!';
	}
	//Min. Threshold
	if(false === parseInt($('#txtMinThreshold').val()) > 0)
	{
		return 'Please fill in Min. Threshold (must be > 0)!';
	}
	//Begin
	if(!$('#txtBegin').val())
	{
		return 'Please fill in Begin Date!';
	}
	//End
	if(!$('#txtEnd').val())
	{
		return 'Please fill in End Date!';
	}
	return '';
}

function validateTabsMetrics()
{
	// nothing to check here?
	return '';
}

function validateTabsSurvey()
{
	// nothing to check here?
	return '';
}

function validateTabsQuotas()
{
	// Check for sum in quotas not > 100%
	var dataCellTotal = 0;
	$('.data-cell-percentile').each(function(){
		dataCellTotal += parseInt($(this).text());
	});
	// do not bother if dataCellTotal >= 0 ... but ...
	if(dataCellTotal > 100)
	{
		return 'Quotas percentile cannot be more than 100%!';
	}
	return '';
}

function validateTabsCells()
{
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
		return 'A study must contail at least one Control cell and at least one Test cell!';
	}
	return '';
}

function validateTabsADCampaign()
{
	if($('#ac-camp-tags tr').length <= 1)
	{
		return 'At leat one tag must be created at Adjuster Campaign tab!';
	}
	return '';
}

function validateTabsADCreative()
{
	if($('#ac-creatives tr').length <= 1)
	{
		return 'At least one creative must be created at the Adjuster Creative tab!';
	}
	return '';
}

function monitorTabs(event, ui)
{
	// Anything needed here...?
}

function submitMain()
{
	// Disable sanity check almost everywhere ...


	var product = parseInt($('input[name=radioProduct]:checked').val());

	var error = validateTabsBasics();
	if(error)
	{
		dialogAlert(error);
		return false;
	}

	/**
	error = validateTabsMetrics();
	if(error)
	{
		dialogAlert(error);
		return false;
	}

	error = validateTabsSurvey();
	if(error)
	{
		dialogAlert(error);
		return false;
	}

	error = validateTabsQuotas();
	if(error)
	{
		dialogAlert(error);
		return false;
	}

	error = validateTabsCells();
	if(error)
	{
		dialogAlert(error);
		return false;
	}

	if(product == 2)
	{
		error = validateTabsADCampaign();
		if(error)
		{
			dialogAlert(error);
			return false;
		}
	}

	if(product == 3)
	{
		error = validateTabsADCreative();
		if(error)
		{
			dialogAlert(error);
			return false;
		}
	}
	*/

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
	var ifrCustomUrl	= $('#txtPasteIframeUrl').val();
	var timeframe	   = $('#selectSurveyTimeframe').val();
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
	var cellType	='<td style="width:50px" class="align-left">'+cText+'</td>';
	var cellIframe  ='<td class="align-center break-word">'+ifrCustomUrl+'</td>';
	var cellSite	='<td class="align-center break-word">'+visitUrl+'</td>';
	var cellTime	='<td class="align-center">'+( $('#selectSurveyTimeframe option[value='+timeframe+']').text() )+'</td>';
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
	var cellOne	 ='<td style="align-left">'+(gender ? $('#selectQuotaGender option[value='+gender+']').text() : '-')+'</td>';
	var cellTwo	 ='<td>'+(age ? $('#selectQuotaAge option[value='+age+']').text() : '-' )+'</td>';
	var cellThree   ='<td>'+(eth  ? $('#selectQuotaEthnicity option[value='+eth+']').text() : '-' )+'</td>';
	var cellFour	='<td class="data-cell-percentile">'+(cell ? $('#selectQuotaCellPerc option[value='+cell+']').text() : '-' )+'</td>';
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
	var action	  = $('#selectOnlineBrowsing').val();
	var url		 = $('#txtWhoVisited').val();
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
	var cellOne	 ='<td style="align-center">'+(action ? $('#selectOnlineBrowsing option[value='+action+']').text() : '-')+'</td>';
	var cellTwo	 ='<td>'+url+'</td>';
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
	var action	  = $('#selectSearchActions').val();
	var qs		  = $('#txtWhoSearchedFor').val();
	var timeframe   = $('#selectTimeframeSearch').val();
	var engines	 = $('.cb-search-on-engines:checked');

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
	var cellOne	 ='<td style="align-center">'+(action ? $('#selectSearchActions option[value='+action+']').text() : '-')+'</td>';
	var cellTwo	 ='<td>'+qs+'</td>';
	var cellThree   ='<td>'+(timeframe ? $('#selectTimeframeSearch option[value='+timeframe+']').text() : '-')+'</td>';
	var cellFour	='<td>'+(seNames.join(', '))+'</td>';
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
	
	$('#cell-adtags input[type=checkbox]:checked').each(function() {
		hiddens[hiddens.length] = {'name': 'cell['+window._cell+'][adtag][]',
			'value': $(this).attr('id').substring('cbAdTag'.length)};
		$(this).removeAttr("checked");
	});

	// append metadata
	$.each(hiddens, function(i, v)
	{
		var html = '<input type="hidden" name="'+ v.name + '" class="cell-' + window._cell + '" value="'+(v.value+'').replace(/"/g, '&quot;')+'" />';
		$('#tabContainer-frag-7 div.subForm').append(html);
	});

	// create a table row for the cell
	var cellOne	 ='<td style="align-center">'+desc+'</td>';
	var cellTwo	 ='<td>'+size+'</td>';
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

function buildDomain()
{
	var name = $.trim($('#txtDomains').val());
	if(!name)
	{
		dialogAlert('Please supply domain name!');
		return;
	}

	var uniqKey = $.srand(8);

	// create a table row for the cell
	var cellOne	 ='<td style="align-center" class="ac-domain-name">'+name+'</td>';
	var cellDelete  ='<td style="width:20px"><a title="Delete" class="button-delete delete-ac-domain" '
						+'href="javascript:void(null)" rel="' + uniqKey + '"></a></td>';
	var row = $('<tr id="ac-domains-row-'+uniqKey+'">'+cellOne+cellDelete+'</tr>');
	$('#ac-camp-domains tbody').append(row);

	// recolor rows
	c = 0;
	$('#ac-camp-domains tbody tr').each(function(){
		++c & 1 ? $(this).removeClass('alt') : $(this).addClass('alt');
	});
	bindDeleteDomain();
}

function bindDeleteDomain()
{
	$('.delete-ac-domain').unbind().bind('click', function(){
		var uniqKey = $(this).attr('rel');
		$('#ac-domains-row-'+uniqKey).remove();
		var c = 0;
		$('#ac-camp-domains tbody tr').each(function(){
			++c & 1 ? $(this).removeClass('alt') : $(this).addClass('alt');
		});
	});
}

function buildTag()
{
	var rows = $('#ac-camp-domains tbody tr:not(:first)');
	if(!rows.length)
	{
		dialogAlert('Please supply domains!');
		return;
	}

	var label   = $('#txtLabelIt').val();
	var jq	  = $('#taJQUERYSelector').val();
	var target  = $('#txtTargetURLSegment').val();

	if(!label || !jq || !target)
	{
		dialogAlert('Please supply all values for Tag-Domain Pairs!');
		return;
	}

	var hiddens = [], uniqKey = $.srand(8);

	hiddens[hiddens.length] = {'name': 'tag['+uniqKey+'][label]', 'value': label};
	hiddens[hiddens.length] = {'name': 'tag['+uniqKey+'][jq]', 'value': jq};
	hiddens[hiddens.length] = {'name': 'tag['+uniqKey+'][target]', 'value': target};

	// append metadata
	$.each(hiddens, function(i, v)
	{
		var html = '<input type="hidden" name="'+v.name+'" value="'+v.value.replace(/"/g, '&quot;')
			+'" class="tag-' + uniqKey + ' tag-data-ac-'+ v.name + '" />';
		$('#tabContainer-frag-2 div.subForm').append(html);
	});

	// add domains array
	var dNames = [];
	$(rows).each(function()
	{
		var domainName = $(this).find('td:first').text();
		dNames[dNames.length] = domainName;
		var html = '<input type="hidden" name="tag['+uniqKey+'][domain][]" value="'
				+ domainName + '" class="tag-' + uniqKey + ' tag-data-ac-domain" />';
		$('#tabContainer-frag-2 div.subForm').append(html);
		$(this).remove();
	});

	// create a table row for the cell
	var cellOne	 ='<td style="align-left" class="ac-label">'+label+'</td>';
	var cellTwo	 ='<td style="align-left" class="ac-domains-list">'+(dNames.join(', '))+'</td>';
	var cellDelete  ='<td style="width:20px"><a title="Delete" class="button-delete delete-ac-tag" '
						+'href="javascript:void(null)" rel="' + uniqKey + '"></a></td>';
	var row = $('<tr id="ac-tag-row-'+uniqKey+'">'+cellOne+cellTwo+cellDelete+'</tr>');
	$('#ac-camp-tags tbody').append(row);

	row = '<tr><td><label for="cbAdTag'+ uniqKey+'"><input type="checkbox" class="cb" name="cbAdTag" id="cbAdTag' + uniqKey + '">' + label + '</label></td></tr>';
	$('#cell-adtags tbody').append(row)
	
	// recolor rows
	var c = 0;
	$('#ac-camp-tags tbody tr').each(function(){
		++c & 1 ? $(this).removeClass('alt') : $(this).addClass('alt');
	});

	// clear all for next tag
	$('#txtLabelIt').val('');
	$('#taJQUERYSelector').val('');
	$('#txtTargetURLSegment').val('');

	bindDeleteTag();
}

function bindDeleteTag()
{
	$('.delete-ac-tag').unbind().bind('click', function(){
		var uniqKey = $(this).attr('rel');
		$('#ac-tag-row-'+uniqKey).remove();
		$('.tag-'+uniqKey).remove();

		var c = 0;
		$('#ac-camp-tags tbody tr').each(function(){
			++c & 1 ? $(this).removeClass('alt') : $(this).addClass('alt');
		});
	});
}

function buildDomainAvail()
{
	var name = $.trim($('#txtDomainsAvail').val());
	if(!name)
	{
		dialogAlert('Please supply domain name!');
		return;
	}

	var uniqKey = $.srand(8);

	// create a table row for the cell
	var cellOne	 ='<td style="align-center" class="avail-domain-name">'+name+'</td>';
	var cellDelete  ='<td style="width:20px"><a title="Delete" class="button-delete delete-avail-domain" '
						+'href="javascript:void(null)" rel="' + uniqKey + '"></a></td>';
	var row = $('<tr id="avail-domains-row-'+uniqKey+'">'+cellOne+cellDelete+'</tr>');
	$('#ac-avail-domains tbody').append(row);

	// recolor rows
	c = 0;
	$('#ac-avail-domains tbody tr').each(function(){
		++c & 1 ? $(this).removeClass('alt') : $(this).addClass('alt');
	});
	bindDeleteDomainAvail();
}

function bindDeleteDomainAvail()
{
	$('.delete-avail-domain').unbind().bind('click', function(){
		var uniqKey = $(this).attr('rel');
		$('#avail-domains-row-'+uniqKey).remove();
		var c = 0;
		$('#ac-avail-domains tbody tr').each(function(){
			++c & 1 ? $(this).removeClass('alt') : $(this).addClass('alt');
		});
	});
}

function buildAvail()
{
	var rows = $('#ac-avail-domains tbody tr:not(:first)');
	if(!rows.length)
	{
		dialogAlert('Please supply domains!');
		return;
	}

	var label   = $('#txtLabelItAvail').val();
	var jq	  = $('#taJQUERYSelectorAvail').val();

	if(!label || !jq)
	{
		dialogAlert('Please supply all values for Build Avails!');
		return;
	}

	var hiddens = [], uniqKey = $.srand(8);

	hiddens[hiddens.length] = {'name': 'creative['+window._creative+']['+uniqKey+'][label]', 'value': label};
	hiddens[hiddens.length] = {'name': 'creative['+window._creative+']['+uniqKey+'][jq]', 'value': jq};

	// append metadata
	$.each(hiddens, function(i, v)
	{
		var html = '<input type="hidden" name="'+v.name+'" value="'+v.value.replace(/"/g, '&quot;')
			+'" class="creative-' + window._creative + ' avail-' + uniqKey + ' avail-data-'+ v.name + '" />';
		$('#tabContainer-frag-3 div.subForm').append(html);
	});

	// add domains array
	var dNames = [];
	$(rows).each(function()
	{
		var domainName = $(this).find('td:first').text();
		dNames[dNames.length] = domainName;
		var html = '<input type="hidden" name="creative['+window._creative+']['+uniqKey+'][domain][]" value="'
				+ domainName + '" class="creative-' + window._creative + ' avail-' + uniqKey + ' avail-data-domain" />';
		$('#tabContainer-frag-3 div.subForm').append(html);
		$(this).remove();
	});

	// create a table row for the cell
	var cellOne	 ='<td style="align-left" class="avail-label">'+label+'</td>';
	var cellTwo	 ='<td style="align-left" class="avail-domains-list">'+(dNames.join(', '))+'</td>';
	var cellDelete  ='<td style="width:20px"><a title="Delete" class="button-delete delete-avail" '
						+'href="javascript:void(null)" rel="' + uniqKey + '"></a></td>';
	var row = $('<tr id="avail-row-'+uniqKey+'">'+cellOne+cellTwo+cellDelete+'</tr>');
	$('#ac-avails tbody').append(row);

	// recolor rows
	var c = 0;
	$('#ac-avails tbody tr').each(function(){
		++c & 1 ? $(this).removeClass('alt') : $(this).addClass('alt');
	});

	// clear all for next tag
	$('#txtLabelItAvail').val('');
	$('#taJQUERYSelectorAvail').val('');
	$('#txtTargetURLSegmentAvail').val('');

	bindDeleteAvail();
}

function bindDeleteAvail()
{
	$('.delete-avail').unbind().bind('click', function(){
		var uniqKey = $(this).attr('rel');
		$('#avail-row-'+uniqKey).remove();
		$('.avail-'+uniqKey).remove();

		var c = 0;
		$('#ac-avails tbody tr').each(function(){
			++c & 1 ? $(this).removeClass('alt') : $(this).addClass('alt');
		});
	});
}

function buildCreative()
{

	// add common features
	var name		= $('#txtAvailName').val();
	var url		 = $('#txtAvailCreativeUrl').val();
	var segment	 = $('#txtAvailTargetURLSegment').val();
	var mimetype	= $('#selectCreativeMimeType').val();


	if(!name || !url || !segment || !mimetype)
	{
		dialogAlert('Please fill in all necessary creative information!');
		return;
	}
	if($('#ac-avails tbody tr').length <= 1)
	{
		dialogAlert('Please create more avails!');
		return;
	}

	var hiddens = [];

	hiddens[hiddens.length] = {'name': 'creative['+window._creative+'][name]', 'value': name};
	hiddens[hiddens.length] = {'name': 'creative['+window._creative+'][url]', 'value': url};
	hiddens[hiddens.length] = {'name': 'creative['+window._creative+'][segment]', 'value': segment};
	hiddens[hiddens.length] = {'name': 'creative['+window._creative+'][mimetype]', 'value': mimetype};

	// add and delete visible rows in avails but leave the metadata
	// create metadata for the creative
	var c = 0;
	$('#ac-avails tbody tr').each(function(){
		if(c++ > 0)
		{
			hiddens[hiddens.length] = {'name': 'creative['+window._creative+'][avails][]',
				'value': $(this).find('a.delete-avail').attr('rel')};
			$(this).remove();
		}
	});

	// append metadata
	$.each(hiddens, function(i, v)
	{
		var html = '<input type="hidden" name="'+ v.name + '" class="creative-' + window._creative + '" value="'+v.value.replace(/"/g, '&quot;')+'" />';
		$('#tabContainer-frag-3 div.subForm').append(html);
	});

	// create a table row for the cell
	var cellOne	 ='<td style="align-left">'+name+'</td>';
	var cellTwo	 ='<td style="align-left">'+($('#selectCreativeMimeType option:selected').text())+'</td>';
	var cellThree   ='<td style="align-left">'+url+'</td>';


	var cellDelete  ='<td style="width:20px"><a title="Delete" class="button-delete delete-creative" '
						+'href="javascript:void(null)" rel="' + window._cell + '"></a></td>';
	var row = $('<tr id="creative-row-'+window._cell+'">'+cellOne+cellTwo+cellThree+cellDelete+'</tr>');
	$('#ac-creatives tbody').append(row);

	// recolor rows
	c = 0;
	$('#ac-creatives tbody tr').each(function(){
		++c & 1 ? $(this).removeClass('alt') : $(this).addClass('alt');
	});

	// ready for new id
	window._creative = $.srand(8);

	$('#txtAvailName').val('');
	$('#txtAvailCreativeUrl').val('');
	$('#txtAvailTargetURLSegment').val('');

	bindDeleteCreative();
}

function bindDeleteCreative()
{
	$('.delete-creative').unbind().bind('click', function(){
		var uniqKey = $(this).attr('rel');
		$('#creative-row-'+uniqKey).remove();
		$('.creative-'+uniqKey).remove();

		var c = 0;
		$('#ac-creatives tbody tr').each(function(){
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

	// show tabs and add effects
	$("#tabContainer").tabs(
		{
			select: function(event, ui) {
				monitorTabs(event, ui);
			}
		}
	);
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

	window._cell		= $.srand(8);
	window._creative	= $.srand(8);

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

	// aj campaign

	$('#btnAddDomain').unbind('click').bind('click', function()
	{
		buildDomain();
	});
	bindDeleteDomain();

	$('#btnAddTag').unbind('click').bind('click', function()
	{
		buildTag();
	});
	bindDeleteTag();

	$('#btnAddDomainAvail').unbind('click').bind('click', function()
	{
		buildDomainAvail();
	});
	bindDeleteDomainAvail();

	$('#btnAddAvail').unbind('click').bind('click', function()
	{
		buildAvail();
	});
	bindDeleteAvail();

	$('#btnAddCreative').unbind('click').bind('click', function()
	{
		buildCreative();
	});
	bindDeleteCreative();

	// main form submit
	$("#mainForm").unbind().bind('submit', function(){
		return false;
	});

	$('#submitBtn').unbind().click(function(){
		submitMain();
	}).button();

	$('#nextBtn').unbind().click(function(){
		advanceMain();
	}).button();

}