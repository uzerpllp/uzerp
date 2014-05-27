{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.21 $ *}
{content_wrapper}
	{advanced_search}
	{paging}
	{assign var=templatemodel value=$gltransactions->getModel()}
	{assign var=fields value=$gltransactions->getHeadings()}
	{assign var=colspan value=0}
	{foreach item=tag key=fieldname from=$fields}
		{if $fieldname=='debit'}
			{assign var=colspan value=$tag@index}
		{/if}
	{/foreach}
	{data_table}
		{include file='elements/datatable_heading.tpl' collection=$gltransactions}
		{foreach name=datagrid item=model from=$gltransactions}
			{grid_row model=$model}
				{foreach name=gridrow item=tag key=fieldname from=$fields}
					{if $fieldname=='docref'}
						<td>
							{if $model->source=='P' && $model->type=='A'}
								{link_to module='purchase_order' controller='porders' action='view' order_number=$model->docref value=$model->docref}
							{elseif $model->source=='P' && ($model->type=='I' || $model->type=='C' || $model->type=='SD')}
								{link_to module='purchase_invoicing' controller='pinvoices' action='view' invoice_number=$model->docref value=$model->docref}
							{elseif $model->source=='S' && ($model->type=='I' || $model->type=='C' || $model->type=='SD')}
								{link_to module='sales_invoicing' controller='sinvoices' action='view' invoice_number=$model->docref value=$model->docref}
							{elseif $model->source=='A'}
								{assign var=asset_source value=substr($model->docref,0,1)}
								{assign var=asset_id value=substr($model->docref,1)}
								{if $asset_source=='A'}
									{link_to module='asset_register' controller='assets' action='index' id=$asset_id value=$model->docref}
								{else}
									{link_to module='asset_register' controller='artransactions' action='index' id=$asset_id value=$model->docref}
								{/if}
							{elseif $model->type=='P' || $model->type=='R'}
								{link_to module='cashbook' controller='cbtransactions' action='view' reference=$model->docref value=$model->docref}
							{else}
								{$model->docref}
							{/if}
						</td>
					{else}
						{grid_cell field=$fieldname cell_num=$smarty.foreach.gridrow.iteration model=$model collection=$collection}
							{$model->getFormatted($fieldname)}
						{/grid_cell}
					{/if}
				{/foreach}
				{if $allow_delete}
					<td>
						{include file='elements/delete_row.tpl'}
					</td>
				{/if}
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
				<strong>Total for Page<br>{$page_total}</strong>
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