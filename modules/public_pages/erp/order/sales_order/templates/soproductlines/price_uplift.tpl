{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.11 $ *}
{content_wrapper}
	{advanced_search}
	{form controller="soproductlines" action="price_uplift"}
		<input type='hidden' name=current_page id=current_page value={$soproductlines->cur_page}>
		{input type='text' model=$soproductline attribute=percent label='Percentage Price Uplift' value=$percent class="numeric percent"}
		{input type='text' model=$soproductline attribute=decimals label='No. decimal places for Price' value=$decimals class="numeric percent"}
		{assign var=100 value=100}
		{assign var=p1 value=100+$percent}
		{assign var=percentage value=$p1/100}
		{assign var=price_format value="%."|cat:$decimals|cat:"f"}
		{paging}
		{data_table class='price_uplift'}
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
				{heading_cell field="price"}
					Price
				{/heading_cell}
				<th class='numeric right'>
					New Price
				</th>
				<th>
					Include
				</th>
			{/heading_row}
			{foreach name=datagrid item=model from=$soproductlines}
				{assign var=id value=$model->id}
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
						{$model->getGrossPrice()|string_format:$price_format}
					{/grid_cell}
					{grid_cell model=$model cell_num=10 field="new_price" no_escape=true}
						{input model=$model class='numeric price' type='text' data_field='new_price' data_row="$id" rowid=$model->id attribute="new_price" number=$model->id value=$selected.$id.new_price|string_format:$price_format label=' ' tags=none}
					{/grid_cell}
					<td>
						{if $selected.$id.select=='true'}
							{input model=$model class="checkbox" type="checkbox" attribute="select" data_field="select" data_row="$id" rowid="$id" number="$id" tags='none' nolabel=true  value="true"}
						{else}
							{input model=$model class="checkbox" type="checkbox" attribute="select" data_field="select" data_row="$id" rowid="$id" number="$id" tags='none' nolabel=true}
						{/if}
					</td>
				{/grid_row}
			{foreachelse}
				<tr>
					<td colspan="0">No matching records found!</td>
				</tr>
			{/foreach}
		{/data_table}
		{paging}
		{submit value='Recalculate'}
	{/form}
	{form controller="soproductlines" action="save_price_uplift"}
		{input type='hidden' model=$soproductline attribute=percent label='Percentage Price Uplift' value=$percent class="numeric percent"}
		{input type='hidden' model=$soproductline attribute=decimals label='No. decimal places for Price' value=$decimals class="numeric percent"}
		{input type='date' model=$soproductline attribute='effective_date' value=$effective_date label='Effective Date'}
		{submit id='saveprices' value='Save Prices'}
	{/form}
	<div id='dialog' style="display:none;">
		<img src="/assets/graphics/spinner.gif" />
		<p>Calculating number of prices to be amended</p>
		<div id='dialogprogressbar'></div>
	</div>
	<div id='updatedialog' style="display:none;">
		<img src="{/assets/graphics/spinner.gif" />
		<p>Updating prices</p>
		<div id='updateprogressbar'></div>
	</div>
{/content_wrapper}
