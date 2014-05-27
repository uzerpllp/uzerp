{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.11 $ *}
{content_wrapper}
	<div id="eglet_setup_container">
		<div id="available_eglets_container" class="eglets_container">
			<h2>Available uzLETs</h2>
			{foreach name=available_eglets item=uzlets key=module from=$available}
				{if $module_count>1}
					{view_section heading=$module expand='closed'}
						<ul id="{$module}_available_eglets" class="available_eglets connectedSortable">
							{foreach name=available_eglets item=title key=name from=$uzlets}
								<li id="{$name}">{$title}</li>
							{/foreach}
						</ul>
					{/view_section}
				{else}
					<ul id="{$module}_available_eglets" class="available_eglets connectedSortable">
						{foreach name=available_eglets item=title key=name from=$uzlets}
							<li id="{$name}">{$title}</li>
						{/foreach}
					</ul>
				{/if}
			{/foreach}
			<ul id="{$module}_available_eglets" class="available_eglets connectedSortable">
			</ul>
		</div>
		<div id="selected_eglets_container" class="eglets_container">
			<h2>Currently Selected uzLETs</h2>
			<ul id="{$module}_selected_eglets" class="selected_eglets connectedSortable">
				{foreach name=selected_eglets item=item key=index from=$selected}
					<li id="{$item.name}">{$item.title}</li>
				{foreachelse}
					<li class="none">None Currently Selected</li>
				{/foreach}
			</ul>
		</div>
	</div>
	<div style="clear:both;" id="select_eglets_footer">
		<p>Drag items between the lists to define what will be shown on the {$module} Dashboard, then click Save</p>
		{form module=$module controller=index action=save notags=true}
			<fieldset id="the_fields">
			</fieldset>
			{input type='hidden' attribute='username' value=$username}
			<fieldset id="the_button">
				{submit another=false notags=true}
			</fieldset>
		{/form}
	</div>
{/content_wrapper}