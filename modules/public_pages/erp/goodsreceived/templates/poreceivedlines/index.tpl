{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.17 $ *}
{content_wrapper}
	{advanced_search}
	<p><strong>Transaction Details</strong></p>
	{input type="hidden" attribute="id" value=$id}
	{paging}
	{data_table}
		{heading_row}
			{heading_cell field="gr_number" class='right'}
				Goods Received Number
			{/heading_cell}
			{heading_cell field="order_number" class='right'}
				Order Number
			{/heading_cell}
			{heading_cell field="delivery_note" }
				Delivery Note
			{/heading_cell}
			{heading_cell field="supplier" }
				Supplier
			{/heading_cell}
			{heading_cell field="received_date" }
				Date Received
			{/heading_cell}
			{heading_cell field="received_qty" class='right'}
				Received Qty
			{/heading_cell}
			{heading_cell field="uom_name" }
				UoM
			{/heading_cell}
			{heading_cell field="description"}
				Description
			{/heading_cell}
			{heading_cell field="status" }
				Status
			{/heading_cell}
			{heading_cell field="invoice_number" class='right'}
				Invoice Number
			{/heading_cell}
			{heading_cell field="none"}
				&nbsp;
			{/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$poreceivedlines}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=1 field="gr_number" class='numeric'}
					{$model->gr_number}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="order_number" class='numeric' no_escape=true}
					{$model->order_number}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="delivery_note"}
					{$model->delivery_note}
				{/grid_cell}
				{grid_cell model=$model cell_num=4 field="supplier"}
					{$model->supplier}
				{/grid_cell}
				{grid_cell model=$model cell_num=5 field="received_date"}
					{$model->getFormatted('received_date')}
				{/grid_cell}
				{grid_cell model=$model cell_num=6 field="received_qty" class='numeric'}
					{$model->received_qty}
				{/grid_cell}
				{grid_cell model=$model cell_num=7 field="uom_name"}
					{$model->uom_name}
				{/grid_cell}
				{grid_cell model=$model cell_num=8 field="description"}
					{$model->order_line->description}
				{/grid_cell}
				{grid_cell model=$model cell_num=9 field="status"}
					{$model->getFormatted('status')}
				{/grid_cell}
				<td class='numeric'>
					{if !is_null($model->invoice_id)}
						{link_to module='purchase_invoicing' controller='pinvoices' action='view' id=$model->invoice_id value=$model->invoice_number}
					{/if}
				</td>
			{/grid_row}
		{foreachelse}
			<tr><td colspan="0">No matching records found!</td></tr>
		{/foreach}
	{/data_table}
	<div id="data_grid_footer" class="clearfix">
		{paging}
		{include file='elements/data_table_actions.tpl'}
	</div>
{/content_wrapper}