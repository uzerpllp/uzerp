{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
<ul>
{foreach name=list item=item key=key from=$collection}
<li id="{$item->id}">{$item->$alias}</li>
{if $hidden}
<li style="display:none;" id="hidden_{$item->id}">{$item->toJSON()}</li>
{/if}
{if $extra}
<li style="display:none;" id="{$extra}_{$item->id}">{$item->$extra}</li>
{/if}
{/foreach}
</ul>