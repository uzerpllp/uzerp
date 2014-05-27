{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.20 $ *}	
{content_wrapper}
	<dl id="view_data_left">
		{form controller="poproductlines" action="save"}
		    <dl class="float-left" >
				{with model=$POProductline legend="POProductline Details"}
					{input type='hidden'  attribute='id' }
					{include file='elements/auditfields.tpl' }
					{select attribute='productline_header_id' options=$headers}
				{/with}
				<div id="header_data">
					{include file="./header_data.tpl"}
				</div>
			</dl>
			<dl class="float-right">
				{with model=$models.POProductline legend="POProductline Details"}
					{select attribute='plmaster_id' label='Supplier'}
					{input type='text' attribute='supplier_product_code' }
					{input type='text' attribute='description' value=$POProductlineHeader->description}
					{select attribute='currency_id' label='Currency' value=$currency}
					{input type='text' attribute='price' }
					{select attribute='glaccount_id' options=$gl_accounts value=$gl_account label='GL Account'}
					{select attribute='glcentre_id' options=$gl_centres label='Cost Centre'}
					{input type='date' attribute='start_date' }
					{input type='date' attribute='end_date' value=$POProductlineHeader->end_date}
					{input type='text' attribute='ean' }
				{/with}
				{submit}
				{submit id='saveform' name='saveadd' value='Save and Add Another'}
			</div>
		{/form}
		{include file='elements/cancelForm.tpl'}
	</dl>
{/content_wrapper}