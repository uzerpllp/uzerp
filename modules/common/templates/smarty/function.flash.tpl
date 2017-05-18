{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}

<div id="flash">
	{if $function_flash.messages}
		
		<script type="text/javascript">
			var button = document.getElementById("messages_button").style.display='block';
		</script>
		
		<ul id="messages">
			
			{foreach from=$function_flash.messages item=message}
				<li>{$message}</li>
			{/foreach}
			
		</ul>
		
	{/if}
	
	{if $function_flash.warnings}
	
		<script type="text/javascript">
			var button = document.getElementById("warnings_button").style.display='block';
		</script>
		
		<ul id="warnings">
			
			{foreach from=$function_flash.warnings item=warning}
				<li>{$warning}</li>
			{/foreach}
			
		</ul>
		
	{/if}
	
	
	{if $function_flash.errors}
	
		<script type="text/javascript">
			var button = document.getElementById("errors_button").style.display='block';
		</script>
		
		<ul id="errors">
			
			{foreach from=$function_flash.errors key=fieldname item=error}
				<li class="error_{$fieldname}">{$error}</li>
			{/foreach}
			
		</ul>
		
	{/if}

</div>