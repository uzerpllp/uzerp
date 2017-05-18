{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.6 $ *}
{content_wrapper}
	{form controller="glbudgets" action="save"}
		{with model=$models.GLBudget legend="GLBudget Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{select attribute='glaccount_id' label='GL Account' options=$accounts}
			{select attribute='glcentre_id' nonone=true label='Cost Centre' options=$centres}
			{select attribute='glperiods_id' class="compulsory" }
			{input type='text'  attribute='value' class="compulsory" }
		{/with}
		{submit}
		{if $action=='new'}
			{submit value='Save and Add Another'}
		{/if}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}