{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="projects" action="savetask"}
		{with model=$models.Task legend="Task Details"}
			{input type='hidden'  attribute='id' }
			{input type='hidden' attribute='project_id' }
			{input type='text'  attribute='name' }
			{input type='text'  attribute='budget' }
			{input type='text'  attribute='progress' }
			{input type='date'  attribute='start_date' }
			{input type='date'  attribute='end_date' }
			{input type='checkbox'  attribute='milestone' }
			{select attribute='parent_task_id' }
			{input type='text'  attribute='description' }
			{select attribute='priority_id' }
		{/with}
		{submit}
	{/form}
{/content_wrapper}