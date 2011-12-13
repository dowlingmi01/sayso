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

	/*$('.button-show-progress').each(function(){
		var v = parseInt($(this).attr('data-rel'));
		$(this).progressbar({
			value: v
		});
	});*/

	// save dates with ajax
	$('.button-datepicker').each(function()
	{
		var _this = this;
		$(this).datetimepicker({
			dateFormat: 'yy-mm-dd',
			timeFormat: 'hh:mm:ss',
			stepHour: 2,
			stepMinute: 10,
			showOn: "button",
			buttonImage: "/images/calendar.gif",
			buttonImageOnly: true,
			onClose: function(dateText, inst)
			{
				$(this).val(dateText);
				$(this).parent().find('img.ui-datepicker-trigger').attr('src', '/images/spinners/spinner-16x16.gif');
				$(this).parent().find('span.admin-date').text(dateText.substr(0, 10));
				var data = {
					field	   : ($(_this).hasClass('date-begin-visible') ? 'begin_date' : 'end_date'),
					date		: dateText,
					study_id	: $(this).parent().find('span.admin-date').attr('data-id')
				};
				$.ajax({
					url		 : '/admin/study/set-time',
					dataType	: 'json',
					data		: data,
					success	 : function(data)
					{
						$(_this).parent().find('img.ui-datepicker-trigger').attr('src', '/images/calendar.gif');
						if(data.messages.length > 0)
						{
							dialogAlert(data.messages.join("<br />"));
						}
					}
				});
			}
		});
	});

	$('.change-status').unbind().bind('click', function()
	{
		var status = $(this).attr('data-status');
		// silently exit when you cannot change it
		if(status > 0)
		{
			return false;
		}
		if(confirm('Launch the study now?\nYou will not be able to edit the study any more...'))
		{
			var _this = this;
			var data = {
				status	  : 10,//status, - currently is set statically
				study_id	: $(this).attr('rel')
			};
			
			$(_this).removeClass('button-study-status-indesign').addClass('button-spinner-16');
			
			$.ajax({
				url		 : '/admin/study/set-status',
				dataType	: 'json',
				data		: data,
				success	 : function(data)
				{
					if(data.result)
					{
						// show changed status
						$(_this).removeClass('button-spinner-16')
							.addClass('button-study-status-launched')
							.attr('data-status', 10);
					}
					else
					{
						// set status back
						$(_this).removeClass('button-spinner-16')
							.addClass('button-study-status-indesign');
					}
					if(data.messages.length > 0)
					{
						dialogAlert(data.messages.join("<br />"));
					}
				}
			});
		}
		return false;
	});

	// absolution theme bugfix
	$('#ui-datepicker-div').hide();

	// stop controls
	$('.y-n input[type=checkbox]').css({cursor:'pointer'}).each(function(){
		$(this).unbind().bind('click', function()
		{
			$(this).hide();
			$(this).parent().append('<img src="/images/spinners/spinner-16x16.gif" width="16" height="16" style="vertical-align:middle" />');
			var _this = this;
			var data = {
				study_id	: $(this).val()
			};
			$.ajax({
				url		 : '/admin/study/set-stopped',
				dataType	: 'json',
				data		: data,
				success	 : function(data)
				{
					if(data.messages.length > 0)
					{
						dialogAlert(data.messages.join("<br />"));
					}
					$(_this).show();
					$(_this).parent().find('img').remove();
				}
			});

		});
	});

}

$(function(){bindLocal();});
