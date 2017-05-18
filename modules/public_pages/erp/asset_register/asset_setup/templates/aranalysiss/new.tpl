{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="aranalysiss" action="save"}
		{with model=$models.ARAnalysis legend="Asset Analysis Details"}
			{input type='hidden'  attribute='id' }
			{input type='hidden'  attribute='usercompanyid' }
			{input type='text'  attribute='description' class="compulsory" }
		{/with}
		{submit}
		{if $action!='edit'}
			{submit value='Save and Add Another'}
		{/if}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}