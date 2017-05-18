{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	{form controller=$controller action="save_process_override"}
		{with model=$plpayment legend="PL Payment Details"}
			{input type='hidden' attribute='id' }
			{input type='hidden' attribute='usercompanyid' }
			{view_data attribute='payment_date'}
			{view_data attribute='status'}
			{view_data attribute='reference'}
			{view_data attribute='number_transactions'}
			{view_data attribute='cb_account_id'}
			{view_data attribute='currency_id'}
			{view_data attribute='payment_type_id'}
			{view_data attribute='payment_total'}
			{view_data attribute='remittance_printed'}
			{view_data attribute='remittance_date'}
			{input type='checkbox' attribute='override'}
			{input type='checkbox' attribute='no_output'}
		{/with}
		{submit}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}