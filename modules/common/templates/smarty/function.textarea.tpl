{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}

{if $function_textarea.display_tags === TRUE}

	<dt {$function_textarea.dt.attrs}>
		<label {$function_textarea.label.attrs}>{$function_textarea.label.value}:</label>
	</dt>
	<dd {$function_textarea.dd.attrs}>

{/if}

<textarea cols="30" rows="5" {$function_textarea.textarea.attrs} >{$function_textarea.textarea.value}</textarea>

{if $function_textarea.display_tags === TRUE}
	</dd>
{/if}