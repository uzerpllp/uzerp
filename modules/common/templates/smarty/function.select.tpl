{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.7 $ *}

{if $function_select.display_tags === TRUE}
	<dt {$function_select.dt.attrs}>
		<label for="{$function_select.select.id}">{$function_select.select.label}:</label>
	</dt>
	<dd {$function_select.dd.attrs}>
{/if}

{if $function_select.autocomplete}

	{if $function_select.data_inline}
		
		<input type="hidden" name="{$function_select.select.attrs.name}" id="{$function_select.select.attrs.id}" value="{$function_select.select.selected}" />
			
		<input alt="Autocomplete enabled" type="text" id="{$function_select.select.attrs.id}_text" value="{$function_select.select.value}" class="{$function_select.select.attrs.class}" data-id="{$function_select.select.attrs.id}" data-action="array"  />
			
		<script type="text/javascript">
			var {$function_select.select.attrs.id} = {$function_select.select.options}
		</script>

	{else}

		<input type="hidden" {$function_select.select.attrs} />
		
		<input alt="Autocomplete enabled" type="text" {$function_select.select.attrs_text} />

	{/if}
	
{else}

	<select {$function_select.select.attrs} >

		{foreach from=$function_select.select.options item=item}
			<option {$item.attrs} >{$item.value}</option>
		{/foreach}

	</select>
	
{/if}

{if isset($function_select.select.fk_link)}
	{link_to _parentid=$function_select.select.id _class="dialog new_link" data=$function_select.select.fk_link img="/themes/default/graphics/new_small.png" alt="new"}
{/if}

{if $function_select.display_tags === TRUE}
	</dd>
{/if}