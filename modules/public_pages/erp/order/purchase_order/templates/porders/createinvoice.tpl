{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.20 $ *}	
{content_wrapper}
	{advanced_search}
	{form controller="porders" action="saveInvoice"}
		<dl id="view_data_left">
			<dt>Supplier Invoice Date</dt>
			<dd>{input type='text' attribute='invoice_date' value="$invoicedate" label=' ' tags='none' class='datefield'}</dd>
			<dt>Supplier Invoice Number</dt>
			<dd>{input type='text' attribute='ext_reference' label=' ' tags='none'}</dd>
		</dl>
		<input type='hidden' name=plmaster_id value={$search_plmaster_id}>
		<input type='hidden' name=stitem_id value={$search_stitem_id}>
		<input type='hidden' name=order_id value={$search_order_id}>
	<dl id="view_data_bottom">
	{data_table class="uz-grid-table"}
		<thead>
			<tr>
				<th class=right>Order Number</th>
				<th class=right>GR Number</th>
				<th class=right>Delivery Note</th>
				<th>Date Received</th>
				<th width=25%>Description</th>
				<th>Item Code</th>
				<th class=right>Qty Received</th>
				<th class=right>UoM</th>
				<th class=right>Order Value</th>
				<th class=right>Currency</th>
				<th class=right>Invoice Value</th>
				<th align=center>Create Invoice?</th>
			</tr>
		</thead>
		<tbody>
			{foreach name=datagrid item=model from=$poreceivedlines}
				{assign var=rowid value=$model->id}
				<tr rel="{$rowid}" data-row-id="{$rowid}">
					<td align=right>
						{link_to module='purchase_order' controller='POrders' action='view' id=$model->order_id value=$model->order_number}
					</td>
					<td align=right>
						{$model->gr_number}
					</td>
					<td align=right>
						{$model->delivery_note}
					</td>
					<td>
						{$model->received_date}
					</td>
					<td width=25%>
						{$model->order_line->description}
					</td>
					<td>
						{$model->stitem}
					</td>
					<td align=right>
						{$model->received_qty}
					</td>
					<td align=right>
						{$model->uom_name}
					</td>
					<td right>
						{$model->net_value}
					</td>
					<td align=right>
						{$model->currency}
					</td>
					
					{if $selected_rows.$rowid !='' }
						{assign var=net_value value=$selected_rows.$rowid}
						{assign var=checked value=true}
					{else}
						{assign var=net_value value=$model->net_value }
						{assign var=checked value=false}
					{/if}
					
					<td align=right>
						{input type="hidden" attribute="previousvalue_$rowid" value=$net_value }
						{input model=$model class="net_value numeric" type="text" attribute="saved_net_value" rowid="$rowid" number="$rowid" value=$net_value tags='none' nolabel=true}
					</td>
					<td align=center>
						{input model=$model class="checkbox" type="checkbox" attribute="createinvoice" value=$checked rowid="$rowid" number="$rowid" tags='none' nolabel=true }
					</td>
				</tr>
			{foreachelse}
				<tr>
					<td colspan="0">No matching records found!</td>
				</tr>
			{/foreach}
			<tr>
				<td colspan=11></td>
			</tr>
			<tr>
				<td colspan=10 align="right">
					Invoice Net Total
				</td>
				<td>
					<input class="numeric" type="text" id="invoice_net_total" name="invoice_net_total" value={$total} disabled=true />
				</td>
				<td></td>
			</tr>
		</tbody>
	{/data_table}
	{paging}
	{submit tags='none'}
		</dl>
	{/form}
{/content_wrapper}