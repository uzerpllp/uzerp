{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.8 $ *}
{foreach item=model from=$collection}
	{if !$view || in_array($model.id, $current)}
		<li class="{$model.type}">
			{if $model.children|count>0}
				{assign var=id value=$parent_id|cat:'-'|cat:$model.id}
				{if $view}
					{if in_array($model.id, $current)}
						{$model.title}
						<ul class="{$class_name}">
							{include file=$permissions_tree collection=$model.children parent_id=$id class_name=$class_name roleid=$roleid}
						</ul>
					{/if}
				{else}
					{if in_array($model.id, $current)}
						<input class="checkbox" type="checkbox" name=permission[{$model.id}] value="{$model.id}" checked="checked">
					{else}
						<input class="checkbox" type="checkbox" name=permission[{$model.id}] value="{$model.id}">
					{/if}
					{$model.title}
					<ul class="{$class_name}">
						{include file=$permissions_tree collection=$model.children parent_id=$id class_name=$class_name roleid=$roleid}
					</ul>
				{/if}
			{else}
				{if $view}
					{if in_array($model.id, $current)}
						{$model.title}
					{/if}
				{else}
					{if in_array($model.id, $current)}
						<input class="checkbox" type="checkbox" name=permission[{$model.id}] value="{$model.id}" checked="checked">
					{else}
						<input class="checkbox" type="checkbox" name=permission[{$model.id}] value="{$model.id}">
					{/if}
					{$model.title}
				{/if}
			{/if}
		</li>
	{/if}
{/foreach}