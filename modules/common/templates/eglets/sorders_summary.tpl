{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.6 $ *}
<div id="{$content.source}_summary">
	<table class='datagrid'>
		<tr>
			<th align=left>
				{if $content.type=='customer'}
					Customer
				{else}
					Item
				{/if}
			</th>
			<th align=right>
				{if $content.type=='item by qty'}
					Quantity
				{else}
					Value
				{/if}
			</th>
		</tr>
		{foreach item=value key=id from=$content.details}
			<tr>
				<td>
					{$id}
				</td>
				</td>
				<td align=right>
				{if $content.type=='item by qty'}
					{$value}
				{else}
					{$value|string_format:"%.2f"}
				{/if}
				</td>
			</tr>
		{/foreach}
	</table>
	<select id="{$content.source}_type" name="type">
		{html_options options=$content.types selected=$content.type}
	</select>
</div>
