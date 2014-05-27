{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.7 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$CBAccount}
			<dl id="view_data_left">
				{view_section heading="bank_details"}
					{view_data attribute="name"}
					{view_data attribute="primary_account"}
					{view_data attribute="currency"}
					{view_data attribute="bank_name"}
					{view_data attribute="bank_address"}
					{view_data attribute="bank_account_name"}
					{view_data attribute="bank_account_number"}
					{view_data attribute="bank_sort_code"}
					{view_data attribute="bank_iban_number"}
					{view_data attribute="bank_bic_code"}
				{/view_section}
			</dl>
			<dl id="view_data_right">
				{view_section heading="balance_details"}
					{view_data attribute="balance"}
					{view_data attribute="statement_balance"}
					{view_data attribute="statement_date"}
					{view_data attribute="statement_page"}
				{/view_section}
				{view_section heading="ledger_details"}
					{view_data attribute="glaccount" label='Account'}
					{view_data attribute="glcentre" label='Cost Centre'}
					{view_data value=$glbalance label='GL Balance'}
				{/view_section}
			</dl>
			<div id="view_data_fullwidth">
				{view_data attribute="description"}
			</div>
		{/with}
	</div>
{/content_wrapper}