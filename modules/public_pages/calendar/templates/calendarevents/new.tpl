{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.6 $ *}
{content_wrapper}
	{form controller="calendarevents" action="save"}
	{with model=$models.CalendarEvent legend="CalendarEvent Details"}
	{input type='hidden'  attribute='id' }
	{input type='hidden'  attribute='usercompanyid' }
	<dl id="view_data_left">
		<dt class="heading">Event Details</dt>
			{input type='text' attribute='title' }
			{input type='text' attribute='location' }
			{datetime attribute='start_time' class="compulsory" }
			{datetime attribute='end_time' class="compulsory" }
			{input type='checkbox' attribute='all_day' }
			{select attribute="calendar_id" nonone='true' options=$calendar_id  class="compulsory" }
			{select attribute='company_id' constrains='person_id'}
			{select attribute='person_id' depends='company_id'}
	</dl>
	<dl id="view_data_right">
		<dt class="heading">Other Details</dt>
			{input type='text' attribute='url' }
			{input type='text' attribute='status' }
			{input type='checkbox' attribute='private' }
			{* This could be re-enabled as a future feature *}
			{* {if $possible_owners}
				<dt>
					<label for="CalendarEvent_owner">Whose Calendar?:</label>
				</dt>
				<dd>
					<select id="CalendarEvent_owner" name="CalendarEvent[owner]">
						{html_options values=$possible_owners output=$possible_owners selected=$smarty.const.EGS_USERNAME}
					</select>
				</dd>
			{/if} *}
	</dl>
	<div id="view_data_fullwidth">
		{textarea  attribute='description' }
	</div>
	{/with}
	<div id="egs_calendar_event_attendee_container" class="grid_form_container">
	<table id="gridform" cellspacing="0" cellpadding="0">
		<thead>
			<tr>
				<th>Person</th>
				<th>Reminder</th>
				<th>Reminder Interval</th>
			</tr>
		</thead>
		<tfoot>
		<tr>
		<td colspan="2">{submit tags='none' id='grid_submit'}</td>
		<td colspan="1"><input type="button" value="Add Attendee" id="addrowbutton" /></td>
		</tr>
		</tfoot>
		<tbody>
			<tr id="element"></tr>
			{*{if $CalendarEventAttendees}
				{foreach from=$CalendarEventAttendees item=attendee key=key}
					<tr id="element">
					{with model=$models.CalendarEventAttendee}
						<td>{select class='calendar_event_attendee_person_id'  attribute='person_id' tags='none' postfix="[$key]" forceselect=true value=$attendee.person_id}</td>
						<td>{input type='checkbox' attribute='reminder' tags='none' postfix="[$key]"}</td>
						<td>{interval attribute='reminder_interval' tags='none' postfix="[$key]"}</td>
					{/with}
					</tr>
				{/foreach}
			{/if}*}
		</tbody>
	
	</table>
	</div>
	{/form}
	<form>
	<table>
	<tr class="gridrow" id="rowtemplate" style="display:none;">
		{with model=$models.CalendarEventAttendee}
			<td>{select class='calendar_event_attendee_person_id'  attribute='person_id' tags='none' postfix='[_REPLACE_]' forceselect=true}</td>
			<td>{input type='checkbox' attribute='reminder' tags='none' postfix='[_REPLACE_]'}</td>
			<td>{interval attribute='reminder_interval' tags='none' postfix='[_REPLACE_]'}</td>
		{/with}
	</tr>
	</table>
	</form>
	</div>
{/content_wrapper}