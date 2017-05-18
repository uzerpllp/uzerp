{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.7 $ *}
{content_wrapper title=$page_title|cat:' for '|cat:$employee->person->getIdentifierValue()}
	{form controller="holidayentitlements" action="save"}
		{with model=$models.Holidayentitlement legend="Holiday Entitlement Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{input type='hidden'  attribute='employee_id'}
			{input type='text'  attribute='num_days' class="compulsory" }
			{input type='date'  attribute='start_date' class="compulsory" }
			{input type='date'  attribute='end_date' class="compulsory" }
			{input type='checkbox'  attribute='statutory_days' }
		{/with}
		{submit}
		{include file='elements/saveAnother.tpl'}
	{/form}
	{include file='elements/cancelForm.tpl'}
	{include file="elements/datatable.tpl" collection=$holidayentitlements}
{/content_wrapper}