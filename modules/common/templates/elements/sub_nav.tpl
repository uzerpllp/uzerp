{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.10 $ *}
{* list for main navigation *} 
				<ul>
{php}
	$this->assign('test',array());
{/php}
{foreach name=list item=listitem from=$list}
{if $listitem->display}
	{if isset($self.pid)}
		{assign var=pid value=$self.pid}
		{assign var=permission value=$access->allPermissions->seek($self.pid)}
		{if $permission->type=='a'}
			{assign var=permission value=$access->allPermissions->seek($permission->parent_id)}
			{assign var=pid value=$permission->parent_id}
		{elseif $permission->type=='c'}
			{assign var=pid value=$permission->parent_id}
		{/if}
	{else}
		{assign var=pid value=$access->getPermission($self.modules.0)}
	{/if}
	{assign var=permission value=$access->allPermissions->seek($pid)}
	{assign var=parent_id value=$permission->parent_id}
	{if $listitem->id eq $self.id || $listitem->id eq $pid || $listitem->id eq $parent_id}
	    {foreach name=sublist item=sublistitem from=$listitem->sub_permissions}
	    	{if $sublistitem->display=='t' && $sublistitem->type <> 'a'
	        	&& (($sublistitem->type eq 'c' && $access->hasPermission($listitem->permission, $sublistitem->permission))
					|| ($sublistitem->type eq 'g' && $access->hasPermission($sublistitem->permission))
					|| ($sublistitem->type eq 'm' && $access->hasPermission($sublistitem->permission))
					|| ($sublistitem->type eq 's' && $access->hasPermission($sublistitem->permission)))}
				{php}
					$test = $this->get_template_vars('test');
					$test[] = $this->get_template_vars('sublistitem');
					$this->assign('test',$test);
				{/php}
			{/if}
        {/foreach}
	    {foreach name=test item=sublistitem from=$test}
	        {if $sublistitem->type eq 'c' && $access->hasPermission($listitem->permission, $sublistitem->permission)}
  				<li{if $smarty.foreach.test.last} class="last" id="subNavLast"{/if}>{link_to pid=$sublistitem->id module=$listitem->permission controller=$sublistitem->permission value=$sublistitem->title|prettify}</li>
			{elseif $sublistitem->type eq 's' && $access->hasPermission($sublistitem->permission)}
				<li{if $smarty.foreach.test.last} class="last" id="subNavLast"{/if}>{link_to pid=$sublistitem->id module=$sublistitem->permission value=$sublistitem->title|prettify}</li>
			{elseif $sublistitem->type eq 'm' && $access->hasPermission($sublistitem->permission)}
				<li{if $smarty.foreach.test.last} class="last" id="subNavLast"{/if}>{link_to pid=$sublistitem->id module=$sublistitem->permission value=$sublistitem->title|prettify}</li>
			{elseif $sublistitem->type eq 'g' && $access->hasPermission($sublistitem->permission)}
				<li{if $smarty.foreach.test.last} class="last" id="subNavLast"{/if}>{link_to pid=$sublistitem->id module=$sublistitem->permission value=$sublistitem->title|prettify}</li>
			{/if}
	    {/foreach}
   	{/if}
{/if}
{/foreach}
				</ul>
