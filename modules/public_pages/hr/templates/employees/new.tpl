{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.13 $ *}
{content_wrapper}
	{form controller="employees" action="save"}
		{with model=$models.Employee legend="Employee Details"}
			{input type='hidden' attribute='id'}
			{include file='elements/auditfields.tpl' }
	
			<dl class="float-left">
				{view_section heading="Employee"}
					{select attribute='person_id' class="compulsory" data=$people forceselect="true"}
					{input type='text' attribute='title' label='title'}
					{input type='text' attribute='firstname' label='First Name'}
					{input type='text' attribute='middlename' label='Middle Name'}
					{input type='text' attribute='surname' label='Last Name'}
					{input type='text' attribute='suffix' label='suffix'}
					{input type='text' attribute='works_number' label='Works Number'}
					{input type='text' attribute='ni' class="compulsory" label='NI_Number'}
					{input type='date' attribute='dob' class="compulsory" label='date_of_birth'}
					{select attribute='employee_grade_id' }
				{/view_section}
				{view_section heading="Job Details" expand='open'}
					{input type='date' attribute='start_date' label='start date'}
					{input type='text' attribute='jobtitle'  label='job title'}
					{input type='text' attribute='department' label='department'}
					{select attribute='mfdept_id' label='or Select from list'}
					{select attribute='reports_to' label='Line Manager' options=$reports_to}
					{select attribute='pay_frequency_id' }
				{/view_section}
			</dl>
	
			<dl class="float-right">
				{view_section heading="Authorisers" expand='open'}
					{select attribute='authorisation_type' multiple=true options=$authorisation_types value=$can_authorise label='Can Authorise' nonone=true}
					{select attribute='expense_authorisers_id' multiple=true options=$can_authorise_expenses value=$expense_authorisers label='Expenses Authorised by' nonone=true}
					{select attribute='holiday_authorisers_id' multiple=true options=$can_authorise_holidays value=$holiday_authorisers label='Holidays Authorised by' nonone=true}
				{/view_section}
			</dl>
		{/with}
		<span id='view_data_bottom'>
			{submit}
			{include file='elements/saveAnother.tpl'}
		</span>
	{/form}
	<dl id='view_data_bottom'>
		{include file='elements/cancelForm.tpl'}
	</dl>
{/content_wrapper}