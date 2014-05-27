 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/*
 * despatch.js
 * 
 * $Revision: 1.11 $
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
		defaultView		: 'agendaWeek',
		editable		: false,
		weekends		: false,
		minTime			: 8,
		maxTime			: 18,
		selectable		: true,
		selectHelper	: true,
		slotMinutes		: 15,
		events			: "/?module=despatch&controller=sodespatchevents&action=getEvents",

		select: function(start, end, allDay) {

			var start_day		= formatDay(start.getDate()),
				start_month		= formatMonth(start.getMonth()),
				start_hour		= formatHour(start.getHours()),
				start_minute	= formatMinute(start.getMinutes()),
				end_day			= formatDay(end.getDate()),
				end_month		= formatMonth(end.getMonth()),
				end_hour		= formatHour(end.getHours()),
				end_minute		= formatMinute(end.getMinutes());
			
			$('input.start_date', '#add_event').val(start_day+'/'+start_month+'/'+start.getFullYear());
			$('input.start_hours', '#add_event').val(start_hour);
			$('input.start_minutes', '#add_event').val(start_minute);
			$('input.end_date', '#add_event').val(end_day+'/'+end_month+'/'+end.getFullYear());
			$('input.end_hours', '#add_event').val(end_hour);
			$('input.end_minutes', '#add_event').val(end_minute);
			
			$('#add_event').dialog('open');
			
		},
		eventClick: function(calEvent, jsEvent, view) {
			if (calEvent.id) {
				window.location.href = "/?module=despatch&controller=sodespatchevents&action=view&id="+calEvent.id;
	            return false;
	        }
		},
	    eventDrop: function(event,dayDelta,minuteDelta,allDay,revertFunc) {
			
			$.uz_ajax({
				url:'/',
				data:{
					module		: "despatch",
					controller	: "sodespatchevents",
					action		: "updateEvent",
					type		: "drop",
					id			: event.id,
					day			: dayDelta,
					minute		: minuteDelta
				},
				success: function(response) {
					if(response.success === false) {
						revertFunc();
						$('#flash').find('ul#errors').remove();
						$('#flash').append("<ul id='errors'><li>Error updating event</li></ul>");
					}
				}
			});
			
	    },
	    eventResize: function(event,dayDelta,minuteDelta,revertFunc) {
			
	    	$.uz_ajax({
				url:'/',
				data:{
					module		: "despatch",
					controller	: "sodespatchevents",
					action		: "updateEvent",
					type		: "resize",
					id			: event.id,
					day			: dayDelta,
					minute		: minuteDelta
				},
				success: function(response) {
					if(response.success === false) {
						revertFunc();
						$('#flash').find('ul#errors').remove();
						$('#flash').append("<ul id='errors'><li>Error updating event</li></ul>");
					}
				}
			});
	    },
	    loading: function( isLoading, view ) {
	    	if(isLoading==true) {
	    		$(".page_title").html("Delivery and Despatch [loading...]");
	    	} else {
	    		$(".page_title").html("Delivery and Despatch");
	    	}
	    },
	    windowResize: function(view) {
	    	$('.fc-agenda-body',this).css('height','auto');
	    }
	});

	$('#sidebar').append($('#legend').show());
	
	$('#legend ul li','#sidebar').hover(
		function () {
			if(!$(this).hasClass('opacity-50')) {
				$('.fc-event','#calendar').addClass('opacity-50');
				var calendar_class = $(this).attr('class');
				$('.fc-event','#calendar').each(function() {
					if($(this).hasClass(calendar_class)) {
						$(this).removeClass('opacity-50');
					}
				});
			}
		}, 
		function () {
			$('.fc-event','#calendar').removeClass('opacity-50');
		}
	);
	
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
			if($calendar.data('event_status') === true) {
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
		
		$("input[type=submit]", $self).attr('disabled','disabled');
		
		$(".required", $self).each(function() {
			if($(this).val()=='') {
				error_count++;
				$(this).css('borderColor','red');
			}
		});
		
		if (error_count > 0) {
			$("input[type=submit]", "#add_event").removeAttr('disabled');
			return false;
		}
		
		$.uz_ajax({
			type	: "POST",
			data	: $self.serialize(),
			url		: $self.attr('action')+'&ajax=',
			success	: function(response) {
			
				$calendar.data('event_status', false);
				
				if(response.success === true) {
					
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
