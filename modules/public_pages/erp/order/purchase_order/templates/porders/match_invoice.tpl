{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.3 $ *}	
{content_wrapper}
	{advanced_search}
	{form controller="porders" action="saveMatchInvoice"}
		<input type='hidden' name=plmaster_id value={$search_plmaster_id}>
		<input type='hidden' name=stitem value={$search_stitem}>
		<input type='hidden' name=order_id value={$search_order_id}>
	<dl id="view_data_bottom">
	{data_table class="uz-grid-table"}
		<thead>
			<tr>
				<th class=right>Order Number</th>
				<th class=right>GR Number</th>
				<th class=right>Delivery Note</th>
				<th>Date Received</th>
				<th width=25%>Item Description</th>
				<th>Item Code</th>
				<th class=right>Qty Received</th>
				<th class=right>UoM</th>
				<th class=right>Order Value</th>
				<th class=right>Currency</th>
				<th>Invoice Number</th>
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
						{$model->item_description}
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
						{select model=$model type="text" attribute="invoice_number" class="invoice_number" options=$model->getUnmatchedInvoices() rowid="$rowid" number="$rowid" tags='none' nolabel=true}
					</td>
				</tr>
			{foreachelse}
				<tr>
					<td colspan="0">No matching records found!</td>
				</tr>
			{/foreach}
		</tbody>
	{/data_table}
	{paging}
	{submit tags='none'}
		</dl>
	{/form}
{/content_wrapper}