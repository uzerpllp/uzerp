{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$WorkSchedule}
			{view_data attribute="job_no"}
			{view_data attribute="description"}
			{view_data attribute="start_date"}
			{view_data attribute="end_date" modifier="overdue"}
			{view_data attribute="status"}
			{view_data attribute="centre_id"}
			{view_data attribute="planned_time"}
			{view_data attribute="actual_time"}
			{view_data attribute="mf_downtime_code_id" label='Downtime Code'}
		{/with}
	</div>
	{paging}
	{data_table}
		{heading_row}
			{heading_cell field="resource"}
				Resource
			{/heading_cell}
			{heading_cell field="resource_code"}
				Name
			{/heading_cell}
			{heading_cell field="resource_rate"}
				Rate
			{/heading_cell}
			{heading_cell field="quantity"}
				quantity
			{/heading_cell}
			{heading_cell}
			{/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$engineeringresources}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=1 field="resource"}
					{$model->resource}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="person"}
					{$model->person}
				{/grid_cell}
		 		{grid_cell model=$model cell_num=3 field="resource_rate"}
					{$model->resource_rate}
				{/grid_cell}
		 		{grid_cell model=$model cell_num=3 field="quantity"}
					{$model->quantity}
				{/grid_cell}
				<td>
					{include file='elements/delete_row.tpl'}
				</td>
			{/grid_row}
		{foreachelse}
			<tr>
				<td colspan="0">No matching records found!</td>
			</tr>
		{/foreach}
	{/data_table}
{/content_wrapper}