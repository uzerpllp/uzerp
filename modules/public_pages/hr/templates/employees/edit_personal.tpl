{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	{form controller="employees" action="save_personal"}
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
				{view_section heading="Bank Details" expand='open'}
					{input type='text'  attribute='bank_name' label='Name'}
					{input type='text'  attribute='bank_address' label='Address'}
					{input type='text'  attribute='bank_account_name' label='Account Name'}
					{input type='text'  attribute='bank_account_number' label='Account Number'}
					{input type='text'  attribute='bank_sort_code' label='Sort Code'}
				{/view_section}
				{view_section heading="Personal Contact Details" expand='open'}
					{input type='hidden' attribute="contact_phone_id" label="phone"}
					{input type='text' attribute="phone" label="phone"}
					{input type='hidden' attribute="contact_mobile_id" label="mobile"}
					{input type='text' attribute="mobile" label="mobile"}
					{input type='hidden' attribute="contact_email_id" label="email"}
					{input type='text' attribute="email" label="email"}
					{select attribute='address_id' nonone=true options=$addresses value=$address->id label='Address'}
					{if $model->address_id==''}
						<div id='address'>
					{else}
						<div id='address' style="display: none;">
					{/if}
					{include file='elements/address.tpl' }
					</div>
				{/view_section}
				{view_section heading="Next of Kin" expand='open'}
					{input type='text'  attribute='next_of_kin' label='Name'}
					{input type='text'  attribute='nok_address' label='Address'}
					{input type='text'  attribute='nok_phone' label='Phone'}
					{input type='text'  attribute='nok_relationship' label='Relationship'}
				{/view_section}
			</dl>
		{/with}
		<span id='view_data_bottom'>
			{submit}
		</span>
	{/form}
	<dl id='view_data_bottom'>
		{include file='elements/cancelForm.tpl'}
	</dl>
{/content_wrapper}