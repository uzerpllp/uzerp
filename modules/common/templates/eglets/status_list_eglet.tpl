{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.2 $ *}
<ul>
{foreach item=equipment from=$content}
<li style="list-style-type: none;">
	<a href="/?module=projects&controller=Equipment&action=view&id={$equipment.id}">
		<img src="/assets/graphics/{$equipment.colour}.png" alt="{$equipment.color}" />
		<span style='{if $equipment.disabled}text-decoration: line-through;{/if}'>
			{$equipment.name}
		</span>
	</a>
</li>
{foreachelse}
<li>No equipment in use for 30 days</li>
{/foreach}
</ul>
