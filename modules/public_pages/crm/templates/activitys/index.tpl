{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}
{content_wrapper}

	<div id="calendar"></div>
	
	<div id="add_event" style="display: none;" >
		<form action="/?module=crm&controller=crmcalendarevents&action=save&" >
			<ul>
				<li>
					<label>Title</label>
					<input type="text" name="CRMCalendarEvent[title]" class="title required" />
				</li>
				<li>
					<label>Calendar</label>
					<select name="CRMCalendarEvent[crm_calendar_id]">
						{foreach from=$dialog_calendars key=k item=v}
							<option value="{$k}">{$v}</option>
						{/foreach}
					</select>
				</li>
				<li>
					<label>Start</label>
					<input type="text" name="CRMCalendarEvent[start_date]" class="datefield start_date required" />
				</li>
				<li>
					<label>End</label>
					<input type="text" name="CRMCalendarEvent[end_date]" class="datefield end_date required" />
				</li>
				<li>
					<label>&nbsp;</label>
					<input type="submit" name="submit" value="Create Event" />
				</li>
			</ul>
		</form>
	</div>
	
	<div id="calendars" class="sidebar_component" style="display: none;" >
		<div>
			<h3>Calendars</h3>
		</div>
		<ul>
			{foreach from=$calendars key=k item=v}
				<li class="fc_{$v.class}" data-calendar-id="{$k}">{$v.title}</li>
			{/foreach}
		</ul>
	</div>
	
{/content_wrapper}