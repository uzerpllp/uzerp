{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.12 $ *}
<div class="tree_div">
		<ul class="tree_div">
			{foreach name=list_eglet item=item key=key from=$content}
				{if count($item.sub)>0}
					<li class="tree_div">
						<img src="{$item.main->icons.icon}" id="image_{$key}">
						{link_to link=$item.main->link value=$item.main->title|truncate:25} &raquo;
						<ul class="tree_sub">
							{include file='eglets/menu_eglet.tpl' content=$item.sub}
						</ul>
					</li>
				{else}
					<li class="tree_div">
						<img src="{$item.main->icons.icon}">
						{link_to link=$item.main->link value=$item.main->title|truncate:25}
					</li>
				{/if}
			{foreachelse}
				<li>No items to show</li>
			{/foreach}
		</ul>
</div>