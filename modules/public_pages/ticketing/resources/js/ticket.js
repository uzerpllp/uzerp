 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/*
 * ticketing.js
 * 
 * $Revision: 1.6 $
 * 
 */

$(document).ready(function(){
	
	/* ticketing -> tickets -> new */
	
	$("#Ticket_originator_person_id","#ticketing-tickets-new").live("change", function(){
		
		$('#Ticket_originator_email_address').uz_ajax({
			data:{
				module		: 'ticketing',
				controller	: 'tickets',
				action		: 'getEmail',
				person_id	: $('#Ticket_originator_person_id').val(),
				company_id	: $('#Ticket_originator_company_id').val(),
				ajax		: ''
			}
		});
		
	});
	
	$('.quick_response', '#ticketing-tickets-view').live('click', function() {
		
		var $self					= $(this),
			$quick_response_item	= $('.quick_response_item'),
			$main_with_sidebar		= $("#main_with_sidebar");
			
		if (!$quick_response_item.length) {
			
			$('#ticket_responses > ul').append('<li class="site quick_response_item"><textarea class="quick_response_input"></textarea></li>');
			$self.html('Save Ticket Response');
			
			
			$main_with_sidebar.scrollTop($main_with_sidebar.prop("scrollHeight"));
			

			$('.quick_response_input').focus();
						
		} else {

			// we must be saving
			
			if ($('.quick_response_input').val().length > 0) {
				
				//save_response
				
				$.uz_ajax({
					url: '?module=ticketing&controller=tickets&action=save_response&ajax=&',
					data:{
						response	: $('.quick_response_input').val(),
						ticket_id	: $('#Ticket_id').val(),
						ajax		: ''
					},
					type: 'POST',
					dataType: 'json',
					success: function(data) {

						if (data.success == true) {
							refresh_current_page();
						} else {
							alert('Error saving response');
						}

					}
				});
				
			} else {
				
				$quick_response_item.remove();
				$self.html('Quick Response');
				
			}
			
		}
		
	});

	/* ticketing -> queues -> new */
	
	$("#TicketQueue_owner","#ticketing-queues-new").live("change", function(){
		
		$('#TicketQueue_email_address').uz_ajax({
			data:{
				module		: 'ticketing',
				controller	: 'queues',
				action		: 'getEmail',
				username	: $('#TicketQueue_owner').val(),
				ajax		: ''
			}
		});
		
	});

});
