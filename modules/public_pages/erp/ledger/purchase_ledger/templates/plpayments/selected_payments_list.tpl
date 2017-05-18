{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	{advanced_search}
	{form controller=$controller action="save_payments"}
		{if isset($id)}
			<input type="hidden" name="id" value="{$id}">
		{/if}
		{if isset($currency_id)}
			<input type="hidden" name="currency_id" value="{$currency_id}">
		{/if}
		{if isset($payment_type_id)}
			<input type="hidden" name="payment_type_id" value="{$payment_type_id}">
		{/if}
		{data_table}
			{heading_row}
				{heading_cell field="supplier"}
					Supplier
				{/heading_cell}
				{heading_cell field="payee_name"}
					Payee
				{/heading_cell}
				{heading_cell field="currency"}
					Currency
				{/heading_cell}
				{heading_cell field="payment_type"}
					Payment Type
				{/heading_cell}
				{heading_cell field="right"}
					Payment Total
				{/heading_cell}
			{/heading_row}
			{assign var=payment_total value=0}
			{foreach name=transactions item=transaction from=$transactions}
				{if $transaction->payment<0}
					{assign var=trans_class value='debit'}
					{assign var=debit_value value=true}
				{else}
					{assign var=trans_class value='credit'}
				{/if}
				<tr class="{$trans_class}">
					<td align=left>
						{$transaction->supplier}
						<input type="hidden" name="PLTransaction[{$transaction->id}][plmaster_id]" value="{$transaction->id}">
						<input type="hidden" name="PLTransaction[{$transaction->id}][company_id]" value="{$transaction->company_id}">
						<input type="hidden" name="PLTransaction[{$transaction->id}][currency_id]" value="{$transaction->currency_id}">
						<input type="hidden" name="PLTransaction[{$transaction->id}][payment_type_id]" value="{$transaction->payment_type_id}">
						<input type="hidden" name="PLTransaction[{$transaction->id}][net_value]" value="{$transaction->payment}">
						{assign var=payment_total value=$payment_total+$transaction->payment}
					</td>
					<td>{$transaction->payee_name}</td>
					<td align=center>{$transaction->currency}</td>
					<td align=center>{$transaction->payment_type}</td>
					<td align=right>{link_to module=$module controller=$controller action='select_for_payment' plmaster_id=$transaction->id value=$transaction->payment|string_format:"%.2f"}</td>
				</tr>
			{/foreach}
		{/data_table}
		<dt><label for="cb_account_id">Bank Account</label>:</dt><dd>
			<select name="cb_account_id" id="cb_account_id">
				{html_options options=$cbaccounts}
			</select>
		</dd>
		<dt>Payment reference</dt>
		<dd>{input type='text' attribute='reference' label=' ' tags='none' class="compulsory"}</dd>
		<dt>Description</dt>
		<dd>{input type='text' attribute='description' label=' ' tags='none' value="Batch Payment"}</dd>
		<dt>Payment Date</dt>
		<dd>{input type='date' class='date' attribute='payment_date' label=' ' tags='none'}</dd>
		<dt>Payment Total</dt>
		<dd>{$payment_total|string_format:"%.2f"}</dd>
		<input type="hidden" name="payment_total" value="{$payment_total|string_format:"%.2f"}">
		{if $debit_value}
			<div align="center"><strong>Negative Payment - please amend selection</strong></div>
		{else}
			{submit value='Pay'}
		{/if}
	{/form}
{/content_wrapper}