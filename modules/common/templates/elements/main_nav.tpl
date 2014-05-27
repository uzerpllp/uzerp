{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.17 $ *}
{strip}

	<ul class="{$class}">
	
		{assign var=menu value=$list}
		
		{foreach name=list item=menuitem from=$menu}
		
			<li class="{$class}">
			
				{link_to pid=$menuitem.id  data=$menuitem.link value=$menuitem.title|escape|prettify}
				{assign var=id value=$menuitem.id}
				
				{if isset($accessTree.$id)}
					{include file="elements/main_nav.tpl" list=$accessTree.$id class="" }
				{/if}
				
			</li>
			
		{/foreach}
		
	</ul>

{/strip}