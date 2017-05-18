{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.22 $ *}
{content_wrapper}
	{form controller="soproductlines" action="save"}
		<div id="view_page" class="clearfix">
		    <dl class="float-left" >
				{with model=$SOProductline legend="SOProductline Details"}
					{input type='hidden'  attribute='id' }
					{include file='elements/auditfields.tpl' }
					{select attribute='productline_header_id' options=$headers}
				{/with}
				<div id="header_data">
					{include file="./header_data.tpl"}
				</div>
			</dl>
			<dl class="float-right">
				{with model=$models.SOProductline legend="SOProductline Details"}
					{select attribute='slmaster_id' label="Customer"}
					{input type='text' attribute='customer_product_code' }
					{input type='text' attribute='description' value=$SOProductlineHeader->description}
					{select attribute='so_price_type_id'}
					{input type='text' attribute='price' label='Gross Price' value=$gross_price}
					{input type='text' attribute='discount' value=$discount|string_format:"%.2f" class="readonly" readonly=true label="Product Group Discount" }
					{input type='text' attribute='net_price' value=$model->getPrice()|string_format:"%.2f" class="readonly" readonly=true label="Net Price" }
					{select attribute='currency_id' label='Currency' value=$currency}
					{select attribute='glaccount_id' options=$gl_accounts value=$gl_account label='GL Account'}
					{select attribute='glcentre_id' options=$gl_centres label='Cost Centre'}
					{input type='date' attribute='start_date' }
					{input type='date' attribute='end_date' value=$SOProductlineHeader->end_date}
					{input type='text' attribute='ean' }
				</dl>
			</div>
		{/with}
		{submit}
		{submit id='saveform' name='saveadd' value='Save and Add Another'}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}