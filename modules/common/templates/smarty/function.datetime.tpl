{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}

{if $function_datetime.display_tags}
	<dt>
{/if}

<label for="{$function_datetime.label.for}">{$function_datetime.label.value}:</label>

{if $function_datetime.display_tags}
	</dt>
	<dd>
{/if}

<input {$function_datetime.date.attrs} class="icon date slim {$function_datetime.date.additional_class}" />&nbsp;
<input {$function_datetime.hour.attrs} class="timefield" />:<input {$function_datetime.minute.attrs} class="timefield" />

{if $function_datetime.display_tags}
	</dd>
{/if}