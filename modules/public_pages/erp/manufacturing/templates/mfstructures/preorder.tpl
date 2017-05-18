{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.9 $ *}
{content_wrapper}
	{form controller="mfstructures" action="preorder"}
		{with model=$models.STItem legend="STItem Details"}
			<input type='hidden' name='stitem_id' value='{$transaction->id}'>
			<div id="view_page" class="clearfix">
				<dl id="view_data_left">
					{view_data model=$transaction attribute="item_code" link_to='"module":"manufacturing","controller":"stitems","action":"view","id":"'|cat:$transaction->id|cat:'"'}
					{view_data model=$transaction attribute="description"}
					{view_data model=$transaction attribute="uom_name"}
					{view_data model=$transaction attribute="comp_class"}
					{view_data model=$transaction attribute="type_code"}
					{view_data model=$transaction attribute="product_group"}
					<dt><label for="qty">Quantity</label>:</dt>
					<dd>
						<input type='text' name='qty' id='qty' value='{$qty}' label='Quantity'>
					</dd>
				</dl>
			</div>
		{/with}
		{submit value='Calculate'}
	{/form}
	{if $mfstructures->count()>0}
		<p><strong>Volumes Required to make {$qty} of above item</strong></p>
		{data_table}
			{heading_row}
				{heading_cell field="ststructure"}
					Stock Item
				{/heading_cell}
				{heading_cell field="start_date"}
					Start Date
				{/heading_cell}
				{heading_cell field="end_date"}
					End Date
				{/heading_cell}
				{heading_cell field="qty" class='right'}
					Quantity
				{/heading_cell}
				{heading_cell field="uom"}
					UoM
				{/heading_cell}
				{heading_cell field="balance" class='right'}
					Current Balance
				{/heading_cell}
			{/heading_row}
			{foreach name=datagrid item=model from=$mfstructures}
				{grid_row model=$model}
					{grid_cell model=$model cell_num=1 field="ststructure"}
						{$model->ststructure}
					{/grid_cell}
					{grid_cell model=$model cell_num=3 field="start_date"}
						{$model->start_date}
					{/grid_cell}
					{grid_cell model=$model cell_num=4 field="end_date"}
						{$model->end_date}
					{/grid_cell}
					{grid_cell model=$model cell_num=5 field="qty" class='numeric'}
						{$model->getRequirement($qty)}
					{/grid_cell}
					{grid_cell model=$model cell_num=6 field="uom"}
						{$model->uom}
					{/grid_cell}
					{grid_cell model=$model cell_num=7 class='numeric'}
						{$model->getCurrentBalance()}
					{/grid_cell}
				{/grid_row}
			{foreachelse}
				<tr><td colspan="0">No matching records found!</td></tr>
			{/foreach}
		{/data_table}
	{/if}
{/content_wrapper}