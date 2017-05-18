{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.7 $ *}
{content_wrapper}
	{form controller="tasks" action="save"}
		{with model=$models.Task legend="Task Details"}
			{input type='hidden'  attribute='id' }
			{if $Task->project_id neq ''}
				{view_data attribute='project'}
				{input type='hidden'  attribute='project_id' }
			{else}
				{select attribute='project_id' }
			{/if}	
			{input type='text'  attribute='name' }
			{select attribute='parent_id' data=$parent_tasks force=true}
			{select attribute='progress' readonly=$readonly}
			{datetime attribute='start_date' value=$start_date readonly=$readonly}
			{datetime attribute='end_date' value=$end_date readonly=$readonly}
			{interval attribute='duration' readonly=$readonly}
			{input type='checkbox'  attribute='milestone' readonly=$readonly}
			{input type='checkbox' attribute='deliverable'}			
			{select attribute='priority_id' }
			{textarea  attribute='description' }
			{submit}
			{include file='elements/saveAnother.tpl'}
		{/with}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}
