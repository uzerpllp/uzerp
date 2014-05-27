{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	{form controller="sodespatchlines" action="print_despatch_notes"}
		{data_table}
			{heading_row}
				{heading_cell field='despatch_number' class='right'}
					Despatch Note
				{/heading_cell}
				{heading_cell field='despatch_date'}
					Due Date
				{/heading_cell}
				{heading_cell field='customer'}
					Customer
				{/heading_cell}
				{heading_cell field=''}
					No.of Copies
				{/heading_cell}
				{heading_cell field=''}
					Print Despatch?
				{/heading_cell}
				</tr>
			{/heading_row}
			{foreach name=datagrid item=model from=$orders}
				{grid_row}
					{grid_cell cell_num=1 model=$model field='despatch_number' class='numeric'}
						{$model->despatch_number}
					{/grid_cell}
					{grid_cell cell_num=2 model=$model field='despatch_date'}
						{$model->getFormatted('despatch_date')}
					{/grid_cell}
					{grid_cell cell_num=3 model=$model field='customer' no_escape=true}
						{$model->customer}
					{/grid_cell}
					{grid_cell cell_num=4 model=$model field='print_copies' no_escape=true}
						{input type="text" class="numeric" attribute="print_copies" model=$model number=$model->despatch_number tags="none" label='' value='1'}
					{/grid_cell}
					{grid_cell model=$model cell_num=5 field="print_despatch" no_escape=true}
						{input type='checkbox' attribute="print_despatch" model=$model number=$model->despatch_number tags=none label='' value=$model->despatch_number}
						{input type='hidden' attribute='slmaster_id' model=$model number=$model->despatch_number}
					{/grid_cell}
				{/grid_row}
			{foreachelse}
				{grid_row}
					<td colspan="0">No matching records found!</td>
				{/grid_row}
			{/foreach}
		{/data_table}
		<dt><label for="printer">Printer</label>:</dt>
			<dd>
				<select name="print[printer]">
			</dd>
			{html_options options=$printers selected=$default_printer}
		</select>
		<input type=hidden name='print[printtype]' value='pdf'>
		<input type=hidden name='print[printaction]' value='Print'>
		{submit value='Print'}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}