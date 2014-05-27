{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$Permission}
			<dl id="view_data_left">
				{view_data attribute="permission"}
				{view_data attribute="description"}
				{view_data attribute="title"}
				{view_data attribute="type"}
				{view_data attribute="display"}
				{view_data attribute="position"}
			</dl>
			<dl id="view_data_bottom">
				<dl id="view_data_left">
					{view_section heading="Users"}
						{foreach item=user from=$Permission->users}
							{view_data value=$user->users_username}
						{/foreach}
					{/view_section}
				</dl>
				<dl id="view_data_left">
					{view_section heading="Roles"}
						{foreach item=role from=$Permission->roles}
							{view_data value=$role->role_roleid}
						{/foreach}
					{/view_section}
				</dl>
			</dl>
			<dl id="view_data_bottom">
				{view_section heading="Companies"}
					{foreach item=company from=$Permission->companies}
						{view_data value=$company->company}
					{/foreach}
				{/view_section}
			</dl>
		{/with}
	</div>
{/content_wrapper}