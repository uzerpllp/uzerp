{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.9 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{with model=$Project}
				{view_section heading="project_details" expand='open'}
					{view_data attribute="job_no"}					
					{view_data attribute="name"}
					{view_data attribute="owner" label='Project Manager'}
					{view_data attribute="key_contact"}
					{view_data attribute="company"}
					{view_data attribute="person" label="client_contact"}
				{/view_section}
				{view_section heading="Timescale" expand='open'}
					{view_data attribute="start_date"}
					{view_data attribute="end_date" modifier="overdue"}
					{view_data attribute="duration()" label="total_duration_(days)"}
					{view_data attribute="progress()"}
				{/view_section}
			{/with}
		</dl>
		<dl id="view_data_right">
			{with model=$Project}
				{view_section heading="further_details" expand='open'}
					{view_data attribute="opportunity" link_to='"module":"crm", "controller":"opportunitys", "action":"view", "id":"'|cat:$model->opportunity_id|cat:'"'}
					{view_data attribute="category"}
					{view_data attribute="work_type"}
					{view_data attribute="consultant_details"}
					{view_data attribute="value"}
					{view_data attribute="cost"}
					{view_data attribute="url"}
					{view_data attribute="phase"}
				{/view_section}
				{view_section heading="project_status" expand='open'}		
					{view_data attribute="status"}
					{view_data attribute="invoiced"}
					{view_data attribute="archived"}
				{/view_section}
				{view_section heading="access_details" expand='closed'}
					{view_data attribute="createdby"}
					{view_data attribute="created"}
					{view_data attribute="alteredby"}
					{view_data attribute="lastupdated"}
				{/view_section}
			{/with}
		</dl>
		<div id="view_data_fullwidth">
			{with model=$Project}
				{view_data attribute="description"}
			{/with}
		</div>
	</div>
{/content_wrapper}
