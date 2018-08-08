{** 
 *	(c) 2018 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller=$self.controller action="save"}
		{with model=$CBTransaction}
			{select attribute="cb_account_id" label='Account' force=true value=$account_id options=$accounts}
			{select attribute='currency_id' value=$currency_id options=$currencies}
			{input type="hidden" attribute="type" value="P"}
            {input type="hidden" attribute="source" value='CV'}
			{input type="text" attribute="net_value" label="Ammount"}
			{input type="date" attribute="transaction_date"}
			{view_data attribute="company_id" label='Company' value=$company->name}
			{input type='hidden' attribute='company_id' value=$company->id}
			{input type="text" attribute="ext_reference"}
			{input type="text" attribute="description"}
			{select attribute="payment_type_id"}
			{select attribute='glaccount_id' options=$gl_accounts nonone=true force=true label='Account *' class="required"}
			{select attribute='glcentre_id' options=$gl_centres nonone=true label='Centre *' class="required"}
			{submit}
			{include file='elements/saveAnother.tpl'}
		{/with}
	{/form}
{/content_wrapper}