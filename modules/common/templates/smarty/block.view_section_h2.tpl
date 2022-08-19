{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.2 $ *}

{if isset($block_view_section.expand)}
	<div class="placeholder">
{/if}

<h2 {$block_view_section_h2.attrs} >
	{$block_view_section_h2.heading}
</h2>

{if isset($block_view_section_h2.expand)}
	<div class="{$block_view_section.expand}">
{/if}

{$block_view_section_h2.content}

{if isset($block_view_section_h2.expand)}
	</div>
	</div>
{/if}
