{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
{content_wrapper}
	{advanced_search}
	<p><strong>Transaction Details</strong></p>
	{input type="hidden" attribute="id" value=$id}
	{paging}
	{form controller="porders" action="save_grn_write_off"}
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
				{heading_cell field="item_description"}
					Item Description
				{/heading_cell}
				{heading_cell field="net_value" class='right'}
					Value
				{/heading_cell}
				{heading_cell field="status" class='right'}
					Status
				{/heading_cell}
				{heading_cell field="invoice_number" class='right'}
					Write Off?
				{/heading_cell}
			{/heading_row}
			{foreach name=datagrid item=model from=$poreceivedlines}
				{assign var=rowid value=$model->id}
				{grid_row model=$model data_rowid=$rowid}
					{grid_cell model=$model cell_num=1 field="gr_number" class='numeric'}
						{$model->gr_number}
					{/grid_cell}
					{grid_cell model=$model cell_num=2 field="order_number" class='numeric' no_escape=true}
						{link_to module='purchase_order' controller='porders' action='view' id=$model->order_id value=$model->order_number}
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
					{grid_cell model=$model cell_num=8 field="item_description"}
						{$model->item_description}
					{/grid_cell}
					{grid_cell model=$model cell_num=9 field="net_value"}
						{$model->net_value}
					{/grid_cell}
					{grid_cell model=$model cell_num=9 field="status"}
						{$model->getFormatted('status')}
					{/grid_cell}
					{grid_cell model=$model cell_num=9 field="grn_write_off" no_escape=true}
						{input type='checkbox' attribute="grn_write_off" model=$model rowid=$rowid rel=$model->id number=$model->id tags=none label='' value=$grn_write_off.$rowid}
					{/grid_cell}
				{/grid_row}
			{foreachelse}
				<tr>
					<td colspan="0">No matching records found!</td>
				</tr>
			{/foreach}
		{/data_table}
		{submit}
	{/form}
	{include file='elements/cancelForm.tpl'}
	<div id="data_grid_footer" class="clearfix">
		{paging}
		{include file='elements/data_table_actions.tpl'}
	</div>
{/content_wrapper}