{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.7 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{with model=$bin}
				{view_data model=$transaction label="Store" value=$whstore}
				{view_data attribute='whlocation' label='location'}
				{view_data attribute='bin_code'}
				{view_data attribute='description'}
			{/with}
		</dl>
	</div>
	{advanced_search}
	{paging}
	{data_table}
		{heading_row}
			{heading_cell field="stitem"}
				Stock Item
			{/heading_cell}
			{heading_cell field="created"}
				Date
			{/heading_cell}
			{heading_cell field="flocation"}
				From/To
			{/heading_cell}
			{heading_cell field="qty"}
				Qty
			{/heading_cell}
			{heading_cell field="balance"}
				Balance
			{/heading_cell}
			 {heading_cell field="uom_name"}
				UoM
			{/heading_cell}
			{heading_cell field="remarks"}
				Remarks
			{/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$sttransactions}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=1 field="stitem"}
					{$model->stitem}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="created"}
					{$model->created|date_format:'%d/%m/%Y %H:%M'}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 }
					{if ($model->qty>0)}
						From
					{else}
						To
					{/if}
				{$model->flocation - $model->fbin}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="qty"}
					{$model->qty}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="balance"}
					{$model->balance}
				{/grid_cell}
				{grid_cell model=$model cell_num=5 field="uom_name"}
					{$model->getUoM()}
				{/grid_cell}
				{grid_cell model=$model cell_num=6 field="remarks"}
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
	<div id="data_grid_footer" class="clearfix">
		{include file='elements/data_table_actions.tpl'}
	</div>
{/content_wrapper}