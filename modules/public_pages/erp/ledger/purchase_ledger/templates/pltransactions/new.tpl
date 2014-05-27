{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.5 $ *}
{content_wrapper}
	{form controller="pltransactions" action="save"}
		{with model=$models.PLTransaction legend="PLTransaction Details"}
			{input type='hidden'  attribute='id' }
			{input type='hidden'  attribute='usercompanyid' }
			{input type='date'  attribute='transaction_date' }
			{input type='text'  attribute='transaction_type' class="compulsory" }
			{input type='text'  attribute='status' class="compulsory" }
			{input type='text'  attribute='our_reference' class="compulsory" }
			{input type='text'  attribute='ext_reference' class="compulsory" }
			{input type='text'  attribute='currency_id' class="compulsory" }
			{input type='text'  attribute='rate' class="compulsory" }
			{input type='text'  attribute='gross_value' class="compulsory" }
			{input type='text'  attribute='tax_value' class="compulsory" }
			{input type='text'  attribute='net_value' class="compulsory" }
			{input type='text'  attribute='twin_currency' class="compulsory" }
			{input type='text'  attribute='twin_rate' class="compulsory" }
			{input type='text'  attribute='twin_gross_value' class="compulsory" }
			{input type='text'  attribute='twin_tax_value' class="compulsory" }
			{input type='text'  attribute='twin_net_value' class="compulsory" }
			{input type='text'  attribute='base_gross_value' class="compulsory" }
			{input type='text'  attribute='base_tax_value' class="compulsory" }
			{input type='text'  attribute='base_net_value' class="compulsory" }
			{input type='text'  attribute='payment_term_id' class="compulsory" }
			{input type='date'  attribute='due_date' class="compulsory" }
			{input type='text'  attribute='cross_ref' }
			{input type='text'  attribute='os_value' class="compulsory" }
			{input type='text'  attribute='twin_os_value' class="compulsory" }
			{input type='text'  attribute='base_os_value' class="compulsory" }
			{input type='text'  attribute='description' }
		{/with}
		{submit}
	{/form}
{/content_wrapper}