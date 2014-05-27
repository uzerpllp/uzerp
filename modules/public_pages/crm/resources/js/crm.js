 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/*
 * despatch.js
 * 
 * $Revision: 1.9 $
 * 
 */

$(document).ready(function() {
	
	var $calendar = $('#calendar');
	
	$calendar.fullCalendar({
		header: {
			left	: 'prev,next today',
			center	: 'title',
			right	: ''
		},
		allDaySlot		: false,
		defaultView		: 'month',
		editable		: false,
		weekends		: true,
		minTime			: 8,
		maxTime			: 18,
		selectable		: true,
		selectHelper	: true,
		slotMinutes		: 15,
		events			: "/?module=crm&controller=crmcalendarevents&action=get_events",

		select: function(start, end, allDay) {

			var start_day		= formatDay(start.getDate()),
				start_month		= formatMonth(start.getMonth()),
				end_day			= formatDay(end.getDate()),
				end_month		= formatMonth(end.getMonth());
			
			$('input.start_date', '#add_event').val(start_day + '/' + start_month + '/' + start.getFullYear());
			$('input.end_date', '#add_event').val(end_day + '/' + end_month + '/' + end.getFullYear());
			
			$('#add_event').dialog('open');
			
		},
		eventClick: function(calEvent, jsEvent, view) {
		
			if (calEvent.id) {
				window.location.href = "/?module=crm&controller=crmcalendarevents&action=view&id=" + calEvent.id;
	            return false;
	        }
	        
		},
	    loading: function( isLoading, view ) {
	    
	    	if (isLoading == true) {
	    		$(".page_title").html("CRM Calendar [loading...]");
	    	} else {
	    		$(".page_title").html("CRM Calendar");
	    	}
	    	
	    },
	    windowResize: function(view) {
	    	$('.fc-agenda-body', this).css('height','auto');
	    }
	});

	// append the calendars list to the sidebar
	$('#sidebar').append($('#calendars').show());
	
	$('#add_event').dialog({
		title: 'Add Event',
		modal: true,
		resizable: false,
		autoResize: true,
		autoOpen: false,
		width: 400,
		overlay: {
			opacity: 0.5,
			background: "black"
		},
		close: function(event, ui) { 
		
			if ($calendar.data('event_status') === true) {
			
				// we're refetching the events from the data source
				// if we could pass data between the dialog and this function we'd be able to paint it directly
				// but it's better than forcing a refresh
				
				$calendar.fullCalendar('refetchEvents');
				
			} else {
			
				$("input[type=text],select","#add_event").each(function() {
					$(this).removeAttr('style').removeAttr('disabled').val('');
				});
				
				$("input[type=submit]","#add_event").removeAttr('disabled');
				
			}
			
			$calendar.fullCalendar('unselect');
			
		}

	}); 
	
	$('form', '#add_event').submit(function (event) {
		
		event.preventDefault();
		
		var $self		= $(this),
			error_count = 0;
		
		$("input[type=submit]", $self).attr('disabled', 'disabled');
		
		$(".required", $self).each(function() {
			
			var $self = $(this);
			
			if ($self.val() == '') {
				error_count++;
				$self.css('borderColor', 'red');
			}
			
		});
		
		if (error_count > 0) {
			$("input[type=submit]", "#add_event").removeAttr('disabled');
			return false;
		}
		
		$.uz_ajax({
			type		: "POST",
			data		: $self.serialize(),
			dataType	: 'json',
			url			: $self.attr('action') + '&ajax=',
			success		: function(response) {
			
				if (response.status === true) {
					
					$("input[type=text], select", "#add_event").each(function() {
						$(this).removeAttr('style').removeAttr('disabled').val('');
					});
					
					$("input[type=submit]", "#add_event").removeAttr('disabled');

					$calendar.data('event_status', true);
					
					$('#add_event').dialog("close");
					
				}
				
			}
		
		});
		
	});
	
	$('.fc-agenda-body').css('height','auto');
	
});
