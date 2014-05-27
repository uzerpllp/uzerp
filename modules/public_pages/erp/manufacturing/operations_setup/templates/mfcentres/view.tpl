{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
	        {view_data model=$transaction attribute="mfdept" label="Department"}
	        {view_data model=$transaction attribute="work_centre"}
			{view_data model=$transaction attribute="centre"}
			{view_data model=$transaction attribute="centre_rate"}
		</dl>
	</div>
	<h3><b>Used in the following operations</b></h3>
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
			{heading_cell field="mfresource"}
				Resource
			{/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$elements}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=1 field="op_no"}
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
				{grid_cell model=$model cell_num=5 field="mfresource"}
					{$model->mfresource}
				{/grid_cell}
			{/grid_row}
		{foreachelse}
			<tr><td colspan="0">No matching records found!</td></tr>
		{/foreach}
	{/data_table}
{/content_wrapper}