{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.6 $ *}
{content_wrapper}
	{paging}
	<h3>Where Used</h3>
	{data_table}
		{heading_row}
			{heading_cell field="line_no"}
				Line No.
			{/heading_cell}
			{heading_cell field="stitem"}
				Stock Item
			{/heading_cell}
			{heading_cell field="start_date"}
				Start Date
			{/heading_cell}
			{heading_cell field="end_date"}
				End Date
			{/heading_cell}
			{heading_cell field="qty" class='right'}
				Quantity
			{/heading_cell}
			{heading_cell field="uom"}
				UoM
			{/heading_cell}
			{heading_cell field="waste_pc" class='right'}
				Waste %
			{/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$mfstructures}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=1 field="line_no"}
					{$model->line_no}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="stitem"}
					{$model->stitem}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="start_date"}
					{$model->start_date}
				{/grid_cell}
				{grid_cell model=$model cell_num=4 field="end_date"}
					{$model->end_date}
				{/grid_cell}
				{grid_cell model=$model cell_num=5 field="qty" class='numeric'}
					{$model->qty}
				{/grid_cell}
				{grid_cell model=$model cell_num=6 field="uom"}
					{$model->uom}
				{/grid_cell}
				{grid_cell model=$model cell_num=7 field="waste_pc" class='numeric'}
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