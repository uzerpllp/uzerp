{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.2 $ *}
{content_wrapper}
	{form controller="employeepayperiods" action="save"}
		{with model=$models.EmployeePayPeriod legend="Employee Pay Period"}
			{input type='hidden' attribute='id' }
			{include file='elements/auditfields.tpl' }
			{datetime attribute='period_start_date' value=$period_start_date}
			{datetime attribute='period_end_date' value=$period_end_date}
			{input type='text' attribute='tax_year' value=$tax_year}
			{input type='text' attribute='tax_month' value=$tax_month}
			{input type='text' attribute='tax_week' value=$tax_week}
			{select attribute='pay_basis'}
		{/with}
		{submit}
		{include file='elements/saveAnother.tpl'}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}