{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="syuomconversions" action="save"}
		{with model=$models.SYuomconversion legend="SYuomconversion Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{select  attribute='from_uom_id' class="compulsory" label='One'}
			{input type='text'  attribute='conversion_factor' label='Contains'}
			{select  attribute='to_uom_id' class="compulsory" label='of'}
		{/with}
		{submit}
	{/form}
	{include file="elements/cancelForm.tpl"}
{/content_wrapper}