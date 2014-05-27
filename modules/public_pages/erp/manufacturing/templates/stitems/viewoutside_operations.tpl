{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.5 $ *}
{content_wrapper}
	<h3>Outside Operations</h3>
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
			{heading_cell field="description"}
				Description
			{/heading_cell}
			{heading_cell field="latest_osc" class="right"}
				Cost
			{/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$mfoutsideoperations}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=1 field="op_no"}
					{$model->op_no}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="start_date"}
					{if !is_null($model->start_date)}
						{$model->start_date|un_fix_date}
					{/if}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="end_date"}
					{if !is_null($model->end_date)}
						{$model->end_date|un_fix_date}
					{/if}
				{/grid_cell}
				{grid_cell model=$model cell_num=4 field="description"}
					{$model->description}
				{/grid_cell}
				{grid_cell model=$model cell_num=5 field="latest_osc"}
					{$model->latest_osc}
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