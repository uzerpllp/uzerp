{**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **}
{* $Revision: 1.1 $ *}

{strip}

	{if $function_input.display_tags === TRUE}
		<dt>
	{/if}

	{if $function_input.display_label === TRUE}
		<label {$function_input.label.attrs}>{$function_input.label.value}:</label>
	{/if}

	{if $function_input.display_tags === TRUE}
		</dt>
		<dd>
	{/if}

	<input {$function_input.attrs} />
	{if isset($function_input.attrs_checkbox)}
		<input {$function_input.attrs_checkbox} />
	{/if}
	{if isset($function_input.field_error)}
		<span class="field-error">{$function_input.field_error}</span>
	{/if}

	{if $function_input.display_tags === TRUE}
		</dd>
	{/if}

{/strip}