{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.9 $ *}
{content_wrapper}
	<div id="calendar"></div>
	<div id="add_event" style="display: none;" >
		<input type="hidden" id="event_status" />
		<form action="/?module=despatch&controller=sodespatchevents&action=saveEvent" >
			<ul>
				<li>
					<label>Title</label>
					<input type="text" name="SODespatchEvent[title]" class="title required" />
				</li>
				<li>
					<label>Status</label>
					<select name="SODespatchEvent[status]" class="required" >
						{html_options options=$status_enums}
					</select>
				</li>
				<li>
					<label>Start</label>
					<input type="text" name="SODespatchEvent[start_time]" class="datefield start_date required" />&nbsp;
					<input type="text" name="SODespatchEvent[start_time_hours]" class="start_hours" />:
					<input type="text" name="SODespatchEvent[start_time_minutes]" class="start_minutes" /></li>
				<li>
					<label>End</label>
					<input type="text" name="SODespatchEvent[end_time]" class="datefield end_date required" />&nbsp;
					<input type="text" name="SODespatchEvent[end_time_hours]" class="end_hours" />:
					<input type="text" name="SODespatchEvent[end_time_minutes]" class="end_minutes" />
				</li>
				<li>
					<label>&nbsp;</label>
					<input type="submit" name="submit" value="Create Event" />
				</li>
			</ul>
		</form>
	</div>
	<div id="legend" class="sidebar_component" style="display: none;" >
		<div>
			<h3>Legend</h3>
		</div>
		<ul>
			{foreach from=$legend key=k item=v}
				<li class="{$v}">{$k}</li>
			{/foreach}
		</ul>
	</div>
{/content_wrapper}