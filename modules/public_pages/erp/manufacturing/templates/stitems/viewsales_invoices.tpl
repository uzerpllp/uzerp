{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.7 $ *}
{content_wrapper}
	{advanced_search}
	<div id="view_page" class="clearfix">
		<h3>Sales Invoice Lines</h3>
		<dt>Total Quantity</dt><dd>{$total_qty}</dt>
		<dt>Total Value</dt><dd>{$total_value|string_format:'%.2f'}</dt>
		<br>
		{paging}
		{data_table}
			{heading_row}
				{heading_cell field="transaction_type"}
					Type
				{/heading_cell}
				{heading_cell field="invoice_number"}
					Number
				{/heading_cell}
				{heading_cell field="customer"}
					Customer
				{/heading_cell}
				{heading_cell field="invoice_date"}
					Invoice Date
				{/heading_cell}
				{heading_cell field="order_number"}
					Order No
				{/heading_cell}
				{heading_cell field="sales_qty" class="right"}
					Order Qty
				{/heading_cell}
				{heading_cell field="uom_name"}
					UoM
				{/heading_cell}
				{heading_cell field="net_value" class="right"}
					Order Value
				{/heading_cell}
			{/heading_row}
			{foreach name=datagrid item=model from=$sinvoicelines}
			{grid_row model=$model}
				<td>
					{$model->transaction_type($model->transaction_type)}
					{if $model->transaction_type=='I'}
						{assign var=multiplier value=1}
					{else}
						{assign var=multiplier value=-1}
					{/if}
				</td>
				<td>
					{link_to module='sales_invoicing' controller='Sinvoices' action='view' id=$model->invoice_id value=$model->invoice_number}
				</td>
				<td>
					{link_to module='sales_ledger' controller='SLCustomers' action='view' id=$model->slmaster_id value=$model->customer}
				</td>
				{grid_cell model=$model cell_num=2 field="invoice_date"}
					{$model->invoice_date|un_fix_date}
				{/grid_cell}
				<td>
					{link_to module='sales_order' controller='Sorders' action='view' id=$model->sales_order_id value=$model->order_number}
				</td>
				{grid_cell model=$model cell_num=3 field="sales_qty"}
					{$model->sales_qty*$multiplier}
				{/grid_cell}
				{grid_cell model=$model cell_num=4 field="uom_name"}
					{$model->uom_name}
				{/grid_cell}
				{grid_cell model=$model cell_num=5 field="net_value"}
					{$model->net_value*$multiplier|string_format:'%.2f'}
				{/grid_cell}
			{/grid_row}
			{foreachelse}
				<tr>
					<td colspan="0">No matching records found!</td>
				</tr>
			{/foreach}
		{/data_table}
		{paging}
	</div>
{/content_wrapper}