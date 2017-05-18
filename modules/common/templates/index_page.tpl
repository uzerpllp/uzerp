{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{*doesn't really do much...*}
{if !isset($usealternative) || !$usealternative}
	{include file="file:{$smarty.const.BASE_TPL_ROOT}layouts/$layout.tpl"}
{else}
	{include file="$layout"}
{/if}
