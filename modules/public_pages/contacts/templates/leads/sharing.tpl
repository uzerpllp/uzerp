{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="companys" action="sharingsave"}
		<input type="hidden" name="id" value="{$id}"/>
		<tr>
		<td class="formlabel"><label for="write[]">Groups able to edit account</label>:</td>
		<td>
		<select multiple="multiple" name="write[]">
			{foreach from=$writeRoles item=item key=key}
			<option label="{$item.name}" value="{$key}" {if $item.selected}selected="selected"{/if}>{$item.name}</option>
			{/foreach}
		</select>
		</td></tr>
		<td class="formlabel"><label for="read[]">Groups able to view account</label>:</td>
		<td>
		<select multiple="multiple" name="read[]">
			{foreach from=$readRoles item=item key=key}
			<option label="{$item.name}" value="{$key}" {if $item.selected}selected="selected"{/if}>{$item.name}</option>
			{/foreach}
		</select>
		</td>
		</tr>
		{submit another="false"}
	{/form}
{/content_wrapper}