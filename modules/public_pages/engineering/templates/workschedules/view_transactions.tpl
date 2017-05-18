{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$WorkSchedule}
			{view_data attribute="job_no"}
			{view_data attribute="description"}
			{view_data attribute="start_date"}
			{view_data attribute="end_date" modifier="overdue"}
			{view_data attribute="status"}
			{view_data attribute="centre_id"}
			{view_data attribute="planned_time"}
			{view_data attribute="actual_time"}
			{view_data attribute="mf_downtime_code_id" label='Downtime Code'}
		{/with}
	</div>
	{include file='elements/datatable.tpl'}
{/content_wrapper}