{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	{form controller="crmcalendarevents" action="save"}
		{with model=$model}
			{view_section heading='calendar_details'}
				{input type='hidden' attribute='id'}
				{input type='text' attribute='title'}
				{select attribute='crm_calendar_id' label='CRM Calendar' value=$crm_calendar_id}
				{input type='date' attribute='start_date'}
				{input type='date' attribute='end_date'}
			{/view_section}
		{/with}
		{submit}
	{/form}
{/content_wrapper}