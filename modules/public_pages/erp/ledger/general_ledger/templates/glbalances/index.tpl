{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.20 $ *}
{content_wrapper}
	{advanced_search}
	{paging}
	{assign var=templatemodel value=$glbalances->getModel()}
	{assign var=fields value=$glbalances->getHeadings()}
	{assign var=colspan value=0}
	{foreach item=tag key=fieldname from=$fields}
		{if $fieldname=='debit'}
			{assign var=colspan value=$tag@index}
		{/if}
	{/foreach}
	{data_table}
		{include file='elements/datatable_heading.tpl' collection=$glbalances}
		{foreach name=datagrid item=model from=$glbalances}
			{grid_row model=$model}
				{foreach name=gridrow item=tag key=fieldname from=$fields}
					{grid_cell field=$fieldname cell_num=2 model=$model collection=$collection}
						{$model->getFormatted($fieldname)}
					{/grid_cell}
				{/foreach}
			{/grid_row}
		{foreachelse}
			<tr>
				<td colspan="0">No matching records found!</td>
			</tr>
		{/foreach}
		{grid_row}
			<td colspan={$colspan-1}>
			</td>
			<td>
				<strong>Total for Page : {$page_total}</strong>
			</td>
			<td class='numeric'>
				<strong>{$page_debit_total}</strong>
			</td>
			<td class='numeric'>
				<strong>{$page_credit_total}</strong>
			</td>
		{/grid_row}
	{/data_table}
	<div style="clear: both;">&nbsp;</div>
{/content_wrapper}