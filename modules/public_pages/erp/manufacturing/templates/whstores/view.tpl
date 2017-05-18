{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.7 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{with model=$transaction}
				{view_data attribute="store_code"}
				{view_data attribute="description"}
				{view_data value=$model->getAddress() label='Address'}
			{/with}
		</dl>
	</div>
	<p><strong>Locations</strong></p>
	{advanced_search}
	{paging}
	{data_table}
		{heading_row}
			{heading_cell field="location"}
				Name
			{/heading_cell}
			{heading_cell field="description"}
				Description
			{/heading_cell}
			{heading_cell field="has_balance"}
				Has Balance
			{/heading_cell}
			{heading_cell field="bin_controlled"}
				Bin Controlled
			{/heading_cell}
			{heading_cell field="saleable"}
				Saleable
			{/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$whlocations}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=1 field="location"}
					{$model->location}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="description"}
					{$model->description}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="has_balance"}
					{$model->has_balance}
				{/grid_cell}
				{grid_cell model=$model cell_num=4 field="bin_controlled"}
					{$model->bin_controlled}
				{/grid_cell}
				{grid_cell model=$model cell_num=4 field="saleable"}
					{$model->saleable}
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