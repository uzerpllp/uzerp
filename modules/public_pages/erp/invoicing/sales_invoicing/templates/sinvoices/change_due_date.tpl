{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.6 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
		{with model=$SInvoice}
			<dt class="heading">{$SInvoice->getFormatted('transaction_type')} Details</dt>
				{view_data attribute="invoice_number" label=$SInvoice->getFormatted('transaction_type')|cat:' number'}
				{view_data attribute="sales_order_number"}
				{view_data attribute="customer" label='customer'}
				{view_data attribute="ext_reference" label='customer reference'}
				{view_data attribute="status"}
				{view_data attribute="date_printed"}
				{view_data attribute="print_count"}
			<dt class="heading">Timescale</dt>
				{view_data attribute="invoice_date" label=$SInvoice->getFormatted('transaction_type')|cat:' date'}
				{view_data attribute="original_due_date"}
				{form controller="sinvoices" action="saveduedate" notags=true}
					{input type="hidden" attribute="id"}
					{input type="hidden" attribute="invoice_number"}
					{include file='elements/auditfields.tpl' }
					{input type='date' attribute="due_date"}
					{submit}
				{/form}
		{/with}
		</dl>
		<dl id="view_data_right">
			{with model=$SInvoice}
			<dt class="heading">Further Details</dt>
				{view_data attribute="currency"}
				{view_data attribute="tax_status"}
				{view_data attribute="net_value"}
				{view_data attribute="tax_value"}
				{view_data attribute="gross_value"}
				{view_data attribute="settlement_discount"}
				{view_data attribute="base_net_value"}
				{view_data attribute="base_tax_value"}
				{view_data attribute="base_gross_value"}
			{/with}
		</dl>
		<table id="invoice_lines">
			<thead>
				<tr>
					<th>Line #</th>
					<th>Item</th>
					<th>Unit price</th>
					<th>Qty</th>
					<th>UoM</th>
					<th>Net Value</th>
					<th>Tax Value</th>
					<th>Gross Value</th>
					<th>Account</th>
					<th>Centre</th>
				</tr>
			</thead>
		{foreach name=lines item=line from=$SInvoice->lines}
			<tr>
				<td>{$line->line_number}</td>
				<td>{$line->item_description}</td>
				<td>{$line->sales_price}</td>
				<td>{$line->sales_qty}</td>
				<td>{$line->uom_name}</td>
				<td>{$line->net_value}</td>
				<td>{$line->tax_value}</td>
				<td>{$line->gross_value}</td>
				<td>{$line->glaccount}</td>
				<td>{$line->glcentre}</td>
			</tr>
		{/foreach}
		</table>
	</div>
{/content_wrapper}