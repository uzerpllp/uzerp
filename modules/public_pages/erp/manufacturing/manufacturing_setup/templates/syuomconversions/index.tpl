{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.6 $ *}
{content_wrapper}
	{paging}
	{data_table}
		{heading_row}
			{heading_cell field="from_uom_name" label="from_uom_name"}
				From UoM
			{/heading_cell}
			{heading_cell field="conversion_factor"}
				Conversion Factor
			{/heading_cell}
			{heading_cell field="to_uom_name" label="to_uom_name"}
				To UoM
			{/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$syuomconversions}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=1 field="from_uom_name"}
					One {$model->from_uom_name}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="conversion_factor"}
					contains {$model->conversion_factor}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="to_uom_name"}
					{$model->to_uom_name}
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