{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="calendars" action="save"}
		{with model=$models.Calendar}
			{input type='hidden' attribute='type' value='personal' }
			{view_section heading='calendar_details'}
				{input type='text' attribute='name'}
				<dt>Calendar Colour:</dt>
				<dd><ul class="calendar_colours">
					{foreach from=$colours key=key item=value}
						<li style="background-color: {$value};"><input name="Calendar[colour]" value="{$value}" type="radio" class="radio"></li>
					{/foreach}
				</ul></dd>
			{/view_section}
		{/with}
		{with model=$models.CalendarShareCollection}
			{view_section heading="sharing"}
				<dt><label for="username">Shared users</label>:</dt><dd>
					<p>These users will have read access to this calendar.</p>
					<select id="username" name="CalendarShareCollection[username][]" multiple>
						{html_options options=$users selected=$shared_users}
					</select>
				</dd>
			{/view_section}
		{/with}
		{submit}
	{/form}
{/content_wrapper}