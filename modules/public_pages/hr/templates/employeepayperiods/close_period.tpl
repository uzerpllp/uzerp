{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	{form controller="employeepayperiods" action="save"}
		{with model=$EmployeePayPeriod legend="Employee Pay Period"}
			{input type='hidden' attribute='id'}
			{include file='elements/auditfields.tpl' }
			{view_data attribute='period_start_date'}
			{view_data attribute='period_end_date'}
			{view_data attribute='tax_year'}
			{view_data attribute='tax_month'}
			{view_data attribute='tax_week'}
			{view_data attribute='pay_basis'}
			{view_data attribute='calendar_week'}
			{input type='hidden' attribute='closed'}
			{input type='hidden' attribute='calendar_week'}
			{input type='date' attribute='processed_date'}
			{input type='text' class='numeric' attribute='processed_period'}
		{/with}
		{submit}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}