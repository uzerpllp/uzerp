{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.10 $ *}
{content_wrapper}
	<h3>Stock Balances</h3>
	{paging}
	{data_table}
		{heading_row}
			{heading_cell field="whlocation"}
				Store/Location
			{/heading_cell}
			{heading_cell field="whbin"}
				Bin
			{/heading_cell}
			{heading_cell field="balance" class="right"}
				Balance
			{/heading_cell}
			<th>
				UoM
			</th>
			{heading_cell field="supply_demand"}
				Supply/Demand
			{/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$stbalances}
			{if $model->whbin_id>0}
				{assign var='clickcontroller' value='whbins'}
				{assign var='linkvaluefield' value='whbin_id'}
			{else}
				{assign var='clickcontroller' value='whlocations'}
				{assign var='linkvaluefield' value='whlocation_id'}
			{/if}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=1 _stitem_id=$model->stitem_id}
					{$model->whlocation}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="whbin"}
					{$model->whbin}
				{/grid_cell}
		 		{grid_cell model=$model cell_num=3 field="balance"}
					{$model->balance}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="uom_name"}
					{$model->stock_item->uom_name}
				{/grid_cell}
				<td>
					{if $model->supply_demand=='t'}
						<img src="/themes/default/graphics/true.png" alt="true" />
					{else}
						<img src="/themes/default/graphics/false.png" alt="false" />
					{/if}
				</td>
			{/grid_row}
		{foreachelse}
			<tr>
				<td colspan="0">No matching records found!</td>
			</tr>
		{/foreach}
	{/data_table}
	{paging}
{/content_wrapper}