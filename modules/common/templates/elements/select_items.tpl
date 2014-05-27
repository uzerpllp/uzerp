{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.10 $ *}
{content_wrapper}
<div id="view_page" class="clearfix common-select_items">
	<dl id="view_data_left" class="selector">
		{advanced_search}
		<span>
			Select Items &gt; {link_to module="$module" controller="$controller" action="select_targets" value="Select $title"}
		</span>
		{if $selected_targets|count > 0}
			{assign var=action value="confirm_relationships"}
			{assign var=button_title value="Continue to confirmation"}
		{else}
			{assign var=action value="select_targets"}
			{assign var=button_title value="Continue to $title selection"}
		{/if}
		{form controller="$controller" action=$action}
			{paging}
			{view_section dont_prettify="true" heading="Items [<a href='#' class='select_all' rel='item_select'>Select All</a>]"}
				<br />
				<table class="datagrid" id="datagrid1" cellspacing="0" cellpadding="0">
					<thead>
						<tr>
							{foreach key=name item=tag from=$selected_item_headings}
								<th>{$tag}</th>
							{/foreach}
							<th>Assign</th>
						</tr>
					</thead>
					<tbody>
						{foreach name=datagrid item=model from=$selectorobjects}
							<tr>
								{assign var=linedetail value=''}
								{foreach key=name item=tag from=$selected_item_headings}
									<td>
										{$model->$name}
										<input type='hidden' value='{$model->$name}' id='{$name}_{$model->id}' />
										{assign var=linedetail value=$linedetail|cat:','|cat:$name|cat:'='|cat:$model->$name}
									</td>
								{/foreach}
								<td>
									{assign var=id value=$model->id}
									{if isset($selected_items.$id)}
										<input checked class="checkbox item_select" id="checkbox_{$id}" type="checkbox" name="TargetSelector[{$id}]" />
									{else}
										<input class="checkbox item_select" id="checkbox_{$id}" type="checkbox" name="TargetSelector[{$id}]" />
									{/if}
								</td>
								<input type="hidden" class="item_data" name="hidden_{$id}" value="{$id}=__REPLACE__{$linedetail}" />
							</tr>
						{foreachelse}
							<tr>
								<td colspan="{$selected_item_headings_count}">
									No matching records found!
								</td>
							</tr>
						{/foreach}
					</tbody>
				</table>
				{paging}
			{/view_section}
			{submit value=$button_title}
		{/form}
		{include file='elements/cancelForm.tpl' cancel_action='cancel_selector_save'}
	</dl>
	<dl id="view_data_right">
		{view_section heading="Selected $title"}
			<div id="view_data_bottom">
				<table class="datagrid" id="datagrid1" cellspacing="0" cellpadding="0">
					<thead>
						<tr>
							{foreach key=name item=tag from=$current_targets_headings}
								<th>{$tag}</th>
							{/foreach}
						</tr>
					</thead>
					<tbody>
						{foreach name=datagrid key=key item=model from=$selected_targets}
							<tr>
								<input type='hidden' name="selected_items[]" value='{$key}' />
								{foreach key=name item=tag from=$current_targets_headings}
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
		{view_section dont_prettify="true" heading="Selected Items [<a href='#' class='remove_all'>Remove All</a>]"}
			<input type="hidden" id="target_link" value="{$link}" />
			<input type="hidden" id="targets_text" />
			<div id="view_data_bottom">
				<div id="targets">
					{include file="elements/selected_items.tpl"}
				</div>
			</div>
		{/view_section}
	</dl>	
</div>
{/content_wrapper}