{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}

{foreach item=value from=$tree}

	<li id="permission_{$value.id}" class="placeholder" data-id="{$value.id}" data-type="{$value.type}">
		<div>
			<span class="expand title">
				{$value.title}
				<span>
					<a href="#" data-type="edit">Edit</a> | <a href="#" data-type="delete">Delete</a>
				</span>
			</span>
		</div>
		 
		{if isset($value.children)}
			<ol style="display: none">
				{include file="./tree.tpl" tree=$value.children}
			</ol>
		{/if}
	</li>

{/foreach}