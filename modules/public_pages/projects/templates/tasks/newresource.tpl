{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}
{content_wrapper}
	{form controller="tasks" action="saveresource"}
	{with model=$models.TaskResource legend="Resource Details"}
		<dl id="view_data_left">
			{view_section heading="resource_details"}
				{input type='hidden'  attribute='id' }
				{input type='hidden'  attribute='task_id' }
				{select  attribute='resource_id' use_collection="true" constraint="project_id,=,{$Task->project_id}"}
				{if $smarty.get.conflict}
					{input type='checkbox' attribute='ignore' label='Ignore conflicts'}
				{/if}
			{/view_section}
			{submit another='false'}
			</dl>
		{/with}
	{/form}
{/content_wrapper}