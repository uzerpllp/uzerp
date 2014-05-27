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
	                {heading_cell field="name"}
	                	Name
	                {/heading_cell}
	                {heading_cell field="description"}
	                	Description
	                {/heading_cell}
	                {heading_cell field="action_name"}
	                	Action
	                {/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$actions}
		{grid_row model=$model}
	                        {grid_cell model=$model cell_num=1 idfield="name"}
	                                {$model->name}
	                        {/grid_cell}
	                        {grid_cell model=$model cell_num=2 field="description"}
	                                {$model->description}
	                        {/grid_cell}
	                        {grid_cell model=$model cell_num=3 field="action_name"}
	                                {$model->action_name}
	                        {/grid_cell}
		{/grid_row}
		{foreachelse}
			<tr><td colspan="0">No matching records found!</td></tr>
		{/foreach}
	{/data_table}
{/content_wrapper}