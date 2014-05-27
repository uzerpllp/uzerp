{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}
{content_wrapper}
<div id="view_page" class="clearfix">
<p>{link_to module="$module" controller="$controller" action="select_items" value="Select Items"} &gt; {link_to module="$module" controller="$controller" action="select_targets" value="Select $target_title"} &gt; Confirm Relationships</p>
	{form controller="$controller" action="save_relationships"}
		<dl id="view_data_left">
			{view_section heading="Items"}
				<br />
				<table class="datagrid" id="datagrid1" cellspacing="0" cellpadding="0">
					<thead>
						<tr>
							{foreach key=name item=tag from=$selected_item_headings}
							<th>{$tag}</th>
							{/foreach}
						</tr>
					</thead>
					<tbody>
						{foreach name=datagrid item=model from=$selected_items}
							<tr>	
								<input type="hidden" name="selected_items[]" value="{$model->id}"  />
								{foreach key=name item=tag from=$selected_item_headings}
								<td>
									{$model.$name}
								</td>
								{/foreach}
							</tr>
						{/foreach}
					</tbody>
				</table>
			{/view_section}
		</dl>
		<dl id="view_data_right">
			{view_section heading="$target_title"}
				<br />
				<table class="datagrid" id="datagrid2" cellspacing="0" cellpadding="0">
					<thead>
						{foreach key=name item=tag from=$selected_target_headings}
						<th>{$tag}</th>
						{/foreach}
					</thead>
					<tbody>
						{foreach name=datagrid item=model from=$selected_targets}
							<tr>	
								<input type="hidden" name="selected_targets[]" value="{$model->id}"  />
								{foreach key=name item=tag from=$selected_target_headings}
								<td>
									{$model.$name}
								</td>
								{/foreach}
							</tr>
						{/foreach}
					</tbody>
				</table>
			{/view_section}
		</dl>	
		<div id="view_data_bottom">
			{if $deleted!='none'}
				{view_section heading="Links to be deleted"}
					{assign var=fields value=$deleted->getHeadings()}
					<table class="datagrid" id="datagrid1" cellspacing="0" cellpadding="0">
						<thead>
							<tr>
								{foreach key=name item=tag from=$fields}
									<th>{$tag}</th>
								{/foreach}
							</tr>
						</thead>
						<tbody>
							{foreach name=datagrid item=model from=$deleted}
								<tr>	
									<input type="hidden" name="delected_links[]" value="{$model->id}"  />
									{foreach key=name item=tag from=$fields}
										<td>
											{$model->$name}
										</td>
									{/foreach}
								</tr>
							{/foreach}
						</tbody>
					</table>
				{/view_section}
			{/if}
			{submit value='Save link'}
		</div>
	{/form}
	<div id="view_data_bottom">
		{include file='elements/cancelForm.tpl' cancel_action='cancel_selector_save'}
	</div>
</div>
{/content_wrapper}