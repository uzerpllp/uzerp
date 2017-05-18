{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{form controller="bankaccounts" action="save_revaluation"}
			{with model=$CBAccount}
				{input type='hidden' attribute=$model->idField}
				{input type='hidden' attribute="balance"}
				{input type='hidden' attribute="method" value=$method}
				{view_data attribute="name" label='Bank Account Name'}
				{view_data attribute="currency"}
				{view_data attribute="current_balance" value=$model->balance}
				{view_data value=$glbalance label='GL Balance'}
				{input attribute='rate' label='Rate' value=$rate}
				{input attribute='new_balance' label='Revaluation' value=$new_balance}
				{input type="date" attribute='transaction_date' label='transaction_date' value=$transaction_date}
				{input attribute='reference' label='reference'}
				{textarea attribute='comment' label='Comment'}
			{/with}
			{submit}
		{/form}
		{include file='elements/cancelForm.tpl'}
	</div>
{/content_wrapper}