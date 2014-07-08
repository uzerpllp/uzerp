{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="projects" action="save"}
		{with model=$models.Project legend="Project Details"}
			{input type='hidden'  attribute='id' }
			{input type='hidden'  attribute='usercompanyid' }
			{input type='text'  attribute='name' class="compulsory" }
			{input type='text' attribute='job_no' class="compulsory"
			{input type='date'  attribute='start_date' class="compulsory" }
			{input type='date'  attribute='end_date' class="compulsory" }
			{input type='text'  attribute='cost' }
			{input type='text'  attribute='url' }
			{select  attribute='phase_id' }
			{input type='checkbox'  attribute='archived' }
			{input type='text'  attribute='description' }
			{input type='checkbox'  attribute='completed' }
			{input type='checkbox'  attribute='invoiced' }
			{select  attribute='category_id' }
			{select attribute='company_id'  cascades='person_id' }
			{select attribute='person_id' }
			{select attribute='work_type_id'}
		{/with}
		{submit}
	{/form}
{/content_wrapper}