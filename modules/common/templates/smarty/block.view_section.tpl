{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.2 $ *}

{if isset($block_view_section.expand)}
	<div class="placeholder">
{/if}

<dt {$block_view_section.attrs} >
	{$block_view_section.heading}
</dt>

{if isset($block_view_section.expand)}
	<div class="{$block_view_section.expand}">
{/if}

{$block_view_section.content}

{if isset($block_view_section.expand)}
	</div>
	</div>
{/if}
