{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="calendarevents" action="save"}
	{foreach from=$cal_fields item=value key=fieldname}
		<input type="hidden" name="CalendarEvent[{$fieldname}]" value="{$value}" />
	{/foreach}
	<p class="center">The dates of the event you have entered clash with another of your events. Would you like to book this event in anyway or go back to choose another day.</p>
	<p class="center"><input type="submit" value="Book in anyway" name="book" />&nbsp;<input type="submit" value="Try new dates" name="dont_book" /></p>
	{/form}
{/content_wrapper}