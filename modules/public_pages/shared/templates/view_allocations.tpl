{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.6 $ *}
{content_wrapper title=$collection->title}
	{advanced_search}
	{paging}
	{assign var=templatemodel value=$collection->getModel()}
	{assign var=fields value=$collection->getHeadings()}
	{data_table}
		<thead>
			<tr>
				{foreach name=headings item=heading key=fieldname from=$fields}
					{heading_cell field=$fieldname model=$collection->getModel()}
						{$heading}
					{/heading_cell}
				{/foreach}
				<th>&nbsp;</th>
			</tr>
		</thead>
		{foreach name=datagrid item=model from=$collection}
			{grid_row model=$model}
				{foreach name=gridrow item=tag key=fieldname from=$fields}
					{if $fieldname=='our_reference'}
						<td>
							{if $model->transaction_type=='P' || $model->transaction_type=='R'}
								{link_to module="cashbook" controller="cbtransactions" action="view" reference=$model->$fieldname value=$model->$fieldname}
							{elseif $model->transaction_type=='I' || $model->transaction_type=='C' || $model->transaction_type=='SD'}
								{link_to module=$invoice_module controller=$invoice_controller action="view" invoice_number=$model->$fieldname value=$model->$fieldname}
							{/if}
						</td>
					{else}
						{grid_cell field=$fieldname cell_num=$smarty.foreach.gridrow.iteration model=$model collection=$collection}
							{if ($model->isEnum($fieldname))}
								{$model->getFormatted($fieldname)}
							{else}
								{$model->getFormatted($fieldname)}
							{/if}
						{/grid_cell}
					{/if}
				{/foreach}
				<td></td>
			{/grid_row}
		{foreachelse}
			<tr>
				<td colspan="0">No matching records found!</td>
			</tr>
		{/foreach}
	{/data_table}
	{paging}
	<div style="clear: both;">&nbsp;</div>
{/content_wrapper}