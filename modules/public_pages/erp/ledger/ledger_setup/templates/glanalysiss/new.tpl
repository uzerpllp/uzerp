{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.5 $ *}
{content_wrapper}
	{form controller="glanalysiss" action="save"}
		{with model=$models.GLAnalysis legend="GLAnalysis Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{select attribute='glsummary_id' }
			{input type='text'  attribute='analysis' class="compulsory" }
		{/with}
		{submit}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}