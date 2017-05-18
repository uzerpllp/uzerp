{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}
{content_wrapper}
	{include file="elements/options.tpl"}
	{search grid=$taxstatuss}
	{include file="elements/datatable.tpl" collection=$taxstatuss}
{/content_wrapper}