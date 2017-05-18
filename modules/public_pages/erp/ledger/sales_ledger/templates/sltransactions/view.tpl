{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.6 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{view_section heading="Details"}
				{view_data model=$transaction attribute="customer" link_to='"module":"sales_ledger","controller":"slcustomers","action":"view","id":"'|cat:$transaction->slmaster_id|cat:'"'}
				{view_data model=$transaction attribute="person"}
				{view_data model=$transaction attribute="transaction_date"}
				{if $transaction->transaction_type==$transaction->invoice() || $transaction->transaction_type==$transaction->creditNote()}
					{view_data model=$transaction attribute="transaction_type" link_to='"module":"sales_invoicing","controller":"sinvoices","action":"view","invoice_number":"'|cat:$transaction->our_reference|cat:'"'}
					{view_data model=$transaction attribute="our_reference" link_to='"module":"sales_invoicing","controller":"sinvoices","action":"view","invoice_number":"'|cat:$transaction->our_reference|cat:'"'}
				{elseif $transaction->transaction_type==$transaction->receipt() || $transaction->transaction_type==$transaction->payment()}
					{view_data model=$transaction attribute="transaction_type" link_to='"module":"cashbook","controller":"cbtransactions","action":"view","reference":"'|cat:$transaction->our_reference|cat:'"'}
					{view_data model=$transaction attribute="our_reference" link_to='"module":"cashbook","controller":"cbtransactions","action":"view","reference":"'|cat:$transaction->our_reference|cat:'"'}
				{else}
					{view_data model=$transaction attribute="transaction_type"}
					{view_data model=$transaction attribute="our_reference"}
				{/if}
				{view_data model=$transaction attribute="description" }
				{view_data model=$transaction attribute="status"}
				{view_data model=$transaction attribute="ext_reference" }
				{view_data model=$transaction attribute="payment_term"}
				{view_data model=$transaction attribute="due_date"}
			{/view_section}
		</dl>
		<dl id="view_data_left">
			{view_section heading="Audit Information"}
				{view_data model=$transaction attribute="createdby" label="Created by"}
				{view_data model=$transaction attribute="created" label="Date Created"}
				{view_data model=$transaction attribute="alteredby" label="Updated by"}
				{view_data model=$transaction attribute="lastupdated" label="Date Updated"}
			{/view_section}
		</dl>
		<dl id="view_data_bottom">
		{view_section heading="Values"}
			{data_table}
				<tr>
					<th>
						Description
					</th>
					<th class="right">
						Actual
					</th>
					<th class="right">
						Base
					</th>
					<th class="right">
						Twin
					</th>
				</tr>
				<tr>
					<td>
						Currency
					</td>
					<td align="right">
						{$transaction->currency}
					</td>
					<td align="right">
						{$transaction->base_currency_name}
					</td>
					<td align="right">
						{$transaction->twin}
					</td>
				</tr>
				<tr>
					<td>
						Rate
					</td>
					<td align="right">
						{$transaction->rate}
					</td>
					<td align="right">
					</td>
					<td align="right">
						{$transaction->twin_rate}
					</td>
				</tr>
				<tr>
					<td>
						Gross Value
					</td>
					<td align="right">
						{$transaction->gross_value}
					</td>
					<td align="right">
						{$transaction->base_gross_value}
					</td>
					<td align="right">
						{$transaction->twin_gross_value}
					</td>
				</tr>
				<tr>
					<td>
						Tax Value
					</td>
					<td align="right">
						{$transaction->tax_value}
					</td>
					<td align="right">
						{$transaction->base_tax_value}
					</td>
					<td align="right">
						{$transaction->twin_tax_value}
					</td>
				</tr>
				<tr>
					<td>
						Net Value
					</td>
					<td align="right">
						{$transaction->net_value}
					</td>
					<td align="right">
						{$transaction->base_net_value}
					</td>
					<td align="right">
						{$transaction->twin_net_value}
					</td>
				</tr>
				<tr>
					<td>
						OS Value
					</td>
					<td align="right">
						{$transaction->os_value}
					</td>
					<td align="right">
						{$transaction->base_os_value}
					</td>
					<td align="right">
						{$transaction->twin_os_value}
					</td>
				</tr>
			{/data_table}
		{/view_section}
		</dl>
	</div>
{/content_wrapper}