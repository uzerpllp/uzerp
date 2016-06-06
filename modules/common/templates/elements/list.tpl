{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.8 $ *}
<ul>
	{foreach name=list item=item key=key from=$sidebar_list_data}
		{if $item eq 'spacer'}
			<li class="sidebar_spacer">&nbsp;</li>
		{else}
			{strip}
				{if $item.tag|prettify neq 'EGS_HIDDEN_FIELD' && !empty($item.link)}
					<li class="{$item.tag|lower|replace:' ':'_'}_related">
					   {*{$item|@var_dump}*}
						{if isset($item.link.newtab)}
							{assign var=class value='newtab'}
						{else}
							{assign var=class value=''}
						{/if}
						{if !empty($item.link)}
							{link_to _id="{$item.id}" _class="{$class} {$item.class}" data=$item.link data_attrs=$item.data_attr value=$item.tag|prettify}
						{else}
							{$item.tag|prettify}
						{/if}
						{if isset($item.new)}
							{if isset($item.new.newtab)}
								{assign var=class value='newtab'}
							{else}
								{assign var=class value=''}
							{/if}
							{link_to _class="$class new_link" data=$item.new img="/themes/default/graphics/new_small.png" alt="new"}
						{/if}
					</li>
				{/if}
			{/strip}
		{/if}
	{/foreach}
</ul>