{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.13 $ *}
{content_wrapper title=$page_title|cat:$title}
	{form controller="expenses" action="save"}
		{with model=$models.Expense legend="Expense Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{select attribute='employee_id' }
			{if $model->expense_number <> ''}
				{view_data attribute='expense_number'}
			{/if}
			{input type='date' attribute='expense_date'}
			{input type='text'  attribute='our_reference'}
			{select attribute='currency_id' value=$base_currency}
			{select attribute='project_id' force=true}
			{select attribute='task_id' force=true options=$tasks}
			{textarea  attribute='description'}
		{/with}
		{submit}
		{include file='elements/saveAnother.tpl'}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}