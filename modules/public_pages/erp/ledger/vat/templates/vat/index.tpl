{** 
 *	(c) 2019 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{if $mtd_authorised}
		<p><em>uzERP is currently authorised to access <abbr title="Making Tax Digital">MTD</abbr> for VAT</em></p>
	{/if}
	{include file="elements/datatable.tpl" collection=$vatreturns}
{/content_wrapper}