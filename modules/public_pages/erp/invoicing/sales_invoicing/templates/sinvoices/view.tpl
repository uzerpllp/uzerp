{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.15 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{view_section heading=$transaction_type|cat:" Header" expand="open"}
			<dl class="float-left">
				{view_section heading=$transaction_type|cat:" Details" expand="open"}
					{with model=$SInvoice}
						{view_data attribute="invoice_number" label=$transaction_type|cat:' number'}
						{view_data attribute="sales_order_number"}
						{view_data attribute="customer" label='customer'}
						{view_data attribute="ext_reference" label='customer reference'}
						{view_data attribute="status"}
						{view_data attribute="date_printed"}
						{view_data attribute="print_count"}
					{/with}
				{/view_section}
			</dl>
			<dl class="float-right">
				{with model=$SInvoice}
					{view_section heading="Created/Updated" expand="closed"}
						{view_data attribute="invoice_date" label=$transaction_type|cat:' date'}
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
				{view_section heading="Invoice Address" expand="closed"}
					{if $SInvoice->person_id<>''}
						{with model=$SInvoice->persondetail}
							{view_data attribute="titlename" label=''}
						{/with}
					{/if}
					{with model=$SInvoice->inv_address}
						{view_data attribute="fulladdress" label="invoice_address" label=''}
					{/with}
				{/view_section}
				{view_section heading="Description" expand="open"}
					{view_data model=$SInvoice attribute="description" tags=none label=' '}
				{/view_section}
				{view_section heading="Project Details" expand="closed"}
					{view_data model=$SInvoice attribute="project_id" label='Project'}
					{view_data model=$SInvoice attribute="task_id" label='Task'}	
				{/view_section}
			</dl>
		{/view_section}
		{view_section heading=$transaction_type|cat:" Lines" expand="open"}
			<div id="view_data_bottom">
				{data_table}
					<thead>
						<tr>
							<th class="right">Line #</th>
							<th>Item</th>
							<th>Account</th>
							<th>Centre</th>
							<th class="right">Unit price</th>
							<th class="right">Qty</th>
							<th>UoM</th>
							<th class="right">Net Value</th>
							<th class="right">Tax Value</th>
							<th class="right">Gross Value</th>
						</tr>
					</thead>
					{foreach name=lines item=line from=$SInvoice->lines}
						{assign var=line_number value=$line->line_number}
						{assign var=id value=$line->id}
						<tr data-line-number="{$line_number}">
							{if $SInvoice->status==$SInvoice->newStatus()}
								<td align='right' class="edit-line">
									{link_to module="sales_invoicing" controller="sinvoicelines" action="edit" id="$id" value="$line_number"}
									{input model=$line type='hidden' rowid="$line_number" attribute='id'}
								</td>
							{else}
								<td align='right'>{$line->line_number}</td>
							{/if}
							<td align='left'>
								{if !is_null($line->stitem_id)}
									{assign var=stitem_id value=$line->stitem_id}
									{assign var=description value=$line->description}
									{link_to module="manufacturing" controller="stitems" action="view" id="$stitem_id" value="$description"}
								{else}
									{$line->description}
								{/if}
							</td>
							<td>{$line->glaccount}</td>
							<td>{$line->glcentre}</td>
							<td class="numeric">{$line->sales_price|string_format:'%.4f'}</td>
							<td class="numeric">{$line->sales_qty}</td>
							<td>{$line->uom_name}</td>
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