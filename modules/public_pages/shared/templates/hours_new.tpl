{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.7 $ *}
{content_wrapper}
	{form controller="hours" action="save"}
		{with model=$models.Hour}
			<dl id="view_data_left">
				{select attribute="person_id" label="Enter hours for" options=$people}
				{view_section heading="hour_details"}
					{input type='hidden' attribute='id' }
					{datetime attribute='start_time'}
					{interval attribute='duration'}
					{select attribute="type_id"}
				{/view_section}
				{view_section heading="linked to"}
					{select attribute="opportunity_id" force=true}
					{select attribute="project_id" force=true}
					{select attribute="task_id" force=true}
					{select attribute="ticket_id" force=true}
				{/view_section}
				{view_section heading=""}
					{submit}
					{include file='elements/saveAnother.tpl'}
				{/view_section}
			</dl>
			<dl id="view_data_right">
				{view_section heading="Description"}
					{textarea attribute='description' tags=none}
				{/view_section}
				{view_section heading="additional_details"}
					{input type='checkbox' attribute='billable'}
					{input type='checkbox' attribute='invoiced'}
					{input type='checkbox' attribute='overtime'}
				{/view_section}
				{view_section heading="summary"}
					<dd id='hours_summary'>
						{include file='elements/datatable_inline.tpl'}
					</dd>
				{/view_section}
			</dl>
		{/with}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}