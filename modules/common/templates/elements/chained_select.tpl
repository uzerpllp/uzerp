{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.2 $ *}
{*
a chained select widget:
-$name
-$options
-$selected
	
*}
<tr>
	<td class="formlabel">
		<label for="{$id}">{$label}</label>
	</td>
	<td>
		<table>
			<tr>
				<td id="chain_container_{$id}">
					<select id="{$id}" name="{$name}" class="chained_select">
					<option value="">Choose one</option>
					{assign var="selected" value=$other_values.0}
					{html_options options=$model->getTopLevel($attribute) selected=$selected}
					</select>
					
				</td>
			</tr>
			
			{foreach name=additional item=item from=$other_values}
			
			<tr>
				<td>
			{if $smarty.foreach.additional.last eq true}
			{assign var="selected" value=$model->$id}
			{else}
			{assign var="next" value=$smarty.foreach.additional.iteration}
			{assign var="selected" value=$other_values.$next}
			{/if}
					<select name="{$name}">
					<option value="">Choose one</option>
					{html_options options=$model->getSiblings($item,$attribute) selected=$selected}
					</select>
				</td>
			</tr>
			{/foreach}
		</table>
	</td>
</tr>