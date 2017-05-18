{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.9 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{with model=$whlocation}
				{view_data attribute='whstore' label='Store'}
				{view_data attribute='location'}
				{view_data attribute='description'}
				{view_data attribute='has_balance'}
				{view_data attribute='supply_demand'}
				{view_data attribute='bin_controlled'}
				{view_data attribute='saleable'}
				{view_data attribute='pickable'}
				{view_data attribute='glaccount' label='GL Account'}
				{view_data attribute='glcentre' label='GL Centre'}
			{/with}
		</dl>
	</div>
	{advanced_search}
	{paging}
	{data_table}
		{heading_row}
			{heading_cell field="bin_code" _whlocation_id=$whlocation->id}
				Bin Code
			{/heading_cell}
			{heading_cell field="description" _whlocation_id=$whlocation->id}
				Description
			{/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$whbins}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=1 field="bin_code"}
	 				{$model->bin_code}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="description"}
					{$model->description}
				{/grid_cell}
			{/grid_row}
		{foreachelse}
			<tr>
				<td colspan="0">No matching records found!</td>
			</tr>
		{/foreach}
	{/data_table}
	{paging}
	<div id="data_grid_footer" class="clearfix">
		{include file='elements/data_table_actions.tpl'}
	</div>
{/content_wrapper}