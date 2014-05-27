{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	{form controller="employeecontractdetails" action="save"}
		{with model=$models.EmployeeContractDetail legend="Employee Contract Detail"}
			{input type='hidden' attribute='id' }
			{input type='hidden' attribute='employee_id' }
			{include file='elements/auditfields.tpl' }
			{input type='text' attribute='std_value' label='Value'}
			{select attribute='from_pay_frequency_id' label=''}
			{select attribute='to_pay_frequency_id' label='per'}
			{input type='date' attribute='start_date'}
			{input type='date' attribute='end_date'}
		{/with}
		{submit}
		{include file='elements/saveAnother.tpl'}
	{/form}
	{include file='elements/cancelForm.tpl'}
	{include file="elements/datatable.tpl" collection=$employeecontractdetails}
{/content_wrapper}