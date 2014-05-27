{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$EmployeePayPeriod legend="Employee Pay Period"}
			{view_data attribute='period_start_date'}
			{view_data attribute='period_end_date'}
			{view_data attribute='tax_year'}
			{view_data attribute='tax_month'}
			{view_data attribute='tax_week'}
			{view_data attribute='pay_basis'}
			{view_data attribute='calendar_week'}
			{view_data attribute='closed'}
			{view_data attribute='processed_date'}
			{view_data attribute='processed_period'}
		{/with}
	</div>
	<div id='current_rates'>
		{include file="elements/datatable.tpl" collection=$employeepayhistorys}
	</div>
{/content_wrapper}