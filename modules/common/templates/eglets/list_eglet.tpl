{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.2 $ *}
<ul>
{foreach name=list_eglet item=item key=key from=$content}
{if $item->tag neq ''}
	<li>{link_to data=$item->url value=$item->tag|truncate:25}</li>
{elseif $item->title neq ''}
	<li>{link_to link=$item->link value=$item->title|truncate:25}</li>
{/if}
{foreachelse}
<li>No items to show</li>
{/foreach}
</ul>
