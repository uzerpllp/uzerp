{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.18 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$SLCustomer}
		<dl id="view_data_left">
		{view_section heading="customer_details"}
			{view_data attribute="name" link_to='"module":"contacts", "controller":"companys", "action":"view", "id":"'|cat:$model->company_id|cat:'"'}
			{if (!is_null($SLCustomer->date_inactive))}
				{view_data attribute="date_inactive"}
			{/if}
			{view_data attribute="currency"}
			{view_data attribute="statement"}
			{view_data attribute="last_statement_date"}
			{view_data attribute="bank_account"}
			{view_data attribute="payment_type"}
			{view_data attribute="payment_term"}
			{view_data attribute="so_price_type"}
			{view_data attribute="sl_analysis"}
			{view_data attribute="invoice_method"}
			{view_data attribute="report_def_id" label='Invoice Print Layout'}
			{view_data attribute="tax_status"}
			{view_data attribute="delivery_term"}
			{view_data value=','|implode:$SLCustomer->despatch_from->rules_list('from_location') label='Despatch From'}
			{view_data label="email_invoices" value=$model->email_invoice->contactmethod->contact}
			{view_data label="email_statements" value=$model->email_statement->contactmethod->contact}
			{view_data attribute="edi_invoice_definition"}
		{/view_section}
		</dl>
		{/with}
		<dl id="view_data_right">
		{with model=$billing_address}
		{view_section heading="billing_address"}
			{view_data attribute="street1"}
			{view_data attribute="street2"}
			{view_data attribute="street3"}
			{view_data attribute="town"}
			{view_data attribute="county"}
			{view_data attribute="postcode"}
			{view_data attribute="country"}
		{/view_section}
		{/with}
		{with model=$SLCustomer}
			{view_section heading="balance_details"}
				{view_data attribute="outstanding_balance"}
				{view_data attribute="credit_limit"}
				{view_data attribute="account_status"}
			{/view_section}
			{view_section heading="access_details"}
				{view_data attribute="created"}
				{view_data attribute="last_paid"}
				{view_data attribute="date_inactive"}
			{/view_section}
		{/with}
		</dl>	
	</div>
{/content_wrapper}