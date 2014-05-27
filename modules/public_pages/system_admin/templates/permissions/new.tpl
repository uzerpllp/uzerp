{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.9 $ *}

{if $action == 'new'}
	<ul class="tabs">
		<li class="current">Standard</li>
		<li>Group</li>
		<li>Custom</li>
	</ul>
{/if}

<div class="permission_container">
	
	{if $action == 'new' or $tab == 'standard'}
		{include file="./new_parts/standard.tpl"}
	{/if}
	
	{if $action == 'new' or $tab == 'group'}
		{include file="./new_parts/group.tpl"}
	{/if}
	
	{if $action == 'new' or $tab == 'custom'}
		{include file="./new_parts/custom.tpl"}
	{/if}
	
</div>

<script>

	$(function() {
		
		$(document).ready(function() {
			$('.permission_container').find('> div').eq(0).show();
		});
		
	});

</script>