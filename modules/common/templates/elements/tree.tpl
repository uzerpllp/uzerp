{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
{foreach item=model from=$collection}
	<li>
		{if $model->$children->count()>0}
			{assign var=id value=$parent_id|cat:'-'|cat:$model->id}
			{showhidediv id=$id name=$model->$name class_name=$class_name hide=true}
				{$model->getFormatted('type')} &nbsp; {$model->permission} &nbsp; Title: {$model->title} &nbsp; Description: {$model->description}
				{showhidediv_body id=$id hide=true}
					{include file='elements/tree.tpl' collection=$model->$children children=$children parent_id=$id class_name=$class_name}
				{/showhidediv_body}
			{/showhidediv}
		{else}
			<img src={$smarty.const.THEME_URL}{$theme}/graphics/menu_noexpand.png>
			{$model->getFormatted('type')} &nbsp; {$model->permission} &nbsp; Title: {$model->title} &nbsp; Description: {$model->description}
		{/if}
	</li>
{/foreach}