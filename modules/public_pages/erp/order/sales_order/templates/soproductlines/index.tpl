{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.13 $ *}
{content_wrapper}
	{advanced_search}
	{paging}
	{data_table}
		{heading_row}
			{heading_cell field="description"}
				Description
			{/heading_cell}
			{heading_cell field="customer"}
				Customer
			{/heading_cell}
			{heading_cell field="customer_product_code"}
				Customer Product Code
			{/heading_cell}
			{heading_cell field="so_price_type"}
				SO Price Type
			{/heading_cell}
			{heading_cell field="glaccount"}
				GL Account
			{/heading_cell}
			{heading_cell field="glcentre"}
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
			{heading_cell field="currency"}
				Currency
			{/heading_cell}
			{heading_cell field="price" class='right'}
				Price
			{/heading_cell}
			{heading_cell field="price" class='right'}
				Product Group Discount
			{/heading_cell}
			{heading_cell field="price" class='right'}
				Net Price
			{/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$soproductlines}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=1 field="description"}
					{$model->description}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="customer"}
					{$model->customer}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="customer_product_code"}
					{$model->customer_product_code}
				{/grid_cell}
				{grid_cell model=$model cell_num=4 field="so_price_type"}
					{$model->so_price_type}
				{/grid_cell}
				{grid_cell model=$model cell_num=5 field="glaccount"}
					{$model->glaccount}
				{/grid_cell}
				{grid_cell model=$model cell_num=6 field="glcentre"}
					{$model->glcentre}
				{/grid_cell}
				{grid_cell model=$model cell_num=7 field="stitem"}
					{$model->stitem}
				{/grid_cell}
				{grid_cell model=$model cell_num=7 field="stproductgroup"}
					{$model->stproductgroup}
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
				{grid_cell model=$model cell_num=9 field="currency"}
					{$model->currency}
				{/grid_cell}
				{grid_cell model=$model cell_num=10 field="price"}
					{$model->getGrossPrice()|string_format:"%.2f"}
				{/grid_cell}
				{grid_cell model=$model cell_num=10 field="price"}
					{$model->getPriceDiscount()}
				{/grid_cell}
				{grid_cell model=$model cell_num=10 field="price"}
					{$model->getPrice()|string_format:"%.2f"}
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