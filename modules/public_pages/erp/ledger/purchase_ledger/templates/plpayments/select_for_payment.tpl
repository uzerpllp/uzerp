{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.2 $ *}
{content_wrapper}
	{advanced_search}
	{form controller=$controller action="processPaymentsList" name="processPaymentsList"}
	{data_table}
		{heading_row}
			{heading_cell field="supplier"}
				Supplier
			{/heading_cell}
			{heading_cell field="right"}
				Our Reference
			{/heading_cell}
			{heading_cell field="transaction_type"}
				Transaction Type
			{/heading_cell}
			{heading_cell field="due_date"}
				Due Date
			{/heading_cell}
			{heading_cell field="right"}
				Gross Value
			{/heading_cell}
			{heading_cell field="right"}
				Settlement Discount
			{/heading_cell}
			{heading_cell field="right"}
				OS Value
			{/heading_cell}
			{heading_cell field="currency"}
				Currency
			{/heading_cell}
			{heading_cell field="currency"}
				Payment Type
			{/heading_cell}
			{heading_cell }
				Discount?
			{/heading_cell}
			{heading_cell }
				Pay?
			{/heading_cell}
		{/heading_row}
	{assign var=count value=0}
	{assign var=payment_total value=0}
	{foreach name=transactions item=transaction from=$transactions}
		{assign var=count value=$count+1}
		{assign var=rowid value='row'|cat:$count}
		<tr rel="{$rowid}">
			<td align=left>
			<input type="hidden" id="PLTransaction_id_{$rowid}" name='PLTransaction[{$rowid}][id]' value={$transaction->id}>
			{link_to module='purchase_ledger' controller='plsuppliers' action='view' id=$transaction->plmaster_id value=$transaction->supplier}</td>
			<td align=right>{$transaction->our_reference}</td>
			<td align=center>{$transaction->getFormatted('transaction_type')}</td>
			<td>{$transaction->getFormatted('due_date')}</td>
			<td align=right>{$transaction->gross_value|string_format:"%.2f"}</td>
			<td align=right>{$transaction->settlement_discount|string_format:"%.2f"}
				<input type="hidden" id='settlement_discount{$rowid}' value={$transaction->settlement_discount*-1}>
			</td>
			<td align=right>{$transaction->os_value|string_format:"%.2f"}
				<input type="hidden" id='os_value{$rowid}' value={$transaction->os_value}>
			</td>
			<td align=center>{$transaction->currency}</td>
			<td align=center>{$transaction->payment_type}</td>
			<td align=center>
				{if $transaction->settlement_discount>0 && !is_null($transaction->pl_discount_glaccount_id)}
					{if $transaction->include_discount==='t'}
						{assign var=payment_total value=$payment_total-$transaction->settlement_discount}
						<input class="checkbox discount" type="checkbox" id="PLTransaction_discount_{$rowid}" name="PLTransaction[{$rowid}][include_discount]" value="{$transaction->include_discount}" checked="checked" />
					{else}
						<input class="checkbox discount" type="checkbox" id="PLTransaction_discount_{$rowid}" name="PLTransaction[{$rowid}][include_discount]" value="{$transaction->include_discount}" />
					{/if}
				{/if}
			 </td>
			<td align=center>
				{if $transaction->for_payment==='t'}
					{assign var=payment_total value=$payment_total+$transaction->os_value}
					<input class="checkbox pay" type="checkbox" id="PLTransaction_for_payment_{$rowid}" name="PLTransaction[{$rowid}][for_payment]" value="{$transaction->for_payment}" checked="checked" />
				{else}
					<input class="checkbox pay" type="checkbox" id="PLTransaction_for_payment_{$rowid}" name="PLTransaction[{$rowid}][for_payment]" value="{$transaction->for_payment}" />
				{/if}
			 </td>
		</tr>
	{/foreach}
		<tr>
			<td align='right' colspan=4>
				Payment total
			</td>
			<td align='right'>
				<input type='text' class="numeric" name="allocated_total" id="allocated_total" value="{$payment_total|string_format:"%.2f"}">
			</td>
			<td align='right' colspan=5>
				<input type='button' class="select_all" value="Select All" style="width: 80px;">
			</td>
		</tr>
	</table>
	{submit another='false'}
	{/data_table}
	{/form}
{/content_wrapper}