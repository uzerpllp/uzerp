{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.7 $ *}
{content_wrapper}
	{form controller="sldiscounts" action="save"}
		{with model=$models.SLDiscount legend="Customer/Product Group Discounts"}
			{include file='elements/auditfields.tpl' }
			{input type='hidden' attribute='id' }
			{select type='text' force=true attribute='slmaster_id' class="compulsory" label='Customer' options=$customers}
			{select type='text' attribute='prod_group_id' class="compulsory" label='Product Group' options=$prod_groups}
			{input type='text' attribute='discount_percentage' class="compulsory" }
			{submit}
			{include file='elements/saveAnother.tpl'}
		{/with}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}