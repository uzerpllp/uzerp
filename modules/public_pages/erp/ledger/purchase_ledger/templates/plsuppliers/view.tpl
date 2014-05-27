{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.15 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$PLSupplier}
		<dl id="view_data_left">
		{view_section heading="supplier_details"}
			{view_data attribute="name" link_to='"module":"contacts", "controller":"companys", "action":"view", "id":"'|cat:$model->company_id|cat:'"'}
			{if (!is_null($PLSupplier->date_inactive))}
				{view_data attribute="date_inactive"}
			{/if}
			{view_data attribute="payee_name"}
			{view_data attribute="currency"}
			{view_data attribute="remittance_advice"}
			{view_data attribute="bank_account"}
			{view_data attribute="payment_type"}
			{view_data attribute="payment_term"}
			{view_data attribute="tax_status"}
			{view_data attribute="delivery_term"}
			{view_data label="default_receive_action" attribute="receive_into"}
			{view_data attribute="order_method"}
			{view_data label="email_order" value=$model->email_order()}
			{view_data label="email_remittance" value=$model->email_remittance()}
		{/view_section}
		{view_section heading="balance_details"}
			{view_data attribute="outstanding_balance"}
			{view_data attribute="credit_limit"}
		{/view_section}
		</dl>
		{/with}
		<dl id="view_data_right">
		{with model=$billing_address}
		{view_section heading="remittance_address"}
			{view_data attribute="street1"}
			{view_data attribute="street2"}
			{view_data attribute="street3"}
			{view_data attribute="town"}
			{view_data attribute="county"}
			{view_data attribute="postcode"}
			{view_data attribute="country"}
		{/view_section}
		{/with}
		{with model=$PLSupplier}
			{view_section heading="bank_details"}
				{view_data attribute="sort_code"}
				{view_data attribute="account_number"}
				{view_data attribute="bank_name_address"}
				{view_data attribute="iban_number"}
				{view_data attribute="bic_code"}
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