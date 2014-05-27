{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.9 $ *}
{content_wrapper}
	{advanced_search}
	{paging}
	{data_table}
		{heading_row}
			{heading_cell field="transfer_number"}
				Transfer Number
			{/heading_cell}
			{heading_cell field="due_transfer_date"}
				Due Transfer Date
			{/heading_cell}
			{if $action=='viewwhtransfers'}
				{heading_cell field="actual_transfer_date"}
					Actual Transfer Date
				{/heading_cell}
			{/if}
			{heading_cell field="from_location"}
				From Location
			{/heading_cell}
			{heading_cell field="tolocation"}
				To Location
			{/heading_cell}
			{heading_cell field="description"}
				Description
			{/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$whtransfers}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=1 field="transfer_number"}
					{$model->transfer_number}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="due_transfer_date"}
					{$model->due_transfer_date}
				{/grid_cell}
				{if $action=='viewwhtransfers'}
					{grid_cell model=$model cell_num=3 field="actual_transfer_date"}
						{$model->actual_transfer_date}
					{/grid_cell}
				{/if}
				{grid_cell model=$model cell_num=2 field="from_location"}
					{$model->from_location}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="to_location"}
					{$model->to_location}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="description"}
					{$model->description}
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