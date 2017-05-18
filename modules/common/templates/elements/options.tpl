{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.2 $ *}
{* displays the action-options for the current page*}
<div id="actionOptions">
	{foreach name=actionOptions key=name item=action from=$actionOptions}
		{capture name=image}
		<img src="/assets/graphics/actions/{$action.image}" alt="{$action.title}" title="{$action.title}"/>
		{/capture}
		{link_to module=$module controller=$controller action=$name value=$smarty.capture.image}
	{/foreach}
</div>
