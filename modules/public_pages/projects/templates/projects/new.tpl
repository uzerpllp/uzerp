{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.6 $ *}
{content_wrapper}
	{form controller="projects" action="save"}
		{with model=$models.Project legend="Project Details"}
		<div id="view_page" class="clearfix">
			<dl id="view_data_left">
				{view_section heading="project_details"}
					{input type='hidden'  attribute='id' }
					{include file='elements/auditfields.tpl' }
					{if $action=='new'}
						{input type='text' attribute='job_no' class="compulsory" }
						{input type='text'  attribute='name' class="compulsory" label="project_name"}
					{else}
						{input type='text' attribute='job_no' readonly=true }
						{input type='text'  attribute='name' readonly=true label="project_name" }
					{/if}
					{select attribute='key_contact_id' label="Project Manager"}
					{select attribute='company_id' constrains='person_id'}
					{select attribute='person_id' depends='company_id' nonone=true label="Client Contact"}
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
					{input type='text'  attribute='value' }
					{input type='text'  attribute='cost' }
					{input type='text'  attribute='url' }
					{select  attribute='phase_id' }
				{/view_section}
				{* {if $action<>'new'}
					{view_section heading="project_status"}
						{input type='checkbox'  attribute='invoiced' }
						{input type='checkbox'  attribute='archived' }
					{/view_section}
				{/if} *}
			</dl>	
		</div>			
		{/with}
		{submit}
		{if $action=='new'}
			{include file='elements/saveAnother.tpl'}
		{/if}
	{/form}
{include file="elements/cancelForm.tpl"}
{/content_wrapper}
