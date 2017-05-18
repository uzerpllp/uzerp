{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	{form controller="sldiscounts" action="save"}
		{with model=$models.SLDiscount legend="Customer/Product Group Discounts"}
			{include file='elements/auditfields.tpl' }
			{input type='hidden' attribute='id' }
			{input type='hidden' attribute='slmaster_id' }
			{input type='hidden' attribute='prod_group_id' }
			{view_data attribute='slmaster_id'}
			{view_data attribute='prod_group_id'}
			{input type='text' attribute='discount_percentage' class="compulsory" }
			{submit}
		{/with}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}