{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{with model=$transaction}
				{view_data attribute="action_name"}
				{view_data attribute="description"}
				{view_data attribute="label"}
				{view_data attribute="type"}
				{view_data attribute="max_rules"}
				{view_data attribute="from_has_balance"}
				{view_data attribute="from_bin_controlled"}
				{view_data attribute="from_saleable"}
				{view_data attribute="to_has_balance"}
				{view_data attribute="to_bin_controlled"}
				{view_data attribute="to_saleable"}
			{/with}
		</dl>
	</div>
	<h3><b>Transfer Rules</b></h3>
	{data_table}
		{heading_row}
			{heading_cell field="from_store"}
				From Store/Location
			{/heading_cell}
			{heading_cell field="to_location"}
				To Store/Location
			{/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$elements}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=1 field="from_store"}
					{$model->from_location}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="to_location"}
					{$model->to_location}
				{/grid_cell}
			{/grid_row}
		{foreachelse}
			<tr><td colspan="0">No matching records found!</td></tr>
		{/foreach}
	{/data_table}
{/content_wrapper}