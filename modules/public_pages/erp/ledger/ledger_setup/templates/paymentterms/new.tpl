{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.7 $ *}
{content_wrapper}
	{form controller="paymentterms" action="save"}
		{with model=$models.PaymentTerm legend="PaymentTerm Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{input type='text'  attribute='description' class="compulsory" }
			{select attribute='basis' }
			{input type='text' attribute='days' class="compulsory" }
			{input type='text' attribute='months' class="compulsory" }
			{input type='text' attribute='discount' class="compulsory" }
			{input type='checkbox' attribute='allow_discount_on_allocation' }
			{select  attribute='pl_discount_glaccount_id' options=$pl_discount_glaccounts nonone=true}
			{select  attribute='pl_discount_glcentre_id' options=$pl_discount_glcentres nonone=true}
			{select  attribute='sl_discount_glaccount_id' options=$sl_discount_glaccounts nonone=true}
			{select  attribute='sl_discount_glcentre_id' options=$sl_discount_glcentres nonone=true}
			{input type='text' attribute='pl_discount_description'}
			{input type='text' attribute='sl_discount_description'}
		{/with}
		{submit}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}