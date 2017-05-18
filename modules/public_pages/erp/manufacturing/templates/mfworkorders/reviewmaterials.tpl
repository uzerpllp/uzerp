{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.9 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{view_data model=$transaction attribute="wo_number" label="Order No."}
			{view_data model=$transaction attribute="stitem" label="Stock Item"}
			{view_data model=$transaction attribute="required_by"}
			{view_data model=$transaction attribute="order_qty"}
			{view_data model=$transaction attribute="made_qty"}
		</dl>
	</div>
	{data_table}
		{heading_row}
			{heading_cell field="line_no" class='right'}
				Line No.
			{/heading_cell}
			{heading_cell field="ststructure"}
				Stock Item
			{/heading_cell}
			{heading_cell field="uom"}
				UoM
			{/heading_cell}
			<th class='right'>
				Obsolete
			</th>
			<th class='right'>
				Std Qty Required
			</th>
			<th class='right'>
				In Stock
			</th>
			<th class='right'>
				Reserved Qty
			</th>
			<th class='right'>
				Std Usage
			</th>
			<th class='right'>
				Actual Usage
			</th>
			<th class='right'>
				Variance
			</th>
		{/heading_row}
		{assign var='adjusted' value='100'}
		{foreach name=datagrid item=model from=$mfwostructures}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=$cell field="line_no" class='numeric'}
					{$model->line_no}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="ststructure"}
					{$model->ststructure}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="uom"}
					{$model->uom}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="obsolete_date"}
					{$model->ststr_item->obsolete_date}
				{/grid_cell}
				{assign var=denominator value=$adjusted-$model->waste_pc}
				{grid_cell model=$model cell_num=4 field="order_qty" class='numeric'}
					{$model->requiredQty()}
				{/grid_cell}
				{grid_cell model=$model cell_num=5 class='numeric'}
					{$model->getCurrentBalance()}
				{/grid_cell}
				{grid_cell model=$model cell_num=5 class='numeric'}
					{$model->getTransactionBalance(TRUE)}
				{/grid_cell}
				{grid_cell model=$model cell_num=5 class='numeric'}
					{assign var='issuedTD' value=$model->madeQty()}
					{$issuedTD}
				{/grid_cell}
				{grid_cell model=$model cell_num=5 class='numeric'}
					{assign var='usedTD' value=$model->getTransactionBalance(FALSE)}
					{$usedTD}
				{/grid_cell}
				{grid_cell model=$model cell_num=5 class='numeric'}
					{($issuedTD-$usedTD)|round:$model->ststr_item->qty_decimals}
				{/grid_cell}
			{/grid_row}
		{foreachelse}
			<tr><td colspan="0">No matching records found!</td></tr>
		{/foreach}
	{/data_table}
{/content_wrapper}