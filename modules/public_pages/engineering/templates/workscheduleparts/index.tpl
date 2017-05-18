{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	{paging}
	{data_table}
		{heading_row}
			{heading_cell field="job_no"}
				job_no
			{/heading_cell}
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
				{grid_cell model=$model cell_num=2 field="job_no"}
					{$model->job_no}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="description"}
					{$model->description}
				{/grid_cell}
		 		{grid_cell model=$model cell_num=1 field="order_qty" _work_schedule_id=$model->work_schedule_id}
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