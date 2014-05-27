{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.7 $ *}
<div id='sorders_item_overview'>
	{assign var='currentpage' value=$content.page}
	{assign var='num_pages' value=$content.num_pages}
	<table class='datagrid'>
		<caption>
			<strong>
				{$content.title}
			</strong>
		</caption>
		<tr>
			<th align=left>
				Item
			</th>
			<th class="right">
				Quantity
			</th>
			<th class="right">
				Value
			</th>
		</tr>
		{foreach item=sorder key=id from=$content.items}
			<tr>
				<td>
					{if $id==''}
						Non-Stock Items
					{else}
						{$id}
					{/if}
				</td>
				<td align=right>
					{$sorder.qty}
				</td>
				<td align=right>
					{$sorder.value|string_format:"%.2f"}
				</td>
			</tr>
		{/foreach}
	</table>
	<p>
		{if $currentpage>1}
			<a class="ajax eglet_paging" href="{$content.url}&page=1">
				<img src="/themes/default/graphics/resultset_first.png" />
			</a>
		{/if}
		{if $currentpage>2}
			<a class="ajax eglet_paging" href="{$content.url}&page={$currentpage-1}">
				<img src="/themes/default/graphics/resultset_previous.png" />
			</a>
		{/if}
		<strong>{$currentpage} of {$num_pages}</strong>
		{if $currentpage+1<$num_pages}
			<a class="ajax eglet_paging" href="{$content.url}&page={$currentpage+1}">
				<img src="/themes/default/graphics/resultset_next.png" />
			</a>
		{/if}
		{if $currentpage<$num_pages}
			<a class="ajax eglet_paging" href="{$content.url}&page={$num_pages}">
				<img src="/themes/default/graphics/resultset_last.png" />
			</a>
		{/if}
	</p>
</div>