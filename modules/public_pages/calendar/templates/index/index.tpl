{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.8 $ *}
{content_wrapper}
	<div id="calendar"></div>
	{* DO NOT ADD ANY STYLE / DISABLED ATTRIBUTES TO THE INPUT ELEMENTS FOLLOWING *}
	<div id="calendar"></div>
	<div id="add_event" style="display: none;" >
		<input type="hidden" id="event_status" />
		<form action="/?module=calendar&amp;controller=calendarevents&amp;action=new_ajax_event" >
			<ul>
				<li>
					<label>Title</label>
					<input type="text" name="CalendarEvent[title]" class="title required" />
				</li>
				<li>
					<label>Start</label>
					<input type="text" name="CalendarEvent[start_time]" class="datefield start_date required" />&nbsp;
					<input type="text" name="CalendarEvent[start_time_hours]" class="start_hours" />:
					<input type="text" name="CalendarEvent[start_time_minutes]" class="start_minutes" /></li>
				<li>
					<label>End</label>
					<input type="text" name="CalendarEvent[end_time]" class="datefield end_date required" />&nbsp;
					<input type="text" name="CalendarEvent[end_time_hours]" class="end_hours" />:
					<input type="text" name="CalendarEvent[end_time_minutes]" class="end_minutes" />
				</li>
				<li>
					<label>Calendar</label>
					<select name="CalendarEvent[calendar_id]" class="required" >
						{html_options options=$writable_calendars}
					</select>
				</li>
				<li>
					<label>&nbsp;</label>
					<input type="submit" name="submit" value="Create Event" />
				</li>
			</ul>
		</form>
	</div>
	<div id="calendar_list" class="sidebar_component" style="display: none;" >
		<div>
			<h3>My Calendars</h3>
		</div>
		<ul>
			{foreach from=$calendars key=k item=v}
				{if $v.show == true}
					<li rel="fc_{$v.id}" class="fc_{$v.className}">{$v.name} <input type="checkbox" id="calendar_{$v.id}" name="calendar[{$v.id}]" checked="checked" /></li>
				{else}
					<li rel="fc_{$v.id}" class="fc_{$v.className} opacity-50">{$v.name} <input type="checkbox" id="calendar_{$v.id}" name="calendar[{$v.id}]" /></li>
				{/if}
			{/foreach}
		</ul>
	</div>
{/content_wrapper}