{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.6 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$whtransfer}
			{assign var=whtransfer_id value=$whtransfer->id}
			<dt class="heading">
				{$whtransfer->action_name}
			</dt>
			{view_data attribute="transfer_number"}
			{view_data attribute="due_transfer_date"}
			{view_data attribute="status"}
			{view_data attribute="actual_transfer_date"}
			{view_data attribute="description"}
			<dl id="view_data_left">
				<dt class="heading">
					Transfer From
				</dt>
				{view_data label='Store' value=$from_store}
				{view_data label='Location' attribute="from_location"}
			</dl>
			<dl id="view_data_left">
				<dt class="heading">
					To
				</dt>
				{view_data label='Store' value=$to_store}
				{view_data label='Location' attribute="to_location"}
			</dl>
		{/with}
	</div>
	{paging}
	{data_table}
		{heading_row}
			{heading_cell field="stitem" _id=$whtransfer_id}
				Item
			{/heading_cell}
			{heading_cell field="transfer_qty" _id=$whtransfer_id}
				Transfer Qty
			{/heading_cell}
			{heading_cell field="uom_name" _id=$whtransfer_id}
				UoM
			{/heading_cell}
			{heading_cell field="remarks" _id=$whtransfer_id}
				Remarks
			{/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$whtransferlines}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=1 field="stitem"}
					{$model->stitem}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="transfer_qty"}
					{$model->transfer_qty}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="uom_name"}
					{$model->uom_name}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="remarks"}
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