{** 
 *	(c) 2022 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
<h2 class="card-title">Enable Two-Factor Authentication</h2>
<p>Protect your account in 3 easy steps:</p>
<div class="card">
	<div class="card-aside">
	</div>
	<div class="card-main">
		<p class="step">DOWNLOAD AN AUTHENTICATOR APP</p>
		<p>Your administrator will have provided guidance on the best app for your needs.<p>
	</div>
</div>

<div class="card">
	<div class="card-aside">
		<img alt="" src="{$qrcode}" />
	</div>
	<div class="card-main">
		<p class="step">SCAN THE QR CODE</p>
		<p>Open the authentication app and scan the image to the left or manually enter the key.</p>
		<p class="step">2FA KEY (MANUAL ENTRY)</p>
		<div class=input-group>
			<input title="Copy this token for manual setup" id="totp-token" value="{$secret}" readonly>
			<button class="btn" type="button" data-clipboard-target="#totp-token">
				Copy
			</button>
		</div>
	</div>
</div>

<div class="card">
	<div class="card-aside">
	</div>
	<div class="card-main">
		<p class="step">LOGIN WITH YOUR CODE</p>
		<p>Enter the 6-digit verification code generated.</p>
		<form enctype="multipart/form-data" id="save_form" name="login" action="/?action=mfaenroll" method="post">
			<input type="hidden" name="csrf_token" id="csrf_token_id" value="{$csrf_token}">
			<input title="Enter the code from your authenticator app" name="authcode" type="password" class="required" autocomplete="off" placeholder="000 000" autofocus>
			<input type="submit" class="submit {$submit_class}" value="Activate">
			{foreach name=request item=item key=key from=$smarty.get}
				<input type="hidden" name="{$key}" value="{$item}">
			{/foreach}
		</form>
	</div>
</div>