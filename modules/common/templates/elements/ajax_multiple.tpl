{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.8 $ *}
{* to prevent problems accessing the data within jQuery *}
{* ensure any elements that depend on a parent element  *}
{* (such as select -> option), make sure that the       *}
{* structure is maintained                              *}

{foreach from=$data key=k item=v}
	{if $v.is_array == true}
		<select id="ajax_{$k}">
			{include file="elements/select_options.tpl" options=$v.data}
		</select>
	{else}
		<div id="ajax_{$k}">
			{$v.data}
		</div>
	{/if}
{/foreach}