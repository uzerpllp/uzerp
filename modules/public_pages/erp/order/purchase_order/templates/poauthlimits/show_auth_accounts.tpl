{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.4 $ *}	
<div id="purchase_order-poauthlimits-show_auth_accounts">
	<ul id="selected_accounts" class="uz-connected-lists">
		{foreach key=key item=account from=$selected_accounts}
			<li data-id="{$key}">{$account}</li>
		{/foreach}
	</ul>
</div>