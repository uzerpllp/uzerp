{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}

<div {$block_content_wrapper.attrs} >

	{if $block_content_wrapper.title ne ''}
		<h1 class="page_title">{$block_content_wrapper.title}</h1>
	{/if}
	
	{if $block_content_wrapper.flash === TRUE}
		{flash}
	{/if}
	
	{$block_content_wrapper.content}

</div>
