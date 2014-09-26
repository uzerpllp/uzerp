{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="projects" action="save"}
		{with model=$models.Project legend="Project Details"}
			<dl id="view_data_left">
				{view_section heading="project_details"}
					{input type='hidden'  attribute='id' }
					{input type='hidden'  attribute='usercompanyid' }
					{input type='text' attribute='job_no' class="compulsory" }
					{input type='text'  attribute='name' class="compulsory" value=$opportunity->name }
					{select attribute='key_contact_id'}
					
					{select attribute='company_id'  cascades='person_id' value=$opportunity->company_id }
					{select attribute='person_id'  cascadesfrom='company_id' value=$opportunity->person_id label="Client Contact" }
					{select attribute='opportunity_id'}
				{/view_section}
				{view_section heading="timescale"}
					{input type='date'  attribute='start_date' class="compulsory" value=$opportunity->enddate }
					{input type='date'  attribute='end_date' class="compulsory" }
				{/view_section}
				{view_section heading="description"}
					{textarea attribute='description' value=$opportunity->description tags=none}	
				{/view_section}	
			</dl>
			<dl id="view_data_right">
				{view_section heading="further_details"}
				    {input attribute="consultant_details"}
					{select attribute='category_id' }
					{select attribute='work_type_id'}
					{input type='text'  attribute='value' value=$opportunity->value }
					{input type='text'  attribute='cost' value=$opportunity->cost }
					{input type='text'  attribute='url' }
					{select  attribute='phase_id' }
				{/view_section}
			 </dl>
		{/with}
		{submit another="false"}
	{/form}
	{include file="elements/cancelForm.tpl"}
{/content_wrapper}