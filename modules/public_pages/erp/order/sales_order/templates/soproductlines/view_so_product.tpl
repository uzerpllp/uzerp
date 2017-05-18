{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.5 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$SOProductlineHeader legend="SOProduct Details"}
		    <dl class="float-left" >
				{view_data attribute='description' link_to='"module":"'|cat:$linkmodule|cat:'","controller":"'|cat:$linkcontroller|cat:'","action":"view","id":"'|cat:$SOProductlineHeader->id|cat:'"'}
				{view_data attribute='stitem' label='Stock Item'}
				{view_data attribute='product_group'}
				{view_data attribute='uom_name'}
				{view_data attribute="tax_rate"}
			</dl>
		    <dl class="float-right" >
				{view_data attribute="gl_account"}
				{view_data attribute="gl_centre"}
				{view_data attribute="start_date"}
				{view_data attribute="end_date"}
				{with model=$model->item_detail}
					{view_data attribute="latest_cost"}
					{view_data attribute="std_cost"}
				{/with}
			</dl>
		{/with}
	</div>
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
					{$model->getGrossPrice()}
				{/grid_cell}
				{grid_cell model=$model cell_num=10 field="price"}
					{$model->getPriceDiscount()}
				{/grid_cell}
				{grid_cell model=$model cell_num=10 field="price"}
					{$model->getPrice()}
				{/grid_cell}
			{/grid_row}
		{foreachelse}
			<tr>
				<td colspan="0">No matching records found!</td>
			</tr>
		{/foreach}
	{/data_table}
	{paging}
{/content_wrapper}