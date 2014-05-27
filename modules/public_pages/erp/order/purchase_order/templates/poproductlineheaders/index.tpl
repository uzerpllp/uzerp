{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.2 $ *}
{content_wrapper}
	{advanced_search}
	{paging}
	{data_table}
		{heading_row}
			{heading_cell field="description"}
				Description
			{/heading_cell}
			{heading_cell field="gl_account"}
				GL Account
			{/heading_cell}
			{heading_cell field="gl_centre"}
				GL Centre
			{/heading_cell}
			{heading_cell field="stitem"}
				Stock Item
			{/heading_cell}
			{heading_cell field="product_group"}
				Product Group
			{/heading_cell}
			{heading_cell field="uom_name"}
				UoM Name
			{/heading_cell}
			{heading_cell field="start_date"}
				Start Date
			{/heading_cell}
			{heading_cell field="end_date"}
				End Date
			{/heading_cell}
			{heading_cell field="latest_cost"}
				Latest Cost
			{/heading_cell}
			{heading_cell field="std_cost"}
				Std Cost
			{/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$poproductlineheaders}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=1 field="description"}
					{$model->description}
				{/grid_cell}
				{grid_cell model=$model cell_num=5 field="gl_account"}
					{$model->gl_account}
				{/grid_cell}
				{grid_cell model=$model cell_num=6 field="gl_centre"}
					{$model->gl_centre}
				{/grid_cell}
				{grid_cell model=$model cell_num=7 field="stitem"}
					{$model->stitem}
				{/grid_cell}
				{grid_cell model=$model cell_num=7 field="product_group"}
					{$model->product_group}
				{/grid_cell}
				{grid_cell model=$model cell_num=8 field="uom_name"}
					{$model->uom_name}
				{/grid_cell}
				{grid_cell model=$model cell_num=9 field="start_date"}
					{$model->getFormatted('start_date')}
				{/grid_cell}
				{grid_cell model=$model cell_num=9 field="end_date"}
					{$model->getFormatted('end_date')}
				{/grid_cell}
				{grid_cell model=$model cell_num=10 field="latest_cost"}
					{$model->latest_cost}
				{/grid_cell}
				{grid_cell model=$model cell_num=11 field="std_cost"}
					{$model->std_cost}
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