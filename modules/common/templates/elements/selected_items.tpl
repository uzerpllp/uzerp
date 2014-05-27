{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}
{with model=$model}
	<table class="datagrid" id="datagrid2" cellspacing="0" cellpadding="0">
		<thead>
			<tr>
				{foreach key=name item=tag from=$selected_item_headings}
				<th>{$tag}</th>
				{/foreach}
				<th width=10px>Remove</th>
			</tr>
		</thead>
		<tbody style="height: auto; width:auto;">
			{foreach key=key item=item from=$selected_items}
				<tr>
					<input type="hidden" name="selected_items[]" value="{$key}" />
					{foreach key=name item=tag from=$selected_item_headings}
					<td>
						{$item.$name}
					</td>
					{/foreach}
					<td width=10px align='center'>
						<button class="item_remove" rel="{$key}" style="padding: 0px 2px 0px;"><img alt="remove" src='{$smarty.const.THEME_URL}{$theme}/graphics/cancel.png'" height=10px/></button>
					</td>
				</tr>
			{foreachelse}
				<tr>
					<td colspan="{$selected_item_headings_count}">None Selected</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
{/with}