/**
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

/*
 * calendar.js
 * 
 * $Revision: 1.12 $
 * 
 */

$(document).ready(function() {
	
	var feed	= [],
		date	= new Date(),
		d		= date.getDate(),
		m		= date.getMonth(),
		y		= date.getFullYear();
	
	var $calendar = $('#calendar');
	
	$calendar.fullCalendar({
		header: {
			left	: 'prev,next today',
			center	: 'title',
			right	: 'month,agendaWeek,agendaDay'
		},
		firstHour		: 8,
		selectable		: true,
		selectHelper	: true,
		select			: function(start, end, allDay) {

			// http://arshaw.com/fullcalendar/docs/utilities/formatDate/
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
			
			$('#add_event').dialog({
				title		: 'Add Event',
                modal		: true,
                resizable	: false,
                autoResize	: true,
                width		: 400,
                overlay: {
                    opacity		: 0.5,
                    background	: "black"
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
						$('#add_event').dialog("close");
					}
					$calendar.fullCalendar('unselect');
				}

			});
		},
		editable: false, // set the entire calendar to editable false, then set individual events back on again
		eventClick: function(calEvent, jsEvent, view) {
			// only fire link if it's set
			if (calEvent.url != '') {
				if(calEvent.url.substr(0,1)=='/') {
					// if the link is internal, open in same window...
					window.location.href = calEvent.url;
				} else {
					// ...otherwise open in new window / tab
					window.open(calEvent.url);
				}
	        }
			return false;
	    },
	    eventDrop: function(event,dayDelta,minuteDelta,allDay,revertFunc) {
			$.uz_ajax({
				url: '/',
				data: {
					module		: "calendar",
					controller	: "calendarevents",
					action		: "updateEvent",
					type		: "drop",
					id			: event.id,
					allDay		: allDay,
					day			: dayDelta,
					minute		: minuteDelta
				},
				success: function(response) {
					if (response.success === false) {
						revertFunc();
						$('#flash').find('ul#errors').remove();
						$('#flash').append("<ul id='errors'><li>Error updating event</li></ul>");
					}
				}
			});
	    },
	    eventResize: function(event,dayDelta,minuteDelta,revertFunc) {
			$.uz_ajax({
				url: '/',
				data: {
					module		: "calendar",
					controller	: "calendarevents",
					action		: "updateEvent",
					type		: "resize",
					id			: event.id,
					day			: dayDelta,
					minute		: minuteDelta
				},
				success: function(response) {
					if (response.success === false) {
						revertFunc();
						$('#flash').find('ul#errors').remove();
						$('#flash').append("<ul id='errors'><li>Error updating event</li></ul>");
					}
				}
			});
	    },
	    loading: function( isLoading, view ) {
	    	if(isLoading==true) {
	    		$(".page_title").html("Calendar [loading...]").trigger("change");
	    	} else {
	    		$(".page_title").html("Calendar").trigger("change");
	    	}
	    }
	});

	$('#sidebar').append($('#calendar_list').show());
	
	$('#calendar_list input','#sidebar').live('click', function() {

		var $self	= $(this),
			id		= $self.attr('id').split('_')[1];
		
		$.uz_ajax({
			data: {
				module		: 'calendar',
				action		: 'change_calendar',
				change_cal	: id + '_' + $self.is(":checked"),
				ajax		: ''
			}
		});
		
		if($self.is(":checked")==true) {
			$self.parents('li').removeClass('opacity-50');
			$calendar.fullCalendar('addEventSource',feed[id]);
		} else {
			$self.parents('li').addClass('opacity-50');
			$calendar.fullCalendar('removeEventSource',feed[id]);
		}
	});
	
	/* we're now using the rel attr of li as we may have duplicate colours */
	$('#calendar_list li','#sidebar').hover(
		function () {
			if(!$(this).hasClass('opacity-50')) {
				$('.fc-event','#calendar').addClass('opacity-50');
				var calendar_class = $(this).attr('rel');
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
	
	$('form','#add_event').submit(function () {
		
		$("input[type=submit]",this).attr('disabled','disabled');
		
		var $self		= $(this),
			error_count	= 0;
		
		$(".required", $self).each(function() {
			if($(this).val()=='') {
				error_count++;
				$(this).css('borderColor','red');
			}
		});
		
		if (error_count > 0) {
			$("input[type=submit]","#add_event").removeAttr('disabled');
			return false;
		}
		
		$.uz_ajax({
			type	: "POST",
			data	: $self.serialize(),
			url		: $self.attr('action')+'&ajax=',
			success: function(response) {
			
				$calendar.data('event_status', false);
			
				if (response.success === true) {
					
					$("input[type=text],select","#add_event").each(function() {
						$self.removeAttr('style').removeAttr('disabled').val('');
					});
					
					$("input[type=submit]","#add_event").removeAttr('disabled');

					$calendar.data('event_status', true);
					
					$('#add_event').dialog("close");
					
				}
			}
		});
		
	    return false;
	});
		
	// load the events, this has to be done ajax style to allow the nice show/hide
	$.uz_ajax({
		data:{
			module	: 'calendar',
			action	: 'getCalendars',
			ajax	: ''
		},
		// ATTN: test this, could be handy to json it
		// this is bad code
		success: function(response) {
			var json_data_object = eval("(" + response + ")");
			for(var i in json_data_object) {
				switch(json_data_object[i].type) {
					case "gcal":
						feed[json_data_object[i].id] = $.fullCalendar.gcalFeed(
								json_data_object[i].gcal_url,
								{
									// put your options here
									className:'fc_'+json_data_object[i].className+' fc_'+json_data_object[i].id,
									editable:false,
									currentTimezone:'Europe/London'
								}
						);
						
						if(json_data_object[i].show==true) {
							$calendar.fullCalendar('addEventSource',feed[json_data_object[i].id]);
						}
						break;
					case "personal":
					case "group":
						feed[json_data_object[i].id]=json_data_object[i].url;
					    if(json_data_object[i].show==true) {
							$calendar.fullCalendar('addEventSource',feed[json_data_object[i].id]);
						}
						break;
				}
			}
		}
	});
	
});