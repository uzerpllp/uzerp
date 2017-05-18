{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.7 $ *}
{content_wrapper}
	{form controller="users" action="save"}
		{with model=$models.User legend="User Details"}
			{view_section heading="user_details"}
				{input type='text'  attribute='username' class="compulsory" }
				{select attribute="person_id" options=$people}
				{input type='password' attribute='password'}
				{input type='text' attribute='email'}
				{input type='hidden' attribute='lastcompanylogin'}
			{/view_section}
			<dl id="view_data_left">
				{view_section heading="access details"}
					{input type='checkbox' attribute='access_enabled'}
				{/view_section}
				{view_section heading=""}
					<dt>
						<label for="user_roles">Roles for {$smarty.const.SYSTEM_COMPANY}</label>
					</dt>
					<dd class="for_multiple">
						<select name="User[roles][]" id="user_roles" multiple="multiple">
							{html_options options=$roles selected=$current}
						</select>
					</dd>
				{/view_section}
				{view_section heading=""}
					<dt>
						<label for="user_companies">System Companies</label>
					</dt>
					<dd class="for_multiple">
						<select name="User[companies][]" id="user_companies" multiple="multiple">
							{html_options options=$companies selected=$selected_companies}
						</select>
					</dd>
				{/view_section}
			</dl>
			<dl id="view_data_right">
				{view_section heading="audit details"}
					{input type='checkbox' attribute='audit_enabled'}
				{/view_section}
				{view_section heading="debug details"}
					{input type='checkbox' attribute='debug_enabled'}
				{/view_section}
				{view_section heading=""}
					<input type="hidden" name="DebugOption[id]" id="debug_id" value={$debug_id}>
					<dt>
						<label for="debug_options">Debug Options</label>
					</dt>
					<dd class="for_multiple">
						<select name="User[debug_options][]" id="debug_options" multiple="multiple">
							{html_options options=$debug_options selected=$selected_options}
						</select>
					</dd>
				{/view_section}
			</dl>
			{view_section heading=""}
				{submit another=false}
			{/view_section}
		{/with}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}