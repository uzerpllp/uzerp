{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	{data_table}
		{heading_row}
			{heading_cell field="currency" class="right"}
				Currency
			{/heading_cell}
			{heading_cell field="payment_type" class="right"}
				Payment Type
			{/heading_cell}
			{heading_cell class="right"}
				Number Transactions
			{/heading_cell}
			{heading_cell class="right"}
				Payment Total
			{/heading_cell}
		{/heading_row}
		{foreach name=transactions item=transaction from=$transactions}
			<tr>
				<td align=right>
					{link_to module=$module controller=$controller action='selected_payments_list' currency_id=$transaction->currency_id payment_type_id=$transaction->payment_type_id value=$transaction->id}
				</td>
				<td align=right>{$transaction->payment_type}</td>
				<td align=right>{$transaction->records}</td>
				<td align=right>{$transaction->payment|string_format:"%.2f"}</td>
			</tr>
		{/foreach}
	{/data_table}
{/content_wrapper}