{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.2 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$WorkSchedule}
			<dl class="float-left">
				{view_data attribute="job_no"}
				{view_data attribute="description"}
				{view_data attribute="start_date"}
				{view_data attribute="end_date" modifier="overdue"}
				{view_data attribute="status"}
				{view_data attribute="centre_id"}
				{view_data attribute="planned_time"}
				{view_data attribute="actual_time"}
				{view_data attribute="mf_downtime_code_id" label='Downtime Code'}
			</dl>
			<dl class="float-right">
				{view_section heading="access_details" expand='closed'}
					{view_data attribute="createdby"}
					{view_data attribute="created"}
					{view_data attribute="alteredby"}
					{view_data attribute="lastupdated"}
				{/view_section}
			</dl>
		{/with}
	</div>
{/content_wrapper}