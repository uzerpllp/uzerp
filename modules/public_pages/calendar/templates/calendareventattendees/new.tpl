{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="calendareventattendees" action="save"}
		{with model=$models.CalendarEventAttendee legend="CalendarEventAttendee Details"}
			{input type='hidden'  attribute='id' }
			{select attribute='calendar_event_id' class="numeric" }
			{input type='text'  attribute='person_name' class="compulsory" }
			{input type='text'  attribute='person_email' }
			{input type='checkbox'  attribute='reminder' }
			{input type='text'  attribute='reminder_interval' }
			{select attribute='person_id' }
		{/with}
		{submit}
	{/form}
{/content_wrapper}