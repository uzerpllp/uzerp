{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.10 $ *}
{content_wrapper}
	{form controller="sodespatchlines" action="confirm_despatch"}
		{data_table}
			{heading_row}
				{heading_cell field='despatch_number'}
					Despatch Note
				{/heading_cell}
				{heading_cell field='despatch_date'}
					Due Date
				{/heading_cell}
				{heading_cell field='order_number'}
					Order
				{/heading_cell}
				{heading_cell field='customer'}
					Customer
				{/heading_cell}
				{heading_cell field=''}
					Confirm Despatch?
				{/heading_cell}
			{/heading_row}
			{foreach name=datagrid item=model from=$orders}
				{grid_row}
					{grid_cell cell_num=1 model=$model field='despatch_number'}
						{$model->despatch_number}
					{/grid_cell}
					{grid_cell cell_num=9 model=$model field='despatch_date'}
						{$model->getFormatted('despatch_date')}
					{/grid_cell}
					{grid_cell cell_num=1 model=$model field='order_number'}
						{$model->order_number}
					{/grid_cell}
					{grid_cell cell_num=9 model=$model field='customer' no_escape=true}
						{$model->customer}
					{/grid_cell}
					{grid_cell model=$model cell_num=9 field="confirm_despatch" no_escape=true}
						{input type='checkbox' attribute="confirm_despatch" model=$model number=$model->despatch_number tags=none label='' value=$model->despatch_number}
						{input type='hidden' attribute='slmaster_id' model=$model number=$model->despatch_number}
					{/grid_cell}
				{/grid_row}
			{foreachelse}
				{grid_row}
					<td colspan="0">No matching records found!</td>
				{/grid_row}
			{/foreach}
		{/data_table}
		{paging}
		{submit}
	{/form}
{/content_wrapper}