{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.7 $ *}
{content_wrapper}
	{form controller="currencys" action="save"}
		{with model=$models.Currency legend="Currency Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{input type='text'  attribute='currency' class="compulsory" }
			{input type='text'  attribute='description' class="compulsory" }
			{input type='text'  attribute='symbol' class="compulsory" }
			{input type='text'  attribute='decdesc' class="compulsory" label='Decimal Description'}
			{input type='text'  attribute='rate'  }
			{select attribute='writeoff_glaccount_id' force=true class="compulsory" label='Writeoff Account'}
			{select attribute='glcentre_id' class="compulsory" label='Cost Centre' options=$glcentres}
			{input type='checkbox'  attribute='datectrl' label='Date Control'}
			{select attribute='method' }
		{/with}
		{submit}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}