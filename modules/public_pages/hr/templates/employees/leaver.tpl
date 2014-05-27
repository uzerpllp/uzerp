{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	{form controller="employees" action="save_leaver"}
		{with model=$employee legend="Employee Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{view_data attribute='employee' label='Name'}
			{view_data attribute='employee_number' label='Employee Number'}
			{view_data attribute='works_number' label='Works Number'}
			{view_data attribute='ni' label='NI Number'}
			{view_data attribute='dob' label='Date of Birth'}
			{view_data attribute='start_date' label='Start Date'}
			{input type='date' attribute='finished_date'}
			{view_data attribute='pay_frequency'}
			{view_data attribute='employee_grade_id'}
			{view_data value=$employee->getOutstandingHolidays() label='Holiday: Days Left'}	
			{view_data attribute='expenses_balance' label='Expenses Balance'}	
		{/with}
		{submit}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}