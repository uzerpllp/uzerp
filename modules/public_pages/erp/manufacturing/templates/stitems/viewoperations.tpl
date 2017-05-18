{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.6 $ *}
{content_wrapper}
	<h3>Operations</h3>
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
			{heading_cell field="volume_target"}
				Volume Target
			{/heading_cell}
			{heading_cell field="volume_uom"}
				UoM
			{/heading_cell}
			{heading_cell field="volume_period"}
				Volume Per
			{/heading_cell}
			{heading_cell field="quality_target"}
				Quality Target
			{/heading_cell}
			{heading_cell field="uptime_target"}
				Uptime Target
			{/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$operations}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=1 field="op_no"}
					{$model->op_no}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="start_date"}
					{if !is_null($model->end_date)}
						{$model->start_date|un_fix_date}
					{/if}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="end_date"}
					{if !is_null($model->end_date)}
						{$model->end_date|un_fix_date}
					{/if}
				{/grid_cell}
				{grid_cell model=$model cell_num=4 field="volume_target"}
					{$model->volume_target}
				{/grid_cell}
				{grid_cell model=$model cell_num=5 field="volume_uom"}
					{$model->volume_uom}
				{/grid_cell}
				{grid_cell model=$model cell_num=6 field="volume_period"}
					{$model->getFormatted('volume_period')}
				{/grid_cell}
				{grid_cell model=$model cell_num=7 field="quality_target"}
					{$model->quality_target}
				{/grid_cell}
				{grid_cell model=$model cell_num=8 field="uptime_target"}
					{$model->uptime_target}
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