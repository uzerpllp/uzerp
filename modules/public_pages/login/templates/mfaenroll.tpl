{** 
 *	(c) 2022 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
<p>Your are required to enrol for 2-factor authentication.</p>
<p>Scan the QR code with your app or use the following token for manual set-up.</p>
<div class=input-group>
	<input id="totp-token" value="{$secret}">
	<button class="btn" type="button" data-clipboard-target="#totp-token">
		Copy
	</button>
</div>
<img alt="" src="{$qrcode}" />
<form enctype="multipart/form-data" id="save_form" name="login" action="/?action=mfaenroll" method="post">
    <input type="hidden" name="csrf_token" id="csrf_token_id" value="{$csrf_token}">
	<label>Enter Code</label>
	<input name="authcode" type="password" class="required" autocomplete="off" autofocus>
	<input type="submit" class="submit {$submit_class}" value="Validate Code &raquo;">
	{foreach name=request item=item key=key from=$smarty.get}
		<input type="hidden" name="{$key}" value="{$item}">
	{/foreach}
</form>