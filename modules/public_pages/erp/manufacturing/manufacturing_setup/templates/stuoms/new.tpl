{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.6 $ *}
{content_wrapper}
	{form controller="stuoms" action="save"}
		{with model=$models.STuom legend="STuom Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{input type='text'  attribute='uom_name' class="compulsory" }
		{/with}
		{submit}
		{submit name="saveAnother" value="Save and Add Another"}
	{/form}
	{include file="elements/cancelForm.tpl"}
{/content_wrapper}