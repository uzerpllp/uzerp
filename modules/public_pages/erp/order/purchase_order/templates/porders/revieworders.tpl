{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.8 $ *}	
{content_wrapper}
	{advanced_search}
	{data_table}
		{heading_row}
			{heading_cell field="order_number"}
				Order<br>Number
			{/heading_cell}
			{heading_cell field="supplier"}
				Supplier
			{/heading_cell}
			{heading_cell field="order_date"}
				Order Date
			{/heading_cell}
			{heading_cell field="due_date"}
				Due Date
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
			{heading_cell }{/heading_cell}
			{heading_cell }{/heading_cell}
			{heading_cell }{/heading_cell}
		{/heading_row}
		{paging}
		{foreach name=datagrid item=model from=$porders}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=1 field="order_number"}
					{$model->order_number}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="supplier"}
					{$model->supplier}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="order_date"}
					{$model->order_date}
				{/grid_cell}
				{grid_cell model=$model cell_num=4 field="due_date"}
					{$model->due_date}
				{/grid_cell}
				{grid_cell model=$model cell_num=5 field="status"}
					{$model->getFormatted('status')}
				{/grid_cell}
				{grid_cell model=$model cell_num=6 field="status"}
					{if $model->status!='Completed' && $model->status!='Cancelled'}
						{if $model->due_date<$today}
							Overdue
						{elseif $model->due_date==$today}
							Due Today
						{/if}
					{/if}
				{/grid_cell}
				{grid_cell model=$model cell_num=7}
					Description
				{/grid_cell}
				{grid_cell model=$model cell_num=8 class="numeric"}
					Required
				{/grid_cell}
				{grid_cell model=$model cell_num=9 class="numeric"}
					Delivered
				{/grid_cell}
				{grid_cell model=$model cell_num=10}
					Status
				{/grid_cell}
			{/grid_row}
			{foreach key=key item=submodel from=$model->lines}
				{grid_row model=$submodel}
					{grid_cell model=$submodel cell_num=2} {/grid_cell}
					{grid_cell model=$submodel cell_num=2} {/grid_cell}
					{grid_cell model=$submodel cell_num=3} {/grid_cell}
					{grid_cell model=$submodel cell_num=4} {/grid_cell}
					{grid_cell model=$submodel cell_num=5} {/grid_cell}
					{grid_cell model=$submodel cell_num=6} {/grid_cell}
					{grid_cell model=$submodel cell_num=7 field="item_description"}
						{$submodel->item_description}
					{/grid_cell}
					{grid_cell model=$submodel cell_num=8 field="revised_qty"}
						{$submodel->revised_qty}
					{/grid_cell}
					{grid_cell model=$submodel cell_num=9 field="del_qty"}
						{$submodel->del_qty}
					{/grid_cell}
					{grid_cell model=$submodel cell_num=10 field="status"}
						{$submodel->getFormatted('status')}
					{/grid_cell}
				{/grid_row}
			{/foreach}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=2}{/grid_cell}
				{grid_cell model=$model cell_num=2}{/grid_cell}
				{grid_cell model=$model cell_num=3}{/grid_cell}
				{grid_cell model=$model cell_num=4}{/grid_cell}
				{grid_cell model=$model cell_num=5}{/grid_cell}
				{grid_cell model=$model cell_num=6}{/grid_cell}
				{grid_cell model=$model cell_num=7}{/grid_cell}
				{grid_cell model=$model cell_num=8}{/grid_cell}
				{grid_cell model=$model cell_num=9}{/grid_cell}
				{grid_cell model=$model cell_num=10}{/grid_cell}
			{/grid_row}
		{foreachelse}
			<tr>
				<td colspan="0">No matching records found!</td>
			</tr>
		{/foreach}
	{/data_table}
{/content_wrapper}