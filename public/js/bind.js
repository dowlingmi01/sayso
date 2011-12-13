/**
 * Main setup script and bindings for Sayso
 * @author davidbjames
 */
$(function () {

	// modals

	$.subscribe(
		'/modal/open',
		function (url) {
			a.ajax({
				url : url,
				success : function (data, status) 
				{
					$('#modal-window')
						.html(data)
						.lightbox_me({
							onLoad : function () { },
							onClose : function () { 
								$.publish('/modal/closed'); 
							},
							modalCSS: {top: '170px'}
						});
					return true;
				}
			});
		}
	);
	
	$('a.modal-control').live('click', function (e) {
		e.preventDefault();
		$.publish('/modal/open', [$(this).attr('href')]);
	});
	
	// modal replacing (going from one to another)

	$.subscribe(
		'/modal/replace',
		function (url) {
			$('#modal-window').hide(0, function () {
				a.ajax({
					url : url,
					success : function (data)
					{
						$('#modal-window').html(data).show();
					}	
				});
			});
		}
	);
	
	$('a.modal-replace').live('click', function (e) {
		e.preventDefault();
		$.publish('/modal/replace', [$(this).attr('href')]);
	});
	
	// modal closing
	
	$.subscribe('/modal/close', function() {
		$('#modal-window').trigger('close');
		// this will also trigger the onClose callback
		// defined above, which publishes '/modal/closed'
	});
	
	$('a.modal-close').live('click', function (e) {
		e.preventDefault();
		$.publish('/modal/close');
	});
	
	// tabs
	
	$('a.tab').live('click', function (e) {
		e.preventDefault();
		
		// update selected tab
		$('a.tab').removeClass('selected');
		$(this).addClass('selected');
		
		a.ajax({
			url : $(this).attr('href'),
			// flag to tell controller to return
			// only the actual content of the view
			data : { innerContent : true },  
			success : function (data) {
				$('#tab-content').html(data);
			}
		});
	}); 
	
	// navigation
	
	$('#nav a:not(.modal-control),a.nav').live('click', function (e) { 
		e.preventDefault();
	
		$('#nav a').removeClass('selected');
		$(this).addClass('selected'); 
		
		if ($('#header span.for-motif').is(':hidden'))
		{
			$('#header span.for-motif').fadeIn('fast');
		}
		
		var _href = $(this).attr('href');
		a.ajax({
			url : _href,
			data : { innerContent : true },
			success : function (data) {
				$('#content').html(data);
			}
		});
	});
	
	// "inner content" (#content div)
	
	$('a.inner-content').live('click', function(e) {
		e.preventDefault();
		var _href = $(this).attr('href');
		a.ajax({
			url : _href,
			data : { innerContent : true },
			success : function (data) {
				$('#content').html(data);
			}
		});
	});
	
	// "outer content" (#container div) includes the navigation header 
	
	$('a.outer-content').live('click', function(e) {
		e.preventDefault();
		var _href = $(this).attr('href');
		a.ajax({
			url : _href,
			success : function (data) {
				$('#container').html(data);
			}
		});
	});
});

