{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.2 $ *}
{content_wrapper}
	{data_table}
		{heading_row}
			{heading_cell field="supplier"}
				Supplier
			{/heading_cell}
			{heading_cell field="right"}
				Our Reference
			{/heading_cell}
			{heading_cell field="right"}
				Ext Reference
			{/heading_cell}
			{heading_cell field="transaction_type"}
				Transaction Type
			{/heading_cell}
			{heading_cell field="due_date"}
				Due Date
			{/heading_cell}
			{heading_cell field="currency"}
				Currency
			{/heading_cell}
			{heading_cell field="currency"}
				Payment Type
			{/heading_cell}
			{heading_cell field="right"}
				Gross Value
			{/heading_cell}
		{/heading_row}
	{assign var=count value=0}
	{assign var=payment_total value=0}
	{foreach name=transactions item=transaction from=$pltrans}
		{assign var=payment_total value=$payment_total+$transaction->gross_value}
		<tr>
			<td align=left>{link_to module=$module controller='plsuppliers' action='view' id=$transaction->plmaster_id value=$transaction->supplier}</td>
			<td align=right>{link_to module='purchase_invoicing' controller='pinvoices' action='view' invoice_number=$transaction->our_reference value=$transaction->our_reference}</td>
			<td align=right>{$transaction->ext_reference}</td>
			<td>{$transaction->getFormatted('transaction_type')}</td>
			<td>{$transaction->getFormatted('due_date')}</td>
			<td>{$transaction->currency}</td>
			<td>{$transaction->payment_type}</td>
			<td align=right>{$transaction->gross_value|string_format:"%.2f"}</td>
		</tr>
	{/foreach}
		<tr>
			<td align='right' colspan=6>
				Payment total
			</td>
			<td align='right'>
				{$payment_total|string_format:"%.2f"}
			</td>
		</tr>
	</table>
	{/data_table}
{/content_wrapper}