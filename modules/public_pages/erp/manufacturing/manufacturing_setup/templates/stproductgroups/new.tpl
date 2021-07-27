{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="stproductgroups" action="save"}
		{with model=$models.STProductgroup legend="STProductgroup Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{input type='text'  attribute='product_group' class="compulsory" }
			{input type='text'  attribute='description' }
			{input type='checkbox'  attribute='active' }
		{/with}
		{submit}
	{/form}
	{include file="elements/cancelForm.tpl"}
{/content_wrapper}