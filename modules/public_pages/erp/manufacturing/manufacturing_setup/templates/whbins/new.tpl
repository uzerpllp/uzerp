{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="whbins" action="save"}
		{with model=$models.WHBin legend="WHBin Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{view_data model=$transaction label="Store" value=$whstore}
			{view_data model=$transaction label="Location" value=$whlocation}
			{input type='text'  attribute='bin_code' class="compulsory" }
			{input type='text'  attribute='description' }
			{input type='hidden'  attribute='whlocation_id' class="compulsory" }
		{/with}
		{submit}
	{/form}
	{include file="elements/cancelForm.tpl"}
{/content_wrapper}