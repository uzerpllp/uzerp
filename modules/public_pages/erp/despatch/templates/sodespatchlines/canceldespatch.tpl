{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.10 $ *}
{content_wrapper}
	{form controller="sodespatchlines" action="cancel_despatchnote"}
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
					Cancel Despatch note?
				{/heading_cell}
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
					{grid_cell model=$model cell_num=4 field="cancel_despatch" no_escape=true}
						{input type='checkbox' attribute="cancel_despatch" model=$model number=$model->despatch_number tags=none label='' value=$model->despatch_number}
						{input type='hidden' attribute='slmaster_id' model=$model number=$model->despatch_number}
					{/grid_cell}
				{/grid_row}
			{foreachelse}
				<tr>
					<td colspan="0">No matching records found!</td>
				</tr>
			{/foreach}
		{/data_table}
		{paging}
		{submit}
	{/form}
{/content_wrapper}