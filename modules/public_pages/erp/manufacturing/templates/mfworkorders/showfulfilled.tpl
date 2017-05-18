{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{data_table}
		{heading_row}
	        {heading_cell field="order_no" class='right'}
	        	Order No.
	        {/heading_cell}
	        {heading_cell field="order_qty" class="right"}
	        	Order Qty
	        {/heading_cell}
	        {heading_cell field="stitem" label="stitem"}
	        	Stock Item
	        {/heading_cell}
	        {heading_cell field="required_by"}
	        	Required by
	        {/heading_cell}
	        {heading_cell field="made_qty" class="right"}
	        	Made Qty
	        {/heading_cell}
	        {heading_cell field="status"}
	        	Status
	        {/heading_cell}
	    {/heading_row}
		{foreach name=datagrid item=model from=$mfworkorders}
		{grid_row model=$model}
	        {grid_cell model=$model cell_num=1 class='numeric'}
	            {$model->wo_number}
	        {/grid_cell}
	        {grid_cell model=$model cell_num=2 field="order_qty" class="numeric"}
	            {$model->order_qty}
	        {/grid_cell}
	        {grid_cell model=$model cell_num=3 field="stitem"}
	            {$model->stitem}
	        {/grid_cell}
	        {grid_cell model=$model cell_num=3 field="required_by"}
	            {$model->getFormatted('required_by')}
	        {/grid_cell}
	        {grid_cell model=$model cell_num=3 field="made_qty" class="numeric"}
	            {$model->made_qty}
	        {/grid_cell}
	        {grid_cell model=$model cell_num=3 field="status"}
	            {$model->getFormatted('status')}
	        {/grid_cell}
		{/grid_row}
		{foreachelse}
			<tr><td colspan="0">No matching records found!</td></tr>
		{/foreach}
	{/data_table}
{/content_wrapper}