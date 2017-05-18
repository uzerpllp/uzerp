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
			{heading_cell field="account"}
				Account
			{/heading_cell}
			{heading_cell field="description"}
				Description
			{/heading_cell}
			{heading_cell field="actype"}
				Account Type
			{/heading_cell}
			{heading_cell field="control"}
				Control Account
			{/heading_cell}
			{heading_cell field="analysis"}
				Analysis Code
			{/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$glaccounts}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=1 field="account"}
						{$model->account}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="description"}
						{$model->description}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="actype"}
						{$model->getFormatted('actype')}
				{/grid_cell}
				{grid_cell model=$model cell_num=4 field="control"}
						{$model->control}
				{/grid_cell}
				{grid_cell model=$model cell_num=5 field="analysis"}
						{$model->analysis}
				{/grid_cell}
			{/grid_row}
		{foreachelse}
			<tr>	
				<td colspan="0">No matching records found!</td>
			</tr>
		{/foreach}
	{/data_table}
	<div id="data_grid_footer" class="clearfix">
		{paging}
		{include file='elements/data_table_actions.tpl'}
	</div>
{/content_wrapper}