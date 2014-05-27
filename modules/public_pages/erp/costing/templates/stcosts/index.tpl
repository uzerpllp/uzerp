{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.10 $ *}
{content_wrapper}
	{advanced_search}
	{paging}
	{data_table}
		{heading_row}
			{heading_cell field="stitem"}
				Stock Item
			{/heading_cell}
			{heading_cell field="type"}
				Type
			{/heading_cell}
			{heading_cell field="cost" class='right'}
				Cost
			{/heading_cell}
			{heading_cell field="mat" class='right'}
				Materials
			{/heading_cell}
			{heading_cell field="lab" class='right'}
				Labour
			{/heading_cell}
			{heading_cell field="osc" class='right'}
				Outside Contract
			{/heading_cell}
			{heading_cell field="ohd" class='right'}
				Overhead
			{/heading_cell}
			{heading_cell field="effect_on_stock" class='right'}
				Effect On Stock
			{/heading_cell}
			{heading_cell field="lastupdated"}
				Date Updated
			{/heading_cell}
			{heading_cell field="alteredby"}
				Updated By
			{/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$stcosts}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=1 field="stitem"}
					{$model->stitem}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="type"}
					{$model->getFormatted('type')}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="cost"}
					{$model->cost}
				{/grid_cell}
				{grid_cell model=$model cell_num=4 field="mat"}
					{$model->mat}
				{/grid_cell}
				{grid_cell model=$model cell_num=5 field="lab"}
					{$model->lab}
				{/grid_cell}
				{grid_cell model=$model cell_num=6 field="osc"}
					{$model->osc}
				{/grid_cell}
				{grid_cell model=$model cell_num=7 field="ohd"}
					{$model->ohd}
				{/grid_cell}
				{grid_cell model=$model cell_num=8 field="effect_on_stock"}
					{$model->effect_on_stock}
				{/grid_cell}
				{grid_cell model=$model cell_num=9 field="lastupdated"}
					{$model->lastupdated}
				{/grid_cell}
				{grid_cell model=$model cell_num=10 field="alteredby"}
					{$model->alteredby}
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