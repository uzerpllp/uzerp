{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.2 $ *}
{if is_array($value)}
	{foreach from=$value key=key item=subvalue}
		{include file="$template_dir/printaction_data.tpl" value=$subvalue parent="$parent[$key]"}
	{/foreach}
{else}
	<input type="hidden" name="{$parent}" value="{$value}" />
{/if}