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
			{heading_cell field="created"}
				Date
			{/heading_cell}
			{heading_cell field="whlocation"}
				From
			{/heading_cell}
			{heading_cell field="whlocation"}
				To
			{/heading_cell}
			{heading_cell field="stitem"}
				Stock Item
			{/heading_cell}
			{heading_cell field="qty" class="right"}
				Qty Moved
			{/heading_cell}
			{heading_cell field="uom"}
				UoM
			{/heading_cell}
			{heading_cell field="balance" class="right"}
				Balance
			{/heading_cell}
			{heading_cell field="remarks"}
				Remarks
			{/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$sttransactions}
		{grid_row model=$model}
			{grid_cell model=$model cell_num=1 field="created"}
				{$model->created|date_format:'%d/%m/%Y %H:%M'}
			{/grid_cell}
			{if $model->qty<0}
				{grid_cell model=$model cell_num=3 field='whlocation'}
					{$model->whlocation}
					{if ($model->whbin!="-" && $model->whbin!="")}
	 					- {$model->whbin}
					{/if}
				{/grid_cell}
				{grid_cell model=$model cell_num=3  field='flocation' no_escape='true'}
					{link_to module=$module controller='whlocations' action='view' id=$model->flocation_id value=$model->flocation}
					{if ($model->fbin!="-" && $model->fbin!="")}
						- {$model->fbin}
					{/if}
				{/grid_cell}
			{else}
				{grid_cell model=$model cell_num=3 field='flocation' no_escape='true'}
					{link_to module=$module controller='whlocations' action='view' id=$model->flocation_id value=$model->flocation}
					{if ($model->fbin!="-" && $model->fbin!="")}
						- {$model->fbin}
					{/if}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field='whlocation'}
					{$model->whlocation}
					{if ($model->whbin!="-" && $model->whbin!="")}
	 					- {$model->whbin}
					{/if}
				{/grid_cell}
			{/if}
			{grid_cell model=$model cell_num=3 field="stitem"}
				{$model->stitem}
			{/grid_cell}
			{grid_cell model=$model cell_num=4 field="qty" class="numeric"}
				{if $model->qty<0}
					{$model->qty*-1}
				{else}
					{$model->qty}
				{/if}
			{/grid_cell}
			{grid_cell model=$model cell_num=5 field="uom_name"}
				{$model->getUoM()}
			{/grid_cell}
			{grid_cell model=$model cell_num=3 field="balance"}
				{$model->balance}
			{/grid_cell}
			{grid_cell model=$model cell_num=9 field="remarks"}
				{$model->remarks}
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