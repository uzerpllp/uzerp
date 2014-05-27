{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	{form controller=$controller action="update_pay_reference"}
		{with model=$PLPayment}
			{input type='hidden'  attribute='id' }
			<dl id="view_data_left">
				{view_section heading="payment_details"}
					{view_data attribute="number_transactions"}
					{view_data attribute="bank_account"}
					{view_data attribute="payment_type"}
					{view_data attribute="currency"}
					{view_data attribute="payment_total"}
					<dt>Start {$PLPayment->payment_type} Number</dt>
					<dd><input type='text'  name='start_reference' ></dd>
					<dt>End {$PLPayment->payment_type} Number</dt>
					<dd><input type='text'  name='end_reference' ></dd>
				{/view_section}
			</dl>
		{/with}
		{submit}
	{/form}
{/content_wrapper}