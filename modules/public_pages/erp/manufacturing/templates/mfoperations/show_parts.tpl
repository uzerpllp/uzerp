{**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **}
{* $Revision: 1.2 $ *}
{content_wrapper}
	{if $mfoperations->count()>0}
		<p><strong>Current Operations</strong></p>
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
				{heading_cell field="volume_target" class="right"}
					{if $stitem->cost_basis == 'VOLUME'}
					Volume Target
					{else}
					Time
					{/if}
				{/heading_cell}
				{heading_cell field="volume_uom"}
					UoM
				{/heading_cell}
				{heading_cell field="volume_period"}
					{if $stitem->cost_basis == 'VOLUME'}
					Volume Period
					{else}
					Time Unit
					{/if}
				{/heading_cell}
				{if $stitem->cost_basis == 'VOLUME'}
				{heading_cell field="quality_target" class="right"}
					Quality Target
				{/heading_cell}
				{heading_cell field="uptime_target" class="right"}
					Uptime Target
				{/heading_cell}
				{/if}
				{heading_cell field="resource_qty"}
					Resource Qty
				{/heading_cell}
				{heading_cell field="resource"}
					Resource
				{/heading_cell}
			{/heading_row}
			{foreach name=datagrid item=model from=$mfoperations}
				{grid_row model=$model}
					{grid_cell model=$model cell_num=2 field="op_no"}
						{$model->op_no}
					{/grid_cell}
					{grid_cell model=$model cell_num=3 field="start_date"}
						{$model->start_date}
					{/grid_cell}
					{grid_cell model=$model cell_num=4 field="end_date"}
						{$model->end_date}
					{/grid_cell}
					{grid_cell model=$model cell_num=6 field="volume_target"}
						{$model->volume_target}
					{/grid_cell}
					{grid_cell model=$model cell_num=5 field="volume_uom"}
						{$model->volume_uom}
					{/grid_cell}
					{grid_cell model=$model cell_num=7 field="volume_period"}
						{$model->getFormatted('volume_period')}
					{/grid_cell}
					{if $stitem->cost_basis == 'VOLUME'}
					{grid_cell model=$model cell_num=8 field="quality_target"}
						{$model->quality_target}
					{/grid_cell}
					{grid_cell model=$model cell_num=9 field="uptime_target"}
						{$model->uptime_target}
					{/grid_cell}
					{/if}
					{grid_cell model=$model cell_num=11 field="resource_qty"}
						{$model->resource_qty}
					{/grid_cell}
					{grid_cell model=$model cell_num=10 field="resource"}
						{$model->resource}
					{/grid_cell}
				{/grid_row}
			{foreachelse}
				<tr><td colspan="0">No matching records found!</td></tr>
			{/foreach}
		{/data_table}
	{/if}
{/content_wrapper}