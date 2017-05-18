{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.5 $ *}

{if $function_interval.hidden === TRUE}

	<input type="hidden" class="interval {$function_interval.class}" {$function_interval.attrs} />
	<input type="hidden" name="{$function_interval.select_name}" value="{$function_interval.unit_value}" />

{else}

	<dt>
		<label {$function_interval.label.attrs} >{$function_interval.label.value}:</label>
	</dt>
	<dd>
		<input type="text" class="interval {$function_interval.class}" {$function_interval.attrs} />
		<select class="small" name="{$function_interval.select_name}">
			<option value="minutes" {$function_interval.minutes_selected} >{$function_interval.minutes_label}</option>
			<option value="hours" {$function_interval.hours_selected} >{$function_interval.hours_label}</option>
			<option value="days" {$function_interval.days_selected} >{$function_interval.days_label}</option>
		</select>
	</dd>
	
{/if}