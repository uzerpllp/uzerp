{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.10 $ *}
{content_wrapper}
	{advanced_search}
	<div id="view_page" class="clearfix">
		<h3>Transactions</h3>
		{paging}
		{data_table}
			{heading_row}
				{heading_cell field="created"}
					Date
				{/heading_cell}
				{heading_cell field="fbin"}
					From
				{/heading_cell}
				{heading_cell field="whbin"}
					To
				{/heading_cell}
				{heading_cell field="qty"}
					Qty
				{/heading_cell}
				{heading_cell field="remarks"}
					Remarks
				{/heading_cell}
			{/heading_row}
			{foreach name=datagrid item=model from=$sttransactions}
				{grid_row model=$model}
					{grid_cell model=$model cell_num=2 field="created"}
						{$model->created|date_format:'%d/%m/%Y %H:%M'}
					{/grid_cell}
					{grid_cell model=$model cell_num=2 }
						{$model->flocation}{if $model->fbin<>''} - {$model->fbin}{/if}
					{/grid_cell}
					{grid_cell model=$model cell_num=2 }
						{$model->whlocation}{if $model->whbin<>'-'}	- {$model->whbin}{/if}
					{/grid_cell}
					{grid_cell model=$model cell_num=4 field="qty"}
						{$model->qty}
					{/grid_cell}
					{grid_cell model=$model cell_num=4 field="remarks"}
						{$model->remarks}
					{/grid_cell}
				{/grid_row}
			{foreachelse}
				<tr>
					<td colspan="0">No matching records found!</td>
				</tr>
			{/foreach}
		{/data_table}
		{paging}
	</div>
{/content_wrapper}