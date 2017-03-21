{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller='users' action="reset_passwords"}
		<dl id="view_data_left">
		<dt><label for="edit_roles">Select Users</label>:</dt>
		<select multiple="multiple" name="users[]" size=15>
			{foreach from=$users item=user}
				<option label="{$user}" value="{$user}">{$user}</option>
			{/foreach}
		</select>
		</dl>
		<dl id="view_data_right">
		<p class="help-text">If a password is entered below, the selected users will have their
		password replaced. If left blank, the selected users will be assigned a randomly
		generated password. Users will be notified via email.</p>
		{input id="User_password" type="password" attribute="password" label="New Password"}
		</dl>
		<dl id="view_data_right"><span id="char-count" class="help-text"></span></dl>
		{*fix IE7*}
		<br /><br />
		{submit another="false"}
	{/form}
{/content_wrapper}
<script>
