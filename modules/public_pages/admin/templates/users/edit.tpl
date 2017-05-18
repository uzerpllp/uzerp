{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="users" action="saveroles"}
		<dl id="view_data_left">
			<input type="hidden" name="username" value="{$username}">
			{view_section heading="access_details"}
			<dt>
				<label for="user_roles">Roles:</label>
			</dt>
			<dd class="for_multiple">
				<select name="roles[]" id="user_roles" multiple="multiple">
				    {html_options options=$roles selected=$current}
				</select>
			</dt>
			<dt>
				{submit}
			</dt>
			
			{/view_section}
		</dl>
	{/form}
{/content_wrapper}