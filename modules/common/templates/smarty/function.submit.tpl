{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}

{if !$dialog}

	{if $function_submit.display_tags}
		<dt class="submit">&nbsp;</dt><dd class="submit">
	{/if}

	<input class="formsubmit uz-validate" type="submit" value="{$function_submit.value}" name="{$function_submit.name}" id="{$function_submit.id}" />

	{$append}

	{if $function_submit.display_tags}
		</dd>
	{/if}
	
{/if}