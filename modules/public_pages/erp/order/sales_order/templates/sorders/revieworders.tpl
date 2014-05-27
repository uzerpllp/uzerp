{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.9 $ *}
{content_wrapper}
	{advanced_search}
	{data_table}
		{heading_row}
			{heading_cell field="order_number" class='right'}
				Order<br>Number
			{/heading_cell}
			{heading_cell field="customer"}
				Customer
			{/heading_cell}
			{heading_cell field="person"}
				Person
			{/heading_cell}
			{heading_cell field="order_date"}
				Order Date
			{/heading_cell}
			{heading_cell field="despatch_date"}
				Due Despatch<br>Date
			{/heading_cell}
			{heading_cell field="status"}
				Status
			{/heading_cell}
			{heading_cell }
				Comment
			{/heading_cell}
			{heading_cell }
				Order Lines
			{/heading_cell}
			{heading_cell }&nbsp{/heading_cell}
			{heading_cell }&nbsp{/heading_cell}
			{heading_cell }&nbsp{/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$sorders}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=1 field="order_number" class='numeric'}
					{$model->order_number}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="customer"}
					{$model->customer}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="person"}
					{$model->person}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="order_date"}
					{$model->getFormatted('order_date')}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="despatch_date"}
					{$model->getFormatted('despatch_date')}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="status"}
					{$model->getFormatted('status')}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="status"}
					{if $model->status!='Completed' && $model->status!='Cancelled'}
						{if $model->despatch_date<$today}
							Overdue
						{elseif $model->despatch_date==$today}
							Due Today
						{/if}
					{/if}
				{/grid_cell}
				{grid_cell model=$model cell_num=2}
					Description
				{/grid_cell}
				{grid_cell model=$model cell_num=2}
					Required
				{/grid_cell}
				{grid_cell model=$model cell_num=2}
					Delivered
				{/grid_cell}
				{grid_cell model=$model cell_num=2}
					Status
				{/grid_cell}
			{/grid_row}
			{foreach key=key item=submodel from=$model->lines}
				{grid_row model=$submodel}
					{grid_cell model=$submodel cell_num=2 no_escape=true}&nbsp{/grid_cell}
					{grid_cell model=$submodel cell_num=2 no_escape=true}&nbsp{/grid_cell}
					{grid_cell model=$submodel cell_num=2 no_escape=true}&nbsp{/grid_cell}
					{grid_cell model=$submodel cell_num=2 no_escape=true}&nbsp{/grid_cell}
					{grid_cell model=$submodel cell_num=2 no_escape=true}&nbsp{/grid_cell}
					{grid_cell model=$submodel cell_num=2 no_escape=true}&nbsp{/grid_cell}
					{grid_cell model=$submodel cell_num=2 no_escape=true}&nbsp{/grid_cell}
					{grid_cell model=$submodel cell_num=2 field="item_description"}
						{$submodel->item_description}
					{/grid_cell}
					{grid_cell model=$submodel cell_num=2 field="revised_qty"}
						{$submodel->revised_qty}
					{/grid_cell}
					{grid_cell model=$submodel cell_num=2 field="del_qty"}
						{$submodel->del_qty}
					{/grid_cell}
					{grid_cell model=$submodel cell_num=2 field="status"}
						{$submodel->getFormatted('status')}
					{/grid_cell}
				{/grid_row}
			{/foreach}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=2}{/grid_cell}
				{grid_cell model=$model cell_num=2}{/grid_cell}
				{grid_cell model=$model cell_num=2}{/grid_cell}
				{grid_cell model=$model cell_num=2}{/grid_cell}
				{grid_cell model=$model cell_num=2}{/grid_cell}
				{grid_cell model=$model cell_num=2}{/grid_cell}
				{grid_cell model=$model cell_num=2}{/grid_cell}
				{grid_cell model=$model cell_num=2}{/grid_cell}
				{grid_cell model=$model cell_num=2}{/grid_cell}
				{grid_cell model=$model cell_num=2}{/grid_cell}
			{/grid_row}
		{foreachelse}
			<tr>
				<td colspan="0">No matching records found!</td>
			</tr>
		{/foreach}
	{/data_table}
	{paging}
{/content_wrapper}