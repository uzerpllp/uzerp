{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.6 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{with model=$transaction}
				<dt class="heading">General</dt>
				{view_data attribute='resource_code'}
				{view_data attribute='description'}
				{view_data attribute='resource_rate'}
			{/with}
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
			{heading_cell field="centre"}
				Centre
			{/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$mfoperations}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=2 field="op_no"}
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
				{grid_cell model=$model cell_num=5 field="centre"}
					{$model->mfcentre}
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