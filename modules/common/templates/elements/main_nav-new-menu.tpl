{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.17 $ *}
{strip}
	{if $lvl != 0}
	<ul {if !empty($class)}class="{$class}"{/if}>
	{assign var=lvl value=2}
	{else}
	{assign var=lvl value=1}
	<ul class="clicky-menu no-js">
		<li><a href="{$user_home}">Home</a></li>
	{/if}
	
		{assign var=menu value=$list}
		
		{foreach name=list item=menuitem from=$menu}
			
			<li {if !empty($class)}class="{$class}"{/if}>
				{if $lvl==1}
					<a class="perm-type-{$menuitem.type}" href="#">
						{$menuitem.title|escape|prettify}
							<svg aria-hidden="true" width="16" height="16">
								<use xlink:href="#arrow" />
							</svg>
					</a>
				{elseif ($menuitem.type == 'g' && $lvl!==1) || ($menuitem.type == 'm' && $menuitem.has_uzlets === false)}
					<span class='nav-group-title perm-type-{$menuitem.type}'>{$menuitem.title|escape|prettify}</span>
				{else}
					{$cls="perm-type-{$menuitem.type}"}
					{link_to _class=$cls pid=$menuitem.id data=$menuitem.link value=$menuitem.title|escape|prettify}
				{/if}
				{assign var=id value=$menuitem.id}
				{if isset($accessTree.$id)}
					{include file="elements/main_nav-new-menu.tpl" list=$accessTree.$id class="" lvl=$lvl}
				{/if}
			</li>
			
		{/foreach}
		
	</ul>
	
{/strip}
