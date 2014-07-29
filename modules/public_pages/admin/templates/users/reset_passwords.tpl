{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller='users' action="reset_passwords"}
		<dl id="view_data_left">
		<dt><label for="edit_roles">Select Users</label>:</dt>
		<select multiple="multiple" name="users[]">
			{foreach from=$users item=user}
				<option label="{$user}" value="{$user}">{$user}</option>
			{/foreach}
		</select>
		</dl>
		<dl id="view_data_right">
		<p>If you enter a password below, all users selected to the left will have their
		password replaced with that. If not, all users selected to the left will be randomly
		assigned, and emailed, a new password.</p>
		{input type="text" attribute="password" label="Password"}
		</dl>
		{*fix IE7*}
		<br /><br />
		{submit another="false"}
	{/form}
{/content_wrapper}
