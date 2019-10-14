{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.1 $ *}
{content_wrapper}
	{form controller="gltransactionheaders" action="save"}
		{with model=$models.GLTransactionHeader legend="GLTransaction Header Details"}
			{input type='hidden' attribute='id'}
			{input type='hidden' attribute='docref'}
			{include file='elements/auditfields.tpl' }
			{select attribute='type' options=$types}
			<span class='standard_field'>
				{input type="date" attribute="transaction_date" value="$transaction_date" label="Transaction Date"} 
			</span>
			{textarea attribute='comment' label='Comment'}</dd>
			{input type='text' attribute='reference'}</dd>
			<span class='standard_field'>
				{input label='Period ' type='text' attribute='period' readonly=true value=$period}
			</span>
			{input label='Accrual? ' type='checkbox' attribute='accrual'}
			<span class='standard_field'>
				{if $model->accrual=='t'}
					{select attribute='accrual_period_id' options=$periods label='Reverse in Period' nonone=true}
				{else}
					{select attribute='accrual_period_id' options=$periods label='Reverse in Period' nonone=true disabled=true}
				{/if}
			</span>
		{/with}
		{submit}
		{include file='elements/saveAnother.tpl' name='saveadd' id='saveadd'}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}