{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.6 $ *}
{foreach item=items key=name from=$collection}
	<li class="{$items->type}">
		{if is_array($items)}
			{assign var=id value=$parent_id|cat:'-'|cat:$name}
			{$name}
			<ul class="{$class_name}">
				{include file=$permissions_tree collection=$items parent_id=$id class_name=$class_name}
			</ul>
		{else}
			{with model=$items}
				{assign var=identifier value=$items->location}
				{input type='hidden' number="$identifier" attribute="id"}
				{input type='hidden' number="$identifier" attribute="name" value=$items->name}
				{input type='hidden' number="$identifier" attribute="module_id" value=$items->module_id}
				{input type='hidden' number="$identifier" attribute="type" value=$items->type}
				{input type='hidden' number="$identifier" attribute="controller"}
				{input type='hidden' number="$identifier" attribute="location" value=$items->location}
				{input type='checkbox' number="$identifier" attribute="register" label=' ' tags='none'}
			{/with}
			{if $items->type=='C' || $items->type=='M'}
				{link_to module="$module" controller="modulecomponents" action='view' id=$items->id value=$name}
			{else}
				{$name}
			{/if}
		{/if}
	</li>
{/foreach}