{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.5 $ *}
{content_wrapper}
	{form controller="taxrates" action="save"}
		{with model=$models.TaxRate legend="TaxRate Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{input type='text'  attribute='taxrate' class="compulsory" }
			{input type='text'  attribute='description' class="compulsory" }
			{input type='text'  attribute='percentage' class="compulsory" }
		{/with}
		{submit}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}