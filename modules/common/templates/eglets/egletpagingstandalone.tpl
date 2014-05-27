{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.8 $ *}
	<p>{if $currentpage>1}
		<a class="ajax eglet_paging" href="/?module={$module}&submodule={$submodule}&controller={$controller}&action=refreshEglet&eglet={$egletname}&page=1">
			<img src="/themes/default/graphics/resultset_first.png" />
		</a>
	{/if}
	{if $currentpage>2}
		<a class="ajax eglet_paging" href="/?module={$module}&submodule={$submodule}&controller={$controller}&action=refreshEglet&eglet={$egletname}&page={$currentpage-1}">
			<img src="/themes/default/graphics/resultset_previous.png" />
		</a>
	{/if}
	<b>{$currentpage} of {$num_pages}</b>
	{if $currentpage+1<$num_pages}
		<a class="ajax eglet_paging" href="/?module={$module}&submodule={$submodule}&controller={$controller}&action=refreshEglet&eglet={$egletname}&page={$currentpage+1}">
			<img src="/themes/default/graphics/resultset_next.png" />
		</a>
	{/if}
	{if $currentpage<$num_pages}
		<a class="ajax eglet_paging" href="/?module={$module}&submodule={$submodule}&controller={$controller}&action=refreshEglet&eglet={$egletname}&page={$num_pages}">
			<img src="/themes/default/graphics/resultset_last.png" />
		</a>
	{/if}</p>