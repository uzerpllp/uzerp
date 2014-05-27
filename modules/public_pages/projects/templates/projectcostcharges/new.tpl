{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.2 $ *}
{content_wrapper}
	{with model=$models.ProjectCostCharge legend="Project Cost Charges Details"}
		{form controller="projectcostcharges" action="save"}
			{input type='hidden'  attribute='id' }
			{select attribute='project_id'}
			{select attribute='task_id' options=$tasks}
			{select attribute='item_type'}
			{select attribute='source_type' }
			{select attribute='stitem_id' options=$items}
			{input attribute='description'}
			{input attribute='net_value'}
			{submit}
			{include file='elements/saveAnother.tpl'}
		{/form}
		{include file='elements/cancelForm.tpl'}
	{/with}
{/content_wrapper}