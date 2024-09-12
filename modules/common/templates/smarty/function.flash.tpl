{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}
<div id="flash">
	{if $function_flash.messages}
		
		<script type="text/javascript">
			var button = document.getElementById("messages_button");
			if (button) {
				button.style.display='block';
			}
		</script>
		
		<ul id="messages">
			
			{foreach from=$function_flash.messages item=message}
				<li>{$message}</li>
			{/foreach}
			
		</ul>
		
	{/if}
	
	{if $function_flash.warnings}
	
		<script type="text/javascript">
			var button = document.getElementById("warnings_button");
			if (button) {
				button.style.display='block';
			}
		</script>
		
		<ul id="warnings">
			
			{foreach from=$function_flash.warnings item=warning}
				<li>{$warning}</li>
			{/foreach}
			
		</ul>
		
	{/if}
	
	
	{if $function_flash.errors}
	
		<script type="text/javascript">
			var button = document.getElementById("errors_button");
			if (button) {
				button.style.display='block';
			}
		</script>
		
		{assign var="errors" value=array()  }
		{foreach from=$function_flash.errors key=fieldname item=error}
			{if empty($fieldname)}
			{append 'errors' $error}
			{/if}
		{/foreach}

		{if $errors}
		<ul id="errors">
			
			{foreach from=$errors item=error}
				<li class="error">{$error}</li>
			{/foreach}
			
		</ul>
		{/if}
		
	{/if}

</div>