{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.10 $ *}
{content_wrapper}
	{advanced_search}
	<p><strong>Transaction Details</strong></p>
	{input type="hidden" attribute="id" value=$id}
	{paging}
	{data_table}
		{heading_row}
			{heading_cell field="despatch_number" class='right'}
				Despatch Number
			{/heading_cell}
			{heading_cell field="order_number" class='right'}
				Order Number
			{/heading_cell}
			{heading_cell field="customer" }
				Customer
			{/heading_cell}
			{heading_cell field="despatch_date" }
				Despatch Date
			{/heading_cell}
			{heading_cell field="despatch_qty" class='right'}
				Despatch Qty
			{/heading_cell}
			{heading_cell field="uom_name" }
				UoM
			{/heading_cell}
			{heading_cell field="item_description"}
				Item Description
			{/heading_cell}
			{heading_cell field="status" }
				Status
			{/heading_cell}
			{heading_cell field="invoice_number" class='right'}
				Invoice Number
			{/heading_cell}
			{heading_cell field="none"}
				&nbsp;
			{/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$sodespatchlines}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=1 field="despatch_number" class='numeric'}
					{$model->despatch_number}
				{/grid_cell}
				<td class='numeric'>
					<a href="/?module=sales_order&controller=sorders&action=view&id={$model->order_id}">
						{$model->order_number}
					</a>
				</td>
				{grid_cell model=$model cell_num=4 field="customer"}
					{$model->customer}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="despatch_date"}
					{$model->getFormatted('despatch_date')}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="despatch_qty" class='numeric'}
					{$model->despatch_qty}
				{/grid_cell}
				{grid_cell model=$model cell_num=7 field="uom_name"}
					{$model->uom_name}
				{/grid_cell}
				{if $model->stitem }
					{grid_cell model=$model cell_num=8 field="stitem"}
						{$model->stitem}
					{/grid_cell}
				{else}
					{grid_cell model=$model cell_num=8 field="description"}
					{$model->description}
					{/grid_cell}
				{/if}
				{grid_cell model=$model cell_num=9 field="status"}
					{$model->getFormatted('status')}
				{/grid_cell}
				<td class='numeric'>
					<a href="/?module=sales_invoicing&controller=sinvoices&action=view&id={$model->invoice_id}">
						{$model->invoice_number}
					</a>
				</td>
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