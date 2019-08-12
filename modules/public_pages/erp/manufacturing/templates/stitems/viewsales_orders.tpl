{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.11 $ *}
{content_wrapper}
	{advanced_search}
	<div id="view_page" class="clearfix">
	<input type="hidden" id="print_force_index" value="true" />
		{paging}
		{data_table}
			{heading_row}
				{heading_cell field="order_number"}
					Order No.
				{/heading_cell}
				{heading_cell field="type"}
					Type
				{/heading_cell}
				{heading_cell field="customer"}
					Customer
				{/heading_cell}
				{heading_cell field="order_qty" class="right"}
					Order Qty
				{/heading_cell}
				{heading_cell field="revised_qty" class="right"}
					Revised Qty
				{/heading_cell}
				{heading_cell field="del_qty" class="right"}
					Delivered Qty
				{/heading_cell}
				{heading_cell field="due_despatch_date"}
					Required by
				{/heading_cell}
				{heading_cell field="status"}
					Status
				{/heading_cell}
			{/heading_row}
			{foreach name=datagrid item=model from=$sorderlines}
			{grid_row model=$model}
				<td>
					{link_to module=$clickmodule controller=$clickcontroller action=$clickaction id=$model->order_id value=$model->order_number}
				</td>
				{grid_cell model=$model cell_num=2 field="type"}
					{$model->getFormatted('type')}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="customer"}
					{$model->customer}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="order_qty"}
					{$model->order_qty}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="revised_qty"}
					{$model->revised_qty}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="del_qty"}
					{$model->del_qty}
				{/grid_cell}
				{grid_cell model=$model cell_num=4 field="due_despatch_date"}
					{$model->getFormatted('due_despatch_date')}
				{/grid_cell}
				{grid_cell model=$model cell_num=5 field="status"}
					{$model->getFormatted('status')}
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