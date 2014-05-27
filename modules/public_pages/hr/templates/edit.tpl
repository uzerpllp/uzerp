{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}
{content_wrapper}
	<div id="available_eglets_container" class="eglets_container rounded">
		<h2>Available EGlets</h2>
		<ul id="crm_available_eglets">
			{foreach name=available_eglets item=title key=name from=$available}
				<li id="{$name}">{$title}</li>
			{/foreach}
		</ul>
	</div>
	<div id="selected_eglets_container" class="eglets_container rounded">
		<h2>Currently Selected uzLets</h2>
		<ul id="crm_selected_eglets">
			{foreach name=selected_eglets item=item key=index from=$selected}
				<li id="{$item.name}">{$item.title}</li>
			{foreachelse}
				<li class="none">None Currently Selected</li>
			{/foreach}
		</ul>
	</div>
	<div style="clear:both;" id="select_eglets_footer">
		<p>Drag items between the lists to define what will be shown on the {$module} Dashboard, then click Save</p>
		{form module=$module controller=index action=save notags=true}
			<fieldset id="the_fields">
			</fieldset>
			<fieldset id="the_button">
				{submit another=false notags=true}
			</fieldset>
		{/form}
	</div>
{/content_wrapper}