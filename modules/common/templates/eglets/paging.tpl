{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.8 $ *}
<p>
	{if ($num_pages>0)}
		{if $currentpage>1}
			<a class="ajax eglet_paging" href="/?module={$module}&submodule={$submodule}&controller={$controller}&action=refreshEglet&uzletid={$uzletid}&uzlet={$egletname}&page=1">
				<img src="/themes/default/graphics/resultset_first.png" />
			</a>
		{/if}
		{if $currentpage>2}
			<a class="ajax eglet_paging" href="/?module={$module}&submodule={$submodule}&controller={$controller}&action=refreshEglet&uzletid={$uzletid}&uzlet={$egletname}&page={$currentpage-1}">
				<img src="/themes/default/graphics/resultset_previous.png" />
			</a>
		{/if}
		<strong>{$currentpage} of {$num_pages}</strong>
		{if $currentpage+1<$num_pages}
			<a class="ajax eglet_paging" href="/?module={$module}&submodule={$submodule}&controller={$controller}&action=refreshEglet&uzletid={$uzletid}&uzlet={$egletname}&page={$currentpage+1}">
				<img src="/themes/default/graphics/resultset_next.png" />
			</a>
		{/if}
		{if $currentpage<$num_pages}
			<a class="ajax eglet_paging" href="/?module={$module}&submodule={$submodule}&controller={$controller}&action=refreshEglet&uzletid={$uzletid}&uzlet={$egletname}&page={$num_pages}">
				<img src="/themes/default/graphics/resultset_last.png" />
			</a>
		{/if}
		{if isset($content->collection_total)}
			<span style='float:right;' align='right'>
				{$content->collection_total_label}&nbsp<b>{$content->collection_total}</b>
			</span>
		{/if}
	{/if}
</p>