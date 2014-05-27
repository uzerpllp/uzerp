{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.9 $ *}
{content_wrapper}
	<input type="hidden" id="alternate_print_action" value="printtrialbalance" />
	{advanced_search}
	{paging}
	{data_table}
		{heading_row}
			{heading_cell data_column="account" field="account"}
				Account
			{/heading_cell}
			{heading_cell data_column="centre" field="centre"}
				Centre
			{/heading_cell}
			{heading_cell data_column="period" field="period"}
				Period
			{/heading_cell}
			{heading_cell data_column="month_actual" class="right"}
				Month Actual
			{/heading_cell}
			{heading_cell data_column="month_budget" class="right"}
				Month Budget
			{/heading_cell}
			{heading_cell data_column="month_variance" class="right"}
				Month Variance
			{/heading_cell}
			{heading_cell data_column="ytd_actual" class="right"}
				YTD Actual
			{/heading_cell}
			{heading_cell data_column="ytd_budget" class="right"}
				YTD Budget
			{/heading_cell}
			{heading_cell data_column="ytd_variance" class="right"}
				YTD Variance
			{/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$collection}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=2 field="account"}
					{$model->account}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="centre"}
					{$model->centre}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="periods"}
					{$period}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="value"}
					{assign var='varCurrent' value=$model->getCurrent($glperiods_id)}
					{$varCurrent|string_format:'%0.2f'}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="value"}
					{assign var='varCurrentBudget' value=$model->getCurrentBudget($glperiods_id)}
					{$varCurrentBudget|string_format:'%0.2f'}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="value"}
					{$varCurrentBudget-$varCurrent|string_format:'%0.2f'}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="value"}
					{assign var='varYTD' value=$model->value}
					{$varYTD|string_format:'%0.2f'}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="value"}
					{assign var='varYTDBudget' value=$model->getYTDBudget($glperiods_id)}
					{$varYTDBudget|string_format:'%0.2f'}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="value"}
					{$varYTDBudget-$varYTD|string_format:'%0.2f'}
				{/grid_cell} 
			{/grid_row}
		{foreachelse}
			<tr>
				<td colspan="0">No matching records found!</td>
			</tr>
		{/foreach}
		{if $cur_page==$num_pages && $row_count > 0}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=2 field="account"}{/grid_cell}
				{grid_cell model=$model cell_num=2 field="centre"}{/grid_cell}
				{grid_cell model=$model cell_num=2 field="periods"}
					Totals
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="value"}
					{$ytd|string_format:'%0.2f'}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="value"}{/grid_cell}
				{grid_cell model=$model cell_num=2 field="value"}{/grid_cell}
				{grid_cell model=$model cell_num=2 field="value"}
					{$current|string_format:'%0.2f'}
				{/grid_cell}
			{/grid_row}
		{/if}
	{/data_table}
	<div id="data_grid_footer" class="clearfix">
		{paging}
		{include file='elements/data_table_actions.tpl'}
	</div>
{/content_wrapper}