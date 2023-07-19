{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$PLPayment}
			<dl class="float-left">
				{view_data attribute='payment_date'}
				{view_data attribute='status'}
				{view_data attribute='reference'}
				{view_data attribute='number_transactions'}
				{view_data attribute='override'}
				{view_data attribute='no_output'}
			</dl>
			<dl id="view_data_right">
				{view_data attribute='cb_account_id'}
				{view_data attribute='currency_id'}
				{view_data attribute='payment_type_id'}
				{view_data attribute='payment_total'}
				<dt>Remmitances printed</dt>
				<dd><ul>
				{foreach name=output item=output from=$outputs}
					{assign d $output.created|un_fix_date}
					{assign t $output.created|date_format:'%H:%M'}
					{assign l  $d|cat: ' - '|cat: $t}
					<li>{link_to module=$module controller='pltransactions' action='output_detail' id=$output.output_header_id value=$l}</li>
				{foreachelse}
					None
				{/foreach}
				</dd></ul>
			</dl>
		{/with}
	</div>
	{data_table}
		{heading_row}
			{heading_cell field="supplier"}
				Supplier
			{/heading_cell}
			{heading_cell field="supplier"}
				Payee
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
			{heading_cell field="right"}
			{/heading_cell}
		{/heading_row}
	{assign var=count value=0}
	{assign var=payment_total value=0}
	{foreach name=transactions item=transaction from=$pltrans}
		{assign var=payment_total value=$payment_total+$transaction->gross_value}
		<tr>
			<td align=left>{$transaction->supplier}</td>
			<td align=left>{$transaction->payee_name}</td>
			<td align=right>{$transaction->ext_reference}</td>
			<td align=center>{$transaction->getFormatted('transaction_type')}</td>
			<td align=center>{$transaction->due_date}</td>
			<td align=center>{$transaction->currency}</td>
			<td align=center>{$transaction->payment_type}</td>
			<td align=right>{$transaction->gross_value*-1|string_format:"%.2f"}</td>
			<td align=right>
				{link_to module=$module controller=$controller action='remittance_list' id=$transaction->id payment_id=$PLPayment->id value='Details'}
			</td>
		</tr>
	{/foreach}
		<tr>
			<td align='right' colspan=7>
				Payment total
			</td>
			<td align='right'>
				{$payment_total*-1|string_format:"%.2f"}
			</td>
		</tr>
	</table>
	{/data_table}
{/content_wrapper}