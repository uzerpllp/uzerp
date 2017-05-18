{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.8 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{view_data model=$transaction attribute="item_code"}
			{view_data model=$transaction attribute="description"}
			{view_data model=$transaction attribute="uom_name"}
			{view_data model=$transaction attribute="comp_class"}
			{view_data model=$transaction attribute="sttypecode" label='type code'}
			{view_data model=$transaction attribute="stproductgroup" label='product group'}
		</dl>
	</div>
	<p><strong>Made From</strong></p>
	{advanced_search}
	{paging}
	{data_table}
		{heading_row}
			{heading_cell field="line_no"}
				Line No.
			{/heading_cell}
			{heading_cell field="ststructure"}
				Stock Item
			{/heading_cell}
			{heading_cell field="start_date"}
				Start Date
			{/heading_cell}
			{heading_cell field="end_date"}
				End Date
			{/heading_cell}
			{heading_cell field="qty"}
				Quantity
			{/heading_cell}
			{heading_cell field="uom"}
				UoM
			{/heading_cell}
			{heading_cell field="waste_pc"}
				Waste %
			{/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$mfstructures}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=1 field="line_no"}
					{$model->line_no}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="ststructure"}
					{$model->ststructure}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="start_date"}
					{$model->start_date}
				{/grid_cell}
				{grid_cell model=$model cell_num=4 field="end_date"}
					{$model->end_date}
				{/grid_cell}
				{grid_cell model=$model cell_num=5 field="qty"}
					{$model->qty}
				{/grid_cell}
				{grid_cell model=$model cell_num=6 field="uom"}
					{$model->uom}
				{/grid_cell}
				{grid_cell model=$model cell_num=7 field="waste_pc"}
					{$model->waste_pc}
				{/grid_cell}
			{/grid_row}
		{foreachelse}
			<tr>
				<td colspan="0">No matching records found!</td>
			</tr>
		{/foreach}
	{/data_table}
	{paging}
{/content_wrapper}