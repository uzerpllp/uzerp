{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
{content_wrapper}
	{form controller="employees" action="save_work"}
		{with model=$employee legend="Employee Details"}
			{input type='hidden'  attribute='id' }
			{input type='hidden'  attribute='person_id' }
			{include file='elements/auditfields.tpl' }
	
			<dl class="float-left">
				{view_section heading="Employee" expand='open'}
					{view_data model=$person attribute='getIdentifierValue()' label='Name'}
					{view_data attribute='employee_number' label='Employee Number'}
					{view_data attribute='works_number' label='Works Number'}
					{view_data attribute='ni' label='NI Number'}
					{view_data attribute='dob' label='Date of Birth'}
					{view_data attribute='start_date' label='Start Date'}
					{view_data attribute='finished_date'}
					{view_data attribute='pay_frequency'}
					{view_data attribute='employee_grade_id'}
					{view_data value=$days_left label='Holiday: Days Left'}	
					{view_data attribute='expenses_balance' label='Expenses Balance'}	
				{/view_section}
				{view_section heading="Job Details" expand='closed'}
					{view_data model=$person attribute='jobtitle'}
					{view_data model=$person attribute='department'}
					{view_data model=$person attribute='person_reports_to' fk='person' fk_field='reports_to'}
					{view_data model=$person attribute='language'}
				{/view_section}
			</dl>
	
			<dl class="float-right">
				{view_section heading="Work Contact Details"}
					{include file='elements/contact_details.tpl' main=true}
					{include file='elements/contact_address.tpl' main=true}
				{/view_section}
			</dl>
			<span id='view_data_bottom'>
				{submit}
			</span>
		{/with}
	{/form}
	<dl id='view_data_bottom'>
		{include file='elements/cancelForm.tpl'}
	</dl>
{/content_wrapper}