{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.14 $ *}
{content_wrapper}
	{advanced_search}
	{paging}
	{form controller=$self.controller action='batchprocess' notags=true}
		{data_table}
			{heading_row}
				{heading_cell field="customer"}
					Customer
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
				{heading_cell field="date_printed"}
					Date Printed
				{/heading_cell}
				{heading_cell field="print_count"}
					Print Count
				{/heading_cell}
				{heading_cell}
					Select?
				{/heading_cell}
			{/heading_row}
			{foreach name=datagrid item=model from=$sinvoices}
				{assign var=rowid value=$model->id}
				{grid_row model=$model data_row_id=$rowid}
					{grid_cell model=$model cell_num=2 field="customer"}
						{$model->customer}
					{/grid_cell}
					{grid_cell model=$model cell_num=1 field="invoice_number"}
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
					{grid_cell model=$model cell_num=7 field="date_printed"}
						{$model->date_printed}
					{/grid_cell}
					{grid_cell model=$model cell_num=8 field="print_count"}
						{$model->print_count}
					{/grid_cell}
					<td>
						{assign var=checked value=''}
						{if $selected_rows.$rowid!=''}
							{assign var=checked value='checked="checked"'}
						{/if}
						<input type='checkbox' id="SInvoices_selected{$rowid}" name='SInvoices[selected][]' value={$model->id} {$checked} />
						<input type="hidden" id="SInvoices_status{$rowid}" name='SInvoices[status][{$model->id}]' value={$model->status} />
					</td>
				{/grid_row}
			{foreachelse}
				<tr>
					<td colspan="0">No matching records found!</td>
				</tr>
			{/foreach}
		{/data_table}
			<dt><label for="printer">Printer</label>:</dt>
				<dd>
					<select name="printer">
				</dd>
				{html_options options=$printers selected=$default_printer}
			</select>
		<input type=hidden name='printtype' value='pdf'>
		<input type=hidden name='printaction' value='Print'>
		<table class='gridform'>
			<tr>
				<td>
					{submit value='Print Only' notags=true}
				</td>
				<td>
					{submit value='Post Only' notags=true}
				</td>
				<td>
					{submit value='Print and Post' notags=true}
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