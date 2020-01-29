{** 
 *	(c) 2020 uzERP LLP (support#uzerp.com). All rights reserved.
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.11 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$Employee}
			<dl class="float-left">
				{view_section heading="Employee" expand='open'}
					{view_data model=$person attribute='getIdentifierValue()' label='Name'}
					{view_data attribute='employee_number' label='Employee Number'}
					{view_data attribute='works_number' label='Works Number'}
					{view_data attribute='ni' label='NI Number'}
					{view_data attribute='dob' label='Date of Birth'}
					{view_data attribute='start_date' label='Start Date'}
					{view_data attribute='finished_date'}
					{view_data attribute='gender'}
					{view_data attribute='pay_basis'}
					{view_data attribute='employee_grade_id'}
					{view_data attribute='expenses_balance' label='Expenses Balance'}	
				{/view_section}
				{view_section heading="Job Details" expand='closed'}
					{view_data model=$person attribute='jobtitle'}
					{view_data model=$person attribute='department'}
					{view_data model=$person attribute='person_reports_to' fk='person' fk_field='reports_to'}
					{view_data model=$person attribute='language'}
				{/view_section}
				{view_section heading="Holiday Summary" expand='closed'}
					{foreach key=label item=days from=$holidays}
						{view_data label=$label value=$days}
					{/foreach}
				{/view_section}
				{view_section heading="Authorisers" expand='closed'}
					{view_data value=$can_authorise label='can_authorise'}
					{view_data value=$expense_authorisers label='Expenses Authorised by'}
					{view_data value=$holiday_authorisers label='Holidays Authorised by'}
				{/view_section}
			</dl>
		{/with}
	
		<dl class="float-right">
			{view_section heading="Work Contact Details" expand='closed'}
				{with model=$person->phone->contactmethod}
					{view_data attribute="contact" label="phone"}
				{/with}
				{with model=$person->mobile->contactmethod}
					{view_data attribute="contact" label="mobile"}
				{/with}
				{with model=$person->fax->contactmethod}
					{view_data attribute="contact" label="fax"}
				{/with}
				{with model=$person->email->contactmethod}
					{view_data attribute="contact" label="email"}
				{/with}
				{with model=$person->main_address->address}
					{view_data attribute="street1"}
					{view_data attribute="street2"}
					{view_data attribute="street3"}
					{view_data attribute="town"}
					{view_data attribute="county"}
					{view_data attribute="postcode"}
					{view_data attribute="country"}
				{/with}
			{/view_section}
			{view_section heading="Bank Details" expand='closed'}
				{with model=$Employee}
					{view_data attribute='bank_name' label='Name'}
					{view_data attribute='bank_address' label='Address'}
					{view_data attribute='bank_account_name' label='Account Name'}
					{view_data attribute='bank_account_number' label='Account Number'}
					{view_data attribute='bank_sort_code' label='Sort Code'}
				{/with}
			{/view_section}
			{view_section heading="Personal Contact Details" expand='closed'}
				{with model=$Employee}
					{view_data attribute="contact_phone_id" label="phone"}
					{view_data attribute="contact_mobile_id" label="mobile"}
					{view_data attribute="contact_email_id" label="email"}
					{view_data attribute="address_id" label="address"}
				{/with}
			{/view_section}
			{with model=$Employee}
				{view_section heading="Next Of Kin" expand='closed'}
					{view_data attribute='next_of_kin' label='Name'}
					{view_data attribute='nok_address' label='Address'}
					{view_data attribute='nok_phone' label='Phone'}
					{view_data attribute='nok_relationship' label='Relationship'}
				{/view_section}
				{view_section heading="Access Details" expand='closed'}
					{view_data attribute='created' label='date created'}
					{view_data attribute='lastupdated' label='date last updated'}
					{view_data attribute='alteredby' label='last updated by'}
				{/view_section}
			{/with}
		</dl>
	</div>
{/content_wrapper}
{include file="./delete_dialog.tpl"}