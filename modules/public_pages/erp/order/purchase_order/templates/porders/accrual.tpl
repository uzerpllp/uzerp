{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.7 $ *}
{content_wrapper}
	{advanced_search}
	<p><strong>Transaction Details</strong></p>
	{input type="hidden" attribute="id" value=$id}
	{paging}
	{form controller="porders" action="saveAccrual"}
		{assign var=fields value=$poreceivedlines->getHeadings()}
		{data_table}
			{heading_row}
				{foreach name=headings item=heading key=fieldname from=$fields}
					{heading_cell field=$fieldname model=$poreceivedlines->getModel()}
						{$heading}
					{/heading_cell}
				{/foreach}
				{heading_cell field="invoice_number"}
					Accrual?
				{/heading_cell}
			{/heading_row}
			{foreach name=datagrid item=model from=$poreceivedlines}
				{assign var=rowid value=$model->id}
				{grid_row model=$model data_rowid=$rowid}
					{foreach name=gridrow item=tag key=fieldname from=$fields}
						{grid_cell field=$fieldname cell_num=$smarty.foreach.gridrow.iteration model=$model collection=$collection}
							{if ($model->isEnum($fieldname))}
								{$model->getFormatted($fieldname)}
							{else}
								{$model->getFormatted($fieldname)}
							{/if}
						{/grid_cell}
					{/foreach}
					{grid_cell model=$model cell_num=9 field="accrual" no_escape=true}
						{input type='checkbox' attribute="accrual" model=$model rowid=$rowid rel=$model->id number=$model->id tags=none label='' value=$accrual.data.$rowid}
					{/grid_cell}
				{/grid_row}
			{foreachelse}
				<tr>
					<td colspan="0">No matching records found!</td>
				</tr>
			{/foreach}
			<tr>
				{assign var=colcount value=count($fields)}
				<td colspan="{$colcount}" align="right">
					Number Selected
				</td>
				<td>
					<strong>
					<span id="selected_count">{$accrual.count}</span>
					</strong>
				</td>
			</tr>
		{/data_table}
		{submit}
	{/form}
	{form controller=$controller action=$action}
		{submit tags=$tags name='selectAll' value=$accrual.text}
	{/form}
	{include file='elements/cancelForm.tpl'}
	<div id="data_grid_footer" class="clearfix">
		{paging}
		{include file='elements/data_table_actions.tpl'}
	</div>
{/content_wrapper}