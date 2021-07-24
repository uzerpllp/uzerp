{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.7 $ *}
{content_wrapper}
	<div id="view_data_left">	
		{form controller=$self.controller action="saveVATAdjustment"}
			{with model=$VatAdjustment}
				{input type='hidden' attribute='vat_return_id' value=$vat_return}
				{input type='text' label='reference' attribute='reference' }
				{input type='text' label='comment' attribute='comment' }
				{input type='text' label='vat_due_sales' attribute='vat_due_sales'}
				{input type='text' label='vat_reclaimed_curr_period' attribute='vat_reclaimed_curr_period'}
				{input type='text' label='total_value_sales_ex_vat' attribute='total_value_sales_ex_vat'}
				{input type='text' label='total_value_purchase_ex_vat' attribute='total_value_purchase_ex_vat'}
				{include file='elements/auditfields.tpl'}
				{submit}
			{/with}
		{/form}
		{include file="elements/cancelForm.tpl" action="index"}
	</div>
{/content_wrapper}