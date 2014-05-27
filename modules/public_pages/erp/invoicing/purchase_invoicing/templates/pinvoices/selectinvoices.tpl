{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.9 $ *}
{content_wrapper}
	{advanced_search}
	{paging}
	{form controller=$self.controller action='batchprocess' notags=true}
		{data_table}
			{heading_row}
				{heading_cell field="supplier"}
					Supplier
				{/heading_cell}
				{heading_cell field="invoice_number"}
					Invoice Number
				{/heading_cell}
				{heading_cell field="invoice_date"}
					Date
				{/heading_cell}
				{heading_cell field="status"}
					Status
				{/heading_cell}
				{heading_cell field="gross_value"}
					Gross Value
				{/heading_cell}
				{heading_cell field="currency"}
					Currency
				{/heading_cell}
				{heading_cell field="base_gross_value"}
					Base Gross Value
				{/heading_cell}
				{heading_cell}
					Select?
				{/heading_cell}
			{/heading_row}
			{foreach name=datagrid item=model from=$pinvoices}
				{grid_row model=$model}
					{grid_cell model=$model cell_num=1 field="supplier"}
						{$model->supplier}
					{/grid_cell}
					{grid_cell model=$model cell_num=2 field="invoice_number"}
						{$model->invoice_number}
					{/grid_cell}
					{grid_cell model=$model cell_num=3 field="invoice_date"}
						{$model->invoice_date}
					{/grid_cell}
					{grid_cell model=$model cell_num=4 field="status"}
						{$model->getFormatted('status')}
					{/grid_cell}
					{grid_cell model=$model cell_num=4 field="gross_value"}
						{$model->gross_value}
					{/grid_cell}
					{grid_cell model=$model cell_num=5 field="currency"}
						{$model->currency}
					{/grid_cell}
					{grid_cell model=$model cell_num=6 field="base_gross_value"}
						{$model->base_gross_value}
					{/grid_cell}
					<td>
						<input type='checkbox' name='PInvoices[selected][]' value={$model->id}>
						<input type="hidden" name='PInvoices[status][{$model->id}]' value={$model->status}>
					</td>
				{/grid_row}
			{foreachelse}
				<tr>
					<td colspan="0">No matching records found!</td>
				</tr>
			{/foreach}
		{/data_table}
		<table class='gridform'>
			<tr>
				<td>
					{submit value='Post' notags=true}
				</td>
				<td>
					{submit value='Cancel' name='cancel' another='false'}
				</td>
			</tr>
		</table>
	{/form}
	{paging}
	<div id="data_grid_footer" class="clearfix">
		{include file='elements/data_table_actions.tpl'}
	</div>
{/content_wrapper}