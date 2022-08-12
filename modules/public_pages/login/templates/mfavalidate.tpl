{** 
 *	(c) 2022 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
<p>Enter a code from your app.</p>
<form enctype="multipart/form-data" id="save_form" name="login" action="/?action=mfavalidate" method="post" >
    <input type="hidden" name="csrf_token" id="csrf_token_id" value="{$csrf_token}">
	<label>Enter Code</label>
	<input name="authcode" type="password" class="required" autocomplete="off" autofocus>
	<input type="submit" class="submit {$submit_class}" value="Validate Code &raquo;">
	{foreach name=request item=item key=key from=$smarty.get}
		<input type="hidden" name="{$key}" value="{$item}">
	{/foreach}
</form>