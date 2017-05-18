{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}
{content_wrapper}
	{form controller="currencyrates" action="save"}
		{with model=$models.CurrencyRate legend="CurrencyRate Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{select attribute='currency_id' }
			{input type='date'  attribute='date' class="compulsory" }
			{input type='text'  attribute='rate' class="compulsory" }
		{/with}
		{submit}
	{/form}
{/content_wrapper}