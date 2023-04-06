{** 
 *	(c) 2022 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
<p class="step">ENTER YOUR AUTHENTICATION CODE</p>
<p>Enter the 6-digit verification code from your app.</p>
<br>
<form enctype="multipart/form-data" id="save_form" name="login" action="/?action=mfavalidate" method="post" >
    <input type="hidden" name="csrf_token" id="csrf_token_id" value="{$csrf_token}">
	<input title="Enter the code from your authenticator app" name="authcode" type="password" class="required" autocomplete="off" autofocus>
	<input type="submit" class="submit" value="Validate">
	<button id="cancel" class="submit secondary-action">Cancel</button>
	{foreach name=request item=item key=key from=$controller_data}
		<input type="hidden" name="{$key|escape}" value="{$item|escape}">
	{/foreach}
</form>