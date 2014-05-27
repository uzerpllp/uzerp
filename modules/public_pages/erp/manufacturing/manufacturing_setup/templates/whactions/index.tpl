{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.9 $ *}
{content_wrapper}
	{advanced_search}
	{paging}
	{data_table}
		{heading_row}
			{heading_cell field="position"}
				Position
			{/heading_cell}
			{heading_cell field="action_name"}
				Name
			{/heading_cell}
			{heading_cell field="description"}
				Description
			{/heading_cell}
			{heading_cell field="type"}
				Type
			{/heading_cell}
			{heading_cell field="description"}
				Defined Rules
			{/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$whactions}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=2 field="position"}
					{$model->position}
				{/grid_cell}
				{grid_cell model=$model cell_num=1 field="action_name"}
					{$model->action_name}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="description"}
					{$model->description}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="description"}
					{$model->getFormatted('type')}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="description"}
					{assign var=rules value=$model->rules|count}
					{$rules}
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