{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.8 $ *}
{content_wrapper title=$page_title|cat:' for '|cat:$employee->person->getIdentifierValue()}
	{form controller="expenses" action="confirmsaverequest"}
		{with model=$Expense legend="Expense Details"}
			{view_section heading="Expense Request Details"}
				{input type='hidden'  attribute='id' }
				{input type='hidden'  attribute='usercompanyid' }
				{input type='hidden'  attribute='employee_id'}
				{input type='hidden'  attribute='approved_by' value=$authoriser}
				{input type='hidden'  attribute='authorised_by' value=$authoriser}
				{view_data attribute='expense_date' label='Date'}
				{view_data attribute='expense_number' label='Expense No.'}
				{view_data attribute='our_reference' label='Reference'}
				{view_data attribute='current_status' value=$current_status}
				{view_data attribute='net_value'}
				{view_data attribute='tax_value'}
				{view_data attribute='gross_value'}
				{view_data attribute='description'}
				
			{/view_section}
		{/with}
		{submit}
		{include file='elements/saveAnother.tpl'}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}