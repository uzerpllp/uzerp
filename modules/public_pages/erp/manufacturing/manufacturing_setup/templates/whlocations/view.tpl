{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.9 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{view_data model=$transaction label="Store" attribute="whstore"}
			{view_data model=$transaction attribute="location"}
			{view_data model=$transaction attribute="description"}
			{view_data model=$transaction attribute="has_balance"}
			{view_data model=$transaction attribute="supply_demand"}
			{view_data model=$transaction attribute="bin_controlled"}
			{view_data model=$transaction attribute="saleable"}
			{view_data model=$transaction attribute="pickable"}
			{view_data model=$transaction attribute="glaccount" label="GL Account"}
			{view_data model=$transaction attribute="glcentre" label="Cost Centre"}
		</dl>
	</div>
	{if $transaction->bin_controlled=="t" }
		<p><strong>Bins</strong></p>
		{advanced_search}
		{paging}
		{data_table}
			{heading_row}
				{heading_cell field="bin_code"}
					Bin Code
				{/heading_cell}
				{heading_cell field="description"}
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
	{/if}
{/content_wrapper}