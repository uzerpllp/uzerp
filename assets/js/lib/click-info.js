/**
 * click-info.js
 *
 * Handle the retrieval and display of additional information
 * using jQueryUI tooltips
 *
 *(c) 2000-2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/
$(document).ready(function() {

	$(document).on('click', 'img.click-info', function(event){
		// Remove any open tooltips
		$.each($('.ui-tooltip'), function (index, element) {
			$(this).remove();
		});
		

		
		// Build the target URL, adding ajax param so we get a simple response
		// if the user is logged-out
	    var module = $(this).parent().data('module');
	    var controller = $(this).parent().data('controller');
	    var id = $(this).parent().data('id');
		var $targetUrl = '/?module=' + module + '&controller=' + controller + '&action=clickinfo&id=' + id + '&ajax=';
		var $item = $(this);
		
		// Setup visual elements
		$item.attr('title', '');
		//$(document.body).css('overflow', 'hidden');
		$item.addClass('spinner');
		
		// Request the data and handle the response
		$.ajax({
			type: 'GET',
			url: $targetUrl
		})
		.done( function(response) {
			// If the user is logged-out, reload the page to allow authentication
			if ($.trim(response) == 'LOGIN_TIMEOUT') {
				window.location.reload();
				return;
			};
			// Prepare and show the tooltip
			var $content = ''; 
			$.each(response, function(k, v) {
				$content = $content + '<li><strong>' + k + ': </strong>' + v + '</li>';
			});
			$item.tooltip({
				content: '<ul>' + $content + '</ul>',
				position: { my: 'center bottom', at: 'center top-10', collision: 'none', of: $item }
			});
			$item.mouseover(function() {
				if ($item.data('ui-tooltip')) {
					$item.tooltip('open');
				}
			});
			
			$item.mouseleave(function() {
				if ($item.data('ui-tooltip')) {
					$item.tooltip('destroy');
					$item.attr('title', 'More info');
				}
			});
		})
		.error( function(xhr){
			$item.tooltip({
				content: '<ul><li>Failed to load info.</li><li>' + xhr.status + ' - ' +  xhr.statusText + '</li></ul>',
				position: { my: 'center bottom', at: 'center top-10', collision: 'none', of: $item }
			});
			$item.tooltip('open');
		})
		.always( function(){
			// Stop the spinner
			$item.removeClass('spinner');
		});
	});
});
