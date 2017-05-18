{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}
{content_wrapper}
	{form controller="glbalances" action="save"}
		{with model=$models.GLBalance legend="GLBalance Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{select attribute='account_id' }
			{select attribute='centre_id' }
			{input type='text'  attribute='year' class="compulsory" }
			{select attribute='periods_id' }
			{input type='text'  attribute='value' class="compulsory" }
		{/with}
		{submit}
	{/form}
{/content_wrapper}