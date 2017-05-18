{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}
{with model=$model}
	<table class="datagrid" id="datagrid2" cellspacing="0" cellpadding="0">
		<thead>
			<tr>
				{foreach key=name item=tag from=$selected_target_headings}
				<th>{$tag}</th>
				{/foreach}
				<th width=10px></th>
			</tr>
		</thead>
		<tbody style="height: auto; width:auto;">
			{foreach key=key item=target from=$selected_targets}
				<tr>
					<input type="hidden" name="selected_targets[]" value="{$key}" />
					{foreach key=name item=tag from=$selected_target_headings}
					<td>
						{$target.$name}
					</td>
					{/foreach}
					<td width=10px align='center'>
						<button class="remove_target" rel="{$key}" style="padding: 0px 2px 0px;"><img alt="remove" src='/assets/graphics/cancel.png'" height=10px/></button>
					</td>
				</tr>
			{foreachelse}
				<tr>
					<td colspan="{$selected_target_headings_count}">None Selected</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
{/with}
