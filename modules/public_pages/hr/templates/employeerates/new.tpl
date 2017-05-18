{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	{form controller="employeerates" action="save"}
		{with model=$models.EmployeeRate legend="Employee Rate"}
			{input type='hidden' attribute='id' }
			{input type='hidden' attribute='employee_id' }
			{include file='elements/auditfields.tpl' }
			{select attribute='payment_type_id' }
			{input type='text' attribute='default_units' }
			{input type='checkbox' attribute='units_variable'}
			{input type='text' attribute='rate_value' }
			{input type='checkbox' attribute='rate_variable'}
			{select attribute='pay_frequency_id' }
			{input type='date' attribute='start_date'}
			{input type='date' attribute='end_date'}
		{/with}
		{submit}
		{include file='elements/saveAnother.tpl'}
	{/form}
	{include file='elements/cancelForm.tpl'}
	<div id='current_rates'>
		{include file="elements/datatable.tpl" collection=$employeerates}
	</div>
{/content_wrapper}