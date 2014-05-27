{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.2 $ *}
{content_wrapper}
	{with model=$models.ProjectBudget legend="Project Budget Details"}
		{form controller="projectbudgets" action="save"}
			{input type='hidden'  attribute='id' }
			<dl id="view_data_left">
				{select attribute='project_id' options=$projects}
				{select attribute='task_id' options=$tasks}
				{select attribute='budget_item_type'}
				{select attribute='budget_item_id' options=$items}
				{input type='text' attribute='description'}
				{input type='text' attribute='quantity'}
				{select attribute='uom_id'}
				{input type='text' attribute='cost_rate'}
				{input type='text' attribute='setup_cost'}
				{input type='text' attribute='charge_rate'}
				{input type='text' attribute='setup_charge'}
			</dl>
			<dl id="view_data_right">
			</dl>
			{submit}
			{include file='elements/saveAnother.tpl'}
		{/form}
		{include file='elements/cancelForm.tpl'}
	{/with}
{/content_wrapper}