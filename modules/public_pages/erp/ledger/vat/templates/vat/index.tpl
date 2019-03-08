{** 
 *	(c) 2019 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{if $tax_period_closed === 'f'}
		<p style="color: red"><strong>Warning:</strong> Tax period is not closed, the figures below may not be final.</p>
	{/if}
	{include file="elements/datatable.tpl" collection=$vatreturns}
	{if isset($tax_period_closed) }
		{if $tax_period_closed !== 't' }
			<a class="button" href="?module=vat&controller=vat&action=calculateVAT">Update VAT Position</a>
			<a class="button confirm" data-uz-confirm-message="Close VAT Period?|This cannot be undone." href="/?pid=568&amp;module=vat&amp;controller=vat&amp;action=closeVatPeriod">Close VAT Period</a>
		{/if}
		{if $finalised === 'f'}
			<a class= "button confirm" data-uz-confirm-message="Submit VAT Return to HMRC?|This cannot be undone." href="?module=vat&controller=vat&action=hmrcPostVat">Submit VAT Return</a>
		{/if}
	{/if}
{/content_wrapper}