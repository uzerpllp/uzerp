{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.5 $ *}
{content_wrapper}
	<input type="hidden" id="alternate_print_action" value="{$action}" />
	<input type="hidden" id="print_force_index" value="true" />
	{include file="elements/datatable_collapsible.tpl" collection=$transactions}
{/content_wrapper}