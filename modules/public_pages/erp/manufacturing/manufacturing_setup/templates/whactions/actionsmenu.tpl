{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.8 $ *}
{content_wrapper}
	{advanced_search}
	{paging}
	{data_table}
		{heading_row}
			{heading_cell field="name"}
				Name
			{/heading_cell}
			{heading_cell field="description"}
				Description
			{/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$whactions}
			{if $model->rules->count()>0}
				{grid_row model=$model}
					{grid_cell model=$model cell_num=1 field="action_name"}
						{$model->action_name}
					{/grid_cell}
					{grid_cell model=$model cell_num=2 field="description"}
						{$model->description}
					{/grid_cell}
				{/grid_row}
			{/if}
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