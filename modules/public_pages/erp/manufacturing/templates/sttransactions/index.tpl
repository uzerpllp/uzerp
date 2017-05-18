{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.11 $ *}
{content_wrapper}
	{advanced_search}
	{paging}
	{data_table}
		{heading_row}
			{heading_cell field="created"}
				Date
			{/heading_cell}
			{heading_cell field="process_name"}
				Process
			{/heading_cell}
			{heading_cell field="process_ref"}
				Ref:
			{/heading_cell}
			{heading_cell field="flocation"}
				Location
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
			{heading_cell field="remarks"}
				Remarks
			{/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$sttransactions}
		{grid_row model=$model}
			{grid_cell model=$model cell_num=1 field="created"}
				{$model->created|date_format:'%d/%m/%Y %H:%M'}
			{/grid_cell}
			{grid_cell model=$model cell_num=2 field="process_name"}
				{$model->getFormatted('process_name')}
			{/grid_cell}
			{grid_cell model=$model cell_num=3 field="process_ref" no_escape=true}
				{assign var=fk value=$model->getFKvalue()}
				{link_to data=$fk.link value=$fk.value}
			{/grid_cell}
			{grid_cell model=$model cell_num=4 }
				{if ($model->qty>0)}
					From
				{else}
					To
				{/if}
					{$model->flocation}
				{if ($model->fbin!="-" && $model->fbin!="")}
					- {$model->fbin}
				{/if}
			{/grid_cell}
			{grid_cell model=$model cell_num=5 field="stitem"}
				{$model->stitem}
			{/grid_cell}
			{grid_cell model=$model cell_num=6 field="qty" class="numeric"}
				{$model->qty*-1}
			{/grid_cell}
			{grid_cell model=$model cell_num=7 field="uom_name"}
				{$model->getUoM()}
			{/grid_cell}
			{grid_cell model=$model cell_num=8 field="remarks"}
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