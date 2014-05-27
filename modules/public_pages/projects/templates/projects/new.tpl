{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.5 $ *}
{content_wrapper}
	{form controller="projects" action="save"}
		{with model=$models.Project legend="Project Details"}
			<dl id="view_data_left">
				{view_section heading="project_details"}
					{input type='hidden'  attribute='id' }
					{input type='hidden'  attribute='usercompanyid' }
					{input type='text'  attribute='name' class="compulsory" }
					{select attribute='company_id' constrains='person_id'}
					{select attribute='person_id' depends='company_id' nonone=true}
					{select attribute='key_contact_id'}
				{/view_section}
				{view_section heading="timescale"}
					{input type='date'  attribute='start_date'}
					{input type='date'  attribute='end_date'}
				{/view_section}		
				{view_section heading="description"}
					{textarea attribute='description' tags=none}	
				{/view_section}		
			</dl>
			<dl id="view_data_right">
				{view_section heading="further_details"}
					{select attribute='opportunity_id' }
					{input attribute="consultant_details"}
					{select attribute='category_id' }
					{select attribute='work_type_id'}
					{input type='text'  attribute='cost' }
					{input type='text'  attribute='url' }
					{select  attribute='phase_id' }
				{/view_section}
				{view_section heading="project_status"}
					{input type='checkbox'  attribute='completed' }
					{input type='checkbox'  attribute='invoiced' }
					{input type='checkbox'  attribute='archived' }
				{/view_section}
			</dl>	
			<dl id="view_data_fullwidth">
				{view_section heading=""}
					{submit}
					{include file='elements/saveAnother.tpl'}
				{/view_section}
			</dl>
		{/with}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}