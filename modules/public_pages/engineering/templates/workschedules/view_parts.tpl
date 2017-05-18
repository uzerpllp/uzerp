{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
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
			{heading_cell field="description"}
				Description
			{/heading_cell}
			{heading_cell field="order_qty" class="right"}
				order_qty
			{/heading_cell}
			{heading_cell field="uom_name"}
				uom_name
			{/heading_cell}
			{heading_cell field="order_number"}
				order_number
			{/heading_cell}
			{heading_cell field="status"}
				status
			{/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$workscheduleparts}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=2 field="description"}
					{$model->description}
				{/grid_cell}
		 		{grid_cell model=$model cell_num=3 field="order_qty"}
					{$model->order_qty}
				{/grid_cell}
		 		{grid_cell model=$model cell_num=4 field="uom_name"}
					{$model->uom_name}
				{/grid_cell}
		 		{grid_cell model=$model cell_num=5 field="order_number"}
					{$model->order_number}
				{/grid_cell}
		 		{grid_cell model=$model cell_num=6 field="status"}
					{$model->order_detail->getFormatted('status')}
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