{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	<dl id="view_data_left">
		{form controller="ledgercategorys" action="save"}
			{with model=$models.LedgerCategory legend="Ledger Category Details"}
				{input type='hidden'  attribute='id' }
				{include file='elements/auditfields.tpl' }
				{if $model->isLoaded()}
					{view_data attribute='category_id'}
				{else}
					{select attribute='category_id' options=$categories}
				{/if}
				{select attribute='ledger_type'}
			{/with}
			{submit}
		{/form}
		{include file='elements/cancelForm.tpl' action='index'}
	</dl>
{/content_wrapper}