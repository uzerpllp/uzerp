{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	<div id="view_page" class="clearfix">
	    <dl id="view_data_left">
	        {with model=$transaction}
	                {view_data attribute='item_code'}
	                {view_data attribute='description'}
	        {/with}
	    </dl>
	</div>
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
	                {heading_cell field="latest_osc"}
	                	Cost
	                {/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$outside_ops}
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
	                        {grid_cell model=$model cell_num=4 field="description"}
	                                {$model->description}
	                        {/grid_cell}
	                        {grid_cell model=$model cell_num=5 field="latest_osc"}
	                                {$model->latest_osc}
	                        {/grid_cell}
		{/grid_row}
		{foreachelse}
			<tr><td colspan="0">No matching records found!</td></tr>
		{/foreach}
	{/data_table}
{/content_wrapper}