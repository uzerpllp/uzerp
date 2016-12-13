{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.8 $ *}
<div id="view_page" class="clearfix  common-selector_list_target">
	<dl id="view_data_left">
		{advanced_search}
		<span>
			{link_to module="$module" controller="$controller" action="select_items" value="Select Items"} &gt; Select {$title}
		</span>
		{if $selected_items|count > 0}
			{assign var=action value="confirm_relationships"}
			{assign var=button_title value="Continue to confirmation"}
		{else}
			{assign var=action value="select_items"}
			{assign var=button_title value="Continue to items selection"}
		{/if}
		{form controller="$controller" action=$action}
			{view_section dont_prettify="true" heading="$title [<a href='#' class='select_all' rel='target_select'>Select All</a>]"}
				<br />
				<table class="datagrid" id="datagrid1" cellspacing="0" cellpadding="0">
					<thead>
						<tr>
							{foreach key=name item=tag from=$selected_target_headings}
								<th>{$tag}</th>
							{/foreach}
							<th>Assign</th>
						</tr>
					</thead>
					<tbody>
						{foreach name=datagrid item=model from=$collection}
							<tr>
								{assign var=linedetail value=''}
								{foreach key=name item=tag from=$selected_target_headings}
								<td>
									{$model->$name}
									<input type='hidden' value='{$model->$name}' id='{$name}_{$model->id}' />
									{assign var=linedetail value=$linedetail|cat:','|cat:$name|cat:'='|cat:$model->$name}
								</td>
								{/foreach}
								<td>
									{assign var=id value=$model->id}
									{if isset($selected_targets.$id)}
										<input checked class="checkbox target_select" id=checkbox_{$id} type="checkbox" name="DataObject[{$id}]" />
									{else}
										<input class="checkbox target_select" id=checkbox_{$id} type="checkbox" name="DataObject[{$id}]" />
									{/if}
								</td>
								<input type="hidden" class="item_data" name="hidden_{$id}" value="{$id}=__REPLACE__{$linedetail}" />
							</tr>
						{foreachelse}
							<tr><td colspan="{$selected_item_headings_count}">No matching records found!</td></tr>
						{/foreach}
					</tbody>
				</table>
				{paging}
			{/view_section}
		{submit value=$button_title}
		{/form}
		{include file='elements/cancelForm.tpl' cancel_action='cancel'}
	</dl>
	<dl id="view_data_right">
		{view_section heading="Selected Items"}
			<div id="view_data_bottom">
				<table class="datagrid" id="datagrid1" cellspacing="0" cellpadding="0">
					<thead>
						<tr>
							{foreach key=name item=tag from=$current_items_headings}
								<th>{$tag}</th>
							{/foreach}
						</tr>
					</thead>
					<tbody>
						{foreach name=datagrid key=key item=model from=$selected_items}
							<tr>
								<input type='hidden' name="selected_items[]" value='{$key}' />
								{foreach key=name item=tag from=$current_items_headings}
									<td>
										{$model.$name}
									</td>
								{/foreach}
							</tr>
						{foreachelse}
							<tr><td colspan="{$selected_item_headings_count}">No matching records found!</td></tr>
						{/foreach}
					</tbody>
				</table>
			</div>
		{/view_section}
		{view_section dont_prettify="true" heading="Selected $title [<a href='#' class='remove_all'>Remove All</a>]"}		
			<input type="hidden" id="target_link" value="{$link}" />
			<input type="hidden" id="targets_text">
			<div id="view_data_bottom">
				<div id="targets">
					{include file="elements/selected_targets.tpl"}
				</div>
			</div>
		{/view_section}
	</dl>	
</div>
