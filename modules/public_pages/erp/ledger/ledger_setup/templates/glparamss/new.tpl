{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.12 $ *}
{content_wrapper}
	{form controller="glparamss" action="save"}
		{with model=$models.GLParams legend="GLParams Details"}
			{include file='elements/auditfields.tpl' }
			{if $action=='edit'}
				{input type='hidden'  attribute='id' }
				{input type='hidden' attribute='paramdesc'}
				{view_data attribute='paramdesc' label='Description'}
			{else}
				{select attribute='paramdesc' label='Description' nonone=true options=$unassigned }
			{/if}
			{include file="./selectlist.tpl"}
		{/with}
		{submit}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}