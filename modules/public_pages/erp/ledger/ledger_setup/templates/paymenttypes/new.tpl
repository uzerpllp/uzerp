{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.6 $ *}
{content_wrapper}
	{form controller="paymenttypes" action="save"}
		{with model=$models.PaymentType legend="PaymentType Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{input type='text'  attribute='name' class="compulsory" }
			{select attribute='method_id' }
		{/with}
		{submit}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}