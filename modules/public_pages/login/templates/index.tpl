{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.11 $ *}
<form enctype="multipart/form-data" id="save_form" name="login" action="/?action=login" method="post" >
    <input type="hidden" name="csrf_token" id="csrf_token_id" value="{$csrf_token}" />
	<label>Username</label>
	<label>Password</label>
	{if isset($smarty.cookies.username)}
		<input name="username" type="text" class="required" value="{$smarty.cookies.username}" />
	{else}
		<input name="username" type="text" class="required" />
	{/if}
	<input name="password" type="password" class="required" />
	<input type="submit" class="submit {$submit_class}" value="Log In &raquo;">
	{foreach name=request item=item key=key from=$smarty.get}
		<input type="hidden" name="{$key}" value="{$item}" />
	{/foreach}
	<input type="hidden" id="rememberUser" name="rememberUser" value="true" />
</form>