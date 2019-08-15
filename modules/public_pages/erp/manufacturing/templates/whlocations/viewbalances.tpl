{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.14 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{with model=$transaction}
				{view_data label="Store" attribute="whstore"}
				{view_data attribute='location'}
				{view_data attribute='description'}
			{/with}
		</dl>
	</div>
	{advanced_search}
	{paging}
	{data_table}
		{heading_row}
			{heading_cell field="stitem"}
				Stock Item
			{/heading_cell}
			{heading_cell field="whbin"}
				Bin
			{/heading_cell}
			{heading_cell field="balance" class="right"}
				Balance
			{/heading_cell}
			{heading_cell field="uom_name"}
				UoM
			{/heading_cell}
			{heading_cell field="supply_demand" class="center"}
				Supply/Demand
			{/heading_cell}
		{/heading_row}
				{foreach name=datagrid item=model from=$stbalances}
			{grid_row model=$model}
				<td>
					{link_to value=$model->stitem module=$module controller=$controller action='viewTransactions' id=$transaction->id stitem_id=$model->stitem_id }
				</td>
				{grid_cell model=$model cell_num=2 field="whbin"}
					{$model->whbin}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="balance"}
					{$model->balance}
				{/grid_cell}
				{grid_cell model=$model cell_num=4 field="uom_name"}
					{$model->uom_name}
				{/grid_cell}
				{grid_cell model=$model cell_num=4 field="supply_demand"}
					{$model->supply_demand}
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