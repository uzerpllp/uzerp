{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	{advanced_search}
	{paging}
	{data_table}
		{heading_row}
			{heading_cell field="payment_date"}
				Payment Date
			{/heading_cell}
			{heading_cell field="status"}
				Status
			{/heading_cell}
			{heading_cell field="reference"}
				Reference
			{/heading_cell}
			{heading_cell field="number_transactions"}
				Number Transactions
			{/heading_cell}
			{heading_cell field="bank_account"}
				Bank Account
			{/heading_cell}
			{heading_cell field="payment_type"}
				Payment Type
			{/heading_cell}
			{heading_cell field="currency"}
				Currency
			{/heading_cell}
			{heading_cell field="payment_total" class='right'}
				Payment Total
			{/heading_cell}
			{heading_cell field="process"}
			{/heading_cell}
		{/heading_row}
		{foreach name=transactions item=transaction from=$plpayments}
			<tr>
				<td align=left>
					{link_to module=$module controller=$controller action='view' id=$transaction->id value=$transaction->payment_date|un_fix_date}
				</td>
				<td align=left>{$transaction->getFormatted('status')}</td>
				<td align=left>{$transaction->reference}</td>
				<td align=center>{$transaction->number_transactions}</td>
				<td align=left>{$transaction->bank_account}</td>
				<td align=left>{$transaction->payment_type}</td>
				<td align=left>{$transaction->currency}</td>
				<td align=right>{$transaction->payment_total|string_format:"%.2f"}</td>
				<td align=right>
					{if $transaction->status=='N'}
						{link_to module=$module controller=$controller action='printDialog' printaction='make_batch_payment' id=$transaction->id value='Process'}
					{/if}
				</td>
			</tr>
		{/foreach}
	{/data_table}
{/content_wrapper}