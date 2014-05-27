{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.8 $ *}
{content_wrapper}
	{advanced_search}
	<div id="view_page" class="clearfix">
		<h3>Sales Product Lines</h3>
		<br>
		{paging}
		{data_table}
			{heading_row}
				{heading_cell field="customer"}
					Customer
				{/heading_cell}
				{heading_cell field="description"}
					Description
				{/heading_cell}
				{heading_cell field="supplier_product_code"}
					Supplier Product Code
				{/heading_cell}
				{heading_cell field="uom_name"}
					UoM
				{/heading_cell}
				{heading_cell field="start_date"}
					Start Date
				{/heading_cell}
				{heading_cell field="end_date"}
					End Date
				{/heading_cell}
				{heading_cell field="so_price_type"}
					Price Type
				{/heading_cell}
				{heading_cell field="price" class="right"}
					Price
				{/heading_cell}
				{heading_cell field="currency"}
					Currency
				{/heading_cell}
			{/heading_row}
			{foreach name=datagrid item=model from=$soproductlines}
			{grid_row model=$model}
				<td>
					{link_to module='sales_ledger' controller='SLCustomers' action='view' id=$model->slmaster_id value=$model->customer}
				</td>
				<td>
					{link_to module='sales_order' controller='SOProductlines' action='edit' id=$model->id value=$model->description}
				</td>
				{grid_cell model=$model cell_num=3 field="customer_product_code"}
					{$model->customer_product_code}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="uom_name"}
					{$model->uom_name}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="start_date"}
					{$model->start_date}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="end_date"}
					{$model->end_date}
				{/grid_cell}
				{grid_cell model=$model cell_num=5 field="so_price_type"}
					{$model->so_price_type}
				{/grid_cell}
				{grid_cell model=$model cell_num=5 field="price"}
					{$model->price|string_format:'%.2f'}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="currency"}
					{$model->currency}
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