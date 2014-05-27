{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.5 $ *}	
{content_wrapper}
	{form controller="companyaddresss" action="save"}
		<dl id="view_data_left">
			{with model=$models.Companyaddress legend="Companyaddress Details"}
				{view_section heading="Address Details"}
				{input type='hidden'  attribute='id' }
				{include file='elements/auditfields.tpl' }
				{input type='text'  attribute='name' }
				{input type='text'  attribute='street1' class="compulsory" }
				{input type='text'  attribute='street2' }
				{input type='text'  attribute='street3' }
				{input type='text'  attribute='town' class="compulsory" }
				{input type='text'  attribute='county' class="compulsory" }
				{input type='text'  attribute='postcode' class="compulsory" }
				{select attribute='countrycode' }
				{select attribute='company_id' }
				{if $model->main=='t'}
					{view_data attribute='main' }
				{else}
					{input type='checkbox'  attribute='main' }
				{/if}
				{input type='checkbox'  attribute='billing' }
				{input type='checkbox'  attribute='shipping' }
				{input type='checkbox'  attribute='payment' }
				{input type='checkbox'  attribute='technical' }
				{/view_section}
			{/with}
			{submit}
		</dl>
	{/form}
	<div id="view_page" class="clearfix">
		<dl id="view_data_right">
			{include file="elements/cancelForm.tpl"}
		</dl>
	</div>
{/content_wrapper}