{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$CalendarEvent}
		<dl id="view_data_left">
			<dt class="heading">Event Details</dt>
			{view_data attribute="title"}
			{view_data attribute="location"}
			{view_data attribute="start_time"}
			{view_data attribute="end_time"}
			{view_data attribute="calendar"}
			{view_data attribute="all_day" label="all_day_event"}
			{view_data attribute="company"}
			{view_data attribute="person"}
		</dl>
		<dl id="view_data_right">
			<dt class="heading">Other Details</dt>
			{view_data attribute="url"}
			{view_data attribute="status"}
			{view_data attribute="private"}
		</dl>
		<div id="view_data_fullwidth">
			{view_data attribute="description"}
		</div>
		{/with}
	</div>
{/content_wrapper}