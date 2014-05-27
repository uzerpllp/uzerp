{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.17 $ *}
{content_wrapper class="clearfix uz-grid"}
	<div id="view_page" class="clearfix">
		{view_section heading=$transaction_type_desc|cat:" Header" expand="open"}
		<dl class="float-left">
			{view_section heading=$transaction_type_desc|cat:" Details" expand="open"}
				{view_data model=$PInvoice attribute="invoice_number" label=$transaction_type_desc|cat:' number'}
				<dt>Purchase Order Number</dt>
				<dd>
					{foreach name=list key=order item=invoice from=$porders}
						{link_to module='purchase_order' controller='porders' action='view' id=$invoice.purchase_order_id value=$order}
						{if !$smarty.foreach.list.last}
							,
						{/if}
					{foreachelse}
						-
					{/foreach}
				</dd>
				{view_data model=$PInvoice attribute="supplier" label='supplier'}
				{view_data model=$PInvoice attribute="our_reference"}
				{view_data model=$PInvoice attribute="ext_reference" label='supplier reference'}
				{view_data model=$PInvoice attribute="status"}
			{/view_section}
		</dl>
		<dl class="float-right">
			{with model=$PInvoice}
				{view_section heading="Created/Updated" expand="closed"}
					{view_data attribute="invoice_date" label=$transaction_type_desc|cat:' date'}
					{view_data attribute="original_due_date"}
					{view_data attribute="due_date"}
					{view_data attribute="createdby" label="Created By"}
					{view_data attribute="created"}
					{view_data attribute="alteredby" label="Altered By"}
					{view_data attribute="lastupdated" label="Last Updated"}
				{/view_section}
				{view_section heading="Invoice Totals" expand="closed"}
					{view_data attribute="currency"}
					{view_data attribute="tax_status"}
					{view_data attribute="net_value"}
					{view_data attribute="tax_value"}
					{view_data attribute="gross_value"}
					{view_data attribute="settlement_discount"}
					{view_data attribute="base_net_value"}
					{view_data attribute="base_tax_value"}
					{view_data attribute="base_gross_value"}
				{/view_section}
			{/with}
			{view_section heading="Description" expand="open"}
				{view_data model=$PInvoice attribute="description" tags=none label=' '}
			{/view_section}
		</dl>
		{/view_section}
		{view_section heading=$transaction_type_desc|cat:" Lines" expand="open"}
		<div id="view_data_bottom">
			{data_table}
				<thead>
					<tr>
						<th class="right">Line #</th>
						<th align='right'>Qty</th>
						<th>Description</th>
						<th align='right'>Price</th>
						<th>Account</th>
						<th>Centre</th>
						<th class="right">Net Value</th>
						<th class="right">Tax Value</th>
						<th class="right">Gross Value</th>
					</tr>
				</thead>
				{foreach name=lines item=line from=$PInvoice->lines}
					<tr>
						{assign var=line_number value=$line->line_number}
						{assign var=id value=$line->id}
						{if $PInvoice->status==$PInvoice->newStatus()}
							<td align='right' class="edit-line">
								{link_to module="purchase_invoicing" controller="pinvoicelines" action="edit" id="$id" value="$line_number"}
								{input model=$line type='hidden' rowid="$line_number" attribute='id'}
							</td>
						{else}
							<td align='right'>{$line->line_number}</td>
						{/if}
						<td>{$line->purchase_qty}</td>
						<td align='left'>
							{if !is_null($line->stitem_id)}
								{assign var=stitem_id value=$line->stitem_id}
								{assign var=description value=$line->description}
								{link_to module="manufacturing" controller="stitems" action="view" id="$stitem_id" value="$description"}
							{else}
								{$line->description}
							{/if}
						</td>
						<td>{$line->purchase_price}</td>
						<td>{$line->glaccount}</td>
						<td>{$line->glcentre}</td>
						<td class="numeric">{$line->net_value|string_format:'%.2f'}</td>
						<td class="numeric">{$line->tax_value|string_format:'%.2f'}</td>
						<td class="numeric">{$line->gross_value|string_format:'%.2f'}</td>
					</tr>
				{/foreach}
			{/data_table}
		</div>
		{/view_section}
	</div>
	<div id="editline">
	</div>
{/content_wrapper}