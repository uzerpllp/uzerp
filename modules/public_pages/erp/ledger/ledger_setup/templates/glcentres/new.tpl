{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.6 $ *}
{content_wrapper}
	{form controller="glcentres" action="save"}
		{with model=$models.GLCentre legend="GLCentre Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{input type='text'  attribute='cost_centre' class="compulsory" }
			{input type='text'  attribute='description' class="compulsory" }
			{select attribute='account_id' size="5" force=true nonone=true label='Accounts' multiple=true options=$accounts value=$selected_accounts}
		{/with}
		{submit}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}