{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}
{content_wrapper}
	{form controller="employeepayhistorys" action="save"}
		{with model=$models.EmployeePayHistory legend="Employee Pay History"}
			{select attribute='employee_pay_periods_id' force=true options=$employee_pay_periods value=$current_pay_period}
			{select attribute='employee_id' use_collection=true force=true options=$employees}
			<div id='hour_types' style='clear:both;'>
				{include file='./hour_types.tpl'}
			</div>
		{/with}
		{submit}
		{include file='elements/saveAnother.tpl'}
	{/form}
	{include file='elements/cancelForm.tpl'}
	<div id='current_rates'>
		{include file="elements/datatable.tpl" collection=$employeepayhistorys}
	</div>
{/content_wrapper}