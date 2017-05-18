{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.3 $ *}
{content_wrapper}
	{form controller="sopricetypes" action="save"}
		{with model=$models.SOPriceType legend="Sales Order Price Type Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{input type='text'  attribute='name' class="compulsory" }
			{input type='text'  attribute='description' class="compulsory" }
		{/with}
		{submit}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}