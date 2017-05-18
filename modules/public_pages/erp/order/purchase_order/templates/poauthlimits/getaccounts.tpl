{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.7 $ *}	
<ul id="available_accounts" class="uz-connected-lists">
	{foreach item=model from=$glaccounts}
		{assign var=id value=$model->glaccount_id}
		{if !isset($selected_accounts.$id)}
			<li data-id="{$model->glaccount_id}">{$model->getIdentifierValue()}</li>
		{/if}
	{/foreach}
</ul>