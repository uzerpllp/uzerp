{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.5 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$POProductlineHeader legend="POProduct Details"}
		    <dl class="float-left" >
				{view_data attribute='description' link_to='"module":"'|cat:$linkmodule|cat:'","controller":"'|cat:$linkcontroller|cat:'","action":"view","id":"'|cat:$POProductlineHeader->id|cat:'"'}
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
			{heading_cell field="supplier"}
				Supplier
			{/heading_cell}
			{heading_cell field="supplier_product_code"}
				Supplier Product Code
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
		{/heading_row}
		{foreach name=datagrid item=model from=$poproductlines}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=1 field="description"}
					{$model->description}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="supplier"}
					{$model->supplier}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="supplier_product_code"}
					{$model->supplier_product_code}
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