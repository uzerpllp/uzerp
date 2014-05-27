{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="arlocations" action="save"}
		{with model=$models.ARLocation legend="Asset Location Details"}
			{input type='hidden'  attribute='id' }
			{input type='hidden'  attribute='usercompanyid' }
			{input type='text'  attribute='description' class="compulsory" }
			{select attribute='pl_glcentre_id' class="compulsory" label='P&L Cost Centre'}
			{select attribute='bal_glcentre_id' class="compulsory" label='Balance Sheet Cost Centre'}
		{/with}
		{submit}
		{if $action!='edit'}
			{submit value='Save and Add Another'}
		{/if}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}