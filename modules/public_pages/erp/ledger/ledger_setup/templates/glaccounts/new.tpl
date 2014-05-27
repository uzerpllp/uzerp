{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.6 $ *}
{content_wrapper}
	{form controller="glaccounts" action="save"}
		{with model=$models.GLAccount legend="GLAccount Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{input type='text'  attribute='account'}
			{input type='text'  attribute='description'}
			{select attribute='actype' label='Account Type' options=$actypes value=$selected_actype}
			{select attribute='glanalysis_id' label='Analysis Code'}
			{input type='checkbox'  attribute='control' }
			{select attribute='centre_id' size="5" force=true nonone=true label='Cost Centres' multiple=true options=$centres value=$selected_centres}
		{/with}
		{submit}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}