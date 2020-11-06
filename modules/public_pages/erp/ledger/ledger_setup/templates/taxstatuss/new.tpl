{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.5 $ *}
{content_wrapper}
	{form controller="taxstatuss" action="save"}
		{with model=$models.TaxStatus legend="TaxStatus Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{input type='text'  attribute='description' class="compulsory" }
			{input type='checkbox'  attribute='apply_tax' class="compulsory" }
			{input type='checkbox' label='EU Tax' attribute='eu_tax' class="compulsory" }
			{input type='checkbox' label='PVA' attribute='postponed_vat_accounting' class="compulsory" }
			{input type='checkbox' attribute='reverse_charge' class="compulsory" }
		{/with}
		{submit}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}