{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="mfresources" action="save"}
		{with model=$models.MFResource legend="MFResource Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{input type='text'  attribute='resource_code' class="compulsory" }
			{input type='text'  attribute='description' class="compulsory" }
			{input type='text'  attribute='resource_rate' class="compulsory" }
		{/with}
		{submit}
	{/form}
	{include file="elements/cancelForm.tpl"}
{/content_wrapper}