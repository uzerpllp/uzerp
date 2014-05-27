{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="mfoutsideoperations" action="save"}
		{with model=$models.MFOutsideOperation legend="MFOutsideOperation Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{input type='text'  attribute='op_no' class="compulsory" }
			{input type='date'  attribute='start_date' class="compulsory" }
			{input type='date'  attribute='end_date' class="compulsory" }
			{select label='Stock Item' attribute='stitem_id' }
			{input type='text'  attribute='description' }
			{input type='text' label='Cost' attribute='latest_osc' }
		{/with}
		{submit}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}