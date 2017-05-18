{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.6 $ *}
{content_wrapper title=$page_title|cat:' for '|cat:$employee->person->getIdentifierValue()}
	{form controller="holidayextradays" action="save"}
		{with model=$models.Holidayextraday legend="Holiday Extra Days Details"}
			{input type='hidden'  attribute='id' }
			{input type='hidden'  attribute='entitlement_period_id'}
			{input type='hidden'  attribute='employee_id'}
			{include file='elements/auditfields.tpl' }
			{input type='text'  attribute='num_days' class="compulsory" }
			{textarea attribute='reason' label="Reason"}
		{/with}
		{submit}
		{include file='elements/saveAnother.tpl'}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}