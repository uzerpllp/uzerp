{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.7 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{view_data model=$transaction attribute="mfdept" label="Department"}
			{view_data model=$transaction attribute="work_centre"}
			{view_data model=$transaction attribute="centre"}
			{view_data model=$transaction attribute="available_qty"}
			{view_data model=$transaction attribute="centre_rate"}
			{view_data model=$transaction attribute="production_recording"}
		</dl>
	</div>
	<h3><b>Used in the following operations</b></h3>
	{paging}
	{data_table}
		{heading_row}
			{heading_cell field="op_no"}
				Op No.
			{/heading_cell}
			{heading_cell field="start_date"}
				Start Date
			{/heading_cell}
			{heading_cell field="end_date"}
				End Date
			{/heading_cell}
			{heading_cell field="stitem"}
				Stock Item
			{/heading_cell}
			{heading_cell field="resource"}
				Resource
			{/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$mfoperations}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=2 field="op_no" }
					{$model->op_no}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="start_date"}
					{$model->start_date}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="end_date"}
					{$model->end_date}
				{/grid_cell}
				{grid_cell model=$model cell_num=4 field="stitem"}
					{$model->stitem}
				{/grid_cell}
				{grid_cell model=$model cell_num=5 field="resource"}
					{$model->mfresource}
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