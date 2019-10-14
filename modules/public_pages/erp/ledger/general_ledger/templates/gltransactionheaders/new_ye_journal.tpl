{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.1 $ *}
{content_wrapper}
	{form controller="gltransactionheaders" action="save"}
	{input type='hidden' attribute='year' value=$transaction_year}
		{with model=$models.GLTransactionHeader}
			{input type='hidden' attribute='id'}
			{input type='hidden' attribute='docref'}
			{input type='hidden' attribute='type' value='Y'}
			{input type='hidden' attribute='transaction_date' value=$transaction_date}
			{include file='elements/auditfields.tpl' }
			<span class='standard_field'>
				{input label='Period ' type='text' attribute='period' readonly=true value=$period}
			</span>
			{textarea attribute='comment' label='Comment'}
		{/with}
		{submit}
		{include file='elements/saveAnother.tpl' name='saveadd' id='saveadd'}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}